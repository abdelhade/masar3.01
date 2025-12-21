<?php

namespace Modules\Manufacturing\Livewire;

use App\Models\Expense;
use App\Models\Item;
use App\Models\OperationItems;
use App\Models\OperHead;
use Livewire\Component;
use Modules\Accounts\Models\AccHead;
use Modules\Manufacturing\Services\ManufacturingInvoiceService;

class EditManufacturingInvoice extends Component
{
    public $currentStep = 1;

    public $showSaveTemplateModal = false;

    public $showLoadTemplateModal = false;

    public $templateName = '';

    public $templates = [];

    public $selectedTemplate = null;

    public $templateExpectedTime = '';

    public $actualTime = '';

    public $quantityMultiplier = 1;

    public $pro_id;

    public $order_id;

    public $stage_id;

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

    public $totalProductsCost = 0;

    public $totalAdditionalExpenses = 0;

    public $totalManufacturingCost = 0;

    public $unitCostPerProduct = 0;

    public $OperatingAccount = '';

    public $rawAccount = '';

    public $expenseAccount;

    public $expenseAccountList = [];

    public $productAccount = '';

    public $Stors = [];

    public $OperatingCenter;

    public $patchNumber;

    public $employee;

    public $employeeList = [];

    public $activeTab = 'general_chat';

    public $originalInvoiceId;

    public $branch_id;

    public $branches;

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

    public function mount($invoiceId)
    {
        $this->branches = userBranches();
        if ($this->branches->isNotEmpty()) {
            $this->branch_id = $this->branches->first()->id;
        }
        // Load accounts FIRST before loading invoice
        // This ensures isProduct() has access to productAccount
        $this->Stors = $this->getAccountsByCode('1104%');
        $this->OperatingCenter = $this->getAccountsByCode('1108%');
        $this->employeeList = $this->getAccountsByCode('2102%');
        $this->expenseAccountList = $this->getAccountsByCode('5%');
        $this->expenseAccount = array_key_first($this->expenseAccountList);

        // Now load the invoice (which will set productAccount from invoice data)
        $this->loadInvoice($invoiceId);

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

    private function loadInvoice($invoiceId)
    {
        try {
            $invoice = OperHead::where('id', $invoiceId)->where('pro_type', 59)->first();
            if (! $invoice) {
                $this->dispatch('error-swal', [
                    'title' => 'خطأ!',
                    'text' => 'الفاتورة غير موجودة.',
                    'icon' => 'error',
                ]);

                return redirect()->to('/manufacturing-invoices');
            }

            $this->originalInvoiceId = $invoice->id;
            $this->pro_id = $invoice->pro_id;
            $this->order_id = $invoice->manufacturing_order_id;
            $this->stage_id = $invoice->manufacturing_stage_id;
            $this->invoiceDate = $invoice->pro_date;
            $this->description = $invoice->info ?? '';
            $this->employee = $invoice->emp_id;
            $this->rawAccount = $invoice->acc1;
            $this->productAccount = $invoice->acc2;
            $this->OperatingAccount = $invoice->store_id;
            $this->totalManufacturingCost = $invoice->pro_value;

            $invoiceItems = OperationItems::where('pro_id', $invoice->id)->get();
            foreach ($invoiceItems as $item) {
                if ($this->isProduct($item)) {
                    $this->loadProductFromInvoice($item);
                } else {
                    $this->loadRawMaterialFromInvoice($item);
                }
            }

            $invoiceExpenses = Expense::where('op_id', $invoice->id)->get();
            foreach ($invoiceExpenses as $expense) {
                $originalDescription = str_replace('مصروف إضافي: ', '', $expense->description);
                $originalDescription = preg_replace('/ - فاتورة:.*$/', '', $originalDescription);

                $this->additionalExpenses[] = [
                    'amount' => $expense->amount,
                    'account_id' => $expense->account_id,
                    'description' => trim($originalDescription),
                ];
            }

            // Calculate totals to populate breakdown variables
            $this->calculateTotals();
        } catch (\Exception $e) {
            $this->dispatch('error-swal', [
                'title' => 'خطأ!',
                'text' => 'حدث خطأ أثناء تحميل الفاتورة: ',
                'icon' => 'error',
            ]);

            return redirect()->to('/manufacturing-invoices');
        }
    }

    /**
     * Determine if an item is a product or raw material
     * Products have qty_in > 0 (they are produced/added to inventory)
     * Raw materials have qty_out > 0 (they are consumed/removed from inventory)
     */
    private function isProduct($item)
    {
        $qtyIn = (float) ($item->qty_in ?? 0);
        $qtyOut = (float) ($item->qty_out ?? 0);

        // Primary check: qty_in and qty_out
        if ($qtyIn > 0 && $qtyOut == 0) {
            return true; // Product
        }

        if ($qtyOut > 0 && $qtyIn == 0) {
            return false; // Raw material
        }

        // Fallback for old data or ambiguous cases
        if ($item->detail_store == $this->productAccount) {
            // If accounts are the same, we can't distinguish by account
            if ($this->productAccount == $this->rawAccount) {
                // If we are here, it means qtyIn and qtyOut are both 0 (or both > 0)
                // Default to Product if it's ambiguous? Or Raw Material?
                // Usually raw materials are more common, but let's check if it has cost_percentage (additional)
                if ($item->additional > 0) {
                    return true;
                } // Has cost percentage -> Product

                return false; // Assume Raw Material
            }

            return true;
        }

        return false;
    }

    private function loadProductFromInvoice($item)
    {
        try {
            $product = Item::find($item->item_id);
            if (! $product) {
                return;
            }

            $quantity = $item->fat_quantity ?? $item->qty_in ?? 1;
            $averageCost = $item->item_price ?? $item->cost_price ?? $product->average_cost ?? 0;

            $this->selectedProducts[] = [
                'id' => uniqid(),
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $quantity,
                'unit_cost' => $averageCost,
                'cost_percentage' => $item->additional ?? 0,
                'average_cost' => $averageCost,
                'total_cost' => $item->detail_value ?? ($quantity * $averageCost),
            ];
        } catch (\Exception $e) {
            $this->dispatch('error', title: 'خطأ', text: 'حدث خطأ أثناء تحميل المنتجات.', icon: 'error');
        }
    }

    private function loadRawMaterialFromInvoice($item)
    {
        try {
            $rawMaterial = Item::with('units')->find($item->item_id);
            if (! $rawMaterial) {
                return;
            }

            $unitsList = $rawMaterial->units->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'cost' => $unit->pivot->cost ?? 0,
                    'available_qty' => $unit->pivot->u_val ?? 0,
                ];
            })->toArray();

            if (empty($unitsList)) {
                $unitsList = [
                    [
                        'id' => 1,
                        'name' => 'قطعة',
                        'cost' => $item->cost_price ?? 0,
                        'available_qty' => 0,
                    ],
                ];
            }

            // Get the saved unit ID from the invoice
            // 1) Prefer fat_unit_id (the unit the user actually selected in the invoice UI)
            // 2) If fat_unit_id is null, try to infer the unit from quantities (fat_quantity vs qty_out and u_val)
            // 3) Finally, fall back to unit_id (base unit) or first unit in the list
            $selectedUnitId = $item->fat_unit_id;

            // Try to infer selected unit from quantities if fat_unit_id is missing but we have data
            $qtyOut = (float) ($item->qty_out ?? 0);
            $fatQty = (float) ($item->fat_quantity ?? 0);
            if (! $selectedUnitId && $qtyOut > 0 && $fatQty > 0 && ! empty($unitsList)) {
                $ratio = $qtyOut / $fatQty;

                $matchedUnit = collect($unitsList)->first(function ($unit) use ($ratio) {
                    $uVal = (float) ($unit['available_qty'] ?? 0);

                    return $uVal > 0 && abs($uVal - $ratio) < 0.0001;
                });

                if ($matchedUnit) {
                    $selectedUnitId = $matchedUnit['id'];
                }
            }

            // Fallback: use unit_id from the row (base unit) if still nothing
            if (! $selectedUnitId) {
                $selectedUnitId = $item->unit_id;
            }
            if (! $selectedUnitId && ! empty($unitsList)) {
                $selectedUnitId = $unitsList[0]['id'];
            }

            $unitExists = collect($unitsList)->contains('id', $selectedUnitId);
            if (! $unitExists && ! empty($unitsList)) {

                $selectedUnitId = $unitsList[0]['id'];
            }

            $quantity = $item->fat_quantity ?? $item->qty_out ?? 1;

            // ✅ استخدام average_cost من Item مباشرة (متوسط التكلفة الحالي)
            $currentAverageCost = $rawMaterial->average_cost ?? 0;

            // حساب التكلفة حسب الوحدة المختارة
            $selectedUnit = collect($unitsList)->firstWhere('id', $selectedUnitId);
            $conversionFactor = $selectedUnit['available_qty'] ?? 1;

            // إذا كانت الوحدة المختارة ليست الوحدة الأساسية، نحسب التكلفة بناءً على عامل التحويل
            $unitCost = $currentAverageCost * $conversionFactor;

            // استخدام التكلفة المحفوظة في الفاتورة إذا كانت موجودة، وإلا استخدام average_cost الحالي
            $savedCost = $item->fat_price ?? $item->item_price ?? $unitCost;

            // حساب total_cost
            $totalCost = $item->detail_value ?? ($quantity * $savedCost);

            $this->selectedRawMaterials[] = [
                'id' => uniqid(),
                'item_id' => $rawMaterial->id,
                'name' => $rawMaterial->name,
                'quantity' => $quantity,
                'unit_id' => $selectedUnitId,
                'unit_cost' => round($savedCost, 2),
                'available_quantity' => $this->getAvailableQuantity($rawMaterial->id, $selectedUnitId),
                'total_cost' => $totalCost,
                'unitsList' => $unitsList,
                'average_cost' => round($currentAverageCost, 2),  // ✅ متوسط التكلفة من Item
            ];
        } catch (\Exception) {

            $this->dispatch('error', title: 'خطأ', text: 'حدث خطأ أثناء تحميل المواد الخام.', icon: 'error');
        }
    }

    private function getAvailableQuantity($itemId, $unitId)
    {
        try {
            $item = Item::with('units')->find($itemId);
            if (! $item || ! $unitId) {
                return 0;
            }

            $unit = $item->units->where('id', $unitId)->first();

            return $unit ? $unit->pivot->u_val : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function loadProductsAndMaterials()
    {
        $this->productsList = Item::with(['prices' => function ($q) {
            $q->withPivot('price');
        }])
            ->select('id', 'name', 'average_cost')
            ->get();

        $this->rawMaterialsList = Item::select('id', 'name', 'average_cost')->get()->toArray();
    }

    public function updatedProductSearchTerm($value)
    {
        $this->productSelectedResultIndex = -1;
        $this->productSearchResults = collect();

        if (empty(trim($value))) {
            return;
        }

        // تنظيف مصطلح البحث
        $searchTerm = trim($value);

        // البحث عن الأصناف التي تطابق الباركود أولاً (أسرع)
        $itemIdsFromBarcode = \App\Models\Barcode::where('barcode', 'like', '%'.$searchTerm.'%')
            ->where('isdeleted', 0)
            ->limit(10)
            ->pluck('item_id')
            ->unique()
            ->toArray();

        // الكويري للبحث عن المنتجات - تحديد 10 نتائج فقط
        $this->productSearchResults = Item::with(['units' => fn ($q) => $q->orderBy('pivot_u_val'), 'prices'])
            ->select('id', 'name', 'average_cost')
            ->where(function ($query) use ($searchTerm, $itemIdsFromBarcode) {
                $query->where('name', 'like', '%'.$searchTerm.'%');
                if (! empty($itemIdsFromBarcode)) {
                    $query->orWhereIn('id', $itemIdsFromBarcode);
                }
            })
            ->limit(10)
            ->get();
    }

    public function updatedRawMaterialSearchTerm($value)
    {
        $this->rawMaterialSelectedResultIndex = -1;
        $this->rawMaterialSearchResults = collect();

        if (empty(trim($value))) {
            return;
        }

        // تنظيف مصطلح البحث
        $searchTerm = trim($value);

        // البحث عن الأصناف التي تطابق الباركود أولاً (أسرع)
        $itemIdsFromBarcode = \App\Models\Barcode::where('barcode', 'like', '%'.$searchTerm.'%')
            ->where('isdeleted', 0)
            ->limit(10)
            ->pluck('item_id')
            ->unique()
            ->toArray();

        // الكويري للبحث عن المواد الخام - تحديد 10 نتائج فقط
        $this->rawMaterialSearchResults = Item::with(['units' => fn ($q) => $q->orderBy('pivot_u_val')])
            ->select('id', 'name', 'average_cost')
            ->where(function ($query) use ($searchTerm, $itemIdsFromBarcode) {
                $query->where('name', 'like', '%'.$searchTerm.'%');
                if (! empty($itemIdsFromBarcode)) {
                    $query->orWhereIn('id', $itemIdsFromBarcode);
                }
            })
            ->limit(10)
            ->get();

        $this->calculateTotals();
    }

    public function addProductFromSearch($itemId)
    {
        $item = Item::with(['units' => fn ($q) => $q->orderBy('pivot_u_val'), 'prices', 'units'])->find($itemId);
        if (! $item) {
            return;
        }

        $existingProductIndex = null;
        foreach ($this->selectedProducts as $index => $product) {
            if ($product['product_id'] === $item->id) {
                $existingProductIndex = $index;
                break;
            }
        }

        if ($existingProductIndex !== null) {
            $this->selectedProducts[$existingProductIndex]['quantity']++;
            $averageCost = $this->selectedProducts[$existingProductIndex]['average_cost'] ?? 0;
            $this->selectedProducts[$existingProductIndex]['total_cost'] =
                round($this->selectedProducts[$existingProductIndex]['quantity'] * $averageCost, 2);

            $this->productSearchTerm = '';
            $this->productSearchResults = collect();
            $this->productSelectedResultIndex = -1;

            $this->calculateTotals();
            $this->updatePercentages();
            $this->dispatch('focusProductQuantity', $existingProductIndex);

            return;
        }

        $price = $item->prices->first()->pivot->price ?? 0;
        $averageCost = $item->average_cost ?? 0;
        $initialTotalCost = round(1 * $averageCost, 2);

        $this->selectedProducts[] = [
            'id' => uniqid(),
            'product_id' => $item->id,
            'name' => $item->name,
            'quantity' => 1,
            'unit_cost' => round($price, 2),
            'total_cost' => $initialTotalCost,
            'average_cost' => $averageCost,
            'cost_percentage' => 0,
            'user_modified_percentage' => false,
        ];

        $this->productSearchTerm = '';
        $this->productSearchResults = collect();
        $this->productSelectedResultIndex = -1;
        $this->calculateTotals();
        $this->updatePercentages();
        $this->dispatch('focusProductQuantity', count($this->selectedProducts) - 1);
    }

    public function removeProduct($index)
    {
        unset($this->selectedProducts[$index]);
        $this->selectedProducts = array_values($this->selectedProducts);
        $this->calculateTotals();
        $this->updatePercentages();
    }

    public function updateProductTotal($index)
    {
        if (isset($this->selectedProducts[$index])) {
            $quantity = (float) ($this->selectedProducts[$index]['quantity'] ?? 0);
            $unitCost = (float) ($this->selectedProducts[$index]['average_cost'] ?? 0);
            $total = $quantity * $unitCost;
            $this->selectedProducts[$index]['total_cost'] = round($total, 2);
        }
    }

    public function updatedSelectedProducts($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) === 3) {
            $index = $parts[1];
            $field = $parts[2];

            if ($field === 'quantity' || $field === 'unit_cost') {
                $this->updateProductTotal($index);
            } elseif ($field === 'cost_percentage') {
                $this->selectedProducts[$index]['user_modified_percentage'] = true;
            }
        }
        $this->calculateTotals();
    }

    private function updatePercentages()
    {
        $count = count($this->selectedProducts);
        if ($count === 0) {
            return;
        }

        $percentage = 100 / $count;
        $percentage = round($percentage, 2);

        foreach ($this->selectedProducts as $index => $product) {
            $this->selectedProducts[$index]['cost_percentage'] = $percentage;
        }
    }

    public function addRawMaterialFromSearch($itemId)
    {
        $item = Item::with('units')->find($itemId);
        if (! $item) {
            return;
        }

        $existingMaterialIndex = null;
        foreach ($this->selectedRawMaterials as $index => $material) {
            if ($material['item_id'] === $item->id) {
                $existingMaterialIndex = $index;
                break;
            }
        }

        if ($existingMaterialIndex !== null) {
            $this->selectedRawMaterials[$existingMaterialIndex]['quantity']++;
            $this->updateRawMaterialTotal($existingMaterialIndex);
            $this->rawMaterialSearchTerm = '';
            $this->rawMaterialSearchResults = collect();
            $this->rawMaterialSelectedResultIndex = -1;
            $this->calculateTotals();
            $this->dispatch('focusRawMaterialQuantity', $existingMaterialIndex);

            return;
        }

        $unitsList = $item->units->map(function ($unit) {
            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'cost' => $unit->pivot->cost ?? 0,
                'available_qty' => $unit->pivot->u_val ?? 0,
            ];
        })->toArray();

        $firstUnit = $unitsList[0] ?? null;
        // ✅ استخدام average_cost من Item مباشرة (لا يمكن تعديله)
        $baseAverageCost = $item->average_cost ?? 0;
        $initialTotalCost = round(1 * $baseAverageCost, 2);

        $this->selectedRawMaterials[] = [
            'id' => uniqid(),
            'item_id' => $item->id,
            'name' => $item->name,
            'quantity' => 1,
            'unit_id' => $firstUnit['id'] ?? null,
            'unit_cost' => round($firstUnit['cost'] ?? 0, 2), // للمرجعية فقط
            'available_quantity' => $firstUnit['available_qty'] ?? 0,
            'total_cost' => $initialTotalCost,
            'unitsList' => $unitsList,
            'average_cost' => round($baseAverageCost, 2), // ✅ متوسط التكلفة من Item (لا يتم تعديله)
        ];

        $this->rawMaterialSearchTerm = '';
        $this->rawMaterialSearchResults = collect();
        $this->rawMaterialSelectedResultIndex = -1;
        $this->calculateTotals();
        $this->dispatch('focusRawMaterialQuantity', count($this->selectedRawMaterials) - 1);
    }

    public function removeRawMaterial($index)
    {
        unset($this->selectedRawMaterials[$index]);
        $this->selectedRawMaterials = array_values($this->selectedRawMaterials);
        $this->calculateTotals();
    }

    public function updatedSelectedRawMaterials($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) !== 2) {
            return;
        }
        $index = $parts[0];
        $field = $parts[1];
        if (! isset($this->selectedRawMaterials[$index])) {
            return;
        }

        if ($field === 'unit_id') {
            $unitId = $value;
            $unit = collect($this->selectedRawMaterials[$index]['unitsList'])
                ->firstWhere('id', $unitId);
            if ($unit) {
                $unitCost = round($unit['cost'] ?? 0, 2);
                $this->selectedRawMaterials[$index]['unit_cost'] = $unitCost;
                $this->selectedRawMaterials[$index]['available_quantity'] = $unit['available_qty'] ?? 0;
                // Update average_cost to match unit_cost for consistency
                $this->selectedRawMaterials[$index]['average_cost'] = $unitCost;
                $this->updateRawMaterialTotal($index);
            }
        }

        if ($field === 'quantity') {
            $quantity = (float) $value;
            if ($quantity < 0) {
                $quantity = 0;
            }
            $this->selectedRawMaterials[$index]['quantity'] = $quantity;
            $this->updateRawMaterialTotal($index);
        }

        // ✅ لا يمكن تعديل average_cost - يتم جلبها من Item مباشرة
        // if ($field === 'average_cost') {
        //     $averageCost = (float) $value;
        //     $this->selectedRawMaterials[$index]['average_cost'] = $averageCost;
        //     $this->updateRawMaterialTotal($index);
        // }

        $this->calculateTotals();
    }

    private function updateRawMaterialTotal($index)
    {
        // ✅ استخدام average_cost من Item (لا يمكن تعديله)
        $averageCost = $this->selectedRawMaterials[$index]['average_cost'] ?? 0;
        $quantity = $this->selectedRawMaterials[$index]['quantity'] ?? 0;
        $totalRowCost = $averageCost * $quantity;
        $this->selectedRawMaterials[$index]['total_cost'] = round($totalRowCost, 2);
    }

    public function calculateTotals()
    {
        $this->totalProductsCost = collect($this->selectedProducts)
            ->sum(fn ($item) => is_numeric($item['total_cost']) ? (float) $item['total_cost'] : 0);

        $this->totalRawMaterialsCost = collect($this->selectedRawMaterials)
            ->sum(fn ($item) => is_numeric($item['total_cost']) ? (float) $item['total_cost'] : 0);

        $this->totalAdditionalExpenses = collect($this->additionalExpenses)
            ->sum(fn ($item) => is_numeric($item['amount']) ? (float) $item['amount'] : 0);

        $this->totalManufacturingCost = $this->totalRawMaterialsCost + $this->totalAdditionalExpenses;

        $totalProductQuantity = collect($this->selectedProducts)
            ->sum(fn ($item) => is_numeric($item['quantity']) ? (float) $item['quantity'] : 0);

        $this->unitCostPerProduct = $totalProductQuantity > 0 ?
            $this->totalManufacturingCost / $totalProductQuantity : 0;
    }

    public function addExpense()
    {
        $this->additionalExpenses[] = [
            'description' => '',
            'amount' => 0,
            'account_id' => $this->expenseAccount,
        ];
        $this->calculateTotals();
    }

    public function removeExpense($index)
    {
        unset($this->additionalExpenses[$index]);
        $this->additionalExpenses = array_values($this->additionalExpenses);
        $this->calculateTotals();
    }

    public function adjustCostsByPercentage()
    {
        if (empty($this->selectedProducts)) {
            return;
        }

        $totalPercentage = collect($this->selectedProducts)->sum(fn ($product) => (float) ($product['cost_percentage'] ?? 0));
        $isSumValid = abs($totalPercentage - 100) < 0.1;

        if (! $isSumValid) {
            $message = $totalPercentage > 100 ? 'مجموع النسب يتجاوز 100%! يرجى التعديل.' :
                'مجموع النسب أقل من 100%! يرجى التعديل.';
            $this->dispatch('show-alert', title: 'خطأ!', text: $message, icon: 'error');

            return;
        }

        $totalManufacturingCost = $this->totalRawMaterialsCost + $this->totalAdditionalExpenses;

        foreach ($this->selectedProducts as $index => $product) {
            $percentage = (float) ($product['cost_percentage'] ?? 0);
            $quantity = (float) ($product['quantity'] ?? 1);

            if ($quantity > 0 && $percentage >= 0) {
                $allocatedCost = ($totalManufacturingCost * $percentage) / 100;
                $newAverageCost = $allocatedCost / $quantity;

                $this->selectedProducts[$index]['average_cost'] = round($newAverageCost, 2);
                $this->selectedProducts[$index]['unit_cost'] = round($newAverageCost, 2);
                $this->selectedProducts[$index]['total_cost'] = round($quantity * $newAverageCost, 2);
                $this->selectedProducts[$index]['user_modified_percentage'] = true;
            }
        }

        $this->calculateTotals();
        $this->dispatch('show-alert', title: 'تم!', text: 'تم توزيع التكاليف بنجاح حسب النسب المحددة.', icon: 'success');
    }

    private function updateCurrentPrices()
    {
        // ✅ Batch loading لتقليل N+1 queries
        $productIds = collect($this->selectedProducts)->pluck('product_id')->unique()->filter()->values()->toArray();
        $rawMaterialIds = collect($this->selectedRawMaterials)->pluck('item_id')->unique()->filter()->values()->toArray();

        $productsMap = [];
        $rawMaterialsMap = [];

        if (! empty($productIds)) {
            $productsMap = Item::whereIn('id', $productIds)
                ->get()
                ->keyBy('id');
        }

        if (! empty($rawMaterialIds)) {
            $rawMaterialsMap = Item::with('units')
                ->whereIn('id', $rawMaterialIds)
                ->get()
                ->keyBy('id');
        }

        foreach ($this->selectedProducts as $index => $product) {
            $item = $productsMap[$product['product_id']] ?? null;
            if ($item) {
                $currentAverageCost = $item->average_cost ?? 0;
                $this->selectedProducts[$index]['average_cost'] = $currentAverageCost;
                $this->selectedProducts[$index]['total_cost'] = $currentAverageCost * $product['quantity'];
            }
        }

        foreach ($this->selectedRawMaterials as $index => $rawMaterial) {
            $item = $rawMaterialsMap[$rawMaterial['item_id']] ?? null;
            if ($item) {
                // ✅ استخدام average_cost من Item مباشرة (لا يمكن تعديله)
                $averageCost = $item->average_cost ?? 0;
                $this->selectedRawMaterials[$index]['average_cost'] = round($averageCost, 2);
                $this->selectedRawMaterials[$index]['total_cost'] = round($averageCost * $rawMaterial['quantity'], 2);

                if ($rawMaterial['unit_id']) {
                    $unit = $item->units->where('id', $rawMaterial['unit_id'])->first();
                    if ($unit) {
                        $currentQuantity = $unit->pivot->u_val ?? 0;
                        $this->selectedRawMaterials[$index]['available_quantity'] = $currentQuantity;

                        $updatedUnitsList = $item->units->map(function ($unit) {
                            return [
                                'id' => $unit->id,
                                'name' => $unit->name,
                                'cost' => $unit->pivot->cost ?? 0,
                                'available_qty' => $unit->pivot->u_val ?? 0,
                            ];
                        })->toArray();

                        $this->selectedRawMaterials[$index]['unitsList'] = $updatedUnitsList;
                    }
                }
            }
        }
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

    public function updateInvoice()
    {
        $service = new ManufacturingInvoiceService;
        $service->updateManufacturingInvoice($this, $this->originalInvoiceId);
    }

    public function openSaveTemplateModal()
    {
        $this->showSaveTemplateModal = true;
    }

    public function closeSaveTemplateModal()
    {
        $this->showSaveTemplateModal = false;
        $this->templateName = '';
        $this->templateExpectedTime;
    }

    public function openLoadTemplateModal()
    {
        try {
            $this->selectedTemplate = null;
            $this->templates = OperHead::where('pro_type', 63)
                ->where('is_manager', 1)
                ->select('id', 'pro_id', 'info', 'pro_date', 'pro_value', 'emp_id', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($template) {
                    // تنسيق اسم النموذج
                    $name = $template->info ?: "نموذج رقم {$template->pro_id}";
                    $date = \Carbon\Carbon::parse($template->pro_date)->format('Y-m-d');
                    $value = number_format($template->pro_value, 2);

                    return [
                        'id' => $template->id,
                        'pro_id' => $template->pro_id,
                        'name' => $template->info,
                        'display_name' => "{$name} ({$value} ج.م - {$date})",
                        'pro_date' => $template->pro_date,
                        'pro_value' => $template->pro_value,
                        'emp_id' => $template->emp_id,
                    ];
                })
                ->toArray();
            $this->showLoadTemplateModal = true;
            $this->dispatch('templates-loaded', count($this->templates));
        } catch (\Exception) {
            $this->dispatch('error-swal', [
                'title' => 'خطأ!',
                'text' => 'حدث خطأ أثناء تحميل النماذج: ',
                'icon' => 'error',
            ]);
        }
    }

    public function applyQuantityMultiplier()
    {
        if ($this->quantityMultiplier <= 0) {
            $this->dispatch('error', title: 'خطأ !', text: 'يجب أن يكون المضاعف أكبر من صفر.', icon: 'error');
        }

        // مضاعفة كميات المواد الخام
        foreach ($this->selectedRawMaterials as $index => $material) {
            $this->selectedRawMaterials[$index]['quantity'] = $material['quantity'] * $this->quantityMultiplier;
            $this->updateRawMaterialTotal($index);
        }

        // مضاعفة كميات المنتجات
        foreach ($this->selectedProducts as $index => $product) {
            $this->selectedProducts[$index]['quantity'] = $product['quantity'] * $this->quantityMultiplier;
            $this->updateProductTotal($index);
        }

        if (! empty($this->templateExpectedTime)) {
            $timeParts = $this->templateExpectedTime * $this->quantityMultiplier;
            $this->templateExpectedTime = $timeParts;
        }

        $this->calculateTotals();
        $this->dispatch('success', title: 'تم !', text: 'تم مضاعفة الكميات بنجاح.', icon: 'success');
    }

    public function loadTemplate()
    {
        if (! $this->selectedTemplate) {
            $this->dispatch('error-swal', [
                'title' => 'خطأ!',
                'text' => 'يرجى اختيار نموذج أولاً.',
                'icon' => 'error',
            ]);

            return;
        }

        try {
            // 1. العثور على النموذج الرئيسي
            $template = OperHead::find($this->selectedTemplate);
            if (! $template) {
                $this->dispatch('error-swal', ['title' => 'خطأ!', 'text' => 'النموذج غير موجود.', 'icon' => 'error']);

                return;
            }

            // 2. إعادة تعيين البيانات الحالية
            $this->selectedProducts = [];
            $this->selectedRawMaterials = [];
            $this->additionalExpenses = [];

            $this->templateExpectedTime = $template->expected_time ?? '';
            $this->actualTime = '';
            $this->quantityMultiplier = 1;

            // 3. تحميل المنتجات والمواد الخام من جدول OperationItems
            $templateItems = OperationItems::where('pro_id', $template->id)->get();
            foreach ($templateItems as $item) {
                if ($this->isProduct($item)) {
                    $this->loadProductFromTemplate($item);
                } else {
                    $this->loadRawMaterialFromTemplate($item);
                }
            }
            // 4. تحميل المصروفات من جدول Expense باستخدام الربط المباشر op_id
            $templateExpenses = Expense::where('op_id', $template->id)->get();
            foreach ($templateExpenses as $expense) {

                // تنظيف الوصف لعرضه للمستخدم بدون النصوص الإضافية
                $originalDescription = str_replace('مصروف إضافي: ', '', $expense->description);
                $originalDescription = preg_replace('/ - نموذج:.*$/', '', $originalDescription);

                $this->additionalExpenses[] = [
                    'amount' => $expense->amount,
                    'account_id' => $expense->account_id,
                    'description' => trim($originalDescription),
                ];
            }

            // 5. تحديث باقي البيانات
            $this->updateCurrentPrices();
            $this->description = $template->info ?? '';
            if ($template->emp_id) {
                $this->employee = $template->emp_id;
            }
            if ($template->acc2) {
                $this->rawAccount = $template->acc2;
            }
            if ($template->acc1) {
                $this->productAccount = $template->acc1;
            }

            $this->calculateTotals();
            $this->closeLoadTemplateModal();

            $this->dispatch('success-swal', title: 'تم !', text: 'تم تحميل النموذج بنجاح.', icon: 'success');
        } catch (\Exception $e) {
            $this->dispatch('error-swal', [
                'title' => 'خطأ!',
                'text' => 'حدث خطأ أثناء تحميل النموذج: '.$e->getMessage(),
                'icon' => 'error',
            ]);
        }
    }

    private function loadProductFromTemplate($item)
    {
        try {
            // البحث عن المنتج في الجدول الأصلي باستخدام item_id
            $product = Item::find($item->item_id); // بدلاً من OperationItems::find

            if (! $product) {
                // إذا لم يوجد في جدول Item، ابحث في جدول آخر حسب نظامك
                return;
            }

            $averageCost = $product->average_cost ?? $product->cost_price ?? 0;

            $this->selectedProducts[] = [
                'id' => uniqid(),
                'product_id' => $product->id,
                'name' => $product->name, // الآن سيحصل على الاسم من الجدول الصحيح
                'quantity' => $item->fat_quantity ?? 1,
                'cost_percentage' => $item->additional ?? 0,
                'average_cost' => $averageCost,
                'total_cost' => $item->total_cost ?? (($item->fat_quantity ?? 1) * $averageCost),
            ];
        } catch (\Exception $e) {
            $this->dispatch('error', title: 'خطأ', text: 'حدث خطأ أثناء تحميل المنتجات.', icon: 'error');

            return null;
        }
    }

    private function loadRawMaterialFromTemplate($item)
    {
        try {
            // هذا صحيح - المواد الخام تأتي من جدول Item
            $rawMaterial = Item::with('units')->find($item->item_id);
            if (! $rawMaterial) {
                return;
            }

            $unitsList = $rawMaterial->units->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'cost' => $unit->pivot->cost ?? 0,
                    'available_qty' => $unit->pivot->u_val ?? 0,
                ];
            })->toArray();

            if (empty($unitsList)) {
                $unitsList = [
                    [
                        'id' => 1,
                        'name' => 'قطعة',
                        'cost' => $item->cost_price ?? 0,
                        'available_qty' => 0,
                    ],
                ];
            }

            $selectedUnitId = $item->unit_id ?? $unitsList[0]['id'];

            $this->selectedRawMaterials[] = [
                'id' => uniqid(),
                'item_id' => $rawMaterial->id,
                'name' => $rawMaterial->name, // هذا يعمل بشكل صحيح
                'quantity' => $item->fat_quantity ?? 1,
                'unit_id' => $selectedUnitId,
                'unit_cost' => $item->cost_price ?? 0,
                'available_quantity' => $this->getAvailableQuantity($rawMaterial->id, $selectedUnitId),
                'total_cost' => $item->total_cost ?? 0,
                'unitsList' => $unitsList,
                'average_cost' => $rawMaterial->average_cost ?? 0,
            ];
        } catch (\Exception $e) {
            $this->dispatch('error', title: 'خطأ', text: 'حدث خطأ أثناء تحميل المواد الخام.', icon: 'error');

            return null;
        }
    }

    public function closeLoadTemplateModal()
    {
        $this->showLoadTemplateModal = false;
        $this->selectedTemplate = null;
    }

    public function saveAsTemplate()
    {
        if (empty($this->templateName)) {
            $this->dispatch('error-swal', [
                'title' => 'خطأ!',
                'text' => 'يرجى إدخال اسم النموذج.',
                'icon' => 'error',
            ]);

            return;
        }

        $operation = OperHead::create([
            'pro_id' => $this->pro_id,
            'is_stock' => 1,
            'is_journal' => 0,
            'is_manager' => 1,
            'info' => $this->templateName,
            'expected_time' => $this->templateExpectedTime, // إضافة الوقت المتوقع
            'pro_date' => $this->invoiceDate,
            'emp_id' => $this->employee,
            'acc1' => $this->productAccount,
            'acc2' => $this->rawAccount,
            'pro_value' => $this->totalManufacturingCost,
            'fat_net' => $this->totalManufacturingCost,
            'user' => \Illuminate\Support\Facades\Auth::user()->id,
            'pro_type' => 63,
        ]);

        foreach ($this->selectedProducts as $product) {
            OperationItems::create([
                'pro_tybe' => 63,
                'pro_id' => $operation->id,
                'item_id' => $product['product_id'],
                'notes' => 'نموذج تصنيع '.$this->templateName,
                'detail_store' => $this->productAccount,
                'is_stock' => 1,
                'additional' => $product['cost_percentage'],
                'fat_price' => $this->totalProductsCost,
                'item_price' => $product['average_cost'],
                'fat_quantity' => $product['quantity'],
                'cost_price' => $product['unit_cost'],
                'total_cost' => $product['total_cost'],
            ]);
        }

        foreach ($this->selectedRawMaterials as $raw) {
            OperationItems::create([
                'pro_tybe' => 63,
                'pro_id' => $operation->id,
                'item_id' => $raw['item_id'],
                'notes' => 'نموذج تصنيع '.$this->templateName,
                'unit_id' => $raw['unit_id'],
                'detail_store' => $this->productAccount,
                'is_stock' => 1,
                'item_price' => $raw['average_cost'],
                'fat_price' => $this->totalRawMaterialsCost,
                'fat_quantity' => $raw['quantity'],
                'cost_price' => $raw['unit_cost'],
                'total_cost' => $raw['total_cost'],
            ]);
        }

        foreach ($this->additionalExpenses as $expense) {
            if (isset($expense['amount']) && $expense['amount'] > 0) {
                Expense::create([
                    'title' => $this->templateName,
                    'pro_type' => 63,
                    'op_id' => $operation->id,
                    'amount' => $expense['amount'],
                    'account_id' => $expense['account_id'] ?? $this->expenseAccount,
                    'description' => 'مصروف إضافي: '.($expense['description'] ?? 'غير محدد').' - نموذج: '.$this->templateName,
                ]);
            }
        }

        $this->dispatch('success-swal', title: 'تم الحفظ!', text: 'تم حفظ نموذج التصنيع بنجاح.', icon: 'success');

        $this->closeSaveTemplateModal();
    }

    public function cancelEdit()
    {
        return redirect()->to('/manufacturing-invoices');
    }

    public function render()
    {
        return view('manufacturing::livewire.edit-manufacturing-invoice');
    }
}
