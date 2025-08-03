<?php

namespace App\Livewire;

use App\Models\{Item, OperHead, AccHead, OperationItems};
use App\Services\ManufacturingInvoiceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ManufacturingInvoice extends Component
{
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

        $this->templates = collect();
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
        }])
            ->select('id', 'name', 'average_cost') // إضافة average_cost
            ->get();

        $this->rawMaterialsList = Item::select('id', 'name', 'average_cost')->get()->toArray();
    }

    public function updatedProductSearchTerm($value)
    {
        $this->productSelectedResultIndex = -1;
        $this->productSearchResults = strlen($value) < 1
            ? collect()
            : Item::with(['units', 'prices'])
            ->select('id', 'name', 'average_cost') // إضافة average_cost
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

            // تصحيح: التأكد من استخدام average_cost الموجود بالفعل لحساب الإجمالي
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
            'total_cost' => $initialTotalCost, // ✅ تصحيح: استخدام الإجمالي المحسوب من متوسط التكلفة
            'average_cost' => $averageCost, // ✅ إضافة: إضافة متوسط التكلفة للمنتج الجديد
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
        $this->updatePercentages(); // تحديث النسب المئوية بعد الحذف
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
                // وضع علامة أن المستخدم عدل النسبة يدوياً
                $this->selectedProducts[$index]['user_modified_percentage'] = true;
            }
        }
    }

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
        if (!$item) {
            return;
        }

        // التحقق من وجود المادة الخام في القائمة
        $existingMaterialIndex = null;
        foreach ($this->selectedRawMaterials as $index => $material) {
            if ($material['item_id'] === $item->id) {
                $existingMaterialIndex = $index;
                break;
            }
        }

        // لو المادة موجودة، نزود الكمية ونحسب التكلفة الإجمالية
        if ($existingMaterialIndex !== null) {
            $this->selectedRawMaterials[$existingMaterialIndex]['quantity']++;

            // ✅ استخدام average_cost في الحساب
            $this->updateRawMaterialTotal($existingMaterialIndex);

            // إعادة تعيين حقول البحث
            $this->rawMaterialSearchTerm = '';
            $this->rawMaterialSearchResults = collect();
            $this->rawMaterialSelectedResultIndex = -1;

            $this->calculateTotals();
            $this->dispatch('focusRawMaterialQuantity', $existingMaterialIndex);
            return;
        }

        // لو المادة جديدة، نضيفها للقائمة
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

        // ✅ حساب التكلفة الإجمالية الأولية بناءً على average_cost
        $initialTotalCost = round(1 * $averageCost, 2);

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
            'average_cost' => $averageCost // ✅ هذا ما سيُستخدم في الحساب
        ];

        // إعادة تعيين حقول البحث
        $this->rawMaterialSearchTerm = '';
        $this->rawMaterialSearchResults = collect();
        $this->rawMaterialSelectedResultIndex = -1;
        $this->calculateTotals();

        // التركيز على حقل الكمية
        $this->dispatch('focusRawMaterialQuantity', count($this->selectedRawMaterials) - 1);
    }

    public function removeRawMaterial($index)
    {
        unset($this->selectedRawMaterials[$index]);
        $this->selectedRawMaterials = array_values($this->selectedRawMaterials);
        $this->calculateTotals();
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

    public function adjustCostsByPercentage()
    {
        if (empty($this->selectedProducts)) {
            return;
        }
        $totalPercentage = 0;
        foreach ($this->selectedProducts as $product) {
            $totalPercentage += (float)($product['cost_percentage'] ?? 0);
        }
        if ($totalPercentage > 100) {
            $this->dispatch('show-alert', [
                'type' => 'warning',
                'message' => 'إجمالي النسب المئوية يتجاوز 100%. سيتم التوزيع بناءً على النسب الحالية.'
            ]);
        }
        $totalManufacturingCost = $this->totalRawMaterialsCost + $this->totalAdditionalExpenses;

        // توزيع التكلفة حسب النسب المئوية
        foreach ($this->selectedProducts as $index => $product) {
            $percentage = (float)($product['cost_percentage'] ?? 0);
            $quantity = (float)($product['quantity'] ?? 1);

            if ($quantity > 0 && $percentage >= 0) {
                // حساب التكلفة المخصصة لهذا المنتج بناءً على النسبة المئوية
                $allocatedCost = ($totalManufacturingCost * $percentage) / 100;

                // تحديث متوسط التكلفة (average_cost) بناءً على التوزيع الجديد
                $newAverageCost = $allocatedCost / $quantity;
                $this->selectedProducts[$index]['average_cost'] = round($newAverageCost, 2);

                // تحديث unit_cost ليساوي average_cost الجديد
                $this->selectedProducts[$index]['unit_cost'] = round($newAverageCost, 2);

                // إعادة حساب total_cost
                $this->selectedProducts[$index]['total_cost'] = round($quantity * $newAverageCost, 2);

                // وضع علامة أن التكلفة تم تعديلها
                $this->selectedProducts[$index]['user_modified_percentage'] = true;
            }
        }
        // إعادة حساب الإجماليات
        $this->calculateTotals();

        // عرض رسالة نجاح
        $this->dispatch('show-alert', [
            'type' => 'success',
            'message' => 'تم توزيع التكاليف بنجاح حسب النسب المحددة'
        ]);
    }

    public function updatedSelectedRawMaterials($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) !== 2) {
            return;
        }
        $index = $parts[0];
        $field = $parts[1];
        if (!isset($this->selectedRawMaterials[$index])) {
            return;
        }

        if ($field === 'unit_id') {
            $unitId = $value;
            $unit = collect($this->selectedRawMaterials[$index]['unitsList'])
                ->firstWhere('id', $unitId);
            if ($unit) {
                // ✅ تحديث unit_cost و available_quantity عند تغيير الوحدة
                $this->selectedRawMaterials[$index]['unit_cost'] = round($unit['cost'] ?? 0, 2);
                $this->selectedRawMaterials[$index]['available_quantity'] = $unit['available_qty'] ?? 0;

                // ✅ إعادة حساب التكلفة الإجمالية فوراً
                $this->updateRawMaterialTotal($index);
            }
        }

        if ($field === 'quantity') {
            // ✅ تحديث الكمية وإعادة حساب التكلفة الإجمالية
            $quantity = (float)$value;
            if ($quantity < 0) {
                $quantity = 0;
            }
            $this->selectedRawMaterials[$index]['quantity'] = $quantity;
            // ✅ إعادة حساب التكلفة الإجمالية بناءً على الكمية الجديدة
            $this->updateRawMaterialTotal($index);
        }

        if ($field === 'average_cost') {
            $averageCost = (float)$value;
            $this->selectedRawMaterials[$index]['average_cost'] = $averageCost;
            $this->updateRawMaterialTotal($index);
        }

        // ✅ إعادة حساب الإجماليات في النهاية
        $this->calculateTotals();
    }
    private function updateRawMaterialTotal($index)
    {
        $totalRowCost = $this->selectedRawMaterials[$index]['average_cost'] * $this->selectedRawMaterials[$index]['quantity'];
        $this->selectedRawMaterials[$index]['total_cost'] = $totalRowCost;
    }

    public function calculateTotals()
    {
        // حساب إجمالي تكلفة المنتجات
        $this->totalProductsCost = collect($this->selectedProducts)
            ->sum(fn($item) => is_numeric($item['total_cost']) ? (float)$item['total_cost'] : 0);

        // ✅ حساب إجمالي تكلفة المواد الخام من total_cost المحسوبة
        $this->totalRawMaterialsCost = collect($this->selectedRawMaterials)
            ->sum(fn($item) => is_numeric($item['total_cost']) ? (float)$item['total_cost'] : 0);

        // حساب إجمالي المصروفات الإضافية
        $this->totalAdditionalExpenses = collect($this->additionalExpenses)
            ->sum(fn($item) => is_numeric($item['amount']) ? (float)$item['amount'] : 0);

        // حساب إجمالي تكلفة التصنيع
        $this->totalManufacturingCost = $this->totalRawMaterialsCost + $this->totalAdditionalExpenses;

        // حساب تكلفة الوحدة لكل منتج
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

    public function openSaveTemplateModal()
    {
        $this->showSaveTemplateModal = true;
    }

    public function closeSaveTemplateModal()
    {
        $this->showSaveTemplateModal = false;
        $this->templateName = '';
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
                        'emp_id' => $template->emp_id
                    ];
                })
                ->toArray();
            $this->showLoadTemplateModal = true;
            $this->dispatch('templates-loaded', count($this->templates));
        } catch (\Exception $e) {
            $this->dispatch('error-swal', [
                'title' => 'خطأ!',
                'text' => 'حدث خطأ أثناء تحميل النماذج: ' . $e->getMessage(),
                'icon' => 'error'
            ]);
        }
    }

    public function loadTemplate()
    {
        if (!$this->selectedTemplate || empty($this->selectedTemplate)) {
            $this->dispatch('error-swal', [
                'title' => 'خطأ!',
                'text' => 'يرجى اختيار نموذج أولاً.',
                'icon' => 'error'
            ]);
            return;
        }

        try {
            $template = OperHead::find($this->selectedTemplate);
            if (!$template) {
                $this->dispatch('error-swal', [
                    'title' => 'خطأ!',
                    'text' => 'النموذج غير موجود.',
                    'icon' => 'error'
                ]);
                return;
            }

            $templateItems = OperationItems::where('pro_id', $template->id)->get();

            if ($templateItems->isEmpty()) {
                $this->dispatch('error-swal', [
                    'title' => 'تحذير!',
                    'text' => 'النموذج فارغ أو لا يحتوي على عناصر.',
                    'icon' => 'warning'
                ]);
                return;
            }

            // إعادة تعيين البيانات الحالية
            $this->selectedProducts = [];
            $this->selectedRawMaterials = [];
            $this->additionalExpenses = [];

            // تحميل العناصر
            foreach ($templateItems as $item) {
                if ($this->isExpense($item)) {
                    // تحميل المصروفات الإضافية
                    $this->loadExpenseFromTemplate($item);
                } elseif ($this->isProduct($item)) {
                    $this->loadProductFromTemplate($item);
                } else {
                    $this->loadRawMaterialFromTemplate($item);
                }
            }

            // تحديث الأسعار الحالية للمنتجات والمواد الخام
            $this->updateCurrentPrices();

            // تحميل المصروفات من حقل details إذا كانت موجودة
            if ($template->details) {
                $templateData = json_decode($template->details, true);
                if (isset($templateData['additional_expenses']) && is_array($templateData['additional_expenses'])) {
                    // دمج المصروفات من details مع المصروفات المحملة من OperationItems
                    foreach ($templateData['additional_expenses'] as $expense) {
                        if (isset($expense['amount']) && $expense['amount'] > 0) {
                            $this->additionalExpenses[] = [
                                'amount' => $expense['amount'],
                                'account_id' => $expense['account_id'] ?? $this->expenseAccount,
                                'description' => $expense['description'] ?? ''
                            ];
                        }
                    }
                }
            }

            // تحديث البيانات الأساسية
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

            $this->dispatch('success', title: 'تم !', text: 'تم تحميل النموذج بنجاح.', icon: 'success');
        } catch (\Exception $e) {
            $this->dispatch('error-swal', [
                'title' => 'خطأ!',
                'text' => 'حدث خطأ أثناء تحميل النموذج: ' . $e->getMessage(),
                'icon' => 'error'
            ]);
        }
    }

    private function isProduct($item)
    {
        // المنتجات: لها item_id وليست مصروفات ولا تحتوي على unit_id
        return !is_null($item->item_id) &&
            is_null($item->unit_id) &&
            $item->fat_tax != 999 &&
            $item->detail_store == $this->productAccount;
    }

    private function isExpense($item)
    {
        // المصروفات: fat_tax = 999 أو item_id = 0 أو null
        return ($item->fat_tax == 999) ||
            (is_null($item->item_id) || $item->item_id == 0) &&
            !is_null($item->cost_price) &&
            $item->cost_price > 0;
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

    private function loadProductFromTemplate($item)
    {
        try {
            $product = Item::find($item->item_id);
            if (!$product) return;

            $averageCost = $product->average_cost ?? 0;
            $this->selectedProducts[] = [
                'id' => uniqid(),
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $item->quantity ?? 1,
                'unit_cost' => $item->cost_price ?? 0,
                'average_cost' => $averageCost, // إضافة متوسط التكلفة
                'total_cost' => ($item->quantity ?? 1) * $averageCost,
            ];
        } catch (\Exception $e) {
            $this->dispatch('erorr', title: 'خطأ', text: 'حدث خطا اثناء نحميل المنتجات.', icon: 'erorr');
            return null;
        }
    }

    private function loadRawMaterialFromTemplate($item)
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
                        'id' => 1, // افتراضي
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
                'quantity' => $item->quantity ?? 1,
                'unit_id' => $selectedUnitId,
                'unit_cost' => $item->cost_price ?? 0,
                'available_quantity' => $this->getAvailableQuantity($rawMaterial->id, $selectedUnitId),
                'total_cost' => $item->total_cost ?? 0,
                'unitsList' => $unitsList,
                'average_cost' => $rawMaterial->average_cost ?? 0  // إضافة هذا السطر

            ];
        } catch (\Exception $e) {
            $this->dispatch('erorr', title: 'خطأ', text: 'حدث خطا اثناء نحميل المواد الخام.', icon: 'erorr');
            return null;
        }
    }

    private function loadExpenseFromTemplate($item)
    {
        try {
            // استخراج الوصف من الملاحظات
            $description = '';
            if ($item->notes) {
                $description = $item->notes;
                // إزالة النصوص الثابتة
                $description = str_replace('مصروف إضافي: ', '', $description);
                $description = preg_replace('/ - نموذج:.*$/', '', $description);
            }

            // استخدام cost_price أو item_price حسب المتاح
            $amount = floatval($item->cost_price ?? $item->item_price ?? 0);

            if ($amount > 0) {
                $this->additionalExpenses[] = [
                    'amount' => $amount,
                    'account_id' => $item->detail_store ?? $this->expenseAccount,
                    'description' => trim($description)
                ];
            }
        } catch (\Exception $e) {
            // تسجيل الخطأ للتتبع
            // \Log::error('Error loading expense from template: ' . $e->getMessage(), [
            //     'item_id' => $item->id ?? null,
            //     'cost_price' => $item->cost_price ?? null,
            //     'item_price' => $item->item_price ?? null
            // ]);
        }
    }

    private function updateCurrentPrices()
    {
        // تحديث أسعار المنتجات باستخدام average_cost
        foreach ($this->selectedProducts as $index => $product) {
            $item = Item::find($product['product_id']);
            if ($item) {
                $currentAverageCost = $item->average_cost ?? 0;
                $this->selectedProducts[$index]['average_cost'] = $currentAverageCost;
                // استخدام متوسط التكلفة في الحساب
                $this->selectedProducts[$index]['total_cost'] = $currentAverageCost * $product['quantity'];
            }
        }

        // ✅ تحديث أسعار المواد الخام باستخدام average_cost
        foreach ($this->selectedRawMaterials as $index => $rawMaterial) {
            $item = Item::with('units')->find($rawMaterial['item_id']);
            if ($item) {
                $averageCost = $item->average_cost ?? 0;
                $this->selectedRawMaterials[$index]['average_cost'] = $averageCost;

                // ✅ استخدام متوسط التكلفة في الحساب
                $this->selectedRawMaterials[$index]['total_cost'] = $averageCost * $rawMaterial['quantity'];

                if ($rawMaterial['unit_id']) {
                    $unit = $item->units->where('id', $rawMaterial['unit_id'])->first();
                    if ($unit) {
                        $currentQuantity = $unit->pivot->u_val ?? 0;
                        $this->selectedRawMaterials[$index]['available_quantity'] = $currentQuantity;

                        // تحديث قائمة الوحدات
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


    public function closeLoadTemplateModal()
    {
        $this->showLoadTemplateModal = false;
        $this->selectedTemplate = null;
    }

    public function saveAsTemplate()
    {
        $this->validate(['templateName' => 'required|min:1']);
        $operation = OperHead::create([
            'pro_id' => $this->pro_id,
            'is_stock' => 1,
            'is_journal' => 0,
            'is_manager' => 1,
            'info' => $this->templateName,
            'pro_date' => $this->invoiceDate,
            // 'store_id' => '', ??
            'emp_id' => $this->employee,
            'acc1' => $this->productAccount,
            'acc2' => $this->rawAccount,
            'pro_value' => $this->totalManufacturingCost,
            'fat_net' => $this->totalManufacturingCost,
            // 'op2' => '', ??
            'user' => Auth::user()->id,
            'pro_type' => 63,
            'details' => json_encode([
                'template_name' => $this->templateName,
                'description' => $this->description,
                'additional_expenses' => $this->additionalExpenses
            ])
        ]);

        foreach ($this->selectedProducts as  $product) {
            OperationItems::create([
                'pro_tybe' => 63,
                'pro_id' => $this->nextProId,
                'item_id' => $product['product_id'],
                'notes' => 'نموذج تصنيع ' . $this->templateName,
                'detail_store' => $this->productAccount,
                'pro_id' => $operation->id,
                'is_stock' => 1,
                'fat_price' => $this->totalProductsCost,
                'quantity' => $product['quantity'],
                'cost_price' => $product['unit_cost'],
                'total_cost' => $product['total_cost'],
            ]);
        }

        foreach ($this->selectedRawMaterials as $raw) {
            OperationItems::create([
                'pro_tybe' => 63,
                'pro_id' => $this->nextProId,
                'item_id' => $raw['item_id'],
                'notes' => 'نموذج تصنيع ' . $this->templateName,
                'unit_id' => $raw['unit_id'],
                'detail_store' => $this->productAccount,
                'pro_id' => $operation->id,
                'is_stock' => 1,
                'item_price' => $raw['unit_cost'],
                'fat_price' => $this->totalRawMaterialsCost,
                'quantity' => $raw['quantity'],
                'cost_price' => $raw['unit_cost'],
                'total_cost' => $raw['total_cost'],
            ]);
        }

        foreach ($this->additionalExpenses as $expense) {
            if (isset($expense['amount']) && $expense['amount'] > 0) {
                OperationItems::create([
                    'pro_tybe' => 63,
                    'pro_id' => $operation->id,
                    'item_id' => null, // أو 0
                    'notes' => 'مصروف إضافي: ' . ($expense['description'] ?? 'غير محدد') . ' - نموذج: ' . $this->templateName,
                    'detail_store' => $expense['account_id'] ?? $this->expenseAccount,
                    'is_stock' => 0,
                    'item_price' => $expense['amount'],
                    'cost_price' => $expense['amount'],
                    'total_cost' => $expense['amount'],
                    'quantity' => 1,
                    'fat_tax' => 999, // علامة للمصروفات الإضافية
                ]);
            }
        }
        $this->dispatch('success-swal', title: 'تم الحفظ!', text: 'تم حفظ نموذج التصنيع بنجاح.', icon: 'success');

        $this->closeSaveTemplateModal();
        session()->flash('message', 'تم حفظ النموذج بنجاح!');
    }

    public function saveInvoice()
    {
        $service = new ManufacturingInvoiceService();
        return $service->saveManufacturingInvoice($this);
    }

    public function render()
    {
        return view('livewire.invoices.manufacturing-invoice');
    }
}
