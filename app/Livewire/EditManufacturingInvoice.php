<?php

namespace App\Livewire;

use App\Models\Expense;
use Livewire\Component;
use App\Services\ManufacturingInvoiceService;
use App\Models\{Item, OperHead, AccHead, OperationItems};

class EditManufacturingInvoice extends Component
{
    public $currentStep = 1;
    public $pro_id;
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
        $this->loadInvoice($invoiceId);
        $this->Stors = $this->getAccountsByCode('1104%');
        $this->OperatingCenter = $this->getAccountsByCode('1108%');
        $this->employeeList = $this->getAccountsByCode('2102%');
        $this->expenseAccountList = $this->getAccountsByCode('5%');
        $this->expenseAccount = array_key_first($this->expenseAccountList);
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
            if (!$invoice) {
                $this->dispatch('error-swal', [
                    'title' => 'خطأ!',
                    'text' => 'الفاتورة غير موجودة.',
                    'icon' => 'error'
                ]);
                return redirect()->to('/manufacturing-invoices');
            }

            $this->originalInvoiceId = $invoice->id;
            $this->pro_id = $invoice->pro_id;
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
                    'description' => trim($originalDescription)
                ];
            }

            $this->updateCurrentPrices();
            $this->calculateTotals();
        } catch (\Exception $e) {
            $this->dispatch('error-swal', [
                'title' => 'خطأ!',
                'text' => 'حدث خطأ أثناء تحميل الفاتورة: ' . $e->getMessage(),
                'icon' => 'error'
            ]);
            return redirect()->to('/manufacturing-invoices');
        }
    }

    private function isProduct($item)
    {
        return !is_null($item->item_id) &&
            is_null($item->unit_id) &&
            $item->fat_tax != 999 &&
            $item->detail_store == $this->productAccount;
    }

    private function loadProductFromInvoice($item)
    {
        try {
            $product = Item::find($item->item_id);
            if (!$product) return;

            $this->selectedProducts[] = [
                'id' => uniqid(),
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $item->qty_in ?? 1,
                'unit_cost' => $item->cost_price ?? 0,
                'cost_percentage' => $item->additional ?? 0,
                'average_cost' => $product->average_cost ?? 0,
                'total_cost' => ($item->qty_in ?? 1) * ($product->average_cost ?? 0),
            ];
        } catch (\Exception $e) {
            $this->dispatch('error', title: 'خطأ', text: 'حدث خطأ أثناء تحميل المنتجات.', icon: 'error');
        }
    }

    private function loadRawMaterialFromInvoice($item)
    {
        try {
            $rawMaterial = Item::with('units')->find($item->item_id);
            if (!$rawMaterial) return;

            $unitsList = $rawMaterial->units->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'cost' => $unit->pivot->cost ?? 0,
                    'available_qty' => $unit->pivot->u_val ?? 0
                ];
            })->toArray();

            if (empty($unitsList)) {
                $unitsList = [
                    [
                        'id' => 1,
                        'name' => 'قطعة',
                        'cost' => $item->cost_price ?? 0,
                        'available_qty' => 0
                    ]
                ];
            }

            $selectedUnitId = $item->unit_id ?? $unitsList[0]['id'];
            $this->selectedRawMaterials[] = [
                'id' => uniqid(),
                'item_id' => $rawMaterial->id,
                'name' => $rawMaterial->name,
                'quantity' => $item->qty_out ?? 1,
                'unit_id' => $selectedUnitId,
                'unit_cost' => $item->cost_price ?? 0,
                'available_quantity' => $this->getAvailableQuantity($rawMaterial->id, $selectedUnitId),
                'total_cost' => $item->detail_value ?? 0,
                'unitsList' => $unitsList,
                'average_cost' => $rawMaterial->average_cost ?? 0
            ];
        } catch (\Exception $e) {
            $this->dispatch('error', title: 'خطأ', text: 'حدث خطأ أثناء تحميل المواد الخام.', icon: 'error');
        }
    }

    private function getAvailableQuantity($itemId, $unitId)
    {
        try {
            $item = Item::with('units')->find($itemId);
            if (!$item || !$unitId) return 0;

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
        $this->productSearchResults = strlen($value) < 1
            ? collect()
            : Item::with(['units', 'prices'])
            ->select('id', 'name', 'average_cost')
            ->whereRaw("name LIKE ? OR name LIKE ? OR name LIKE ?", [
                "{$value}%",
                "%{$value}",
                "%{$value}%",
            ])
            ->take(5)->get();
    }

    public function updatedRawMaterialSearchTerm($value)
    {
        $this->rawMaterialSelectedResultIndex = -1;
        $this->rawMaterialSearchResults = strlen($value) < 1
            ? collect()
            : Item::with('units')
            ->whereRaw("name LIKE ? OR name LIKE ? OR name LIKE ?", [
                "{$value}%",
                "%{$value}",
                "%{$value}%",
            ])
            ->take(5)->get();
        $this->calculateTotals();
    }

    public function addProductFromSearch($itemId)
    {
        $item = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices', 'units'])->find($itemId);
        if (!$item) return;

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
            'user_modified_percentage' => false
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
            $quantity = (float)($this->selectedProducts[$index]['quantity'] ?? 0);
            $unitCost = (float)($this->selectedProducts[$index]['average_cost'] ?? 0);
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
        if ($count === 0) return;

        $percentage = 100 / $count;
        $percentage = round($percentage, 2);

        foreach ($this->selectedProducts as $index => $product) {
            $this->selectedProducts[$index]['cost_percentage'] = $percentage;
        }
    }

    public function addRawMaterialFromSearch($itemId)
    {
        $item = Item::with('units')->find($itemId);
        if (!$item) return;

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
                'available_qty' => $unit->pivot->u_val ?? 0
            ];
        })->toArray();

        $firstUnit = $unitsList[0] ?? null;
        $averageCost = $item->average_cost ?? 0;
        $initialTotalCost = round(1 * $averageCost, 2);

        $this->selectedRawMaterials[] = [
            'id' => uniqid(),
            'item_id' => $item->id,
            'name' => $item->name,
            'quantity' => 1,
            'unit_id' => $firstUnit['id'] ?? null,
            'unit_cost' => round($firstUnit['cost'] ?? 0, 2),
            'available_quantity' => $firstUnit['available_qty'] ?? 0,
            'total_cost' => $initialTotalCost,
            'unitsList' => $unitsList,
            'average_cost' => $averageCost
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
        if (count($parts) !== 2) return;
        $index = $parts[0];
        $field = $parts[1];
        if (!isset($this->selectedRawMaterials[$index])) return;

        if ($field === 'unit_id') {
            $unitId = $value;
            $unit = collect($this->selectedRawMaterials[$index]['unitsList'])
                ->firstWhere('id', $unitId);
            if ($unit) {
                $this->selectedRawMaterials[$index]['unit_cost'] = round($unit['cost'] ?? 0, 2);
                $this->selectedRawMaterials[$index]['available_quantity'] = $unit['available_qty'] ?? 0;
                $this->updateRawMaterialTotal($index);
            }
        }

        if ($field === 'quantity') {
            $quantity = (float)$value;
            if ($quantity < 0) {
                $quantity = 0;
            }
            $this->selectedRawMaterials[$index]['quantity'] = $quantity;
            $this->updateRawMaterialTotal($index);
        }

        if ($field === 'average_cost') {
            $averageCost = (float)$value;
            $this->selectedRawMaterials[$index]['average_cost'] = $averageCost;
            $this->updateRawMaterialTotal($index);
        }

        $this->calculateTotals();
    }

    private function updateRawMaterialTotal($index)
    {
        $totalRowCost = $this->selectedRawMaterials[$index]['average_cost'] * $this->selectedRawMaterials[$index]['quantity'];
        $this->selectedRawMaterials[$index]['total_cost'] = $totalRowCost;
    }

    public function calculateTotals()
    {
        $this->totalProductsCost = collect($this->selectedProducts)
            ->sum(fn($item) => is_numeric($item['total_cost']) ? (float)$item['total_cost'] : 0);

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
            'amount' => 0,
            'account_id' => $this->expenseAccount
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
        if (empty($this->selectedProducts)) return;

        $totalPercentage = collect($this->selectedProducts)->sum(fn($product) => (float)($product['cost_percentage'] ?? 0));
        $isSumValid = abs($totalPercentage - 100) < 0.1;

        if (!$isSumValid) {
            $message = $totalPercentage > 100 ? 'مجموع النسب يتجاوز 100%! يرجى التعديل.' :
                'مجموع النسب أقل من 100%! يرجى التعديل.';
            $this->dispatch('show-alert', title: 'خطأ!', text: $message, icon: 'error');
            return;
        }

        $totalManufacturingCost = $this->totalRawMaterialsCost + $this->totalAdditionalExpenses;

        foreach ($this->selectedProducts as $index => $product) {
            $percentage = (float)($product['cost_percentage'] ?? 0);
            $quantity = (float)($product['quantity'] ?? 1);

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
        foreach ($this->selectedProducts as $index => $product) {
            $item = Item::find($product['product_id']);
            if ($item) {
                $currentAverageCost = $item->average_cost ?? 0;
                $this->selectedProducts[$index]['average_cost'] = $currentAverageCost;
                $this->selectedProducts[$index]['total_cost'] = $currentAverageCost * $product['quantity'];
            }
        }

        foreach ($this->selectedRawMaterials as $index => $rawMaterial) {
            $item = Item::with('units')->find($rawMaterial['item_id']);
            if ($item) {
                $averageCost = $item->average_cost ?? 0;
                $this->selectedRawMaterials[$index]['average_cost'] = $averageCost;
                $this->selectedRawMaterials[$index]['total_cost'] = $averageCost * $rawMaterial['quantity'];

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
                                'available_qty' => $unit->pivot->u_val ?? 0
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
        $service = new ManufacturingInvoiceService();
        $service->updateManufacturingInvoice($this, $this->originalInvoiceId);
    }

    public function cancelEdit()
    {
        return redirect()->to('/manufacturing-invoices');
    }

    public function render()
    {
        return view('livewire.invoices.edit-manufacturing-invoice');
    }
}
