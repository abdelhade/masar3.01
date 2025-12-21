<?php

namespace Modules\Manufacturing\Livewire;

use App\Models\Expense;
use App\Models\Item;
use App\Models\OperationItems;
use App\Models\OperHead;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\Accounts\Models\AccHead;
use Modules\Manufacturing\Services\ManufacturingInvoiceService;

class ManufacturingInvoice extends Component
{
    public $showSaveTemplateModal = false;

    public $showLoadTemplateModal = false;

    public $templateName = '';

    public $templates = [];

    public $selectedTemplate = null;

    public $templateExpectedTime = '';

    public $actualTime = '';

    public $quantityMultiplier = 1;

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

    public $branch_id;

    public $branches;

    public $order_id;

    public $stage_id;

    public $productAccount = '';

    public $Stors = [];

    public $OperatingCenter;

    public $patchNumber;

    public $employee;

    public $employeeList = [];

    public $activeTab = 'general_chat';

    public $isSaving = false;

    // protected $rules = [
    //     'invoiceDate' => 'required|date',
    //     'rawAccount' => 'required',
    //     'productAccount' => 'required',
    //     'OperatingAccount' => 'required',
    //     'employee' => 'required',

    //     'selectedProducts.*.product_id' => 'required',
    //     'selectedProducts.*.quantity' => 'required|numeric|min:0.01',
    //     'selectedProducts.*.unit_cost' => 'required|numeric|min:0',
    //     'selectedProducts.*.cost_percentage' => 'required|numeric|min:0|max:100',

    //     'selectedRawMaterials.*.item_id' => 'required',
    //     'selectedRawMaterials.*.quantity' => 'required|numeric|min:0.01',
    //     'selectedRawMaterials.*.unit_cost' => 'required|numeric|min:0',

    //     'additionalExpenses.*.description' => 'required|string|min:3',
    //     'additionalExpenses.*.amount' => 'required|numeric|min:0',
    // ];

    // protected $messages = [
    //     'selectedProducts.*.cost_percentage.max' => 'النسبة يجب ألا تزيد عن 100%',
    //     'selectedProducts.*.cost_percentage.min' => 'النسبة يجب ألا تقل عن 0%',
    //     'required' => 'هذا الحقل مطلوب',
    //     'min' => 'القيمة أقل من المسموح',
    //     'numeric' => 'يجب إدخال قيمة رقمية',
    //     'employee.required' => 'يجب اختيار الموظف',

    // ];

    public function mount()
    {
        $this->order_id = request()->query('order_id');
        $this->stage_id = request()->query('stage_id');

        $this->branches = userBranches();
        if ($this->branches->isNotEmpty()) {
            $this->branch_id = $this->branches->first()->id;
        }

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

        // Auto-load template if coming from manufacturing order stage
        if ($this->order_id && $this->stage_id) {
            $this->autoLoadTemplateFromOrder();
        }
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

    // تم نقل جميع methods التنقل بالأسهم إلى Alpine.js للسرعة

    public function loadProductsAndMaterials()
    {
        $this->productsList = collect();
        $this->rawMaterialsList = [];
    }

    public function updatedProductSearchTerm($value)
    {
        $this->productSelectedResultIndex = -1;
        $this->productSearchResults = collect();

        if (empty(trim($value)) || strlen(trim($value)) < 2) {
            return;
        }

        $searchTerm = trim($value);

        // استخدام cache للنتائج المتكررة
        $cacheKey = 'product_search_'.md5($searchTerm);

        $this->productSearchResults = cache()->remember($cacheKey, 60, function () use ($searchTerm) {
            // البحث المحسّن بدون joins غير ضرورية
            $itemIdsFromBarcode = \App\Models\Barcode::where('barcode', $searchTerm)
                ->orWhere('barcode', 'like', $searchTerm.'%')
                ->where('isdeleted', 0)
                ->limit(5)
                ->pluck('item_id')
                ->toArray();

            return Item::select('id', 'name', 'average_cost')
                ->where(function ($query) use ($searchTerm, $itemIdsFromBarcode) {
                    $query->where('name', 'like', $searchTerm.'%')
                        ->orWhere('name', 'like', '% '.$searchTerm.'%');
                    if (! empty($itemIdsFromBarcode)) {
                        $query->orWhereIn('id', $itemIdsFromBarcode);
                    }
                })
                ->limit(10)
                ->get();
        });
    }

    public function updatedRawMaterialSearchTerm($value)
    {
        $this->rawMaterialSelectedResultIndex = -1;
        $this->rawMaterialSearchResults = collect();

        if (empty(trim($value)) || strlen(trim($value)) < 2) {
            return;
        }

        $searchTerm = trim($value);

        // استخدام cache للنتائج المتكررة
        $cacheKey = 'raw_material_search_'.md5($searchTerm);

        $this->rawMaterialSearchResults = cache()->remember($cacheKey, 60, function () use ($searchTerm) {
            $itemIdsFromBarcode = \App\Models\Barcode::where('barcode', $searchTerm)
                ->orWhere('barcode', 'like', $searchTerm.'%')
                ->where('isdeleted', 0)
                ->limit(5)
                ->pluck('item_id')
                ->toArray();

            return Item::select('id', 'name', 'average_cost')
                ->where(function ($query) use ($searchTerm, $itemIdsFromBarcode) {
                    $query->where('name', 'like', $searchTerm.'%')
                        ->orWhere('name', 'like', '% '.$searchTerm.'%');
                    if (! empty($itemIdsFromBarcode)) {
                        $query->orWhereIn('id', $itemIdsFromBarcode);
                    }
                })
                ->limit(10)
                ->get();
        });
    }

    public function addProductFromSearch($itemId)
    {
        $item = Item::with(['units' => fn ($q) => $q->orderBy('pivot_u_val'), 'prices', 'units'])->find($itemId);
        if (! $item) {
            return;
        }

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
            // لا نحسب التكلفة تلقائياً - فقط عند توزيع التكاليف

            $this->productSearchTerm = '';
            $this->productSearchResults = collect();
            $this->productSelectedResultIndex = -1;

            $this->distributePercentagesEqually();
            $this->calculateTotals();

            $this->dispatch('focusProductQuantity', $existingProductIndex);

            return;
        }

        $price = $item->prices->first()->pivot->price ?? 0;
        $averageCost = $item->average_cost ?? 0;

        $this->selectedProducts[] = [
            'id' => uniqid(),
            'product_id' => $item->id,
            'name' => $item->name,
            'quantity' => 1,
            'unit_cost' => $averageCost, // سعر التكلفة الأخير
            'total_cost' => $averageCost, // إجمالي بناءً على السعر
            'average_cost' => $averageCost, // سعر التكلفة الأخير
            'cost_percentage' => 0,
            'user_modified_percentage' => false,
        ];
        $this->productSearchTerm = '';
        $this->productSearchResults = collect();
        $this->productSelectedResultIndex = -1;
        $this->distributePercentagesEqually();
        $this->calculateTotals();

        $this->dispatch('focusProductQuantity', count($this->selectedProducts) - 1);
    }

    public function removeProduct($index)
    {
        unset($this->selectedProducts[$index]);
        $this->selectedProducts = array_values($this->selectedProducts);
        $this->distributePercentagesEqually(); // تحديث النسب المئوية بعد الحذف
        $this->calculateTotals();
    }

    // تم نقل هذه الدالة إلى client-side باستخدام Alpine.js
    // public function updateProductTotal($index) - MOVED TO CLIENT-SIDE

    // تم نقل معظم هذه العمليات إلى client-side باستخدام Alpine.js
    // public function updatedSelectedProducts - MOSTLY MOVED TO CLIENT-SIDE

    public function addRawMaterialFromSearch($itemId)
    {
        $item = Item::with('units')->find($itemId);
        if (! $item) {
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
                'available_qty' => $unit->pivot->u_val ?? 0,
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
            'base_cost' => $averageCost, // ✅ تخزين التكلفة الأساسية (للوحدة الصغرى)
            'average_cost' => round($averageCost, 2), // ✅ متوسط التكلفة من Item (لا يتم تعديله)
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

    // private function convertToNumber($propertyName)
    // {
    //     $parts = explode('.', $propertyName);
    //     if (count($parts) < 3) return;

    //     $index = $parts[1];
    //     $field = $parts[2];
    //     $type = $parts[0];

    //     if ($type === 'selectedProducts' && isset($this->selectedProducts[$index][$field])) {
    //         $this->selectedProducts[$index][$field] = (float)$this->selectedProducts[$index][$field];
    //     }

    //     if ($type === 'selectedRawMaterials' && isset($this->selectedRawMaterials[$index][$field])) {
    //         $this->selectedRawMaterials[$index][$field] = (float)$this->selectedRawMaterials[$index][$field];
    //     }

    //     if ($type === 'additionalExpenses' && isset($this->additionalExpenses[$index][$field])) {
    //         $this->additionalExpenses[$index][$field] = (float)$this->additionalExpenses[$index][$field];
    //     }
    // }

    // تم نقل هذه الدالة إلى client-side باستخدام Alpine.js للسرعة
    // public function adjustCostsByPercentage() - MOVED TO CLIENT-SIDE

    // تم نقل هذه الدالة إلى client-side باستخدام Alpine.js
    // private function updateRawMaterialTotal($index) - MOVED TO CLIENT-SIDE

    // تم تحسين هذه الدالة - تستدعى فقط عند الحفظ أو التحديثات المهمة
    public function calculateTotals()
    {
        // حساب سريع للإجماليات فقط عند الحاجة
        $this->totalProductsCost = collect($this->selectedProducts)
            ->sum(fn ($item) => (float) ($item['total_cost'] ?? 0));

        $this->totalRawMaterialsCost = collect($this->selectedRawMaterials)
            ->sum(fn ($item) => (float) ($item['total_cost'] ?? 0));

        $this->totalAdditionalExpenses = collect($this->additionalExpenses)
            ->sum(fn ($item) => (float) ($item['amount'] ?? 0));

        $this->totalManufacturingCost = $this->totalRawMaterialsCost + $this->totalAdditionalExpenses;

        $totalProductQuantity = collect($this->selectedProducts)
            ->sum(fn ($item) => (float) ($item['quantity'] ?? 0));

        $this->unitCostPerProduct = $totalProductQuantity > 0 ?
            $this->totalManufacturingCost / $totalProductQuantity : 0;
    }

    public function addExpense()
    {
        $this->additionalExpenses[] = [
            'description' => '',
            'amount' => 0,
        ];
        $this->calculateTotalsAndDistribute();
    }

    public function removeExpense($index)
    {
        unset($this->additionalExpenses[$index]);
        $this->additionalExpenses = array_values($this->additionalExpenses);
        $this->calculateTotalsAndDistribute();
        $this->calculateTotals();
    }

    public function calculateTotalsAndDistribute()
    {
        $this->calculateTotals();
        $totalExpenses = collect($this->additionalExpenses)->sum(function ($expense) {
            return is_numeric($expense['amount']) ? $expense['amount'] : 0;
        });
        $this->totalManufacturingCost = $this->totalRawMaterialsCost + $totalExpenses;
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
        } catch (\Exception $e) {
            $this->dispatch('error-swal', [
                'title' => 'خطأ!',
                'text' => 'حدث خطأ أثناء تحميل النماذج: '.$e->getMessage(),
                'icon' => 'error',
            ]);
        }
    }

    // تم نقل هذه الدالة إلى client-side باستخدام Alpine.js للسرعة
    // public function applyQuantityMultiplier() - MOVED TO CLIENT-SIDE

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

            // توزيع النسب تلقائياً إذا لم تكن محددة
            $this->distributePercentagesEqually();
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

    /**
     * Auto-load manufacturing template when creating invoice from a production stage
     */
    private function autoLoadTemplateFromOrder()
    {
        try {
            // Get the manufacturing order
            $order = \Modules\Manufacturing\Models\ManufacturingOrder::find($this->order_id);
            if (! $order || ! $order->item_id) {
                return;
            }

            // Find a template for this item
            // Templates are OperHead records with pro_type=63 and is_manager=1
            // We look for templates that have the same item in their operation items
            $template = OperHead::where('pro_type', 63)
                ->where('is_manager', 1)
                ->whereHas('operationItems', function ($query) use ($order) {
                    $query->where('item_id', $order->item_id);
                })
                ->orderBy('created_at', 'desc')
                ->first();

            if ($template) {
                // Set the selected template and load it
                $this->selectedTemplate = $template->id;
                $this->loadTemplate();

                // Dispatch success message
                $this->dispatch('success-swal', [
                    'title' => 'تم تحميل النموذج!',
                    'text' => 'تم تحميل نموذج التصنيع تلقائياً بناءً على الصنف في أمر الإنتاج.',
                    'icon' => 'success',
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail - if no template found, user can create invoice manually
            \Log::info('Could not auto-load template: '.$e->getMessage());
        }
    }

    private function isProduct($item)
    {
        // المنتجات: لها item_id وليست مصروفات ولا تحتوي على unit_id
        return ! is_null($item->item_id) &&
            is_null($item->unit_id) &&
            $item->fat_tax != 999 &&
            $item->detail_store == $this->productAccount;
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

    // دالة للتحقق من نوع العنصر
    // private function isProduct($item)
    // {
    //     // تحقق من نوع العنصر - قد تحتاج لتعديل هذه الطريقة حسب نظامك
    //     // يمكنك استخدام حقل في OperationItems أو التحقق من جدول Item
    //     $itemRecord = Item::find($item->item_id);

    //     // مثال: إذا كان للمنتجات نوع معين في جدول Item
    //     return $itemRecord && $itemRecord->type == 'product';

    //     // أو يمكنك استخدام طريقة أخرى مثل:
    //     // return $item->is_product == 1; // إذا كان لديك حقل في OperationItems
    // }

    // private function loadExpenseFromTemplate($item)
    // {
    //     try {
    //         // استخراج الوصف من الملاحظات
    //         $description = '';
    //         if ($item->notes) {
    //             $description = $item->notes;
    //             // إزالة النصوص الثابتة
    //             $description = str_replace('مصروف إضافي: ', '', $description);
    //             $description = preg_replace('/ - نموذج:.*$/', '', $description);
    //         }

    //         // استخدام cost_price أو item_price حسب المتاح
    //         $amount = floatval($item->cost_price ?? $item->item_price ?? 0);

    //         if ($amount > 0) {
    //             $this->additionalExpenses[] = [
    //                 'amount' => $amount,
    //                 'account_id' => $item->detail_store ?? $this->expenseAccount,
    //                 'description' => trim($description)
    //             ];
    //         }
    //     } catch (\Exception $e) {
    //         // تسجيل الخطأ للتتبع
    //         // \Log::error('Error loading expense from template: ' . $e->getMessage(), [
    //         //     'item_id' => $item->id ?? null,
    //         //     'cost_price' => $item->cost_price ?? null,
    //         //     'item_price' => $item->item_price ?? null
    //         // ]);
    //     }
    // }

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

        // تحديث أسعار المنتجات باستخدام average_cost
        foreach ($this->selectedProducts as $index => $product) {
            $item = $productsMap[$product['product_id']] ?? null;
            if ($item) {
                $currentAverageCost = $item->average_cost ?? 0;
                $this->selectedProducts[$index]['average_cost'] = $currentAverageCost;
                // استخدام متوسط التكلفة في الحساب
                $this->selectedProducts[$index]['total_cost'] = $currentAverageCost * $product['quantity'];
            }
        }

        // ✅ تحديث أسعار المواد الخام باستخدام average_cost
        foreach ($this->selectedRawMaterials as $index => $rawMaterial) {
            $item = $rawMaterialsMap[$rawMaterial['item_id']] ?? null;
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
                                'available_qty' => $unit->pivot->u_val ?? 0,
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
            'user' => Auth::user()->id,
            'pro_type' => 63,
        ]);

        foreach ($this->selectedProducts as $product) {
            OperationItems::create([
                'pro_tybe' => 63,
                'pro_id' => $this->nextProId,
                'item_id' => $product['product_id'],
                'notes' => 'نموذج تصنيع '.$this->templateName,
                'detail_store' => $this->productAccount,
                'pro_id' => $operation->id,
                'is_stock' => 1,
                'additional' => $product['cost_percentage'],
                'fat_price' => $this->totalProductsCost,
                'item_price' => $product['average_cost'],
                'fat_quantity' => $product['quantity'],
                'cost_price' => $product['unit_cost'] ?? $product['average_cost'] ?? 0,
                'total_cost' => $product['total_cost'],
            ]);
        }

        foreach ($this->selectedRawMaterials as $raw) {
            OperationItems::create([
                'pro_tybe' => 63,
                'pro_id' => $this->nextProId,
                'item_id' => $raw['item_id'],
                'notes' => 'نموذج تصنيع '.$this->templateName,
                'unit_id' => $raw['unit_id'],
                'detail_store' => $this->productAccount,
                'pro_id' => $operation->id,
                'is_stock' => 1,
                // 'additional' => $raw['cost_percentage'],
                'item_price' => $raw['average_cost'],
                'fat_price' => $this->totalRawMaterialsCost,
                'fat_quantity' => $raw['quantity'],
                'cost_price' => $raw['unit_cost'] ?? $raw['average_cost'] ?? 0,
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
        session()->flash('message', 'تم حفظ النموذج بنجاح!');
    }

    // Method للـ sync من Alpine.js
    public function syncFromAlpine($products, $rawMaterials, $expenses, $totals)
    {
        $this->selectedProducts = $products;
        $this->selectedRawMaterials = $rawMaterials;
        $this->additionalExpenses = $expenses;

        // تحديث الإجماليات
        $this->totalProductsCost = $totals['totalProductsCost'] ?? 0;
        $this->totalRawMaterialsCost = $totals['totalRawMaterialsCost'] ?? 0;
        $this->totalAdditionalExpenses = $totals['totalExpenses'] ?? 0;
        $this->totalManufacturingCost = $totals['totalManufacturingCost'] ?? 0;
    }

    // توزيع النسب بالتساوي تلقائياً - فقط إذا لم تكن محددة
    public function distributePercentagesEqually()
    {
        $count = count($this->selectedProducts);
        if ($count === 0) {
            return;
        }

        // تحقق من وجود نسب محددة مسبقاً
        $hasExistingPercentages = false;
        foreach ($this->selectedProducts as $product) {
            if (($product['cost_percentage'] ?? 0) > 0) {
                $hasExistingPercentages = true;
                break;
            }
        }

        // إذا كانت هناك نسب محددة، لا تعيد توزيعها
        if ($hasExistingPercentages) {
            return;
        }

        $percentage = round(100 / $count, 2);
        $remainder = round(100 - ($percentage * $count), 2);

        foreach ($this->selectedProducts as $index => &$product) {
            $product['cost_percentage'] = $percentage;
            // إضافة الباقي للمنتج الأول
            if ($index === 0 && $remainder != 0) {
                $product['cost_percentage'] = round($percentage + $remainder, 2);
            }
        }
    }

    // توزيع التكاليف حسب النسب المئوية
    public function distributeCostsByPercentage()
    {
        if (count($this->selectedProducts) === 0) {
            $this->dispatch('error-swal', [
                'title' => 'خطأ!',
                'text' => 'لا توجد منتجات لتوزيع التكلفة عليها',
                'icon' => 'error',
            ]);

            return;
        }

        // إجمالي التكاليف (مواد خام + مصاريف)
        $totalCost = $this->totalManufacturingCost;

        foreach ($this->selectedProducts as $index => &$product) {
            $percentage = (float) ($product['cost_percentage'] ?? 0);
            $quantity = (float) ($product['quantity'] ?? 1);

            if ($percentage > 0 && $quantity > 0) {
                // حساب التكلفة المخصصة لهذا المنتج
                $allocatedCost = ($totalCost * $percentage) / 100;

                // حساب تكلفة الوحدة
                $unitCost = $allocatedCost / $quantity;

                $product['average_cost'] = round($unitCost, 2);
                $product['unit_cost'] = round($unitCost, 2);
                $product['total_cost'] = round($allocatedCost, 2);
            }
        }

        $this->calculateTotals();
        $this->dispatch('success-swal', [
            'title' => 'تم!',
            'text' => 'تم توزيع التكاليف بنجاح',
            'icon' => 'success',
        ]);
    }

    // Method لتحديث المواد الخام عند تغيير القيم
    public function updatedSelectedRawMaterials($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) !== 2) {
            return;
        }

        $index = (int) $parts[0];
        $field = $parts[1];

        if (! isset($this->selectedRawMaterials[$index])) {
            return;
        }

        if ($field === 'quantity' || $field === 'average_cost') {
            $this->updateRawMaterialTotal($index);
            $this->calculateTotals();
        }
    }

    // Method لتحديث المنتجات عند تغيير القيم
    public function updatedSelectedProducts($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) !== 2) {
            return;
        }

        $index = (int) $parts[0];
        $field = $parts[1];

        if (! isset($this->selectedProducts[$index])) {
            return;
        }

        // فقط حساب الإجماليات بدون تغيير التكاليف
        if ($field === 'quantity') {
            $this->calculateTotals();
        }
    }

    // Method لحساب إجمالي المنتج - فقط عند توزيع التكاليف
    private function updateProductTotal($index)
    {
        if (! isset($this->selectedProducts[$index])) {
            return;
        }

        $product = &$this->selectedProducts[$index];
        $quantity = (float) ($product['quantity'] ?? 0);
        $averageCost = (float) ($product['average_cost'] ?? 0);

        // فقط عند توزيع التكاليف يتم حساب الإجمالي
        if ($averageCost > 0) {
            $product['total_cost'] = round($quantity * $averageCost, 2);
        }
    }

    // Method لتحديث المصروفات
    public function updatedAdditionalExpenses($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) !== 2) {
            return;
        }

        $index = (int) $parts[0];
        $field = $parts[1];

        if ($field === 'amount') {
            $this->calculateTotals();
        }
    }

    // Method مطلوبة لحساب إجمالي المواد الخام
    private function updateRawMaterialTotal($index)
    {
        if (! isset($this->selectedRawMaterials[$index])) {
            return;
        }

        $material = &$this->selectedRawMaterials[$index];
        $quantity = (float) ($material['quantity'] ?? 0);
        $averageCost = (float) ($material['average_cost'] ?? 0);

        $material['total_cost'] = round($quantity * $averageCost, 2);
    }

    // البحث السريع للمنتجات
    public function searchProducts($term)
    {
        try {
            if (strlen(trim($term)) < 1) {
                return [];
            }

            $searchTerm = trim($term);

            $results = Item::select('id', 'name', 'average_cost', 'code')
                ->where(function ($query) use ($searchTerm) {
                    $query->where('name', 'like', '%'.$searchTerm.'%')
                        ->orWhere('code', 'like', '%'.$searchTerm.'%');
                })
                ->limit(10)
                ->get();

            return $results->toArray();
        } catch (\Exception $e) {
            \Log::error('Product search error: '.$e->getMessage());

            return [];
        }
    }

    // البحث السريع للمواد الخام
    public function searchRawMaterials($term)
    {
        try {
            if (strlen(trim($term)) < 1) {
                return [];
            }

            $searchTerm = trim($term);

            $results = Item::select('id', 'name', 'average_cost', 'code')
                ->where(function ($query) use ($searchTerm) {
                    $query->where('name', 'like', '%'.$searchTerm.'%')
                        ->orWhere('code', 'like', '%'.$searchTerm.'%');
                })
                ->limit(10)
                ->get();

            return $results->toArray();
        } catch (\Exception $e) {
            \Log::error('Raw material search error: '.$e->getMessage());

            return [];
        }
    }

    public function saveInvoice()
    {
        // منع الحفظ المتكرر
        if ($this->isSaving) {
            return;
        }

        $this->isSaving = true;

        try {
            $service = new ManufacturingInvoiceService;
            $result = $service->saveManufacturingInvoice($this);

            // إذا نجح الحفظ، سيتم إرسال success-swal من الـ Service مع reload flag
            // لا نعيد تعيين isSaving هنا لأن الصفحة ستعيد التحميل
            return $result;
        } catch (\Exception $e) {
            $this->isSaving = false;
            $this->dispatch('error-swal', [
                'title' => 'خطأ!',
                'text' => 'حدث خطأ أثناء الحفظ: '.$e->getMessage(),
                'icon' => 'error',
            ]);

            return false;
        }
    }

    // Method مطلوبة لـ Alpine.js
    public function toJSON()
    {
        return json_encode([
            'selectedProducts' => $this->selectedProducts,
            'selectedRawMaterials' => $this->selectedRawMaterials,
            'additionalExpenses' => $this->additionalExpenses,
            'totalRawMaterialsCost' => $this->totalRawMaterialsCost,
            'totalProductsCost' => $this->totalProductsCost,
            'totalAdditionalExpenses' => $this->totalAdditionalExpenses,
            'totalManufacturingCost' => $this->totalManufacturingCost,
        ]);
    }

    public function render()
    {
        return view('manufacturing::livewire.manufacturing-invoice');
    }
}
