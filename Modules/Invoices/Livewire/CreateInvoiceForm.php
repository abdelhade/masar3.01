<?php

namespace Modules\Invoices\Livewire;

use App\Models\Item;
use App\Models\User;
use App\Enums\ItemType;
use App\Models\Barcode;
use Livewire\Component;
use App\Helpers\ItemViewModel;
use App\Models\OperationItems;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Accounts\Models\AccHead;
use Illuminate\Support\Facades\Cache;
use Modules\Settings\Models\Currency;
use Modules\Invoices\Models\InvoiceTemplate;
use Modules\Invoices\Services\SaveInvoiceService;
use Modules\Invoices\Livewire\Traits\HandlesExpiryDates;
use Modules\Invoices\Livewire\Traits\HandlesInvoiceData;
use Modules\Invoices\Services\Invoice\DetailValueValidator;
use Modules\Invoices\Services\Invoice\DetailValueCalculator;

class CreateInvoiceForm extends Component
{
    use HandlesExpiryDates, HandlesInvoiceData;

    public $type;
    public $acc1_id;
    public $acc2_id;
    public $emp_id;
    public $pro_date;
    public $accural_date;
    public $pro_id;
    public $serial_number;

    // ✅ تم نقل UI state properties إلى Alpine.js:
    // - barcodeSearchResults → Alpine.js (يمكن إضافة component للباركود)
    // - selectedBarcodeResultIndex → Alpine.js
    public $barcodeTerm = ''; // يمكن الاحتفاظ به فقط للـ mount
    public $barcodeSearchResults = []; // للتوافق مع Blade template حتى يتم تحويله بالكامل إلى Alpine.js
    public int $selectedBarcodeResultIndex = -1; // للتوافق مع الكود القديم
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
    public $isCurrentAccountCash = false;
    public $acc2Role;
    public $cashAccounts;
    public $selectedRowIndex = -1;
    /** @var Collection<int, \App\Models\Item> */
    public $items;
    public $invoiceItems = [];

    public $cash_box_id = '';
    public $received_from_client = 0;
    public $subtotal = 0;
    public $discount_percentage = 0;
    public $discount_value = 0;
    public $additional_percentage = 0;
    public $additional_value = 0;
    public $total_after_additional = 0;
    public $vat_percentage = 0;
    public $vat_value = 0;
    public $withholding_tax_percentage = 0;
    public $withholding_tax_value = 0;
    public $notes = '';
    public $settings = [];
    public $branch_id;
    public $branches;
    public $currency_id;
    public $currency_rate;

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
        'description' => '',
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
        // 'itemSelected' => 'handleItemSelected', // ✅ تم إزالة هذا لأن async-select يستخدم wire:model.live مباشرة
        'batch-selected' => 'selectBatch',
    ];

    public $oldUnitId = null;

    public function updatingInvoiceItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) >= 2 && $parts[1] === 'unit_id') {
            $index = (int) $parts[0];
            if (isset($this->invoiceItems[$index]['unit_id'])) {
                $this->oldUnitId = $this->invoiceItems[$index]['unit_id'];
            }
        }
    }

    public function mount($type, $hash)
    {
        $permissionName = 'create ' . ($this->titles[$type] ?? 'Unknown');
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user || ! $user->can($permissionName)) {
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
            $this->updateCurrencyFromAccount($this->acc1_id);
        }
    }

    public function updateCurrencyFromAccount($accountId)
    {
        if (!isMultiCurrencyEnabled() || !$accountId) {
            return;
        }

        $account = AccHead::find($accountId);

        // إذا كان الحساب مرتبط بعملة محددة
        if ($account && $account->currency_id) {
            $this->currency_id = $account->currency_id;

            // جلب سعر الصرف الحالي للعملة
            $currency = Currency::with('latestRate')->find($account->currency_id);
            if ($currency) {
                $this->currency_rate = $currency->latestRate->rate ?? 1;
            }
        } else {
            // العودة للعملة الافتراضية إذا لم يكن للحساب عملة خاصة
            $defaultCurrency = getDefaultCurrency();
            if ($defaultCurrency) {
                $this->currency_id = $defaultCurrency->id;
                $this->currency_rate = 1; // العملة الأساسية دائماً 1
            }
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
        if (! isset($this->invoiceItems[$index])) {
            return;
        }

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

        if (! $this->currentTemplate) {
            $this->currentTemplate = InvoiceTemplate::getDefaultForType($this->type);
        }

        $this->dispatch('template-changed', [
            'template' => $this->currentTemplate->toArray(),
        ]);
    }

    /**
     * التحقق من ظهور عمود معين
     */
    public function shouldShowColumn(string $columnKey): bool
    {
        if (! $this->currentTemplate) {
            return true; // إذا لم يكن هناك نموذج، أظهر كل الأعمدة
        }

        return $this->currentTemplate->hasColumn($columnKey);
    }

    /**
     * الحصول على الأعمدة المرئية
     */
    public function getVisibleColumns(): array
    {
        if (! $this->currentTemplate) {
            return [];
        }

        return $this->currentTemplate->visible_columns ?? [];
    }

    /**
     * ✅ الحصول على ترتيب الحقول القابلة للتحرير ديناميكياً من Template
     * يُستخدم في Alpine.js للتنقل بالكيبورد بين الحقول
     *
     * @return array ترتيب أسماء الحقول القابلة للتحرير
     */
    public function getEditableFieldsOrder(): array
    {
        // الحقول القابلة للتحرير بالترتيب الافتراضي
        $defaultEditableFields = ['quantity', 'price', 'discount', 'sub_value'];

        if (! $this->currentTemplate) {
            return $defaultEditableFields;
        }

        // جلب الأعمدة المرتبة من Template
        $orderedColumns = $this->currentTemplate->getOrderedColumns();

        // فلترة الأعمدة للحصول على القابلة للتحرير فقط
        $editableColumns = array_filter($orderedColumns, function ($column) {
            return in_array($column, ['quantity', 'price', 'discount', 'sub_value']);
        });

        // إرجاع الحقول المرتبة أو الافتراضية إذا كانت فارغة
        return ! empty($editableColumns) ? array_values($editableColumns) : $defaultEditableFields;
    }

    public function handleAccountCreated($data)
    {
        $account = $data['account'];
        $type = $data['type'];

        // تحديث قائمة الحسابات
        if ($type === 'client' || $type === 'supplier') {
            // تحديث الحساب المختار
            $this->acc1_id = $account['id'];

            // تحديث العملة من الحساب المنشأ حديثاً
            if (isMultiCurrencyEnabled()) {
                $this->updateCurrencyFromAccount($this->acc1_id);
            }

            // ✅ إعادة تحميل جميع القوائم والبيانات المرتبطة بالفرع لضمان تحديث قوائم الحسابات بالكامل
            // هذا يضمن بقاء جميع أنواع الحسابات متاحة في البحث ويحل مشكلة اختفاء السكرول
            $this->loadBranchFilteredData($this->branch_id);

            // ✅ إعادة تعيين القيم عند تغيير العميل (عبر الإنشاء الجديد)
            $this->discount_percentage = 0;
            $this->discount_value = 0;
            $this->additional_percentage = 0;
            $this->additional_value = 0;
            $this->received_from_client = 0;

            if ($this->showBalance) {
                $this->currentBalance = $this->getAccountBalance($this->acc1_id);
            }

            // ✅ إعادة حساب الإجماليات (سيقوم داخلياً بفحص الحساب النقدي وحساب الرصيد)
            $this->calculateTotals();

            // ✅ إرسال حدث للـ Alpine.js لتصفير القيم فوراً في الواجهة
            $this->dispatch('reset-invoice-parameters');
        }

        $this->dispatch('success', [
            'title' => 'نجح!',
            'text' => 'تم إضافة الحساب بنجاح وتم تحديده في الفاتورة.',
            'icon' => 'success',
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
        // ✅ لا حاجة لـ dispatch branch-changed-completed أو refreshItems
        // async-select يعيد التحميل تلقائياً عند تغيير key
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
        // ✅ إعادة تعيين القيم عند تغيير العميل
        $this->discount_percentage = 0;
        $this->discount_value = 0;
        $this->additional_percentage = 0;
        $this->additional_value = 0;
        $this->received_from_client = 0;

        // ============================================================
        // ✅ تحديث العملة من الحساب المختار
        // ============================================================
        if ($value && isMultiCurrencyEnabled()) {
            $this->updateCurrencyFromAccount($value);
        }
        // ============================================================
        // نهاية كود تحديث العملة
        // ============================================================

        // ✅ إعادة حساب الإجماليات (سيقوم داخلياً بفحص الحساب النقدي وحساب الرصيد)
        $this->calculateTotals();

        // ✅ إرسال حدث للـ Alpine.js لتصفير القيم فوراً في الواجهة
        $this->dispatch('reset-invoice-parameters');

        if ($this->showBalance) {
            $this->currentBalance = $this->getAccountBalance($value);
        }

        // ✅ Update installment modal if it exists (for sales invoices type 10)
        if ($this->type == 10 && setting('enable_installment_from_invoice')) {
            $this->dispatch('client-changed-in-invoice', [
                'clientAccountId' => $value,
                'invoiceTotal' => $this->total_after_additional,
            ]);
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

        $this->calculateTotals();
    }


    private function checkCashAccount($accountId)
    {
        $this->isCurrentAccountCash = false;

        if (! $accountId) {
            return;
        }

        // للعملاء في فواتير المبيعات ومردود المبيعات واتفاقيات التسعير
        if (in_array($this->type, [10, 12, 26]) && in_array($accountId, $this->cashClientIds)) {
            $this->isCurrentAccountCash = true;
        }
        // للموردين في فواتير المشتريات ومردود المشتريات
        elseif (in_array($this->type, [11, 13]) && in_array($accountId, $this->cashSupplierIds)) {
            $this->isCurrentAccountCash = true;
        }

        // إذا كان حساب نقدي، املأ المبلغ المدفوع بقيمة الفاتورة
        if ($this->isCurrentAccountCash && $this->total_after_additional > 0) {
            $this->received_from_client = $this->total_after_additional;
        }
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
            'description' => $item->description ?? '',
        ];
    }

    public function createItemFromPrompt($name, $barcode)
    {

        // ✅ استدعاء createNewItem وإرجاع النتيجة
        return $this->createNewItem($name, $barcode);
    }

    public function addItemByBarcode()
    {
        $barcode = trim($this->barcodeTerm);
        if (empty($barcode)) {
            return;
        }
        $item = Item::with(['units' => fn($q) => $q->orderBy('item_units.u_val', 'asc'), 'prices'])
            ->whereHas('barcodes', function ($query) use ($barcode) {
                $query->where('barcode', $barcode);
            })
            ->first();

        if (! $item) {
            // هذا الجزء يبقى كما هو لإظهار نافذة إنشاء صنف جديد
            return $this->dispatch('prompt-create-item-from-barcode', barcode: $barcode);
        }

        $this->addedFromBarcode = true;

        // Use the fast add method which handles incrementing if exists
        $result = $this->addItemFromSearchFast($item->id);

        if ($result && isset($result['success']) && $result['success']) {
            $index = $result['index'];
            $this->barcodeTerm = '';
            $this->barcodeSearchResults = [];
            $this->selectedBarcodeResultIndex = -1;
            $this->lastQuantityFieldIndex = $index;

            // Notify user
            if (isset($result['exists']) && $result['exists']) {
                $this->dispatch('alert', ['type' => 'info', 'message' => 'تم زيادة كمية الصنف.']);
            } else {
                $this->dispatch('alert', ['type' => 'success', 'message' => 'تم إضافة الصنف بنجاح.']);
            }

            $this->dispatch('focus-quantity', ['index' => $index]);
        }
    }

    // ✅ تم نقل updatedBarcodeTerm إلى Alpine.js - تبقى فارغة للتوافق

    public function handleQuantityEnter($index)
    {
        if (! isset($this->invoiceItems[$index])) {
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

        // ✅ تم نقل focusBarcodeField إلى Alpine.js
        // يمكن استخدام Alpine.js event بدلاً من window.focusBarcodeField()
    }

    public function removeRow($index)
    {
        unset($this->invoiceItems[$index]);
        $this->invoiceItems = array_values($this->invoiceItems);
        $this->calculateTotals();

        // ✅ مسح تفاصيل الصنف المختار إذا تم حذفه من القائمة
        $stillExists = false;
        foreach ($this->invoiceItems as $item) {
            if (($item['item_id'] ?? null) == $this->currentSelectedItem) {
                $stillExists = true;
                break;
            }
        }

        if (! $stillExists) {
            $this->currentSelectedItem = null;
            $this->selectedItemData = [];
        }
    }

    protected static $itemsCache = [];

    // ✅ تم حذف updatedSearchTerm() - تم استبداله بـ searchItems() method محسّن
    /**
     * Get item complete data for adding to invoice (called from Alpine.js)
     */
    public function getItemForInvoice($itemId)
    {
        $item = Item::with([
            'units' => fn($q) => $q->orderBy('pivot_u_val', 'asc'),
            'prices',
        ])->find($itemId);

        if (! $item) {
            return null;
        }

        // ✅ فحص الرصيد المتاح قبل الإضافة (فقط لفواتير البيع)
        if (in_array($this->type, [10, 12, 14, 16, 22])) {
            /** @var User|null $user */
            $user = Auth::user();
            if (! $user || ! $user->can('prevent_transactions_without_stock')) {
                $availableQty = OperationItems::where('item_id', $item->id)
                    ->where('detail_store', $this->acc2_id)
                    ->selectRaw('SUM(qty_in - qty_out) as total')
                    ->value('total') ?? 0;

                if ($availableQty <= 0) {
                    $this->dispatch(
                        'error',
                        title: 'تحذير!',
                        text: 'لا يوجد رصيد كافي من هذا الصنف في المخزن المحدد.',
                        icon: 'warning'
                    );

                    return ['error' => 'insufficient_stock'];
                }
            }
        }

        // التحقق من وجود الصنف في الفاتورة
        $existingItemIndex = null;
        foreach ($this->invoiceItems as $index => $invoiceItem) {
            if ($invoiceItem['item_id'] === $item->id) {
                $existingItemIndex = $index;
                break;
            }
        }

        // إذا كان الصنف موجود
        if ($existingItemIndex !== null) {
            $this->lastQuantityFieldIndex = $existingItemIndex;

            return [
                'exists' => true,
                'index' => $existingItemIndex,
            ];
        }

        // إضافة صنف جديد
        $firstUnit = $item->units->first();
        $unitId = $firstUnit?->id;
        $price = $this->calculateItemPrice($item, $unitId, $this->selectedPriceType);

        // ✅ فحص السعر الصفري للمشتريات
        if (in_array($this->type, [11, 15]) && $price == 0) {
            /** @var User|null $user */
            $user = Auth::user();
            if (! $user || ! $user->can('allow_purchase_with_zero_price')) {
                $this->dispatch(
                    'error',
                    title: 'خطأ!',
                    text: 'لا يمكن أن يكون سعر الشراء صفرًا.',
                    icon: 'error'
                );

                return ['error' => 'zero_price'];
            }
        }

        if (($this->settings['prevent_negative_invoice'] ?? '0') == '1' && $price < 0) {
            $this->dispatch(
                'error',
                title: 'خطأ!',
                text: 'لا يمكن إدخال سعر سالب في الفاتورة.',
                icon: 'error'
            );

            return ['error' => 'negative_price'];
        }

        $vm = new ItemViewModel(null, $item, $unitId);
        $unitOptions = $vm->getUnitOptions();
        $availableUnits = collect($unitOptions)->map(function ($unit) {
            return [
                'id' => $unit['value'],
                'name' => $unit['label'],
                'u_val' => $unit['u_val'] ?? 1,
            ];
        })->toArray();

        $quantity = ($this->settings['default_quantity_greater_than_zero'] ?? '0') == '1' && $this->type == 10 ? 1 : 1;

        $newItem = [
            'item_id' => $item->id,
            'unit_id' => $unitId,
            'name' => $item->name,
            'quantity' => $quantity,
            'price' => $price,
            'sub_value' => $price * $quantity,
            'discount' => 0,
            'available_units' => $availableUnits,
            'length' => null,
            'width' => null,
            'height' => null,
            'density' => 1,
            'batch_number' => null,
            'expiry_date' => null,
        ];

        $this->invoiceItems[] = $newItem;
        $this->lastQuantityFieldIndex = count($this->invoiceItems) - 1;

        $this->recalculateSubValues();
        $this->calculateTotals();

        return [
            'success' => true,
            'index' => $this->lastQuantityFieldIndex,
            'item' => $newItem,
        ];
    }

    /**
     * ✅ Livewire method محسّن للبحث عن الأصناف
     * يستخدم eager loading ويحسن الأداء بشكل كبير
     *
     * @param  string  $term  نص البحث
     * @return array نتائج البحث (أول 7 أصناف)
     */
    public function searchItems(string $term): array
    {
        $searchTerm = trim($term);

        // ✅ إرجاع array فارغ إذا كان البحث فارغ
        if (empty($searchTerm) || strlen($searchTerm) < 2) {
            return [];
        }

        // ✅ Cache key بناءً على معايير البحث
        $cacheKey = sprintf(
            'item_search_%s_%s_%s_%s_%s',
            md5($searchTerm),
            $this->type,
            $this->branch_id ?? 'all',
            $this->selectedPriceType ?? 1,
            'v2'
        );

        // ✅ محاولة جلب النتائج من الـ cache (30 ثانية)
        return Cache::remember($cacheKey, 30, function () use ($searchTerm) {
            // ✅ بناء الاستعلام مع eager loading لتجنب N+1
            $query = Item::select('items.id', 'items.name', 'items.code')
                ->where('items.isdeleted', 0)
                ->where(function ($q) use ($searchTerm) {
                    $q->where('items.name', 'like', $searchTerm . '%')
                        ->orWhere('items.code', 'like', $searchTerm . '%');
                });

            // ✅ تطبيق الفلاتر حسب نوع الفاتورة
            if (in_array($this->type, [11, 13, 15, 17])) {
                $query->where('items.type', ItemType::Inventory->value);
            } elseif ($this->type == 24) {
                $query->where('items.type', ItemType::Service->value);
            }

            // ✅ فلترة حسب الفرع
            if ($this->branch_id) {
                $query->where(function ($q) {
                    $q->where('items.branch_id', $this->branch_id)
                        ->orWhereNull('items.branch_id');
                });
            }

            // ✅ جلب أول 7 أصناف فقط (بدلاً من 10)
            $items = $query->limit(7)->distinct()->get();

            if ($items->isEmpty()) {
                return [];
            }

            // ✅ جلب جميع البيانات المطلوبة في استعلام واحد (eager loading)
            $itemIds = $items->pluck('id')->toArray();
            $selectedPriceId = $this->selectedPriceType ?? 1;

            // ✅ جلب الوحدات لكل الأصناف في استعلام واحد
            $unitsData = DB::table('item_units')
                ->join('units', 'item_units.unit_id', '=', 'units.id')
                ->whereIn('item_units.item_id', $itemIds)
                ->select(
                    'item_units.item_id',
                    'units.id as unit_id',
                    'units.name as unit_name',
                    'item_units.u_val as uval'
                )
                ->orderBy('item_units.item_id')
                ->orderBy('item_units.u_val', 'asc')
                ->get()
                ->groupBy('item_id');

            // ✅ جلب الأسعار لكل الأصناف في استعلام واحد
            $firstUnitIds = $unitsData->map(function ($units) {
                return $units->first()->unit_id ?? null;
            })->filter()->toArray();

            $pricesData = DB::table('item_prices')
                ->whereIn('item_id', $itemIds)
                ->where('price_id', $selectedPriceId)
                ->whereIn('unit_id', $firstUnitIds)
                ->select('item_id', 'unit_id', 'price')
                ->get()
                ->keyBy(function ($price) {
                    return $price->item_id . '_' . $price->unit_id;
                });

            // ✅ بناء النتائج
            $formatted = $items->map(function ($item) use ($unitsData, $pricesData) {
                $units = $unitsData->get($item->id, collect());
                $firstUnit = $units->first();

                // ✅ جلب السعر
                $price = 0;
                if ($firstUnit) {
                    $priceKey = $item->id . '_' . $firstUnit->unit_id;
                    $priceData = $pricesData->get($priceKey);
                    $price = $priceData->price ?? 0;
                }

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'unit_id' => $firstUnit->unit_id ?? null,
                    'unit_name' => $firstUnit->unit_name ?? null,
                    'price' => $price,
                    'units' => $units->map(function ($unit) {
                        return [
                            'id' => $unit->unit_id,
                            'name' => $unit->unit_name,
                            'uval' => $unit->uval ?? 1,
                        ];
                    })->toArray(),
                ];
            })->toArray();

            return $formatted;
        });
    }

    public function addItemFromSearchFast($itemId)
    {
        $item = Item::with([
            'units' => fn($q) => $q->orderBy('item_units.u_val', 'asc'), // ✅ صحح اسم الـ column
            'prices',
        ])->find($itemId);

        if (! $item) {
            return ['success' => false, 'message' => 'Item not found'];
        }

        // التحقق من وجود الصنف بالفعل
        $existingItemIndex = null;
        foreach ($this->invoiceItems as $index => $invoiceItem) {
            if ($invoiceItem['item_id'] == $item->id) {
                $existingItemIndex = $index;
                break;
            }
        }

        if ($existingItemIndex !== null) {
            // الصنف موجود - نزود الكمية
            $this->invoiceItems[$existingItemIndex]['quantity']++;
            $this->recalculateSubValues();
            $this->calculateTotals();

            // ✅ تحديث بيانات الصنف المختار تلقائياً (Auto-Select)
            $this->updateSelectedItemData(
                $item,
                $this->invoiceItems[$existingItemIndex]['unit_id'],
                $this->invoiceItems[$existingItemIndex]['price']
            );

            return [
                'success' => true,
                'exists' => true,
                'index' => $existingItemIndex,
            ];
        }

        // إضافة صنف جديد
        $firstUnit = $item->units->first();
        $unitId = $firstUnit?->id;
        $price = $this->calculateItemPrice($item, $unitId, $this->selectedPriceType);

        $quantity = ($this->settings->default_quantity_greater_than_zero ?? 0) == 1 && $this->type == 10 ? 1 : 1;

        // ✅ استخدم الـ units مباشرة بدون ItemViewModel - تحويل إلى array
        $availableUnits = $item->units->map(function ($unit) {
            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'u_val' => $unit->pivot->u_val ?? 1,
            ];
        })->toArray();

        $newItem = [
            'item_id' => $item->id,
            'unit_id' => $unitId,
            'name' => $item->name,
            'quantity' => $quantity,
            'price' => $price,
            'sub_value' => $price * $quantity,
            'discount' => 0,
            'available_units' => $availableUnits,
            'length' => null,
            'width' => null,
            'height' => null,
            'density' => 1,
            'batch_number' => null,
            'expiry_date' => null,
        ];

        $this->invoiceItems[] = $newItem;
        $newIndex = count($this->invoiceItems) - 1;

        $this->recalculateSubValues();
        $this->calculateTotals();

        // ✅ تحديث بيانات الصنف المختار تلقائياً (Auto-Select)
        $this->updateSelectedItemData($item, $unitId, $price);

        return [
            'success' => true,
            'index' => $newIndex,
            'item' => $newItem,
        ];
    }

    /**
     * Add item to invoice from Alpine.js API call
     * This method is called by Alpine.js after getting item data from API
     */
    public function addItemToInvoice(array $itemData)
    {
        // Add item to invoiceItems array
        $this->invoiceItems[] = $itemData;
        $newIndex = count($this->invoiceItems) - 1;

        // Recalculate
        $this->recalculateSubValues();
        $this->calculateTotals();

        // Update selected item data if item exists
        $item = Item::with(['units', 'prices'])->find($itemData['item_id']);
        if ($item) {
            $this->updateSelectedItemData(
                $item,
                $itemData['unit_id'],
                $itemData['price']
            );
        }

        return [
            'success' => true,
            'index' => $newIndex,
        ];
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
        if (! isset($this->invoiceItems[$index])) {
            return;
        }

        $itemId = $this->invoiceItems[$index]['item_id'];
        $item = $this->items->firstWhere('id', $itemId);

        if (! $item) {
            return;
        }

        // إعداد الوحدات المتاحة
        $vm = new ItemViewModel(null, $item);
        $opts = $vm->getUnitOptions();

        $unitsCollection = collect($opts)->map(fn($entry) => [
            'id' => $entry['value'],
            'name' => $entry['label'],
            'u_val' => $entry['u_val'] ?? 1,
        ])->toArray();

        $this->invoiceItems[$index]['available_units'] = $unitsCollection;

        // إذا لم يتم تحديد وحدة، اختر الأولى
        if (empty($this->invoiceItems[$index]['unit_id'])) {
            $firstUnit = ! empty($unitsCollection) ? $unitsCollection[0] : null;
            if ($firstUnit) {
                $this->invoiceItems[$index]['unit_id'] = $firstUnit['id'] ?? null;
            }
        }
        // تحديث السعر بناءً على الوحدة المختارة
        $this->updatePriceForUnit($index);
    }

    public function updateQuantityForUnit($index)
    {
        if (! isset($this->invoiceItems[$index])) {
            return;
        }

        $itemId = $this->invoiceItems[$index]['item_id'];
        $unitId = $this->invoiceItems[$index]['unit_id'];
        $oldUnitId = $this->oldUnitId;

        if (! $itemId || ! $unitId || ! $oldUnitId || $unitId == $oldUnitId) {
            return;
        }

        $item = $this->items->firstWhere('id', $itemId);
        // ✅ إذا لم يتم العثور على الصنف في القائمة المحملة، قم بجلبه من قاعدة البيانات
        if (! $item) {
            $item = Item::with(['units'])->find($itemId);
        }
        if (! $item) {
            return;
        }

        $oldUnit = $item->units->where('id', $oldUnitId)->first();
        $newUnit = $item->units->where('id', $unitId)->first();

        if ($oldUnit && $newUnit) {
            $oldUVal = $oldUnit->pivot->u_val ?? 1;
            $newUVal = $newUnit->pivot->u_val ?? 1;

            if ($newUVal == 0) {
                return;
            } // Avoid division by zero

            // Calculate conversion factor: old / new
            $conversionFactor = $oldUVal / $newUVal;

            $currentQty = (float) ($this->invoiceItems[$index]['quantity'] ?? 0);
            $newQty = $currentQty * $conversionFactor;

            $this->invoiceItems[$index]['quantity'] = round($newQty, 4);
        }
    }

    public function updatePriceForUnit($index)
    {
        if (! isset($this->invoiceItems[$index])) {
            return;
        }

        $itemId = $this->invoiceItems[$index]['item_id'];
        $unitId = $this->invoiceItems[$index]['unit_id'];

        if (! $itemId || ! $unitId) {
            return;
        }

        $item = $this->items->firstWhere('id', $itemId);

        // ✅ إذا لم يتم العثور على الصنف في القائمة المحملة (لأنه تم البحث عنه وإضافته)، قم بجلبه من قاعدة البيانات
        if (! $item) {
            $item = Item::with(['units', 'prices'])->find($itemId);
        }

        if (! $item) {
            return;
        }

        if ($this->type == 11 && (! setting('allow_purchase_price_change'))) {
            $this->dispatch(
                'error',
                title: 'خطأ!',
                text: 'غير مسموح بتغيير سعر البيع في فاتورة المشتريات.',
                icon: 'error'
            );

            return;
        }
        // حساب السعر للوحدة المختارة
        $currentPrice = (float) ($this->invoiceItems[$index]['price'] ?? 0);
        $price = $this->calculateItemPrice($item, $unitId, $this->selectedPriceType, $currentPrice, $this->oldUnitId);

        // إعادة تعيين الوحدة القديمة بعد الاستخدام
        $this->oldUnitId = null;

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
        if (count($parts) < 2) {
            return;
        }

        $rowIndex = (int) $parts[0];
        $field = $parts[1];

        if (in_array($field, ['length', 'width', 'height', 'density']) && $this->enableDimensionsCalculation) {
            $this->calculateQuantityFromDimensions($rowIndex);

            return;
        }

        if ($field === 'quantity') {
            $this->quantityClickCount = 0;

            // ✅ فحص الرصيد عند تغيير الكمية
            if (in_array($this->type, [10, 12, 14, 16, 22])) {
                /** @var User|null $user */
                $user = Auth::user();
                if (! $user || ! $user->can('prevent_transactions_without_stock')) {
                    $itemId = $this->invoiceItems[$rowIndex]['item_id'] ?? null;
                    $requestedQty = (float) $value;

                    $availableQty = OperationItems::where('item_id', $itemId)
                        ->where('detail_store', $this->acc2_id)
                        ->selectRaw('SUM(qty_in - qty_out) as total')
                        ->value('total') ?? 0;

                    if ($requestedQty > $availableQty) {
                        $this->invoiceItems[$rowIndex]['quantity'] = $availableQty;
                        $this->dispatch(
                            'error',
                            title: 'تحذير!',
                            text: "الكمية المطلوبة ({$requestedQty}) أكبر من المتاح ({$availableQty}). تم تعديل الكمية تلقائياً.",
                            icon: 'warning'
                        );
                    }
                }
            }

            if ($this->expiryDateMode !== 'disabled' && in_array($this->type, [10, 12, 14, 16, 22])) {
                $isValid = $this->validateBatchQuantity($rowIndex);
                if (! $isValid && $this->expiryDateMode === 'nearest_first') {
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
            //     $this->updateUnits($rowIndex);

            //     $itemId = $this->invoiceItems[$rowIndex]['item_id'];
            //     if ($itemId) {
            //         $item = Item::with(['units', 'prices'])->find($itemId);
            //         if ($item) {
            //             $unitId = $this->invoiceItems[$rowIndex]['unit_id'];
            //             $price = $this->invoiceItems[$rowIndex]['price'];
            //             $this->updateSelectedItemData($item, $unitId, $price);
            //         }
            //     }
            // }
            // if ($field === 'item_id') {
            //     // عند تغيير الصنف، قم بتحديث الوحدات
            //     $this->updateUnits($rowIndex);

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
            // $this->updatePriceForUnit($rowIndex);
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
            if (($this->settings['allow_edit_invoice_value'] ?? '0') != '1') {
                // $this->dispatch(
                //     'error',
                //     title: 'خطأ!',
                //     text: 'غير مسموح بتعديل قيمة الفاتورة مباشرة.',
                //     icon: 'error'
                // );
                return;
            }
            $this->calculateQuantityFromSubValue($rowIndex);
        } elseif ($field === 'price') {
            // ✅ فحص صلاحية تغيير السعر
            /** @var User|null $user */
            $user = Auth::user();
            if (! $user || ! $user->can('allow_price_change')) {
                $this->dispatch(
                    'error',
                    title: 'خطأ!',
                    text: 'غير مسموح بتغيير سعر البيع. تواصل مع المسؤول للحصول على الصلاحية.',
                    icon: 'error'
                );
                // إرجاع السعر للقيمة السابقة
                $itemId = $this->invoiceItems[$rowIndex]['item_id'];
                $unitId = $this->invoiceItems[$rowIndex]['unit_id'];
                $item = Item::with(['units', 'prices'])->find($itemId);
                if ($item) {
                    $this->invoiceItems[$rowIndex]['price'] = $this->calculateItemPrice($item, $unitId, $this->selectedPriceType);
                }

                return;
            }

            if ($this->type == 11 && ! setting('allow_purchase_price_change')) {
                $this->dispatch(
                    'error',
                    title: 'خطأ!',
                    text: 'غير مسموح بتغيير سعر الشراء في فاتورة المشتريات.',
                    icon: 'error'
                );

                return;
            }
        } elseif ($field === 'discount') {
            // ✅ فحص صلاحية تغيير الخصم
            /** @var User|null $user */
            $user = Auth::user();
            if (! $user || ! $user->can('allow_discount_change')) {
                // $this->dispatch(
                //     'error',
                //     title: 'خطأ!',
                //     text: 'غير مسموح بتعديل الخصم. تواصل مع المسؤول للحصول على الصلاحية.',
                //     icon: 'error'
                // );
                $this->invoiceItems[$rowIndex]['discount'] = 0;

                return;
            }
        }

        if (in_array($field, ['quantity', 'price', 'discount'])) {
            if (($this->settings['prevent_negative_invoice'] ?? '0') == '1' && $value < 0) {
                $this->invoiceItems[$rowIndex][$field] = 0;
                $this->dispatch(
                    'error',
                    title: 'خطأ!',
                    text: 'لا يمكن إدخال قيم سالبة في الفاتورة.',
                    icon: 'error'
                );
            }

            if ($field === 'price' && ($this->settings['allow_zero_price_in_invoice'] ?? '0') != '1' && $value == 0) {
                // ✅ فحص السعر الصفري للمشتريات
                if (in_array($this->type, [11, 15])) {
                    /** @var User|null $user */
                    $user = Auth::user();
                    if (! $user || ! $user->can('allow_purchase_with_zero_price')) {
                        $this->invoiceItems[$rowIndex]['price'] = 0;
                        $this->dispatch(
                            'error',
                            title: 'خطأ!',
                            text: 'لا يمكن أن يكون سعر الشراء صفرًا.',
                            icon: 'error'
                        );
                    }
                }
            }

            $this->recalculateSubValues();
            $this->calculateTotals();
        }

        $this->calculateBalanceAfterInvoice();
    }

    private function updatePriceToLastCustomerPrice($index)
    {
        if (! isset($this->invoiceItems[$index])) {
            return;
        }

        $itemId = $this->invoiceItems[$index]['item_id'];
        $unitId = $this->invoiceItems[$index]['unit_id'];

        if (! $itemId || ! $unitId || ! $this->acc1_id) {
            return;
        }

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
        if (! isset($this->invoiceItems[$index])) {
            return;
        }

        $itemId = $this->invoiceItems[$index]['item_id'];
        $unitId = $this->invoiceItems[$index]['unit_id'];

        if (! $itemId || ! $unitId || ! $this->acc1_id) {
            return;
        }

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
        if (! isset($this->invoiceItems[$index])) {
            return;
        }

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
            $itemTotal = $qty * $price;

            // ✅ التحقق من أن الخصم لا يتجاوز قيمة الصنف
            if ($discount > $itemTotal) {
                $this->invoiceItems[$index]['discount'] = $itemTotal;
                $discount = $itemTotal;
                $this->dispatch(
                    'error',
                    title: 'تحذير!',
                    text: 'الخصم لا يمكن أن يكون أكبر من قيمة الصنف. تم تعديل الخصم تلقائياً.',
                    icon: 'warning'
                );
            }

            $sub = $itemTotal - $discount;
            $this->invoiceItems[$index]['sub_value'] = round(max(0, $sub), 2);
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

        // Calculate base total after discount and additional
        $baseTotal = round($this->subtotal - $this->discount_value + $this->additional_value, 2);

        // Calculate VAT and Withholding Tax if enabled
        if (isVatEnabled() || isWithholdingTaxEnabled()) {
            $this->vat_value = round(($baseTotal * $this->vat_percentage) / 100, 2);
            $this->withholding_tax_value = round(($baseTotal * $this->withholding_tax_percentage) / 100, 2);

            // Final total = base + VAT - withholding tax
            $this->total_after_additional = round($baseTotal + $this->vat_value - $this->withholding_tax_value, 2);
        } else {
            $this->vat_value = 0;
            $this->withholding_tax_value = 0;
            $this->total_after_additional = $baseTotal;
        }

        $this->checkCashAccount($this->acc1_id);

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
        info('createNewItem called', ['name' => $name, 'barcode' => $barcode]);

        try {
            // التحقق من وجود صنف بنفس الاسم
            $existingItem = Item::where('name', $name)->first();

            if ($existingItem) {
                // إضافة الصنف الموجود للفاتورة
                $result = $this->addItemFromSearchFast($existingItem->id);
                $this->searchTerm = '';
                $this->barcodeTerm = '';

                // Return the result so the frontend can use it
                return $result;
            }

            // توليد كود فريد
            $defaultBarcode = (Item::max('code') + 1) ?? 1;

            // ✅ إذا لم يتم إرسال باركود، استخدم الكود التلقائي
            $finalBarcode = $barcode ?? $defaultBarcode;

            // ✅ التحقق من الباركود فقط إذا كان مختلف عن الكود التلقائي
            if ($barcode && $barcode != $defaultBarcode) {
                $existingBarcode = Barcode::where('barcode', $finalBarcode)->exists();

                if ($existingBarcode) {
                    $this->dispatch('error', [
                        'title' => 'خطأ!',
                        'text' => 'الباركود "' . $finalBarcode . '" مستخدم من قبل. سيتم استخدام باركود تلقائي.',
                        'icon' => 'error',
                    ]);

                    // استخدم الكود التلقائي بدلاً من الباركود المكرر
                    $finalBarcode = $defaultBarcode;
                }
            }

            // إنشاء الصنف
            $newItem = Item::create([
                'name' => $name,
                'code' => $defaultBarcode,
            ]);

            // ربط الوحدة الافتراضية
            $newItem->units()->attach(1, ['u_val' => 1, 'cost' => 0]);

            // إنشاء الباركود
            $newItem->barcodes()->create([
                'barcode' => $finalBarcode,
                'unit_id' => 1,
            ]);

            // إعادة تحميل الصنف مع الـ relationships
            $newItem = Item::with([
                'units' => fn($q) => $q->orderBy('item_units.u_val', 'asc'),
                'prices',
            ])->find($newItem->id);

            // إضافة الصنف للفاتورة تلقائياً
            $result = $this->addItemFromSearchFast($newItem->id);

            $this->searchTerm = '';
            $this->barcodeTerm = '';

            return $result;
        } catch (\Exception) {

            $this->dispatch('error', [
                'title' => 'خطأ!',
                'text' => 'حدث خطأ: ',
                'icon' => 'error',
            ]);

            // Return error result
            return [
                'success' => false,
            ];
        }
    }

    // ✅ تم نقل حسابات الخصم والإضافي إلى Alpine.js component (invoiceCalculations)
    // تم حذف: updatedDiscountPercentage, updatedDiscountValue, updatedAdditionalPercentage, updatedAdditionalValue
    // جميع الحسابات تتم في Alpine.js والمزامنة تتم عبر syncFromAlpine() قبل الحفظ

    // ✅ تم نقل handleKeyDown إلى Alpine.js (invoiceSearch component)
    // يمكن حذف هذه method لأنها لم تعد مستخدمة

    // public function handleKeyUp()
    // {
    //     if ($this->selectedResultIndex > -1) {
    //         $this->selectedResultIndex--;
    //         $this->isCreateNewItemSelected = false;
    //     }
    // }

    // public function handleEnter()
    // {
    //     if ($this->selectedResultIndex >= 0 && $this->searchResults && !$this->isCreateNewItemSelected) {
    //         $item = $this->searchResults->get($this->selectedResultIndex);
    //         if ($item) {
    //             $this->addItemFromSearch($item->id);
    //         }
    //     }
    //     // لو تم تحديد زر "إنشاء صنف جديد"
    //     elseif ($this->isCreateNewItemSelected && strlen($this->searchTerm) > 0) {
    //         $this->createNewItem($this->searchTerm);
    //         $this->isCreateNewItemSelected = false; // إعادة تعيين الحالة
    //     }
    // }

    // ✅ تم نقل moveToNextField إلى Alpine.js (invoiceCalculations.moveToNextField)
    // يمكن حذف هذه method لأنها لم تعد مستخدمة

    // ✅ تم نقل checkSearchResults إلى Alpine.js - محذوفة

    /**
     * ✅ استقبال البيانات من Alpine.js قبل الحفظ
     * يُستدعى من @submit.prevent="syncToLivewire()" في النموذج
     *
     * @param  array  $alpineData  البيانات المحسوبة في Alpine.js
     */
    public function syncFromAlpine(array $alpineData): void
    {
        // مزامنة القيم الحسابية من Alpine.js
        if (isset($alpineData['subtotal'])) {
            $this->subtotal = (float) $alpineData['subtotal'];
        }
        if (isset($alpineData['discount_percentage'])) {
            $this->discount_percentage = (float) $alpineData['discount_percentage'];
        }
        if (isset($alpineData['discount_value'])) {
            $this->discount_value = (float) $alpineData['discount_value'];
        }
        if (isset($alpineData['additional_percentage'])) {
            $this->additional_percentage = (float) $alpineData['additional_percentage'];
        }
        if (isset($alpineData['additional_value'])) {
            $this->additional_value = (float) $alpineData['additional_value'];
        }
        if (isset($alpineData['total_after_additional'])) {
            $this->total_after_additional = (float) $alpineData['total_after_additional'];
        }
        if (isset($alpineData['received_from_client'])) {
            $this->received_from_client = (float) $alpineData['received_from_client'];
        }

        // مزامنة بيانات الأصناف إذا تم إرسالها
        if (isset($alpineData['invoiceItems']) && is_array($alpineData['invoiceItems'])) {
            foreach ($alpineData['invoiceItems'] as $index => $item) {
                if (isset($this->invoiceItems[$index])) {
                    if (isset($item['sub_value'])) {
                        $this->invoiceItems[$index]['sub_value'] = (float) $item['sub_value'];
                    }
                    if (isset($item['quantity'])) {
                        $this->invoiceItems[$index]['quantity'] = (float) $item['quantity'];
                    }
                    if (isset($item['price'])) {
                        $this->invoiceItems[$index]['price'] = (float) $item['price'];
                    }
                    if (isset($item['discount'])) {
                        $this->invoiceItems[$index]['discount'] = (float) $item['discount'];
                    }
                }
            }
        }
    }

    public function saveForm()
    {
        // ✅ 1. إعادة حساب جميع الإجماليات للتأكد من صحتها
        $this->recalculateSubValues();
        $this->calculateTotals();

        // ✅ 2. Validation نهائي
        if (empty($this->invoiceItems)) {
            $this->dispatch(
                'error',
                title: 'خطأ!',
                text: 'يجب إضافة صنف واحد على الأقل.',
                icon: 'error'
            );

            return null;
        }

        // التحقق من صحة الأصناف
        foreach ($this->invoiceItems as $index => $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['price'] ?? 0);

            if ($quantity <= 0) {
                $this->dispatch(
                    'error',
                    title: 'خطأ!',
                    text: 'الكمية في الصف ' . ($index + 1) . ' يجب أن تكون أكبر من صفر.',
                    icon: 'error'
                );

                return null;
            }

            if ($price < 0) {
                $this->dispatch(
                    'error',
                    title: 'خطأ!',
                    text: 'السعر في الصف ' . ($index + 1) . ' لا يمكن أن يكون سالباً.',
                    icon: 'error'
                );

                return null;
            }
        }

        if (($this->settings['allow_zero_invoice_total'] ?? '0') != '1' && $this->total_after_additional == 0) {
            $this->dispatch(
                'error',
                title: 'خطأ!',
                text: 'قيمة الفاتورة لا يمكن أن تكون صفرًا.',
                icon: 'error'
            );

            return null;
        }

        // ✅ 3. الحفظ
        $calculator = new DetailValueCalculator;
        $validator = new DetailValueValidator;
        $service = new SaveInvoiceService($calculator, $validator);
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

    public function getAcc1OptionsProperty()
    {
        return collect($this->acc1List ?? [])->map(function ($account) {
            // Handle both object and array access just in case
            $id = is_array($account) ? ($account['id'] ?? null) : ($account->id ?? null);
            $name = is_array($account) ? ($account['aname'] ?? '') : ($account->aname ?? '');
            $code = is_array($account) ? ($account['code'] ?? '') : ($account->code ?? '');
            // Determine group based on code
            $group = 'أخرى';
            if (str_starts_with($code, '1103')) {
                $group = 'العملاء';
            } elseif (str_starts_with($code, '2101')) {
                $group = 'الموردين';
            } elseif (str_starts_with($code, '2102')) {
                $group = 'الموظفين';
            } elseif (str_starts_with($code, '1104')) {
                $group = 'المخازن';
            } elseif (str_starts_with($code, '53')) {
                $group = 'المصروفات';
            }

            return [
                'value' => $id,
                'label' => $name,
                'group' => $group,
            ];
        })->values()->toArray();
    }

    public function openInstallmentModal()
    {
        // Check if client is selected (acc1_id for sales invoices - العميل)
        if (! $this->acc1_id || $this->acc1_id === 'null' || $this->acc1_id === null) {
            $this->dispatch('show-warning', [
                'title' => 'تحذير',
                'text' => 'يرجى اختيار العميل في الفاتورة أولاً',
            ]);

            return;
        }

        // Make sure totals are calculated before sending
        $this->calculateTotals();

        // Dispatch event with invoice data (total_after_additional is the final total)
        $this->dispatch('update-installment-data', [
            'invoiceTotal' => $this->total_after_additional, // Final total after discount and additional
            'clientAccountId' => $this->acc1_id, // acc1_id is the client in sales invoices (العميل)
        ]);
    }

    public function openInstallmentModalWithTotal($finalTotal)
    {
        // Check if client is selected (acc1_id for sales invoices - العميل)
        if (! $this->acc1_id || $this->acc1_id === 'null' || $this->acc1_id === null) {
            $this->dispatch('show-warning', [
                'title' => 'تحذير',
                'text' => 'يرجى اختيار العميل في الفاتورة أولاً',
            ]);

            return;
        }
        // Use the final total from Alpine.js (includes VAT and withholding tax)
        $this->dispatch('update-installment-data', [
            'invoiceTotal' => floatval($finalTotal), // Final total from Alpine.js
            'clientAccountId' => $this->acc1_id, // acc1_id is the client in sales invoices (العميل)
        ]);
    }

    public function render()
    {
        return view('invoices::livewire.invoices.create-invoice-form', [
            'acc1Options' => $this->acc1Options,
        ]);
    }
}
