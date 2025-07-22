<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Unit;
use App\Models\AccHead;
use Livewire\Component;
use App\Models\OperHead;
use App\Models\JournalHead;
use App\Models\JournalDetail;
use App\Models\OperationItems;
use Illuminate\Support\Facades\Auth;

class ManufacturingInvoice extends Component
{
    protected $listeners = ['refresh' => '$refresh'];
    public function hydrate()
    {
        $this->calculateTotals();
    }
    public $currentStep = 1;
    public $pro_id;
    public $nextProId;
    public $invoiceDate;
    public $description = '';
    public $selectedProducts = [];
    public $productsList = [];
    public $selectedRawMaterials = [];
    public $rawMaterialsList = [];
    public $additionalExpenses = [];
    public $productSearchTerm = '';
    public $productSearchResults;
    public $productSelectedResultIndex = -1;
    public $rawMaterialSearchTerm = '';
    public $rawMaterialSearchResults;
    public $rawMaterialSelectedResultIndex = -1;
    public $totalRawMaterialsCost = 0;
    public $totalAdditionalExpenses = 0;
    public $totalManufacturingCost = 0;
    public $unitCostPerProduct = 0;
    public $OperatingAccount = '';
    public $rawAccount = '';
    public $productAccount = '';
    public $Stors = [];
    public $OperatingCenter;
    public $patchNumber;
    public $employeeList = [];

    protected $rules = [
        'invoiceDate' => 'required|date',
        'rawAccount' => 'required',
        'productAccount' => 'required',
        'OperatingAccount' => 'required',
        'employee' => 'required',

        'selectedProducts.*.product_id' => 'required',
        'selectedProducts.*.quantity' => 'required|numeric|min:0.01',
        'selectedProducts.*.unit_cost' => 'required|numeric|min:0',
        'selectedProducts.*.cost_percentage' => 'required|numeric|min:0|max:100',

        'selectedRawMaterials.*.item_id' => 'required',
        'selectedRawMaterials.*.quantity' => 'required|numeric|min:0.01',
        'selectedRawMaterials.*.unit_cost' => 'required|numeric|min:0',

        'additionalExpenses.*.description' => 'required|string|min:3',
        'additionalExpenses.*.amount' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'selectedProducts.*.cost_percentage.max' => 'النسبة يجب ألا تزيد عن 100%',
        'selectedProducts.*.cost_percentage.min' => 'النسبة يجب ألا تقل عن 0%',
        'required' => 'هذا الحقل مطلوب',
        'min' => 'القيمة أقل من المسموح',
        'numeric' => 'يجب إدخال قيمة رقمية',
        'employee.required' => 'يجب اختيار الموظف',

    ];

    public function mount()
    {
        $this->invoiceDate = now()->format('Y-m-d');

        $this->nextProId = OperHead::max('pro_id') + 1 ?? 1;
        $this->pro_id = $this->nextProId;

        $this->Stors = $this->getAccountsByCode('123%');
        $this->OperatingCenter = $this->getAccountsByCode('124%');
        $this->employeeList = $this->getAccountsByCode('213%');
        $this->loadProductsAndMaterials();
    }

    private function getAccountsByCode($code)
    {
        return AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', $code)
            ->select('id', 'aname')
            ->pluck('aname', 'id')
            ->toArray();
    }

    public function handleKeyDownProduct()
    {
        if ($this->productSearchResults && $this->productSearchResults->count() > 0) {
            $this->productSelectedResultIndex = min(
                $this->productSelectedResultIndex + 1,
                $this->productSearchResults->count() - 1
            );
        }
    }

    public function handleKeyUpProduct()
    {
        if ($this->productSearchResults && $this->productSearchResults->count() > 0) {
            $this->productSelectedResultIndex = max(
                $this->productSelectedResultIndex - 1,
                0
            );
        }
    }

    public function handleEnterProduct()
    {
        if (
            $this->productSearchResults &&
            $this->productSearchResults->count() > 0 &&
            $this->productSelectedResultIndex >= 0
        ) {
            $selectedItem = $this->productSearchResults->skip($this->productSelectedResultIndex)->first();
            if ($selectedItem) {
                $this->addProductFromSearch($selectedItem->id);
                $this->dispatch('focusProductQuantity', count($this->selectedProducts) - 1);
            }
        }
    }

    public function handleKeyDownRawMaterial()
    {
        if ($this->rawMaterialSearchResults && $this->rawMaterialSearchResults->count() > 0) {
            $this->rawMaterialSelectedResultIndex = min(
                $this->rawMaterialSelectedResultIndex + 1,
                $this->rawMaterialSearchResults->count() - 1
            );
        }
    }

    public function handleKeyUpRawMaterial()
    {
        if ($this->rawMaterialSearchResults && $this->rawMaterialSearchResults->count() > 0) {
            $this->rawMaterialSelectedResultIndex = max(
                $this->rawMaterialSelectedResultIndex - 1,
                0
            );
        }
    }

    public function handleEnterRawMaterial()
    {
        if (
            $this->rawMaterialSearchResults &&
            $this->rawMaterialSearchResults->count() > 0 &&
            $this->rawMaterialSelectedResultIndex >= 0
        ) {
            $selectedItem = $this->rawMaterialSearchResults->skip($this->rawMaterialSelectedResultIndex)->first();
            if ($selectedItem) {
                $this->addRawMaterialFromSearch($selectedItem->id);
                $this->dispatch('focusRawMaterialQuantity', count($this->selectedRawMaterials) - 1);
            }
        }
    }

    public function loadProductsAndMaterials()
    {
        $this->productsList = Item::with(['prices' => function ($q) {
            $q->withPivot('price');
        }])->get();
        $this->rawMaterialsList = Item::select('id', 'name')->get()->toArray();
    }

    public function updatedProductSearchTerm($value)
    {
        $this->productSelectedResultIndex = -1;
        $this->productSearchResults = strlen($value) < 1
            ? collect()
            : Item::with(['units', 'prices'])
            ->whereRaw("name LIKE ? OR name LIKE ? OR name LIKE ?", [
                "{$value}%",
                "%{$value}",
                "%{$value}%",
            ])
            ->take(5)->get();
    }

    public function updatedRawMaterialSearchTerm($value)
    {
        $unitId = $this->selectedRawMaterials[$value]['unit_id'] ?? null;
        if (! $unitId) {
            // مسح التكلفة لو تم إلغاء الاختيار
            $this->selectedRawMaterials[$value]['unit_cost'] = 0;
            $this->selectedRawMaterials[$value]['total_cost'] = 0;
            return $this->calculateTotals();
        }

        $unit = Unit::find($unitId);
        // لنفرض عندك حقل cost في الـ Unit model أو في pivot
        $cost = $unit->cost ?? ($unit->pivot->cost ?? 0);

        // حط التكلفة مكانها مع تقريب لمكان عشري واحد
        $this->selectedRawMaterials[$value]['unit_cost']  = round($cost, 1);
        // احسب التكلفة الإجمالية للعنصر (quantity × unit_cost)
        $quantity = $this->selectedRawMaterials[$value]['quantity'] ?? 0;
        $this->selectedRawMaterials[$value]['total_cost'] = round($quantity * $cost, 1);

        // وأخيراً أعِد حساب المجاميع الكلية
        $this->calculateTotals();
    }

    public function addProductFromSearch($itemId)
    {
        $item = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices'])->find($itemId);
        if (! $item) return;
        $price = $item->prices->first()->pivot->price ?? 0;
        $price = round($price, 1);

        $this->selectedProducts[] = [
            'id' => uniqid(),
            'product_id' => $item->id,
            'name' => $item->name,
            'quantity' => 1,
            'unit_cost' => $price,
            'total_cost' => $price,
            'cost_percentage' => 0
        ];

        $this->productSearchTerm = '';
        $this->productSearchResults = collect();
        $this->productSelectedResultIndex = -1;
        $this->calculateTotals();
    }

    public function addRawMaterialFromSearch($itemId)
    {
        $item = Item::with('units')->find($itemId);
        if (! $item) return;

        $unitsList = $item->units->map(function ($unit) {
            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'cost' => $unit->pivot->cost,
                'available_qty' => $unit->pivot->u_val
            ];
        })->toArray();

        $firstUnit = $unitsList[0] ?? null;
        $this->selectedRawMaterials[] = [
            'id' => uniqid(),
            'item_id' => $item->id,
            'name' => $item->name,
            'quantity' => 1,
            'unit_cost' => $firstUnit['cost'] ?? 0,
            'available_quantity' => $firstUnit['available_qty'] ?? 0,
            'total_cost' => $firstUnit['cost'] ?? 0,
            'unitsList' => $unitsList
        ];

        $this->rawMaterialSearchTerm = '';
        $this->rawMaterialSearchResults = collect();
        $this->rawMaterialSelectedResultIndex = -1;
        $this->calculateTotals();
    }

    public function removeProduct($index)
    {
        unset($this->selectedProducts[$index]);
        $this->selectedProducts = array_values($this->selectedProducts);
        $this->calculateTotals();
    }

    public function removeRawMaterial($index)
    {
        unset($this->selectedRawMaterials[$index]);
        $this->selectedRawMaterials = array_values($this->selectedRawMaterials);
        $this->calculateTotals();
    }

    public function updated($propertyName)
    {
        if (
            str_contains($propertyName, 'quantity') ||
            str_contains($propertyName, 'unit_cost') ||
            str_contains($propertyName, 'amount') ||
            str_contains($propertyName, 'cost_percentage')
        ) {
            $this->convertToNumber($propertyName);
        }
        $this->calculateTotals();

        if (str_contains($propertyName, 'selectedProducts')) {
            $this->updateProductTotal($propertyName);
        }

        if (str_contains($propertyName, 'selectedRawMaterials')) {
            $this->updateRawMaterialTotal($propertyName);
        }
    }

    private function convertToNumber($propertyName)
    {
        $parts = explode('.', $propertyName);
        if (count($parts) < 3) return;

        $index = $parts[1];
        $field = $parts[2];
        $type = $parts[0];

        if ($type === 'selectedProducts' && isset($this->selectedProducts[$index][$field])) {
            $this->selectedProducts[$index][$field] = (float)$this->selectedProducts[$index][$field];
        }

        if ($type === 'selectedRawMaterials' && isset($this->selectedRawMaterials[$index][$field])) {
            $this->selectedRawMaterials[$index][$field] = (float)$this->selectedRawMaterials[$index][$field];
        }

        if ($type === 'additionalExpenses' && isset($this->additionalExpenses[$index][$field])) {
            $this->additionalExpenses[$index][$field] = (float)$this->additionalExpenses[$index][$field];
        }
    }

    public function updateProductTotal($propertyName)
    {
        $parts = explode('.', $propertyName);
        if (count($parts) < 2) return;

        $index = $parts[1];
        $field = $parts[2];

        if (in_array($field, ['quantity', 'unit_cost']) && isset($this->selectedProducts[$index])) {
            $this->selectedProducts[$index]['total_cost'] =
                $this->selectedProducts[$index]['quantity'] * $this->selectedProducts[$index]['unit_cost'];
        }
    }

    public function updateRawMaterialTotal($propertyName)
    {
        $parts = explode('.', $propertyName);
        if (count($parts) < 2) return;

        $index = $parts[1];
        $field = $parts[2];

        if (in_array($field, ['quantity', 'unit_cost']) && isset($this->selectedRawMaterials[$index])) {
            $this->selectedRawMaterials[$index]['total_cost'] =
                $this->selectedRawMaterials[$index]['quantity'] * $this->selectedRawMaterials[$index]['unit_cost'];
        }
    }

    public function updatedSelectedRawMaterials($value, $key)
    {
        $parts = explode('.', $key);
        $index = $parts[0];
        $field = $parts[1] ?? null;

        if (!$field) return;

        if ($field === 'unit_id') {
            $unitId = $value;
            $unit = collect($this->selectedRawMaterials[$index]['unitsList'])
                ->firstWhere('id', $unitId);
            if ($unit) {
                $this->selectedRawMaterials[$index]['unit_cost'] = $unit['cost'];
                $this->selectedRawMaterials[$index]['available_quantity'] = $unit['available_qty'];
                $this->selectedRawMaterials[$index]['total_cost'] =
                    $this->selectedRawMaterials[$index]['quantity'] * $unit['cost'];
            }
        }
        if (in_array($field, ['quantity', 'unit_cost'])) {
            $this->selectedRawMaterials[$index]['total_cost'] =
                $this->selectedRawMaterials[$index]['quantity'] * $this->selectedRawMaterials[$index]['unit_cost'];
        }

        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->totalRawMaterialsCost = collect($this->selectedRawMaterials)
            ->sum(fn($item) => is_numeric($item['total_cost']) ? (float)$item['total_cost'] : 0);

        $this->totalAdditionalExpenses = collect($this->additionalExpenses)
            ->sum(fn($item) => is_numeric($item['amount']) ? (float)$item['amount'] : 0);

        $this->totalManufacturingCost = $this->totalRawMaterialsCost + $this->totalAdditionalExpenses;

        $totalProductQuantity = collect($this->selectedProducts)
            ->sum(fn($item) => is_numeric($item['quantity']) ? (float)$item['quantity'] : 0);

        $this->unitCostPerProduct = $totalProductQuantity > 0 ?
            $this->totalManufacturingCost / $totalProductQuantity : 0;
    }
    public function addExpense()
    {
        $this->additionalExpenses[] = [
            'description' => '',
            'amount' => 0
        ];
    }

    public function removeExpense($index)
    {
        unset($this->additionalExpenses[$index]);
        $this->additionalExpenses = array_values($this->additionalExpenses);
        $this->calculateTotals();
    }

    public function distributeCosts()
    {
        $this->calculateTotals();
        $productsWithPositivePercentage = collect($this->selectedProducts)
            ->filter(fn($p) => (float)$p['cost_percentage'] > 0);
        if ($productsWithPositivePercentage->isEmpty()) {
            foreach ($this->selectedProducts as $index => $product) {
                $this->selectedProducts[$index]['total_cost'] = 0;
                $this->selectedProducts[$index]['unit_cost'] = 0;
            }
            return;
        }
        $totalPercentage = $productsWithPositivePercentage->sum(fn($p) => (float)$p['cost_percentage']);
        $adjustmentFactor = 100 / $totalPercentage;
        foreach ($this->selectedProducts as $index => $product) {
            $percentage = (float)$product['cost_percentage'];

            if ($percentage > 0) {
                $adjustedPercentage = $percentage * $adjustmentFactor;
                $productCost = $this->totalManufacturingCost * ($adjustedPercentage / 100);

                $this->selectedProducts[$index]['total_cost'] = $productCost;

                $quantity = (float)$product['quantity'];
                if ($quantity > 0) {
                    $this->selectedProducts[$index]['unit_cost'] = $productCost / $quantity;
                } else {
                    $this->selectedProducts[$index]['unit_cost'] = 0;
                }
            } else {
                $this->selectedProducts[$index]['total_cost'] = 0;
                $this->selectedProducts[$index]['unit_cost'] = 0;
            }
        }
    }

    public function saveInvoice()
    {
        dd($this->all());

        // التحقق من توفر المواد الخام
        // foreach ($this->selectedRawMaterials as $index => $material) {
        //     $available = (float)$material['available_quantity'];
        //     $required = (float)$material['quantity'];

        //     if ($required > $available) {
        //         $this->addError(
        //             "selectedRawMaterials.$index.quantity",
        //             "الكمية المطلوبة ($required) تتجاوز الكمية المتاحة ($available)"
        //         );
        //         return;
        //     }
        // }
        // if (count($this->selectedProducts) === 0) {
        //     session()->flash('error', 'يجب إضافة منتج واحد على الأقل');
        //     return;
        // }

        if ($totalPercentage !== 100.0) {
            $this->addError('cost_percentage', 'مجموع نسب التكلفة يجب أن يساوي 100%');
            return;
        }
        // التحقق من وجود مواد خام
        if (count($this->selectedRawMaterials) === 0) {
            session()->flash('error', 'يجب إضافة مادة خام واحدة على الأقل');
            return;
        }
        $operation = OperHead::create([
            'pro_id' => $this->pro_id,
            'is_stock' => 1,
            'is_finance' => 0,
            'is_manager' => 0,
            'is_journal' => 1,
            'info' => $this->description,
            'pro_date' => $this->invoiceDate,
            'pro_num' => $this->pro_id,
            'pro_serial' => $this->patchNumber,
            'store_id' =>  $this->productAccount,
            'emp_id' => $this->employee,
            'acc1' => $this->rawAccount,
            'acc2' => $this->productAccount,
            'pro_value' => $this->totalManufacturingCost,
            'user' => Auth::id(),
            'pro_type' => 59,
        ]);

        $journalId = JournalHead::max('journal_id') + 1;
        // قيد الخامات
        JournalHead::create([
            'journal_id' => $journalId,
            'total' => $this->totalRawMaterialsCost,
            'date' => $this->invoiceDate,
            'op_id' => $operation->id,
            'pro_type' => 59,
            'details' => $this->description,
            'user' => Auth::id(),
        ]);

        JournalDetail::create([
            'journal_id' => $journalId,
            'account_id' => $this->OperatingAccount,
            'debit' => $this->totalRawMaterialsCost,
            'credit' => 0,
            'type' => 59,
            'info' =>  $this->description,
            'op_id' => $operation->id,
        ]);

        JournalDetail::create([
            'journal_id' => $journalId,
            'account_id' => $this->rawAccount,
            'debit' => 0,
            'credit' => $this->totalRawMaterialsCost,
            'type' => 59,
            'info' =>  $this->description,
            'op_id' => $operation->id,
        ]);

        foreach ($this->selectedRawMaterials as $rawItem) {
            $itemId = $rawItem['item_id'];
            $unitId = $rawItem['unit_id'];
            $quantity = $rawItem['quantity'];
            $unitCost = $rawItem['unit_cost'];
            $unitValue = collect($this->selectedRawMaterials)->pluck('available_quantity');

            $qtyOut = $quantity * $unitValue;

            $fatQty = $qtyOut;

            $costPrice = $qtyOut * ($unitCost / $unitValue);
            // $unit = collect($rawItem['unitsList'])->firstWhere('id', $unitId);
            // $totalValue = $qtyOut * $price;
            // $totalRawCost += $totalValue;

            OperationItems::create([
                'pro_tybe'     => 59,
                'detail_store' => $this->rawAccount,
                'pro_id'       => $this->pro_id,
                'item_id'      => $itemId,
                'unit_id'      => $unitId,
                'qty_in'       => 0,
                'qty_out'      => $qtyOut,
                'fat_quantity' => $fatQty,
                'item_price'   => $unitCost,
                'cost_price'   => $costPrice,
                'fat_price'    => $unitCost,
                // 'current_stock_value' => $this->current_stock_value,
                // 'item_discount' => $this->item_discount,
                // 'additional' => $this->additional,
                // 'detail_value' => $this->detail_value,
                // 'profit' => $this->profit,
                // 'batch_number' => $this->batch_number,
                // 'expiry_date' => $this->expiry_date,
                // 'serial_numbers' => $this->serial_numbers,
                'is_stock' => 1,
                'isdeleted' => $this->isdeleted,
                // 'currency_id' => $this->currency_id,
                // 'currency_rate' => $this->currency_rate,
                // 'tenant' => $this->tenant,
                // 'branch' => $this->branch,
                'detail_value' => $costPrice,

                'notes'        => 'خروج مواد خام للتصنيع',
            ]);
        }

        // OperationItems::create([
        //     'pro_tybe' => 59,
        //     'detail_store' => $this->rawAccount,
        //     'pro_id' => $this->pro_id,
        //     'item_id' => $this->item_id,
        //     'unit_id' => $this->unit_id,
        //     'unit_value' => $this->unit_value,
        //     'qty_in' => 0,
        //     'qty_out' => $this->qty_out,
        //     'item_price' => $this->item_price,
        //     'cost_price' => $this->cost_price,
        //     'notes' => $this->notes,
        //     // 'batch_number' => $this->batch_number,
        //     'expiry_date' => $this->expiry_date,
        //     // 'serial_numbers' => $this->serial_numbers,
        //     'is_stock' => $this->is_stock,
        //     'isdeleted' => $this->isdeleted,
        //     // 'currency_id' => $this->currency_id,
        //     // 'currency_rate' => $this->currency_rate,
        //     // 'tenant' => $this->tenant,
        //     // 'branch' => $this->branch,
        // ]);

        OperationItems::create([
            'pro_tybe' => $this->pro_tybe,
            // 'detail_store' => $this->detail_store,
            'pro_id' => $this->pro_id,
            'item_id' => $this->item_id,
            'unit_id' => $this->unit_id,
            'unit_value' => $this->unit_value,
            'qty_in' => $this->qty_in,
            'qty_out' => $this->qty_out,
            'item_price' => $this->item_price,
            'cost_price' => $this->cost_price,
            // 'current_stock_value' => $this->current_stock_value,
            // 'item_discount' => $this->item_discount,
            // 'additional' => $this->additional,
            // 'detail_value' => $this->detail_value,
            // 'profit' => $this->profit,
            'notes' => $this->notes,
            // 'batch_number' => $this->batch_number,
            'expiry_date' => $this->expiry_date,
            // 'serial_numbers' => $this->serial_numbers,
            'is_stock' => $this->is_stock,
            'isdeleted' => $this->isdeleted,
            // 'currency_id' => $this->currency_id,
            // 'currency_rate' => $this->currency_rate,
            // 'tenant' => $this->tenant,
            // 'branch' => $this->branch,
        ]);

        JournalDetail::create([
            'journal_id' => $this->journal_id,
            'account_id' => $this->account_id,
            'debit' => $this->debit,
            'credit' => $this->credit,
            'type' => $this->type,
            'info' => $this->info,
            'op2' => $this->op2,
            'op_id' => $this->op_id,
            'isdeleted' => $this->isdeleted,
            'tenant' => $this->tenant,
            'branch' => $this->branch,
        ]);

        JournalHead::create([
            'journal_id' => $this->journal_id,
            'total' => $this->total,
            'date' => $this->date,
            'op_id' => $this->op_id,
            'pro_type' => $this->pro_type,
            'details' => $this->details,
            'op2' => $this->op2,
            'isdeleted' => $this->isdeleted,
            'user' => $this->user,
            'tenant' => $this->tenant,
            'branch' => $this->branch,
        ]);
    }

    public function render()
    {
        return view('livewire.invoices.manufacturing-invoice');
    }
}
