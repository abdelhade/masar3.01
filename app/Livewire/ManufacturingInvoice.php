<?php

namespace App\Livewire;

use App\Models\{
    Item,
    OperHead,
    AccHead,
    OperationItems
};
use App\Services\ManufacturingInvoiceService;
use Livewire\Component;

class ManufacturingInvoice extends Component
{
    // protected $listeners = ['refresh' => '$refresh'];
    // public function hydrate()
    // {
    //     $this->calculateTotals();
    // }

    public $showSaveTemplateModal = false;
    public $showLoadTemplateModal = false;
    public $templateName = '';
    public $templates = [];
    public $selectedTemplate = null;

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
        $this->Stors = $this->getAccountsByCode('1104%');
        $this->OperatingCenter = $this->getAccountsByCode('1108%');
        $this->employeeList = $this->getAccountsByCode('2102%');

        $this->expenseAccountList = $this->getAccountsByCode('5%');
        $this->expenseAccount = array_key_first($this->expenseAccountList);

        $this->employee = array_key_first($this->employeeList);

        $this->OperatingAccount = array_key_first($this->OperatingCenter);
        $this->rawAccount = array_key_first($this->Stors);
        $this->productAccount = array_key_first($this->Stors);
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

        // تم حذف الكود الذي كان يعيد تعيين unit_cost و total_cost للمواد الخام هنا
        // تحديث التكاليف للمواد المختارة أصبح يتم فقط عند تغيير الوحدة أو الكمية

        $this->calculateTotals();
    }

    public function addProductFromSearch($itemId)
    {
        $item = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices'])->find($itemId);
        if (!$item) return;

        // التحقق من وجود المنتج في القائمة
        $existingProductIndex = null;
        foreach ($this->selectedProducts as $index => $product) {
            if ($product['product_id'] === $item->id) {
                $existingProductIndex = $index;
                break;
            }
        }

        if ($existingProductIndex !== null) {
            $this->selectedProducts[$existingProductIndex]['quantity']++;

            // إعادة حساب التكلفة الإجمالية للمنتج مع تقريب لمنزلتين
            $this->selectedProducts[$existingProductIndex]['total_cost'] =
                round($this->selectedProducts[$existingProductIndex]['quantity'] *
                    $this->selectedProducts[$existingProductIndex]['unit_cost'], 2);

            $this->productSearchTerm = '';
            $this->productSearchResults = collect();
            $this->productSelectedResultIndex = -1;

            $this->calculateTotals();
            $this->updatePercentages(); // تحديث النسب المئوية

            $this->dispatch('focusProductQuantity', $existingProductIndex);
            return;
        }

        $price = $item->prices->first()->pivot->price ?? 0;
        $price = round($price, 2); // تقريب لمنزلتين

        $this->selectedProducts[] = [
            'id' => uniqid(),
            'product_id' => $item->id,
            'name' => $item->name,
            'quantity' => 1,
            'unit_cost' => $price,
            'total_cost' => $price,
            'cost_percentage' => 0,
            'user_modified_percentage' => false // علامة أن النسبة غير معدلة يدوياً

        ];

        $this->productSearchTerm = '';
        $this->productSearchResults = collect();
        $this->productSelectedResultIndex = -1;
        $this->calculateTotals();
        $this->updatePercentages(); // تحديث النسب المئوية

        $this->dispatch('focusProductQuantity', count($this->selectedProducts) - 1);
    }
    public function removeProduct($index)
    {
        unset($this->selectedProducts[$index]);
        $this->selectedProducts = array_values($this->selectedProducts);
        $this->calculateTotals();
        $this->updatePercentages(); // تحديث النسب المئوية بعد الحذف
    }

    public function updateProductTotal($index)
    {
        if (isset($this->selectedProducts[$index])) {
            $quantity = (float)($this->selectedProducts[$index]['quantity'] ?? 0);
            $unitCost = (float)($this->selectedProducts[$index]['unit_cost'] ?? 0);

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
                // وضع علامة أن المستخدم عدل النسبة يدوياً
                $this->selectedProducts[$index]['user_modified_percentage'] = true;
            }
        }
    }

    // دالة جديدة لتوزيع النسب المئوية
    private function updatePercentages()
    {
        $count = count($this->selectedProducts);
        if ($count === 0) return;

        // حساب النسبة لكل منتج (100 / عدد المنتجات)
        $percentage = 100 / $count;
        $percentage = round($percentage, 2); // تقريب لمنزلتين

        // تطبيق النسبة على جميع المنتجات
        foreach ($this->selectedProducts as $index => $product) {
            $this->selectedProducts[$index]['cost_percentage'] = $percentage;
        }
    }

    public function addRawMaterialFromSearch($itemId)
    {
        $item = Item::with('units')->find($itemId);
        if (! $item) return;

        // التحقق من وجود المادة الخام في القائمة
        $existingMaterialIndex = null;
        foreach ($this->selectedRawMaterials as $index => $material) {
            if ($material['item_id'] === $item->id) {
                $existingMaterialIndex = $index;
                break;
            }
        }

        // إذا كانت المادة الخام موجودة، زيادة الكمية وإعادة حساب التكلفة
        if ($existingMaterialIndex !== null) {
            $this->selectedRawMaterials[$existingMaterialIndex]['quantity']++;

            // إعادة حساب التكلفة الإجمالية للمادة الخام
            $this->selectedRawMaterials[$existingMaterialIndex]['total_cost'] =
                $this->selectedRawMaterials[$existingMaterialIndex]['quantity'] *
                $this->selectedRawMaterials[$existingMaterialIndex]['unit_cost'];

            // إعادة تعيين حقول البحث
            $this->rawMaterialSearchTerm = '';
            $this->rawMaterialSearchResults = collect();
            $this->rawMaterialSelectedResultIndex = -1;

            $this->calculateTotals();

            // التركيز على حقل الكمية للمادة الخام الموجودة
            $this->dispatch('focusRawMaterialQuantity', $existingMaterialIndex);

            return; // الخروج من الدالة
        }

        // إذا لم تكن المادة الخام موجودة، إضافة مادة خام جديدة (الكود الأصلي)
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

        // التركيز على حقل الكمية للمادة الخام الجديدة
        $this->dispatch('focusRawMaterialQuantity', count($this->selectedRawMaterials) - 1);
    }

    // public function removeProduct($index)
    // {
    //     unset($this->selectedProducts[$index]);
    //     $this->selectedProducts = array_values($this->selectedProducts);
    //     $this->calculateTotals();
    // }

    public function removeRawMaterial($index)
    {
        unset($this->selectedRawMaterials[$index]);
        $this->selectedRawMaterials = array_values($this->selectedRawMaterials);
        $this->calculateTotals();
    }

    // public function updated($propertyName)
    // {
    //     if (
    //         str_contains($propertyName, 'quantity') ||
    //         str_contains($propertyName, 'unit_cost') ||
    //         str_contains($propertyName, 'amount') ||
    //         str_contains($propertyName, 'cost_percentage')
    //     ) {
    //         $this->convertToNumber($propertyName);
    //     }
    //     $this->calculateTotals();

    //     if (str_contains($propertyName, 'selectedProducts')) {
    //         $this->updateProductTotal($propertyName);
    //     }

    //     if (str_contains($propertyName, 'selectedRawMaterials')) {
    //         $this->updateRawMaterialTotal($propertyName);
    //     }
    // }

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

    // public function updateProductTotal($propertyName)
    // {
    //     $parts = explode('.', $propertyName);
    //     if (count($parts) < 2) return;

    //     $index = $parts[1];
    //     $field = $parts[2];

    //     if (in_array($field, ['quantity', 'unit_cost']) && isset($this->selectedProducts[$index])) {
    //         $this->selectedProducts[$index]['total_cost'] =
    //             $this->selectedProducts[$index]['quantity'] * $this->selectedProducts[$index]['unit_cost'];
    //     }
    // }

    public function adjustCostsByPercentage()
    {
        $totalCost = 0;

        // حساب التكلفة الإجمالية لجميع المنتجات
        foreach ($this->selectedProducts as $product) {
            $totalCost += $product['quantity'] * $product['unit_cost'];
        }

        // توزيع التكلفة حسب النسب المئوية
        foreach ($this->selectedProducts as $index => $product) {
            $allocatedCost = ($totalCost * $product['cost_percentage']) / 100;
            $this->selectedProducts[$index]['unit_cost'] = $product['quantity'] > 0
                ? round($allocatedCost / $product['quantity'], 2)
                : 0;

            // تحديث التكلفة الإجمالية
            $this->updateProductTotal($index);
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
            'amount' => 0
        ];
    }

    public function removeExpense($index)
    {
        unset($this->additionalExpenses[$index]);
        $this->additionalExpenses = array_values($this->additionalExpenses);
        $this->calculateTotals();
    }

    // public function distributeCosts()
    // {
    //     $this->calculateTotals();
    //     $productsWithPositivePercentage = collect($this->selectedProducts)
    //         ->filter(fn($p) => (float)$p['cost_percentage'] > 0);
    //     if ($productsWithPositivePercentage->isEmpty()) {
    //         foreach ($this->selectedProducts as $index => $product) {
    //             $this->selectedProducts[$index]['total_cost'] = 0;
    //             $this->selectedProducts[$index]['unit_cost'] = 0;
    //         }
    //         return;
    //     }
    //     $totalPercentage = $productsWithPositivePercentage->sum(fn($p) => (float)$p['cost_percentage']);
    //     $adjustmentFactor = 100 / $totalPercentage;
    //     foreach ($this->selectedProducts as $index => $product) {
    //         $percentage = (float)$product['cost_percentage'];

    //         if ($percentage > 0) {
    //             $adjustedPercentage = $percentage * $adjustmentFactor;
    //             $productCost = $this->totalManufacturingCost * ($adjustedPercentage / 100);

    //             $this->selectedProducts[$index]['total_cost'] = $productCost;

    //             $quantity = (float)$product['quantity'];
    //             if ($quantity > 0) {
    //                 $this->selectedProducts[$index]['unit_cost'] = $productCost / $quantity;
    //             } else {
    //                 $this->selectedProducts[$index]['unit_cost'] = 0;
    //             }
    //         } else {
    //             $this->selectedProducts[$index]['total_cost'] = 0;
    //             $this->selectedProducts[$index]['unit_cost'] = 0;
    //         }
    //     }
    // }


    public function openSaveTemplateModal()
    {
        $this->showSaveTemplateModal = true;
    }

    // إغلاق مودال الحفظ
    public function closeSaveTemplateModal()
    {
        $this->showSaveTemplateModal = false;
        $this->templateName = '';
    }

    // فتح مودال التحميل
    public function openLoadTemplateModal()
    {
        // $this->templates = OperationItems::where('pro_type', 63)->get();
        $this->showLoadTemplateModal = true;
    }

    // إغلاق مودال التحميل
    public function closeLoadTemplateModal()
    {
        $this->showLoadTemplateModal = false;
        $this->selectedTemplate = null;
    }

    // حفظ النموذج
    // public function saveAsTemplate()
    // {
    //     $this->validate(['templateName' => 'required|min:1']);
    //     dd($this->all());

    //     $operation = OperHead::create([

    //         'pro_id' => '',
    //         'is_stock	' => '',
    //         'is_journal' => '',
    //         'is_manager' => '',
    //         'info' => '',
    //         'pro_date' => '',
    //         'store_id' => '',
    //         'emp_id' => '',
    //         'acc1' => '',
    //         'acc2' => '',
    //         'pro_value' => '',
    //         'fat_net' => '',
    //         'op2' => '',
    //         'user' => '',
    //         'pro_type' => '',

    //     ]);

    //     foreach ($this->selectedProducts as $index => $product) {
    //         OperationItems::create([

    //             'pro_tybe' => 63,
    //             'pro_id' => $this->nextProId,
    //             'item_id' => $product['product_id'],
    //             'notes' =>$this->templateName,
    //             'unit_id' => '',
    //             'detail_store' => '',
    //             'pro_id' => '',
    //             'is_stock	' => '',
    //             'item_price' => '',

    //             'fat_price' => '',
    //             'fat_quantity' => '',
    //             'item_price' => '',
    //             'cost_price' => '',
    //             'item_discount' => '',
    //             'additional' => '',
    //             '' => '',
    //             '' => '',
    //             'quantity' => $product['quantity'],
    //             'cost_price' => $product['unit_cost'],
    //             'total_cost' => $product['total_cost'],
    //             // 'cost_percentage' => $product['cost_percentage'],
    //             'user_modified_percentage' => $product['user_modified_percentage']
    //         ]);
    //     }

    //     foreach ($this->selectedRawMaterials as $index => $raw) {
    //         OperationItems::create([

    //             'pro_tybe' => 63,
    //             'pro_id' => $this->nextProId,
    //             'item_id' => $raw['product_id'],
    //             'unit_id' => '',
    //             'detail_store' => '',
    //             'pro_id' => '',
    //             'is_stock	' => '',
    //             'item_price' => '',

    //             'fat_price' => '',
    //             'fat_quantity' => '',
    //             'item_price' => '',
    //             'cost_price' => '',
    //             'item_discount' => '',
    //             'additional' => '',
    //             '' => '',
    //             '' => '',
    //             'quantity' => $raw['quantity'],
    //             'cost_price' => $raw['unit_cost'],
    //             'total_cost' => $raw['total_cost'],

    //         ]);
    //     }

    //     // \App\Models\ManufacturingTemplate::create([
    //     //     'name' => $this->templateName,
    //     //     'operating_account' => $this->OperatingAccount,
    //     //     'employee_id' => $this->employee,
    //     //     'description' => $this->description,

    //     //     'products' => json_encode($this->selectedProducts),
    //     //     'raw_materials' => json_encode($this->selectedRawMaterials),
    //     //     'expenses' => json_encode($this->additionalExpenses)
    //     // ]);

    //     $this->closeSaveTemplateModal();
    //     session()->flash('message', 'تم حفظ النموذج بنجاح!');
    // }

    // تحميل النموذج
    public function loadTemplate()
    {
        // if ($template = \App\Models\ManufacturingTemplate::find($this->selectedTemplate)) {
        //     $this->OperatingAccount = $template->operating_account;
        //     $this->employee = $template->employee_id;
        //     $this->description = $template->description;

        //     $this->selectedProducts = json_decode($template->products, true);
        //     $this->selectedRawMaterials = json_decode($template->raw_materials, true);
        //     $this->additionalExpenses = json_decode($template->expenses, true);

        //     $this->closeLoadTemplateModal();
        // }
    }


    public function saveInvoice()
    {
        // dd($this->all());
        $service = new ManufacturingInvoiceService();
        return $service->saveManufacturingInvoice($this);
    }

    public function render()
    {
        return view('livewire.invoices.manufacturing-invoice');
    }
}
