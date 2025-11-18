<?php

namespace App\Livewire;

use App\Enums\ItemType;
use Livewire\Component;
use App\Helpers\ItemViewModel;
use Illuminate\Support\Collection;
use App\Services\SaveInvoiceService;
use Modules\Accounts\Models\AccHead;
use App\Livewire\Traits\HandlesExpiryDates;
use App\Livewire\Traits\HandlesInvoiceData;
use Modules\Invoices\Models\InvoiceTemplate;
use App\Models\{OperationItems, Item, Barcode};

class CreateInvoiceForm extends Component
{
    use HandlesInvoiceData, HandlesExpiryDates;
    public $type;
    public $acc1_id;
    public $acc2_id;
    public $emp_id;
    public $pro_date;
    public $accural_date;
    public $pro_id;
    public $serial_number;
    public $barcodeTerm = '';
    public $barcodeSearchResults;
    public $selectedBarcodeResultIndex = -1;
    public bool $addedFromBarcode = false;
    public $searchedTerm = '';

    public $cashClientIds = [];
    public $cashSupplierIds = [];

    public $isCreateNewItemSelected = false;

    public $currentBalance = 0;
    public $balanceAfterInvoice = 0;
    public $showBalance = false;

    public $priceTypes = [];
    public $selectedPriceType = 1;
    public $status = 0;
    public $selectedUnit = [];

    public $searchTerm = '';
    public $searchResults;
    public $selectedResultIndex = -1;
    public int $quantityClickCount = 0;
    public $lastQuantityFieldIndex = null;

    public $acc1List;
    public $acc2List;
    public $employees;
    public $deliverys;
    public $statues = [];
    public $delivery_id = null;
    public $nextProId;
    public $acc1Role;
    public $acc2Role;
    public $cashAccounts;

    public $selectedRowIndex = -1;

    /** @var Collection<int, \App\Models\Item> */
    public $items;

    public $invoiceItems = [];

    public $currentRowIndex = null;
    public $focusField = null;

    public $cash_box_id = '';
    public $received_from_client = 0;
    public $subtotal = 0;
    public $discount_percentage = 0;
    public $discount_value = 0;
    public $additional_percentage = 0;
    public $additional_value = 0;
    public $total_after_additional = 0;
    public $notes = '';
    public $settings = [];

    public $branch_id;
    public $branches;
    public $op2; // parent operation id when creating a child/converted operation

    public $currentSelectedItem = null;
    public $selectedItemData = [
        'name' => '',
        'code' => '',
        'available_quantity' => 0,
        'unit_name' => '',
        'price' => 0,
        'cost' => 0,
        'barcode' => '',
        'category' => '',
        'description' => ''
    ];

    public $recommendedItems = [];

    public $availableTemplates;
    public $selectedTemplateId = null;
    public $currentTemplate = null;

    public $enableDimensionsCalculation = false;
    public $dimensionsUnit = 'cm'; // cm أو m

    public $titles = [
        10 => 'Sales Invoice',
        11 => 'Purchase Invoice',
        12 => 'Sales Return',
        13 => 'Purchase Return',
        14 => 'Sales Order',
        15 => 'Purchase Order',
        16 => 'Quotation to Customer',
        17 => 'Quotation from Supplier',
        18 => 'Damaged Goods Invoice',
        19 => 'Dispatch Order',
        20 => 'Addition Order',
        21 => 'Store-to-Store Transfer',
        22 => 'Booking Order',
        24 => 'Service Invoice',
        25 => 'Requisition',
        26 => 'Pricing Agreement',
    ];

    protected $listeners = [
        'account-created' => 'handleAccountCreated',
        'branch-changed' => 'handleBranchChange',
        'itemSelected' => 'handleItemSelected',
        'batch-selected' => 'selectBatch',
    ];

    public function mount($type, $hash)
    {
        $permissionName = 'create ' . ($this->titles[$type] ?? 'Unknown');
        if (!auth()->user()->can($permissionName)) {
            abort(403, 'You do not have permission to create ' . ($this->titles[$type] ?? 'this invoice type'));
        }

        $this->op2 = request()->get('op2');
        $this->enableDimensionsCalculation = (setting('enable_dimensions_calculation') ?? '0') == '1';
        $this->dimensionsUnit = setting('dimensions_unit', 'cm');
        $this->loadExpirySettings();
        $this->initializeInvoice($type, $hash);
        $this->loadTemplatesForType();
    }



    public function handleItemSelected($data)
    {
        if ($data['wireModel'] === 'acc1_id') {
            $this->acc1_id = $data['value'];

            // تحديث الرصيد والتوصيات كما في updatedAcc1Id
            if ($this->showBalance && $data['value']) {
                $this->currentBalance = $this->getAccountBalance($data['value']);
                $this->calculateBalanceAfterInvoice();
            }

            if ($this->type == 10 && $data['value']) {
                $this->recommendedItems = $this->getRecommendedItems($data['value']);
            } else {
                $this->recommendedItems = [];
            }

            $this->checkCashAccount($data['value']);
        }
    }

    public function loadTemplatesForType()
    {
        $this->availableTemplates = InvoiceTemplate::getForType($this->type);

        // تحديد النموذج الافتراضي
        $defaultTemplate = InvoiceTemplate::getDefaultForType($this->type);

        if ($defaultTemplate) {
            $this->selectedTemplateId = $defaultTemplate->id;
            $this->currentTemplate = $defaultTemplate;
        } elseif ($this->availableTemplates->isNotEmpty()) {
            $firstTemplate = $this->availableTemplates->first();
            $this->selectedTemplateId = $firstTemplate->id;
            $this->currentTemplate = $firstTemplate;
        }
    }

    public function calculateQuantityFromDimensions($index)
    {
        if (!isset($this->invoiceItems[$index])) return;

        $item = $this->invoiceItems[$index];
        $length = (float) ($item['length'] ?? 0);
        $width = (float) ($item['width'] ?? 0);
        $height = (float) ($item['height'] ?? 0);
        $density = (float) ($item['density'] ?? 1);

        // إذا كانت جميع القيم موجودة
        if ($length > 0 && $width > 0 && $height > 0) {
            // حساب الكمية حسب الوحدة المختارة
            $quantity = $length * $width * $height * $density;

            // إذا كانت الوحدة سنتيمتر، نحول إلى متر مكعب
            if ($this->dimensionsUnit === 'cm') {
                $quantity = $quantity / 1000000; // تحويل من سم³ إلى م³
            }

            $this->invoiceItems[$index]['quantity'] = round($quantity, 3);
            $this->recalculateSubValues();
            $this->calculateTotals();
        }
    }
    /**
     * Get where conditions for acc1 based on invoice type
     */
    public function getAcc1WhereConditions(): array
    {
        $conditions = [
            'isdeleted' => 0,
            'is_basic' => 0,
        ];

        // تحديد نوع الحساب حسب نوع الفاتورة
        if (in_array($this->type, [10, 12, 14, 16, 22, 26])) {
            // عملاء (Clients) - الكود يبدأ بـ 1103
            $conditions['code_like'] = '1103%';
        } elseif (in_array($this->type, [11, 13, 15, 17, 25])) {
            // موردين (Suppliers) - الكود يبدأ بـ 2101
            $conditions['code_like'] = '2101%';
        } elseif ($this->type == 21) {
            // تحويل من مخزن (المخازن) - الكود يبدأ بـ 1107
            $conditions['code_like'] = '1107%';
        }

        // فلترة حسب الفرع
        if ($this->branch_id) {
            $conditions['branch_id'] = $this->branch_id;
        }

        return $conditions;
    }
    /**
     * تغيير النموذج المختار
     */
    public function updatedSelectedTemplateId($templateId)
    {
        $this->currentTemplate = InvoiceTemplate::find($templateId);

        if (!$this->currentTemplate) {
            $this->currentTemplate = InvoiceTemplate::getDefaultForType($this->type);
        }

        $this->dispatch('template-changed', [
            'template' => $this->currentTemplate->toArray()
        ]);
    }

    /**
     * التحقق من ظهور عمود معين
     */
    public function shouldShowColumn(string $columnKey): bool
    {
        if (!$this->currentTemplate) {
            return true; // إذا لم يكن هناك نموذج، أظهر كل الأعمدة
        }

        return $this->currentTemplate->hasColumn($columnKey);
    }

    /**
     * الحصول على الأعمدة المرئية
     */
    public function getVisibleColumns(): array
    {
        if (!$this->currentTemplate) {
            return [];
        }

        return $this->currentTemplate->visible_columns ?? [];
    }

    public function handleAccountCreated($data)
    {
        $account = $data['account'];
        $type = $data['type'];

        // تحديث قائمة الحسابات
        if ($type === 'client' || $type === 'supplier') {
            // إعادة تحميل acc1List حسب الفرع أيضاً
            $this->acc1_id = $account['id'];
            $this->dispatch('refreshItems')->to('app::searchable-select');

            if ($type === 'client') {
                $this->acc1List = $this->getAccountsByCodeAndBranch('1103%', $this->branch_id);
            } else {
                $this->acc1List = $this->getAccountsByCodeAndBranch('2101%', $this->branch_id);
            }

            // تحديد الحساب الجديد كمختار
            $this->acc1_id = $account['id'];

            // إضافة: تحديث قوائم الحسابات النقدية أيضاً
            if ($type === 'client') {
                $this->cashClientIds = AccHead::where('isdeleted', 0)
                    ->where('is_basic', 0)
                    ->where('code', 'like', '110301%')
                    ->pluck('id')
                    ->toArray();
            } else {
                $this->cashSupplierIds = AccHead::where('isdeleted', 0)
                    ->where('is_basic', 0)
                    ->where('code', 'like', '210101%')
                    ->pluck('id')
                    ->toArray();
            }

            if ($this->showBalance) {
                $this->currentBalance = $this->getAccountBalance($this->acc1_id);
                $this->calculateBalanceAfterInvoice();
            }

            // تحقق من الحساب النقدي للحساب الجديد
            $this->checkCashAccount($this->acc1_id);
        }

        $this->dispatch('success', [
            'title' => 'نجح!',
            'text' => 'تم إضافة الحساب بنجاح وتم تحديده في الفاتورة.',
            'icon' => 'success'
        ]);
    }

    public function updatedBranchId($value)
    {
        $this->handleBranchChange($value);
    }

    public function handleBranchChange($branchId)
    {
        $this->loadBranchFilteredData($branchId);
        $this->resetSelectedValues();

        // $previousAcc1Id = $this->acc1_id;
        $this->acc1_id = $this->acc1List->first()->id ?? null;

        if ($this->showBalance && $this->acc1_id) {
            $this->currentBalance = $this->getAccountBalance($this->acc1_id);
            $this->calculateBalanceAfterInvoice();
        } else {
            $this->currentBalance = 0;
        }

        if ($this->type == 10 && $this->acc1_id) {
            $this->recommendedItems = $this->getRecommendedItems($this->acc1_id);
        } else {
            $this->recommendedItems = [];
        }

        // Reload items based on the branch
        $this->items = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices'])
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)->orWhereNull('branch_id');
            })
            ->take(20)
            ->get();

        $this->calculateTotals();

        $this->dispatch('branch-changed-completed', [
            'branch_id' => $branchId,
            'acc1_id' => $this->acc1_id,
            'acc1List' => $this->acc1List->map(fn($item) => ['value' => $item->id, 'text' => $item->aname])->toArray(),
            'currentBalance' => $this->currentBalance,
        ]);

        $this->dispatch('refreshItems')->to('app::searchable-select');
    }

    // private function getFilteredItems()
    // {
    //     $query = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices']);
    //     if (($this->settings['allow_hide_items_by_company'] ?? '0') == '1' && $this->acc1_id) {
    //         $companyId = AccHead::where('id', $this->acc1_id)->value('company_id');
    //         if ($companyId) {
    //             $query->where('company_id', $companyId);
    //         }
    //     }
    //     return $query->get();
    // }

    public function updatedAcc1Id($value)
    {
        if ($this->showBalance) {
            $this->currentBalance = $this->getAccountBalance($value);
            $this->calculateBalanceAfterInvoice();
        }

        // جلب التوصيات لأكثر 5 أصناف تم شراؤها من قبل العميل
        if (in_array($this->type, [10, 26]) && $value) {
            $this->recommendedItems = $this->getRecommendedItems($value);

            // تحديث الأسعار للأصناف الموجودة في الفاتورة
            if ($this->type == 10) {
                // فحص أي الأوبشنات مفعل
                $usePricingAgreement = (setting('invoice_use_pricing_agreement') ?? '0') == '1';
                $useLastCustomerPrice = (setting('invoice_use_last_customer_price') ?? '0') == '1';

                // تحذير إذا كان الاثنين مفعلين
                if ($usePricingAgreement && $useLastCustomerPrice) {
                    $this->dispatch(
                        'error',
                        title: 'تحذير!',
                        text: 'لا يمكن تفعيل "استخدام آخر سعر من اتفاقية تسعير" و "استخدام آخر سعر بيع" معاً. الرجاء إيقاف أحدهما من الإعدادات.',
                        icon: 'warning'
                    );
                    return;
                }

                // تطبيق التسعير حسب الأوبشن المفعل
                if ($usePricingAgreement) {
                    foreach ($this->invoiceItems as $index => $item) {
                        $this->updatePriceFromPricingAgreement($index);
                    }
                } elseif ($useLastCustomerPrice) {
                    foreach ($this->invoiceItems as $index => $item) {
                        $this->updatePriceToLastCustomerPrice($index);
                    }
                }
            } elseif ($this->type == 26) {
                // اتفاقية تسعير - دائماً تستخدم آخر سعر من الاتفاقيات
                foreach ($this->invoiceItems as $index => $item) {
                    $this->updatePriceFromPricingAgreement($index);
                }
            }
        } else {
            $this->recommendedItems = [];
        }

        $this->checkCashAccount($value);
    }

    private function checkCashAccount($accountId)
    {
        if (!$accountId || $this->total_after_additional <= 0) {
            return;
        }

        $isCashAccount = false;

        // للعملاء في فواتير المبيعات ومردود المبيعات واتفاقيات التسعير
        if (in_array($this->type, [10, 12, 26]) && in_array($accountId, $this->cashClientIds)) {
            $isCashAccount = true;
        }
        // للموردين في فواتير المشتريات ومردود المشتريات
        elseif (in_array($this->type, [11, 13]) && in_array($accountId, $this->cashSupplierIds)) {
            $isCashAccount = true;
        }

        // إذا كان حساب نقدي، املأ المبلغ المدفوع بقيمة الفاتورة
        if ($isCashAccount) {
            $this->received_from_client = $this->total_after_additional;
        }
        // إذا لم يكن نقدي، لا تغير المبلغ (اتركه كما هو للتعديل اليدوي)
    }

    private function getRecommendedItems($clientId)
    {
        // تحديد نوع الفاتورة المصدرية حسب النوع الحالي
        $sourceType = $this->type == 26 ? 26 : 10; // اتفاقية تسعير أو مبيعات

        return OperationItems::whereHas('operhead', function ($query) use ($clientId, $sourceType) {
            $query->where('pro_type', $sourceType)
                ->where('acc1', $clientId);
        })
            ->groupBy('item_id')
            ->selectRaw('item_id, SUM(qty_out) as total_quantity')
            ->with(['item' => function ($query) {
                $query->select('id', 'name');
            }])
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get()
            ->map(function ($operationItem) {
                return [
                    'id' => $operationItem->item_id,
                    'name' => $operationItem->item->name,
                    'total_quantity' => $operationItem->total_quantity,
                ];
            })
            ->toArray();
    }

    // public function addRecommendedItem($itemId)
    // {
    //     // جلب بيانات الصنف
    //     $item = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices'])
    //         ->find($itemId);

    //     if ($item) {
    //         $price = $item->prices->where('id', $this->selectedPriceType)->first()->price ?? 0;
    //         $unit = $item->units->first();

    //         $this->invoiceItems[] = [
    //             'item_id' => $item->id,
    //             'name' => $item->name,
    //             'quantity' => 1, // الكمية الافتراضية
    //             'price' => $price,
    //             'total' => $price,
    //             'unit_id' => $unit->id ?? null,
    //             'unit_name' => $unit->name ?? '',
    //             'store_id' => $this->acc2_id,
    //         ];

    //         $this->calculateTotals();
    //     }
    // }

    public function calculateBalanceAfterInvoice()
    {
        $subtotal = 0;
        foreach ($this->invoiceItems as $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['price'] ?? 0);
            $subtotal += $quantity * $price;
        }

        $discountValue = $this->discount_value;
        $additionalValue = $this->additional_value;
        $netTotal = $subtotal - $discountValue + $additionalValue;
        $receivedAmount = (float) $this->received_from_client;

        $effect = 0;

        if ($this->type == 10) { // فاتورة مبيعات
            $effect = $netTotal - $receivedAmount; // يزيد الرصيد بالباقي (مديونية العميل)
        } elseif ($this->type == 11) { // فاتورة مشتريات
            $effect = - ($netTotal - $receivedAmount); // يقل الرصيد بالمستحق (مديونيتك للمورد)
        } elseif ($this->type == 12) { // مردود مبيعات
            $effect = -$netTotal + $receivedAmount; // يقل المديونية - المدفوع
        } elseif ($this->type == 13) { // مردود مشتريات
            $effect = $netTotal - $receivedAmount; // يزيد الرصيد بالمردود - المدفوع (إرجاع جزء من الدفع)
        }

        $this->balanceAfterInvoice = $this->currentBalance + $effect;

        // $this->checkCashAccount($this->acc1_id);
    }

    public function updatedReceivedFromClient()
    {
        $this->calculateTotals();
        $this->calculateBalanceAfterInvoice();
    }

    public function updatedTotalAfterAdditional()
    {
        $this->checkCashAccount($this->acc1_id);
        $this->calculateBalanceAfterInvoice();
    }

    public function updateSelectedItemData($item, $unitId, $price)
    {
        $this->currentSelectedItem = $item->id;

        $availableQtyInSelectedStore = OperationItems::where('item_id', $item->id)
            ->where('detail_store', $this->type == 21 ? $this->acc1_id : $this->acc2_id)
            ->selectRaw('SUM(qty_in - qty_out) as total')
            ->value('total') ?? 0;

        $totalAvailableQty = OperationItems::where('item_id', $item->id)
            ->selectRaw('SUM(qty_in - qty_out) as total')
            ->value('total') ?? 0;

        $unitName = $item->units->where('id', $unitId)->first()->name ?? '';

        $selectedStoreName = AccHead::where('id', $this->type == 21 ? $this->acc1_id : $this->acc2_id)->value('aname') ?? '';

        $lastPurchasePrice = OperationItems::where('item_id', $item->id)
            ->where('is_stock', 1)
            ->whereIn('pro_tybe', [11, 20]) // عمليات الشراء والإضافة للمخزن
            ->where('qty_in', '>', 0)
            ->orderBy('created_at', 'desc')
            ->value('item_price') ?? 0;

        $this->selectedItemData = [
            'name' => $item->name,
            'code' => $item->code ?? '',
            'available_quantity_in_store' => $availableQtyInSelectedStore,
            'total_available_quantity' => $totalAvailableQty,
            'selected_store_name' => $selectedStoreName,
            'unit_name' => $unitName,
            'price' => $price,
            'average_cost' => $item->average_cost ?? 0,
            'last_purchase_price' => $lastPurchasePrice, // إضافة السعر الأخير هنا
            'description' => $item->description ?? ''
        ];
    }

    public function createItemFromPrompt($name, $barcode)
    {
        $this->createNewItem($name, $barcode);
    }

    public function addItemByBarcode()
    {
        $barcode = trim($this->barcodeTerm);
        if (empty($barcode)) {
            return;
        }
        $item = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices'])
            ->whereHas('barcodes', function ($query) use ($barcode) {
                $query->where('barcode', $barcode);
            })
            ->first();

        if (!$item) {
            // هذا الجزء يبقى كما هو لإظهار نافذة إنشاء صنف جديد
            return $this->dispatch('prompt-create-item-from-barcode', barcode: $barcode);
        }

        $this->addedFromBarcode = true;

        $lastIndex = count($this->invoiceItems) - 1;
        if ($lastIndex >= 0 && $this->invoiceItems[$lastIndex]['item_id'] === $item->id) {
            $this->invoiceItems[$lastIndex]['quantity']++;
            $this->recalculateSubValues();
            $this->calculateTotals();
            $this->barcodeTerm = '';
            return;
        }

        $this->addItemFromSearch($item->id);
        $this->barcodeTerm = '';
        $this->barcodeSearchResults = collect();
        $this->selectedBarcodeResultIndex = -1;
        $this->lastQuantityFieldIndex = count($this->invoiceItems) - 1;
        $newRowIndex = count($this->invoiceItems) - 1;

        $this->dispatch('alert', ['type' => 'success', 'message' => 'تم إضافة الصنف بنجاح.']);
        $this->dispatch('focus-quantity', ['index' => $newRowIndex]);
        $this->calculateTotals();
    }

    public function updatedBarcodeTerm($value)
    {
        $this->selectedBarcodeResultIndex = -1;
        $this->barcodeSearchResults = collect();
    }

    public function handleQuantityEnter($index)
    {
        if (!isset($this->invoiceItems[$index])) {
            return;
        }

        $this->quantityClickCount++;
        $this->lastQuantityFieldIndex = $index;

        // تحديث الكمية بناءً على عدد الضغطات
        if (($this->settings['default_quantity_greater_than_zero'] ?? '0') == '1' && $this->type == 10) {
            $this->invoiceItems[$index]['quantity'] = max(1, $this->quantityClickCount);
        } else {
            $this->invoiceItems[$index]['quantity'] = $this->quantityClickCount;
        }

        if (($this->settings['prevent_negative_invoice'] ?? '0') == '1' && $this->invoiceItems[$index]['quantity'] < 0) {
            $this->invoiceItems[$index]['quantity'] = 0;
            $this->dispatch(
                'error',
                title: 'خطأ!',
                text: 'لا يمكن إدخال كمية سالبة في الفاتورة.',
                icon: 'error'
            );
        }

        $this->recalculateSubValues();
        $this->calculateTotals();

        if ($this->quantityClickCount === 1) {
            $this->js('window.focusBarcodeField()');
        }
    }

    public function removeRow($index)
    {
        unset($this->invoiceItems[$index]);
        $this->invoiceItems = array_values($this->invoiceItems);
        $this->calculateTotals();
    }

    protected static $itemsCache = [];

    public function updatedSearchTerm($value)
    {
        $this->searchResults = collect();
        $this->selectedResultIndex = -1;

        $searchTerm = trim($value);
        if (empty($searchTerm)) {
            return;
        }

        $limit = strlen($searchTerm) == 1 ? 10 : 20;

        // تحسين: ترتيب الشروط حسب السرعة - البحث بالاسم أولاً (أسرع مع الفهرس)
        $query = Item::select('id', 'name', 'code')
            ->where('name', 'like', $searchTerm . '%');

        // إضافة البحث بالكود فقط إذا لم يكن رقم (لأن البحث بالرقم أبطأ)
        if (!is_numeric($searchTerm)) {
            $query->orWhere('code', 'like', $searchTerm . '%');
        } else {
            // إذا كان رقم، ابحث بالكود مباشرة (أسرع)
            $query->orWhere('code', $searchTerm);
        }

        // تطبيق الفلاتر حسب نوع الفاتورة
        if (in_array($this->type, [11, 13, 15, 17])) {
            $query->where('type', ItemType::Inventory->value);
        } elseif ($this->type == 24) {
            $query->where('type', ItemType::Service->value);
        }

        $this->searchResults = $query->limit($limit)->get();

        // البحث في الباركود فقط إذا لم توجد نتائج
        if ($this->searchResults->isEmpty()) {
            $barcodeQuery = Item::select('items.id', 'items.name', 'items.code')
                ->join('barcodes', 'items.id', '=', 'barcodes.item_id')
                ->where('barcodes.barcode', 'like', $searchTerm . '%');

            if (in_array($this->type, [11, 13, 15, 17])) {
                $barcodeQuery->where('items.type', ItemType::Inventory->value);
            } elseif ($this->type == 24) {
                $barcodeQuery->where('items.type', ItemType::Service->value);
            }

            $this->searchResults = $barcodeQuery->limit($limit)->distinct()->get();
        }
    }

    public function addItemFromSearch($itemId)
    {
        // تحسين: تحميل محسّن مع ترتيب الوحدات
        $item = Item::with([
            'units' => fn($q) => $q->orderBy('pivot_u_val', 'asc'),
            'prices'
        ])->find($itemId);

        if (!$item) return;
        // التحقق من وجود الصنف في الفاتورة
        $existingItemIndex = null;
        foreach ($this->invoiceItems as $index => $invoiceItem) {
            if ($invoiceItem['item_id'] === $item->id) {
                $existingItemIndex = $index;
                break;
            }
        }
        // إذا كان الصنف موجود، زيادة الكمية بدلاً من إضافة صف جديد
        if ($existingItemIndex !== null) {
            $this->invoiceItems[$existingItemIndex]['quantity']++;
            $this->recalculateSubValues();
            $this->calculateTotals();

            // تحديث بيانات الصنف المختار
            $unitId = $this->invoiceItems[$existingItemIndex]['unit_id'];
            $price = $this->invoiceItems[$existingItemIndex]['price'];
            $this->updateSelectedItemData($item, $unitId, $price);

            // إعادة تعيين حقول البحث
            $this->searchTerm = '';
            $this->searchResults = collect();
            $this->selectedResultIndex = -1;
            $this->barcodeTerm = '';
            $this->barcodeSearchResults = collect();
            $this->selectedBarcodeResultIndex = -1;

            // تحديث فهرس الكمية الأخير
            $this->lastQuantityFieldIndex = $existingItemIndex;

            if ($this->addedFromBarcode) {
                $this->js('window.focusBarcodeSearch()'); // ركز على الباركود
            } else {
                $this->js('window.focusLastQuantityField()'); // ركز على الكمية
            }
            $newRowIndex = count($this->invoiceItems) - 1;

            $this->dispatch('alert', ['type' => 'success', 'message' => 'تم إضافة الصنف بنجاح.']);
            $this->dispatch('focus-quantity', ['index' => $newRowIndex]);
            return; // الخروج من الدالة
        }

        // إذا لم يكن الصنف موجود، إضافة صف جديد (الكود الأصلي)
        $firstUnit = $item->units->first();
        $unitId = $firstUnit?->id;

        $vm = new ItemViewModel(null, $item, $unitId);
        $salePrices = $vm->getUnitSalePrices();
        $price = $salePrices[$this->selectedPriceType]['price'] ?? 0;

        // إذا كان نوع الفاتورة 18، استخدم average_cost كسعر
        if (in_array($this->type, [11, 15])) { // فاتورة مشتريات أو أمر شراء
            // استخدام آخر سعر شراء
            $lastPurchasePrice = OperationItems::where('item_id', $item->id)
                ->where('is_stock', 1)
                ->whereIn('pro_tybe', [11, 20]) // عمليات الشراء والإضافة للمخزن
                ->where('qty_in', '>', 0)
                ->orderBy('created_at', 'desc')
                ->value('item_price') ?? 0;

            $price = $lastPurchasePrice;

            // إذا لم يكن هناك سعر شراء سابق، استخدم التكلفة المتوسطة
            if ($price == 0) {
                $price = $item->average_cost ?? 0;
            }
        } elseif ($this->type == 18) {
            $price = $item->average_cost ?? 0;
        } else {
            $price = $salePrices[$this->selectedPriceType]['price'] ?? 0;

            // فحص أي الأوبشنات مفعل
            $usePricingAgreement = (setting('invoice_use_pricing_agreement') ?? '0') == '1';
            $useLastCustomerPrice = (setting('invoice_use_last_customer_price') ?? '0') == '1';

            // تحذير إذا كان الاثنين مفعلين
            if ($usePricingAgreement && $useLastCustomerPrice) {
                $this->dispatch(
                    'error',
                    title: 'تحذير!',
                    text: 'لا يمكن تفعيل "استخدام آخر سعر من اتفاقية تسعير" و "استخدام آخر سعر بيع" معاً. الرجاء إيقاف أحدهما من الإعدادات.',
                    icon: 'warning'
                );
                return $price; // استخدام السعر الافتراضي
            }

            // استخدام آخر سعر من اتفاقية التسعير (فقط للمبيعات)
            if ($this->type == 10 && $usePricingAgreement && $this->acc1_id) {
                $pricingAgreementPrice = OperationItems::whereHas('operhead', function ($query) {
                    $query->where('pro_type', 26)
                        ->where('acc1', $this->acc1_id);
                })
                    ->where('item_id', $itemId)
                    ->where('unit_id', $unitId)
                    ->orderBy('created_at', 'desc')
                    ->value('item_price');

                if ($pricingAgreementPrice && $pricingAgreementPrice > 0) {
                    $price = $pricingAgreementPrice;
                }
            }
            // استخدام آخر سعر للعميل إذا كان ممكناً (فقط للمبيعات)
            elseif ($this->type == 10 && $useLastCustomerPrice && $this->acc1_id) {
                $lastCustomerPrice = OperationItems::whereHas('operhead', function ($query) {
                    $query->where('pro_type', 10)
                        ->where('acc1', $this->acc1_id);
                })
                    ->where('item_id', $itemId)
                    ->where('unit_id', $unitId)
                    ->orderBy('created_at', 'desc')
                    ->value('item_price');

                if ($lastCustomerPrice && $lastCustomerPrice > 0) {
                    $price = $lastCustomerPrice;
                }
            }
            // استخدام آخر سعر من اتفاقية التسعير (دائماً لنوع 26)
            elseif ($this->type == 26 && $this->acc1_id) {
                $pricingAgreementPrice = OperationItems::whereHas('operhead', function ($query) {
                    $query->where('pro_type', 26)
                        ->where('acc1', $this->acc1_id);
                })
                    ->where('item_id', $itemId)
                    ->where('unit_id', $unitId)
                    ->orderBy('created_at', 'desc')
                    ->value('item_price');

                if ($pricingAgreementPrice && $pricingAgreementPrice > 0) {
                    $price = $pricingAgreementPrice;
                }
            }
        }

        // التحقق من منع السعر صفر
        // if ((!setting('allow_purchase_price_change')) && $price == 0) {
        //     $this->dispatch(
        //         'error',
        //         title: 'خطأ!',
        //         text: 'لا يمكن أن يكون السعر صفرًا في الفاتورة.',
        //         icon: 'error'
        //     );
        //     return;
        // }

        // التحقق من منع الأرقام السالبة
        if (($this->settings['prevent_negative_invoice'] ?? '0') == '1' && $price < 0) {
            $this->dispatch(
                'error',
                title: 'خطأ!',
                text: 'لا يمكن إدخال سعر سالب في الفاتورة.',
                icon: 'error'
            );
            return;
        }

        $unitOptions = $vm->getUnitOptions();

        $availableUnits = collect($unitOptions)->map(function ($unit) {
            return (object) [
                'id' => $unit['value'],
                'name' => $unit['label'],
            ];
        });

        $quantity = ($this->settings['default_quantity_greater_than_zero'] ?? '0') == '1' && $this->type == 10 ? 1 : 1;

        $this->invoiceItems[] = [
            'item_id' => $item->id,
            'unit_id' => $unitId,
            'name' => $item->name,
            'quantity' => $quantity,
            'price' => $price,
            'sub_value' => $price * $quantity, // quantity * price
            'discount' => 0,
            'available_units' => $availableUnits,

            'length' => null,
            'width' => null,
            'height' => null,
            'density' => 1,
            'batch_number' => null,
            'expiry_date' => null,

        ];
        $this->updateSelectedItemData($item, $unitId, $price);

        $newIndex = count($this->invoiceItems) - 1;

        if (in_array($this->type, [10, 12, 14, 16, 22])) { // فواتير صادرة (بيع من المخزن)
            $storeId = $this->acc2_id;
            $this->autoAssignExpiryData($itemId, $storeId, $newIndex);
        }
        $this->barcodeTerm = '';
        $this->barcodeSearchResults = collect();
        $this->selectedBarcodeResultIndex = -1;
        $this->lastQuantityFieldIndex = count($this->invoiceItems) - 1;

        if ($this->addedFromBarcode) {
            $this->js('window.focusBarcodeSearch()'); // ركز على الباركود
        } else {
            $this->js('window.focusLastQuantityField()'); // ركز على الكمية
        }

        $this->searchTerm = '';
        $this->searchResults = collect();
        $this->selectedResultIndex = -1;

        $this->calculateTotals();
        if (in_array($this->type, [10, 12, 14, 16, 22])) {
            $storeId = $this->acc2_id;
            $this->autoAssignExpiryData($itemId, $storeId, $newIndex);
        }
    }

    public function updatedAcc2Id()
    {
        if ($this->currentSelectedItem) {
            $item = Item::with('units', 'prices')->find($this->currentSelectedItem);

            if ($item) {
                $currentInvoiceItem = collect($this->invoiceItems)->first(function ($invoiceItem) {
                    return $invoiceItem['item_id'] == $this->currentSelectedItem;
                });

                if ($currentInvoiceItem) {
                    $unitId = $currentInvoiceItem['unit_id'];
                    $price = $currentInvoiceItem['price'];
                    $this->updateSelectedItemData($item, $unitId, $price);
                }
            }
        }

        // ✅ إضافة جديدة: تحديث بيانات الدفعات عند تغيير المخزن
        if ($this->expiryDateMode !== 'disabled' && in_array($this->type, [10, 12, 14, 16, 22])) {
            foreach ($this->invoiceItems as $index => $item) {
                if (isset($item['item_id'])) {
                    $this->refreshBatchesForStore($index);
                }
            }
        }
    }


    public function selectItemFromTable($itemId, $unitId, $price)
    {
        $item = Item::with(['units', 'prices'])->find($itemId);
        if ($item) {
            $this->updateSelectedItemData($item, $unitId, $price);
        }
    }

    public function getSelectedUnits()
    {
        foreach ($this->invoiceItems as $invoiceItem) {
            $this->selectedUnit[$invoiceItem['item_id']] = $invoiceItem['unit_id'];
        }
    }

    public function updateUnits($index)
    {
        if (!isset($this->invoiceItems[$index])) return;

        $itemId = $this->invoiceItems[$index]['item_id'];
        $item = $this->items->firstWhere('id', $itemId);

        if (! $item) return;

        // إعداد الوحدات المتاحة
        $vm = new ItemViewModel(null, $item);
        $opts = $vm->getUnitOptions();

        $unitsCollection = collect($opts)->map(fn($entry) => (object)[
            'id' => $entry['value'],
            'name' => $entry['label'],
        ]);

        $this->invoiceItems[$index]['available_units'] = $unitsCollection;

        // إذا لم يتم تحديد وحدة، اختر الأولى
        if (empty($this->invoiceItems[$index]['unit_id'])) {
            $firstUnit = $unitsCollection->first();
            if ($firstUnit) {
                $this->invoiceItems[$index]['unit_id'] = $firstUnit->id;
            }
        }
        // تحديث السعر بناءً على الوحدة المختارة
        $this->updatePriceForUnit($index);
    }

    public function updatePriceForUnit($index)
    {
        if (!isset($this->invoiceItems[$index])) return;

        $itemId = $this->invoiceItems[$index]['item_id'];
        $unitId = $this->invoiceItems[$index]['unit_id'];

        if (!$itemId || !$unitId) return;

        $item = $this->items->firstWhere('id', $itemId);
        if (!$item) return;

        if ($this->type == 11 && (!setting('allow_purchase_price_change'))) {
            $this->dispatch(
                'error',
                title: 'خطأ!',
                text: 'غير مسموح بتغيير سعر البيع في فاتورة المشتريات.',
                icon: 'error'
            );
            return;
        }
        // حساب السعر للوحدة المختارة
        $vm = new ItemViewModel(null, $item, $unitId);
        if (in_array($this->type, [11, 15])) { // فاتورة مشتريات أو أمر شراء
            // استخدام آخر سعر شراء
            $lastPurchasePrice = OperationItems::where('item_id', $item->id)
                ->where('is_stock', 1)
                ->whereIn('pro_tybe', [11, 20]) // عمليات الشراء والإضافة للمخزن
                ->where('qty_in', '>', 0)
                ->orderBy('created_at', 'desc')
                ->value('item_price') ?? 0;

            $price = $lastPurchasePrice;

            // إذا لم يكن هناك سعر شراء سابق، استخدم التكلفة المتوسطة
            if ($price == 0) {
                $price = $item->average_cost ?? 0;
            }
        } elseif ($this->type == 18) { // فاتورة توالف
            $price = $item->average_cost ?? 0;
        } else { // باقي أنواع الفواتير
            $salePrices = $vm->getUnitSalePrices();
            $price = $salePrices[$this->selectedPriceType]['price'] ?? 0;

            // فحص أي الأوبشنات مفعل
            $usePricingAgreement = (setting('invoice_use_pricing_agreement') ?? '0') == '1';
            $useLastCustomerPrice = (setting('invoice_use_last_customer_price') ?? '0') == '1';

            // تحذير إذا كان الاثنين مفعلين
            if ($usePricingAgreement && $useLastCustomerPrice) {
                $this->dispatch(
                    'error',
                    title: 'تحذير!',
                    text: 'لا يمكن تفعيل "استخدام آخر سعر من اتفاقية تسعير" و "استخدام آخر سعر بيع" معاً. الرجاء إيقاف أحدهما من الإعدادات.',
                    icon: 'warning'
                );
                // استخدام السعر الافتراضي بدون تطبيق الأوبشنات
                return $price;
            }

            // استخدام آخر سعر من اتفاقية التسعير (فقط للمبيعات)
            if ($this->type == 10 && $usePricingAgreement && $this->acc1_id) {
                $pricingAgreementPrice = OperationItems::whereHas('operhead', function ($query) {
                    $query->where('pro_type', 26)
                        ->where('acc1', $this->acc1_id);
                })
                    ->where('item_id', $itemId)
                    ->where('unit_id', $unitId)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($pricingAgreementPrice && $pricingAgreementPrice->item_price > 0) {
                    $price = $pricingAgreementPrice->item_price;
                }
            }
            // استخدام آخر سعر للعميل إذا كان ممكناً (فقط للمبيعات)
            elseif ($this->type == 10 && $useLastCustomerPrice && $this->acc1_id) {
                $lastCustomerPrice = OperationItems::whereHas('operhead', function ($query) {
                    $query->where('pro_type', 10)
                        ->where('acc1', $this->acc1_id);
                })
                    ->where('item_id', $itemId)
                    ->where('unit_id', $unitId)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($lastCustomerPrice && $lastCustomerPrice->item_price > 0) {
                    $price = $lastCustomerPrice->item_price;
                }
            }
        }

        if (($this->settings['allow_zero_price_in_invoice'] ?? '0') != '1' && $price == 0) {
            $this->dispatch(
                'error',
                title: 'خطأ!',
                text: 'لا يمكن أن يكون السعر صفرًا في الفاتورة.',
                icon: 'error'
            );
            return;
        }

        if (($this->settings['prevent_negative_invoice'] ?? '0') == '1' && $price < 0) {
            $this->dispatch(
                'error',
                title: 'خطأ!',
                text: 'لا يمكن إدخال سعر سالب في الفاتورة.',
                icon: 'error'
            );
            return;
        }

        $this->invoiceItems[$index]['price'] = $price;

        $this->recalculateSubValues();
        $this->calculateTotals();
    }

    public function updatedInvoiceItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) < 2) return;

        $rowIndex = (int) $parts[0];
        $field = $parts[1];

        if (in_array($field, ['length', 'width', 'height', 'density']) && $this->enableDimensionsCalculation) {
            $this->calculateQuantityFromDimensions($rowIndex);
            return;
        }

        if ($field === 'quantity') {
            $this->quantityClickCount = 0; // إعادة تعيين عداد الضغطات
            if ($this->expiryDateMode !== 'disabled' && in_array($this->type, [10, 12, 14, 16, 22])) {
                $isValid = $this->validateBatchQuantity($rowIndex);

                // إذا كان في وضع FIFO وتم تجاوز الكمية، حاول التقسيم التلقائي
                if (!$isValid && $this->expiryDateMode === 'nearest_first') {
                    $itemId = $this->invoiceItems[$rowIndex]['item_id'] ?? null;
                    $storeId = $this->acc2_id;
                    $requestedQuantity = (float) $value;

                    if ($itemId && $storeId) {
                        $this->autoSplitQuantityAcrossBatches($itemId, $storeId, $requestedQuantity, $rowIndex);
                    }
                }
            }

            if (($this->settings['prevent_negative_invoice'] ?? '0') == '1' && $value < 0) {
                $this->invoiceItems[$rowIndex]['quantity'] = 0;
                $this->dispatch(
                    'error',
                    title: 'خطأ!',
                    text: 'لا يمكن إدخال كمية سالبة في الفاتورة.',
                    icon: 'error'
                );
            }

            if (($this->settings['default_quantity_greater_than_zero'] ?? '0') == '1' && $this->type == 10 && $value <= 0) {
                $this->invoiceItems[$rowIndex]['quantity'] = 1;
                $this->dispatch(
                    'error',
                    title: 'خطأ!',
                    text: 'يجب أن تكون الكمية أكبر من صفر في فواتير البيع.',
                    icon: 'error'
                );
            }
            $this->recalculateSubValues();
            $this->calculateTotals();
        } elseif ($field === 'item_id') {
            $this->updateUnits($rowIndex);

            $itemId = $this->invoiceItems[$rowIndex]['item_id'];
            if ($itemId) {
                $item = Item::with(['units', 'prices'])->find($itemId);
                if ($item) {
                    $unitId = $this->invoiceItems[$rowIndex]['unit_id'];
                    $price = $this->invoiceItems[$rowIndex]['price'];
                    $this->updateSelectedItemData($item, $unitId, $price);
                }
            }
        }
        if ($field === 'item_id') {
            // عند تغيير الصنف، قم بتحديث الوحدات
            $this->updateUnits($rowIndex);

            // تحديث بيانات الصنف المختار
            $itemId = $this->invoiceItems[$rowIndex]['item_id'];
            if ($itemId) {
                $item = Item::with(['units', 'prices'])->find($itemId);
                if ($item) {
                    $unitId = $this->invoiceItems[$rowIndex]['unit_id'];
                    $price = $this->invoiceItems[$rowIndex]['price'];
                    $this->updateSelectedItemData($item, $unitId, $price);
                }
            }
        } elseif ($field === 'unit_id') {
            // عند تغيير الوحدة، قم بتحديث السعر
            $this->updatePriceForUnit($rowIndex);

            // تحديث بيانات الصنف مع الوحدة الجديدة
            $itemId = $this->invoiceItems[$rowIndex]['item_id'];
            if ($itemId) {
                $item = Item::with(['units', 'prices'])->find($itemId);
                if ($item) {
                    $unitId = $this->invoiceItems[$rowIndex]['unit_id'];
                    $price = $this->invoiceItems[$rowIndex]['price'];
                    $this->updateSelectedItemData($item, $unitId, $price);
                }
            }
        } elseif ($field === 'sub_value') {
            // حساب عكسي: حساب الكمية من القيمة الفرعية
            if (($this->settings['allow_edit_invoice_value'] ?? '0') != '1') {
                $this->dispatch(
                    'error',
                    title: 'خطأ!',
                    text: 'غير مسموح بتعديل قيمة الفاتورة.',
                    icon: 'error'
                );
                return;
            }
            $this->calculateQuantityFromSubValue($rowIndex);
        } elseif ($field === 'price' && $this->type == 11 && (!setting('allow_purchase_price_change'))) {
            $this->dispatch(
                'error',
                title: 'خطأ!',
                text: 'غير مسموح بتغيير سعر البيع في فاتورة المشتريات.',
                icon: 'error'
            );
            return;
        } elseif (in_array($field, ['quantity', 'price', 'discount'])) {
            if (($this->settings['prevent_negative_invoice'] ?? '0') == '1' && ($value < 0)) {
                $this->invoiceItems[$rowIndex][$field] = 0;
                $this->dispatch(
                    'error',
                    title: 'خطأ!',
                    text: 'لا يمكن إدخال قيم سالبة في الفاتورة.',
                    icon: 'error'
                );
            }
            if ($field === 'price' && ($this->settings['allow_zero_price_in_invoice'] ?? '0') != '1' && $value == 0) {
                $this->invoiceItems[$rowIndex]['price'] = 0;
                $this->dispatch(
                    'error',
                    title: 'خطأ!',
                    text: 'لا يمكن أن يكون السعر صفرًا في الفاتورة.',
                    icon: 'error'
                );
            }
            $this->recalculateSubValues();
            $this->calculateTotals();
        }
        $this->calculateBalanceAfterInvoice();
    }

    private function updatePriceToLastCustomerPrice($index)
    {
        if (!isset($this->invoiceItems[$index])) return;

        $itemId = $this->invoiceItems[$index]['item_id'];
        $unitId = $this->invoiceItems[$index]['unit_id'];

        if (!$itemId || !$unitId || !$this->acc1_id) return;

        // البحث عن آخر سعر بيع لهذا العميل لهذا الصنف مع نفس الوحدة
        $lastPrice = OperationItems::whereHas('operhead', function ($query) {
            $query->where('pro_type', 10) // فواتير المبيعات فقط
                ->where('acc1', $this->acc1_id);
        })
            ->where('item_id', $itemId)
            ->where('unit_id', $unitId)
            ->orderBy('created_at', 'desc')
            ->first();

        // إذا وُجد سعر سابق، استخدمه
        if ($lastPrice && $lastPrice->item_price > 0) {
            $this->invoiceItems[$index]['price'] = $lastPrice->item_price;
            $this->recalculateSubValues();
            $this->calculateTotals();
        }
    }

    private function updatePriceFromPricingAgreement($index)
    {
        if (!isset($this->invoiceItems[$index])) return;

        $itemId = $this->invoiceItems[$index]['item_id'];
        $unitId = $this->invoiceItems[$index]['unit_id'];

        if (!$itemId || !$unitId || !$this->acc1_id) return;

        // البحث عن آخر سعر من اتفاقية التسعير لهذا العميل لهذا الصنف مع نفس الوحدة
        $lastPrice = OperationItems::whereHas('operhead', function ($query) {
            $query->where('pro_type', 26) // اتفاقيات التسعير فقط
                ->where('acc1', $this->acc1_id);
        })
            ->where('item_id', $itemId)
            ->where('unit_id', $unitId)
            ->orderBy('created_at', 'desc')
            ->first();

        // إذا وُجد سعر من اتفاقية، استخدمه
        if ($lastPrice && $lastPrice->item_price > 0) {
            $this->invoiceItems[$index]['price'] = $lastPrice->item_price;
            $this->recalculateSubValues();
            $this->calculateTotals();
        }
    }

    public function updatedSelectedPriceType()
    {
        if (($this->settings['allow_edit_price_payments'] ?? '0') != '1') {
            $this->dispatch(
                'error',
                title: 'خطأ!',
                text: 'غير مسموح بتعديل الفئات السعرية في الفواتير.',
                icon: 'error'
            );
            return;
        }
        foreach ($this->invoiceItems as $index => $item) {
            if ($item['item_id'] && $item['unit_id']) {
                $this->updatePriceForUnit($index);
            }
        }
        if ($this->currentSelectedItem) {
            $index = array_search($this->currentSelectedItem, array_column($this->invoiceItems, 'item_id'));
            if ($index !== false) {
                $item = Item::with(['units', 'prices'])->find($this->currentSelectedItem);
                $unitId = $this->invoiceItems[$index]['unit_id'];
                $price = $this->invoiceItems[$index]['price'];
                $this->updateSelectedItemData($item, $unitId, $price);
            }
        }
    }

    public function calculateQuantityFromSubValue($index)
    {
        if (!isset($this->invoiceItems[$index])) return;

        if (($this->settings['allow_edit_invoice_value'] ?? '0') != '1') {
            $this->dispatch(
                'error',
                title: 'خطأ!',
                text: 'غير مسموح بتعديل قيمة الفاتورة.',
                icon: 'error'
            );
            return;
        }

        $item = $this->invoiceItems[$index];
        $subValue = (float) $item['sub_value'];
        $price = (float) $item['price'];
        $discount = (float) $item['discount'];

        // تجنب القسمة على صفر
        if ($price <= 0) {
            $this->invoiceItems[$index]['sub_value'] = 0;
            $this->invoiceItems[$index]['quantity'] = 0;
            $this->calculateTotals();
            return;
        }

        // حساب الكمية الجديدة
        if (($this->settings['change_quantity_on_value_edit'] ?? '0') == '1') {
            $newQuantity = ($subValue + $discount) / $price;
            $this->invoiceItems[$index]['quantity'] = round($newQuantity, 3);
        } else {
            $this->invoiceItems[$index]['price'] = ($subValue + $discount) / $item['quantity'];
        }

        if (($this->settings['prevent_negative_invoice'] ?? '0') == '1' && $this->invoiceItems[$index]['quantity'] < 0) {
            $this->invoiceItems[$index]['quantity'] = 0;
            $this->dispatch(
                'error',
                title: 'خطأ!',
                text: 'لا يمكن أن تكون الكمية سالبة.',
                icon: 'error'
            );
        }
        $this->calculateTotals();
    }

    public function recalculateSubValues()
    {
        foreach ($this->invoiceItems as $index => $item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['price'];
            $discount = (float) $item['discount'];
            $sub = ($qty * $price) - $discount;
            $this->invoiceItems[$index]['sub_value'] = round($sub, 2);
        }
    }

    public function calculateTotals()
    {
        $validSubValues = collect($this->invoiceItems)->pluck('sub_value')->map(function ($value) {
            return is_numeric($value) ? (float) $value : 0;
        });

        $this->subtotal = $validSubValues->sum();

        $discountPercentage = (float) ($this->discount_percentage ?? 0);
        $additionalPercentage = (float) ($this->additional_percentage ?? 0);

        $this->discount_value = round(($this->subtotal * $discountPercentage) / 100, 2);
        $this->additional_value = round(($this->subtotal * $additionalPercentage) / 100, 2);
        $this->total_after_additional = round($this->subtotal - $this->discount_value + $this->additional_value, 2);

        $this->checkCashAccount($this->acc1_id);

        // 4. تحقق من أن الإجمالي ليس صفر (اختياري)
        // if (!setting('allow_purchase_price_change') && $this->total_after_additional == 0) {
        //     $this->dispatch('error-swal', [
        //         'title' => 'خطأ!',
        //         'text'  => 'قيمة الفاتورة لا يمكن أن تكون صفرًا.',
        //         'icon'  => 'error'
        //     ]);
        // }

        if ($this->showBalance) {
            $this->calculateBalanceAfterInvoice();
        }
    }

    public function calculateSubtotal()
    {
        $this->subtotal = 0;
        foreach ($this->invoiceItems as $index => $item) {
            $quantity = $item['quantity'] ?? 0;
            $price = $item['price'] ?? 0;
            $this->invoiceItems[$index]['total'] = $quantity * $price;
            $this->subtotal += $quantity * $price;
        }
        $this->calculateTotalAfterDiscount();
        $this->calculateTotalAfterAdditional();

        if ($this->showBalance) {
            $this->calculateBalanceAfterInvoice();
        }
    }

    public function createNewItem($name, $barcode = null)
    {
        $existingItem = Item::where('name', $name)->first();
        if ($existingItem) {
            return;
        }

        $defaultBarcode = Item::max('code') + 1 ?? 1;
        $finalBarcode = $barcode ?? $defaultBarcode;

        $existingBarcode = Barcode::where('barcode', $finalBarcode)->exists();

        if ($existingBarcode) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'هذا الباركود (' . $finalBarcode . ') مستخدم بالفعل لصنف آخر.']);
            return;
        }

        $newItem = Item::create([
            'name' => $name,
            'code' => $defaultBarcode,
        ]);

        $newItem->units()->attach([
            1 => [
                'u_val' => 1,
                'cost' => 0
            ]
        ]);

        $newItem->barcodes()->create([
            'barcode' => $finalBarcode,
            'unit_id' => 1
        ]);

        $this->updateSelectedItemData($newItem, 1, 0);
        $this->addItemFromSearch($newItem->id);

        $this->searchTerm = '';
        $this->barcodeTerm = '';
    }

    public function updatedDiscountPercentage()
    {
        $discountPercentage = (float) ($this->discount_percentage ?? 0);
        $this->discount_value = ($this->subtotal * $discountPercentage) / 100;
        $this->calculateTotals();
        $this->calculateBalanceAfterInvoice();
    }

    public function updatedDiscountValue()
    {
        if ($this->discount_value >= 0 && $this->subtotal > 0) {
            $this->discount_percentage = ($this->discount_value * 100) / $this->subtotal;
            $this->calculateTotals();
            $this->calculateBalanceAfterInvoice();
        }
    }

    public function updatedAdditionalPercentage()
    {
        $additionalPercentage = (float) ($this->additional_percentage ?? 0);
        $this->additional_value = ($this->subtotal * $additionalPercentage) / 100;
        $this->calculateTotals();
        $this->calculateBalanceAfterInvoice();
    }

    public function updatedAdditionalValue()
    {
        $afterDiscount = $this->subtotal - $this->discount_value;
        if ($this->additional_value >= 0 && $afterDiscount > 0) {
            $this->additional_percentage = ($this->additional_value * 100) / $afterDiscount;
            $this->calculateTotals();
            $this->calculateBalanceAfterInvoice();
        }
    }

    public function handleKeyDown()
    {
        if ($this->searchResults->count() > 0) {
            $this->isCreateNewItemSelected = false;
            $this->selectedResultIndex = min(
                $this->selectedResultIndex + 1,
                $this->searchResults->count() - 1
            );
        }
        // لو مفيش نتائج، حدد زر إنشاء صنف جديد
        elseif (strlen($this->searchTerm) > 0) {
            $this->isCreateNewItemSelected = true;
        }
    }

    public function handleKeyUp()
    {
        if ($this->searchResults->count() > 0) {
            $this->isCreateNewItemSelected = false;
            $this->selectedResultIndex = max($this->selectedResultIndex - 1, -1);
        }
        // لو مفيش نتائج، لغي تحديد زر إنشاء صنف جديد
        elseif (strlen($this->searchTerm) > 0) {
            $this->isCreateNewItemSelected = false;
        }
    }

    public function handleEnter()
    {
        if ($this->selectedResultIndex >= 0) {
            $item = $this->searchResults->get($this->selectedResultIndex);
            $this->addItemFromSearch($item->id);
        }
        // لو تم تحديد زر "إنشاء صنف جديد"
        elseif ($this->isCreateNewItemSelected && strlen($this->searchTerm) > 0) {
            $this->createNewItem($this->searchTerm);
            $this->isCreateNewItemSelected = false; // إعادة تعيين الحالة
        }
    }

    public function checkSearchResults()
    {
        $searchTerm = trim($this->searchTerm);
        if (!empty($searchTerm) && $this->searchResults->isEmpty()) {
            $this->searchedTerm = $searchTerm;
            return $this->dispatch('item-not-found', ['term' => $searchTerm, 'type' => 'search']);
        }
    }

    public function saveForm()
    {
        if (($this->settings['allow_zero_invoice_total'] ?? '0') != '1' && $this->total_after_additional == 0) {
            $this->dispatch(
                'error',
                title: 'خطأ!',
                text: 'قيمة الفاتورة لا يمكن أن تكون صفرًا.',
                icon: 'error'
            );
            return null;
        }
        $service = new SaveInvoiceService();
        $operationId = $service->saveInvoice($this);

        // If this save was a conversion from a previous operation, redirect back to the tracking view
        // for the original root operation so the user sees the updated workflow state.
        $rootId = request()->get('origin_id') ?? $this->op2 ?? request()->get('parent_id') ?? request()->get('source_pro_id');

        if ($operationId && $rootId) {
            return redirect()->route('invoices.track', ['id' => $rootId]);
        }

        return $operationId;
    }

    public function saveAndPrint()
    {
        $operationId = $this->saveForm();
        if ($operationId) {
            $printUrl = route('invoice.print', ['operation_id' => $operationId]);
            $this->dispatch('open-print-window', url: $printUrl);
        }
    }

    public function render()
    {
        return view('livewire.invoices.create-invoice-form');
    }
}
