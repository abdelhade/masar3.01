<?php

namespace App\Livewire;

use App\Enums\InvoiceStatus;
use App\Helpers\ItemViewModel;
use App\Models\Barcode;
use App\Models\Item;
use App\Models\JournalDetail;
use App\Models\OperationItems;
use App\Models\OperHead;
use App\Models\Price;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Modules\Accounts\Models\AccHead;
use Modules\Invoices\Models\InvoiceTemplate;
use RealRashid\SweetAlert\Facades\Alert;

class EditInvoiceForm extends Component
{
    public $operationId;

    public $operation;
    public $type;
    public $acc1_id;

    protected $listeners = [
        'account-created' => 'handleAccountCreated',
        'branch-changed' => 'handleBranchChange',
        'batch-selected' => 'selectBatch',
    ];


    public $acc2_id;

    public $emp_id;

    public $pro_date;

    public $accural_date;

    public $pro_id;

    public $serial_number;

    public $deliverys = [];

    public $delivery_id = null;

    public $barcodeTerm = '';

    public $barcodeSearchResults;

    public $selectedBarcodeResultIndex = -1;

    public bool $addedFromBarcode = false;

    public $searchedTerm = '';

    public $isCreateNewItemSelected = false;

    public $currentBalance = 0;

    public $balanceAfterInvoice = 0;

    public $showBalance = false;

    public $branch_id;

    public $branches;

    public $showConvertModal = false;

    public $selectedConvertType = null;

    public $convertFromTypes = [];

    public $originalInvoiceId = null;

    public $priceTypes = [];

    public $selectedPriceType = 1;

    public $selectedUnit = [];

    public $searchTerm = '';

    public $searchResults;

    public $selectedResultIndex = -1;

    public int $quantityClickCount = 0;

    public $lastQuantityFieldIndex = null;

    public $acc1List = [];

    public $acc2List = [];

    public $employees = [];

    public $acc1Role;

    public $acc2Role;

    public $isCurrentAccountCash = false;

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

    public $currentSelectedItem = null;

    public $selectedItemData = [
        'name' => '',
        'code' => '',
        'available_quantity' => 0,
        'unit_name' => '',
        'price' => 0,
        'cost' => 0,
        'batch-selected' => 'selectBatch',
        'barcode' => '',
        'category' => '',
        'description' => '',
        'average_cost' => '',
        'last_purchase_price' => 0,
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

    public $titles = [
        10 => 'فاتوره مبيعات',
        11 => 'فاتورة مشتريات',
        12 => 'مردود مبيعات',
        13 => 'مردود مشتريات',
        14 => 'امر بيع',
        15 => 'امر شراء',
        16 => 'عرض سعر لعميل',
        17 => 'عرض سعر من مورد',
        18 => 'فاتورة توالف',
        19 => 'امر صرف',
        20 => 'امر اضافة',
        21 => 'تحويل من مخزن لمخزن',
        22 => 'امر حجز',
    ];

    public $is_disabled = false; // Direct edit mode enabled by default

    // Template properties
    public $availableTemplates;

    public $selectedTemplateId = null;

    public $currentTemplate = null;

    // Status properties for sale orders (type 14)
    public $statues = [];

    public $status = null;

    public function mount($operationId)
    {
        $this->branches = userBranches();
        if ($this->branches->isNotEmpty()) {
            $this->branch_id = $this->branches->first()->id;
        }
        $this->operationId = $operationId;
        $this->operation = OperHead::with(['operationItems.item.units', 'operationItems.item.prices'])
            ->findOrFail($operationId);

        $this->type = $this->operation->pro_type;
        $this->acc1_id = $this->operation->acc1;
        $this->acc2_id = $this->operation->acc2;
        $this->emp_id = $this->operation->emp_id;
        $this->pro_date = $this->operation->pro_date;
        $this->accural_date = $this->operation->accural_date;
        $this->pro_id = $this->operation->pro_id;
        $this->serial_number = $this->operation->pro_serial;
        $this->selectedPriceType = $this->operation->price_list ?? 1;
        $this->discount_percentage = $this->operation->fat_disc_per ?? 0;
        $this->discount_value = $this->operation->fat_disc ?? 0;
        $this->additional_percentage = $this->operation->fat_plus_per ?? 0;
        $this->additional_value = $this->operation->fat_plus ?? 0;
        $this->subtotal = $this->operation->fat_total ?? 0;
        $this->total_after_additional = $this->operation->fat_net ?? 0;
        $this->notes = $this->operation->info ?? '';
        $this->received_from_client = $this->operation->paid_from_client ?? 0;

        $this->items = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices'])->get();

        $this->cashAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('is_fund', 1)
            ->select('id', 'aname')
            ->get();

        // ✅ تحميل قوائم الحسابات حسب الفرع ونوع الفاتورة
        $this->loadBranchFilteredData($this->branch_id);

        // ✅ التحقق من حالة الحساب النقدي
        $this->checkCashAccount($this->acc1_id);

        $this->priceTypes = Price::pluck('name', 'id')->toArray();
        $this->searchResults = collect();
        $this->barcodeSearchResults = collect();
        $this->loadInvoiceItems();

        $this->showBalance = in_array($this->type, [10, 11, 12, 13]);
        if ($this->showBalance && $this->acc1_id) {
            $this->currentBalance = $this->getAccountBalance($this->acc1_id);
            $this->calculateBalanceAfterInvoice();
        }
        $this->barcodeSearchResults = collect();

        $this->deliverys = $this->getAccountsByCode('2102%');

        // Load templates for this invoice type
        $this->loadTemplatesForType();

        // Initialize statuses for sale orders (type 14)
        if ($this->type == 14) {
            $this->statues = InvoiceStatus::cases();
            $this->status = $this->operation->status ?? InvoiceStatus::DELIVERED->value;
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

    private function getAccountsByCode(string $code)
    {
        return AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', $code)
            ->select('id', 'aname')
            ->get();
    }

    protected static array $accountCache = [];

    protected function loadBranchFilteredData($branchId)
    {
        if (!$branchId) return;

        $clientsAccounts = $this->getAccountsByCodeAndBranch('1103%', $branchId);
        $suppliersAccounts = $this->getAccountsByCodeAndBranch('2101%', $branchId);
        $employeesAccounts = $this->getAccountsByCodeAndBranch('2102%', $branchId);
        $wasted = $this->getAccountsByCodeAndBranch('55%', $branchId);
        $accounts = $this->getAccountsByCodeAndBranch('1108%', $branchId);
        $stores = $this->getAccountsByCodeAndBranch('1104%', $branchId);

        // ✅ تعيين أسماء الحسابات حسب نوع الفاتورة
        $map = [
            10 => ['acc1_role' => 'عميل', 'acc2_role' => 'مخزن'],
            11 => ['acc1_role' => 'مورد', 'acc2_role' => 'مخزن'],
            12 => ['acc1_role' => 'عميل', 'acc2_role' => 'مخزن'],
            13 => ['acc1_role' => 'مورد', 'acc2_role' => 'مخزن'],
            14 => ['acc1_role' => 'عميل', 'acc2_role' => 'مخزن'],
            15 => ['acc1_role' => 'مورد', 'acc2_role' => 'مخزن'],
            16 => ['acc1_role' => 'عميل', 'acc2_role' => 'مخزن'],
            17 => ['acc1_role' => 'مورد', 'acc2_role' => 'مخزن'],
            18 => ['acc1_role' => 'تالف', 'acc2_role' => 'مخزن'],
            19 => ['acc1_role' => 'حساب', 'acc2_role' => 'مخزن'],
            20 => ['acc1_role' => 'حساب', 'acc2_role' => 'مخزن'],
            21 => ['acc1_role' => 'مخزن من', 'acc2_role' => 'مخزن إلى'],
            22 => ['acc1_role' => 'عميل', 'acc2_role' => 'مخزن'],
            24 => ['acc1_role' => 'مصروف', 'acc2_role' => 'مورد'],
            25 => ['acc1_role' => 'مصروف', 'acc2_role' => 'مخزن'],
            26 => ['acc1_role' => 'عميل', 'acc2_role' => 'مخزن'],
        ];
        $this->acc1Role = $map[$this->type]['acc1_role'] ?? 'الحساب الأول';
        $this->acc2Role = $map[$this->type]['acc2_role'] ?? 'الحساب الثاني';

        // ✅ تحقق من الإعداد
        $allowAllClientTypes = setting('invoice_enable_all_client_types') == '1';

        // القيم المجمعة (للاستخدام عند تفعيل الإعداد)
        $mergedAccounts = null;
        if ($allowAllClientTypes) {
             $mergedAccounts = collect()
                ->merge($clientsAccounts)
                ->merge($suppliersAccounts)
                ->merge($employeesAccounts)
                ->unique('id')
                ->values();
        }

        // تحديد acc1 حسب نوع الفاتورة
        if (in_array($this->type, [10, 12, 14, 16, 22])) {
            $this->acc1List = $allowAllClientTypes ? $mergedAccounts : $clientsAccounts;
        } elseif (in_array($this->type, [11, 13, 15, 17])) {
            $this->acc1List = $allowAllClientTypes ? $mergedAccounts : $suppliersAccounts;
        } elseif ($this->type == 18) {
            $this->acc1List = $wasted;
        } elseif (in_array($this->type, [19, 20])) {
            $this->acc1List = $accounts;
        } elseif ($this->type == 21) {
            $this->acc1List = $stores;
        } elseif ($this->type == 25) {
            $this->acc1List = $this->getAccountsByCodeAndBranch('53%', $branchId);
        } elseif ($this->type == 24) {
            $this->acc1List = $this->getAccountsByCodeAndBranch('5%', $branchId);
        }

        $this->acc2List = $this->type == 24 ? $suppliersAccounts : $stores;
        $this->employees = $this->getAccountsByCodeAndBranch('2102%', $branchId);
        $this->deliverys = $this->getAccountsByCodeAndBranch('2102%', $branchId);
    }

    protected function getAccountsByCodeAndBranch(string $code, $branchId)
    {
        $cacheKey = $code . '_' . $branchId;
        if (!isset(static::$accountCache[$cacheKey])) {
            static::$accountCache[$cacheKey] = AccHead::where('isdeleted', 0)
                ->where('is_basic', 0)
                ->where('code', 'like', $code)
                ->where('branch_id', $branchId)
                ->select('id', 'code', 'aname')
                ->orderBy('id')
                ->get();
        }
        return static::$accountCache[$cacheKey];
    }

    private function getAccountBalance($accountId)
    {
        $totalDebit = JournalDetail::where('account_id', $accountId)
            ->where('isdeleted', 0)
            ->sum('debit');

        $totalCredit = JournalDetail::where('account_id', $accountId)
            ->where('isdeleted', 0)
            ->sum('credit');

        return $totalDebit - $totalCredit;
    }

    private function loadInvoiceItems()
    {
        $this->invoiceItems = [];
        foreach ($this->operation->operationItems as $operationItem) {
            $item = $operationItem->item;
            if (! $item) {
                continue;
            }

            $availableUnits = $item->units->map(
                fn($unit) => (object) [
                    'id' => $unit->id,
                    'name' => $unit->name,
                ]
            );

            $displayUnitId = $operationItem->fat_unit_id ?: $operationItem->unit_id;

            // 1. Fetch the u_val (conversion factor)
            $unit = $item->units->firstWhere('id', $displayUnitId);
            $uVal = $unit?->pivot?->u_val ?? DB::table('item_units')
                ->where('item_id', $item->id)
                ->where('unit_id', $displayUnitId)
                ->value('u_val') ?? 1;

            // 2. Get the stored base quantity (operation always stores base units)
            $baseQty = $operationItem->qty_in > 0 ? $operationItem->qty_in : $operationItem->qty_out;

            // 3. Divide by u_val to get display quantity
            $quantity = $uVal > 0 ? $baseQty / $uVal : $baseQty;
            $price = $operationItem->fat_price ?? $operationItem->item_price;

            $this->invoiceItems[] = [
                'operation_item_id' => $operationItem->id,
                'item_id' => $item->id,
                'unit_id' => $displayUnitId,
                'name' => $item->name,
                'quantity' => $quantity,
                'price' => $price,
                'sub_value' => $operationItem->detail_value ?? ($price * $quantity) - ($operationItem->item_discount ?? 0),
                'discount' => $operationItem->item_discount ?? 0,
                'available_units' => $availableUnits,
                'notes' => $operationItem->notes ?? '',
            ];
        }
    }

    public function getCompatibleConversionTypes()
    {
        $conversionRules = [
            10 => [12, 14, 16],
            11 => [13, 15, 17],
            12 => [10, 14, 16],
            13 => [11, 15, 17],
            14 => [10, 16],
            15 => [11, 17],
            16 => [10, 14],
            17 => [11, 15],
            18 => [19, 20],
            19 => [18, 20],
            20 => [18, 19],
            21 => [19, 20],
            22 => [10, 14, 16],
        ];
        $allowedTypes = $conversionRules[$this->type] ?? array_keys($this->titles);
        $allowedTypes = array_filter($allowedTypes, fn($type) => $type !== $this->type);

        return array_intersect_key($this->titles, array_flip($allowedTypes));
    }

    public function closeConvertModal()
    {
        $this->showConvertModal = false;
        $this->selectedConvertType = null;
        $this->convertFromTypes = [];
    }

    public function getConversionConfirmationMessage()
    {
        if (! $this->selectedConvertType) {
            return '';
        }
        $fromType = $this->titles[$this->type] ?? 'غير محدد';
        $toType = $this->titles[$this->selectedConvertType] ?? 'غير محدد';

        return "هل أنت متأكد من تحويل الفاتورة من \"$fromType\" إلى \"$toType\"؟";
    }

    // public function canConvertInvoice()
    // {
    //     return !empty($this->invoiceItems) && !$this->is_disabled;
    // }

    public function convertInvoice()
    {
        if (! $this->selectedConvertType) {
            Alert::toast('يرجى اختيار نوع الفاتورة المراد التحويل إليها', 'error');

            return;
        }
        $oldType = $this->type;
        $this->originalInvoiceId = $this->operation->id;
        $this->type = (int) $this->selectedConvertType;
        $this->updateAccountsForNewType();
        $this->updatePricesForNewType();
        $this->calculateTotals();
        $this->closeConvertModal();
        Alert::toast('تم تحويل الفاتورة بنجاح من ' . $this->titles[$oldType] . ' إلى ' . $this->titles[$this->type], 'success');
    }

    // Direct edit mode - no need for enableEditing method
    // Edit is always enabled

    public function updatedAcc1Id($value)
    {
        // ✅ إعادة تعيين القيم عند تغيير العميل
        $this->discount_percentage = 0;
        $this->discount_value = 0;
        $this->additional_percentage = 0;
        $this->additional_value = 0;
        $this->received_from_client = 0;

        // ✅ التحقق من الحساب النقدي وتحديث الحالة
        $this->checkCashAccount($value);

        // ✅ إعادة حساب الإجماليات
        $this->calculateTotals();

        // ✅ إرسال حدث للـ Alpine.js لتصفير القيم فوراً في الواجهة
        $this->dispatch('reset-invoice-parameters');

        if ($this->showBalance) {
            $this->currentBalance = $this->getAccountBalance($value);
            $this->calculateBalanceAfterInvoice();
        }
    }

    public function checkCashAccount($accountId)
    {
        if (!$accountId) {
            $this->isCurrentAccountCash = false;
            return;
        }

        $cashClientIds = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '110301%')
            ->pluck('id')
            ->toArray();

        $cashSupplierIds = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '210101%')
            ->pluck('id')
            ->toArray();

        $this->isCurrentAccountCash = in_array($accountId, $cashClientIds) || in_array($accountId, $cashSupplierIds);
    }

    public function calculateBalanceAfterInvoice()
    {
        $subtotal = 0;
        foreach ($this->invoiceItems as $item) {
            $quantity = $item['quantity'] ?? 0;
            $price = $item['price'] ?? 0;
            $subtotal += $quantity * $price;
        }
        $discountValue = $this->discount_value;
        $additionalValue = $this->additional_value;
        $netTotal = $subtotal - $discountValue + $additionalValue;
        $effect = 0;
        if ($this->type == 10) {
            $effect = $netTotal;
        } elseif ($this->type == 11) {
            $effect = -$netTotal;
        } elseif ($this->type == 12) {
            $effect = -$netTotal;
        } elseif ($this->type == 13) {
            $effect = $netTotal;
        }
        $this->balanceAfterInvoice = $this->currentBalance + $effect;
    }

    public function createItemFromPrompt($name, $barcode)
    {
        // ✅ استدعاء createNewItem وإرجاع النتيجة
        $this->createNewItem($name, $barcode);
        // ✅ إرجاع نتيجة نجاح (EditInvoiceForm لا يرجع index مثل CreateInvoiceForm)
        return [
            'success' => true,
            'message' => 'تم إنشاء الصنف بنجاح'
        ];
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
        if (! $item) {
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
    }

    // public function updatedBarcodeTerm($value)
    // {
    //     $this->selectedBarcodeResultIndex = -1;
    //     $this->barcodeSearchResults = collect();
    // }

    public function handleQuantityEnter($index)
    {
        if (! isset($this->invoiceItems[$index])) {
            return;
        }
        $this->quantityClickCount++;
        $this->lastQuantityFieldIndex = $index;
        $this->invoiceItems[$index]['quantity'] = $this->quantityClickCount;
        $this->recalculateSubValues();
        $this->calculateTotals();
        if ($this->quantityClickCount === 1) {
            $this->js('window.focusBarcodeField()');
        }
    }

    public function updateSelectedItemData($item, $unitId, $price)
    {
        $this->currentSelectedItem = $item->id;
        $availableQtyInSelectedStore = OperationItems::where('item_id', $item->id)
            ->where('detail_store', $this->acc2_id)
            ->selectRaw('SUM(qty_in - qty_out) as total')
            ->value('total') ?? 0;
        $totalAvailableQty = OperationItems::where('item_id', $item->id)
            ->selectRaw('SUM(qty_in - qty_out) as total')
            ->value('total') ?? 0;
        $unitName = $item->units->where('id', $unitId)->first()->name ?? '';
        $selectedStoreName = AccHead::where('id', $this->acc2_id)->value('aname') ?? '';
        $lastPurchasePrice = OperationItems::where('item_id', $item->id)
            ->where('is_stock', 1)
            ->whereIn('pro_tybe', [11, 20])
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
            'last_purchase_price' => $lastPurchasePrice,
            'description' => $item->description ?? '',
        ];
    }

    public function removeRow($index)
    {
        unset($this->invoiceItems[$index]);
        $this->invoiceItems = array_values($this->invoiceItems);
        $this->calculateTotals();
        $this->calculateBalanceAfterInvoice();
    }

    public function updatedSearchTerm($value)
    {
        $this->selectedResultIndex = -1;
        $this->items = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices'])->get();
        $this->searchResults = strlen($value) < 1
            ? collect()
            : Item::with(['units', 'prices'])
            ->where('name', 'like', "%{$value}%")
            ->take(5)->get();
    }

    public function updatedBarcodeTerm($value)
    {
        $this->barcodeSearchResults = strlen($value) < 1
            ? collect()
            : Item::with(['units', 'prices'])
            ->where('code', 'like', "%{$value}%")
            ->take(5)->get();
    }

    // public function addItemByBarcode()
    // {
    //     if (empty($this->barcodeTerm)) {
    //         return;
    //     }

    //     $item = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices'])
    //         ->where('code', $this->barcodeTerm)
    //         ->first();

    //     if (!$item) {
    //         $this->dispatch('item-not-found');
    //         $this->barcodeTerm = '';
    //         $this->barcodeSearchResults = collect();
    //         return;
    //     }

    //     $this->addItemFromSearch($item->id);
    //     $this->barcodeTerm = '';
    //     $this->barcodeSearchResults = collect();
    // }

    public function addItemFromSearch($itemId)
    {
        $item = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices'])->find($itemId);
        if (! $item) {
            return;
        }

        $existingItemIndex = null;
        foreach ($this->invoiceItems as $index => $invoiceItem) {
            if ($invoiceItem['item_id'] === $item->id) {
                $existingItemIndex = $index;
                break;
            }
        }
        if ($existingItemIndex !== null) {
            $this->invoiceItems[$existingItemIndex]['quantity']++;
            $this->recalculateSubValues();
            $this->calculateTotals();
            $unitId = $this->invoiceItems[$existingItemIndex]['unit_id'];
            $price = $this->invoiceItems[$existingItemIndex]['price'];
            $this->updateSelectedItemData($item, $unitId, $price);
            $this->searchTerm = '';
            $this->searchResults = collect();
            $this->selectedResultIndex = -1;
            $this->barcodeTerm = '';
            $this->barcodeSearchResults = collect();
            $this->selectedBarcodeResultIndex = -1;
            $this->lastQuantityFieldIndex = $existingItemIndex;
            if ($this->addedFromBarcode) {
                $this->js('window.focusBarcodeSearch()');
            } else {
                $this->js('window.focusLastQuantityField()');
            }
            $newRowIndex = count($this->invoiceItems) - 1;
            $this->dispatch('alert', ['type' => 'success', 'message' => 'تم إضافة الصنف بنجاح.']);
            $this->dispatch('focus-quantity', ['index' => $newRowIndex]);

            return;
        }

        $firstUnit = $item->units->first();
        $unitId = $firstUnit?->id;
        $price = $this->calculateItemPrice($item, $unitId, $this->selectedPriceType);
        $vm = new ItemViewModel(null, $item, $unitId);
        $unitOptions = $vm->getUnitOptions();
        $availableUnits = collect($unitOptions)->map(function ($unit) {
            return (object) [
                'id' => $unit['value'],
                'name' => $unit['label'],
            ];
        });
        $this->invoiceItems[] = [
            'item_id' => $item->id,
            'unit_id' => $unitId,
            'name' => $item->name,
            'quantity' => 1,
            'price' => $price,
            'sub_value' => $price * 1,
            'discount' => 0,
            'available_units' => $availableUnits,
        ];
        $this->updateSelectedItemData($item, $unitId, $price);
        $this->barcodeTerm = '';
        $this->barcodeSearchResults = collect();
        $this->selectedBarcodeResultIndex = -1;
        $this->lastQuantityFieldIndex = count($this->invoiceItems) - 1;
        if ($this->addedFromBarcode) {
            $this->js('window.focusBarcodeSearch()');
        } else {
            $this->js('window.focusLastQuantityField()');
        }
        $this->searchTerm = '';
        $this->searchResults = collect();
        $this->selectedResultIndex = -1;
        $this->calculateTotals();
        $this->calculateBalanceAfterInvoice();
    }

    public function updatedAcc2Id()
    {
        if ($this->currentSelectedItem) {
            $item = Item::with(['units', 'prices'])->find($this->currentSelectedItem);
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
        $vm = new ItemViewModel(null, $item, $selectedUnitId = null);
        $opts = $vm->getUnitOptions();
        $unitsCollection = collect($opts)->map(fn($entry) => (object) [
            'id' => $entry['value'],
            'name' => $entry['label'],
        ]);
        $this->invoiceItems[$index]['available_units'] = $unitsCollection;
        if (empty($this->invoiceItems[$index]['unit_id'])) {
            $firstUnit = $unitsCollection->first();
            if ($firstUnit) {
                $this->invoiceItems[$index]['unit_id'] = $firstUnit->id;
            }
        }
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

        // ✅ إذا لم يتم العثور على الصنف في القائمة المحملة، قم بجلبه من قاعدة البيانات
        if (! $item) {
            $item = Item::with(['units', 'prices'])->find($itemId);
        }

        if (! $item) {
            return;
        }
        $currentPrice = (float) ($this->invoiceItems[$index]['price'] ?? 0);
        $price = $this->calculateItemPrice($item, $unitId, $this->selectedPriceType, $currentPrice, $this->oldUnitId);

        // إعادة تعيين الوحدة القديمة بعد الاستخدام
        $this->oldUnitId = null;
        $this->invoiceItems[$index]['price'] = $price;
        $this->recalculateSubValues();
        $this->calculateTotals();
        $this->calculateBalanceAfterInvoice();
    }

    public function updatedInvoiceItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) < 2) {
            return;
        }
        $rowIndex = (int) $parts[0];
        $field = $parts[1];
        if ($field === 'quantity') {
            $this->quantityClickCount = 0;
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
        } elseif ($field === 'unit_id') {
            // ✅ حفظ الوحدة القديمة قبل التغيير لتحويل السعر تلقائياً
            // $this->oldUnitId = $this->invoiceItems[$rowIndex]['unit_id'] ?? null;

            // عند تغيير الوحدة، قم بتحديث السعر فقط (الكمية تبقى كما هي)
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
            $this->calculateQuantityFromSubValue($rowIndex);
        } elseif (in_array($field, ['quantity', 'price', 'discount'])) {
            $this->recalculateSubValues();
            $this->calculateTotals();
        }
        $this->calculateBalanceAfterInvoice();
    }

    public function updatedSelectedPriceType()
    {
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
        // ✅ الحساب في Alpine.js - هنا للتحقق فقط
        if (! isset($this->invoiceItems[$index])) {
            return;
        }
        $item = $this->invoiceItems[$index];
        $subValue = (float) $item['sub_value'];
        $price = (float) $item['price'];
        $discount = (float) $item['discount'];
        if ($price <= 0) {
            $this->invoiceItems[$index]['sub_value'] = 0;
            $this->invoiceItems[$index]['quantity'] = 0;
            $this->calculateTotals();

            return;
        }
        $newQuantity = ($subValue + $discount) / $price;
        $this->invoiceItems[$index]['quantity'] = round($newQuantity, 3);
        $this->calculateTotals();
    }

    public function recalculateSubValues()
    {
        // ✅ تبسيط: فقط للتحقق - الحسابات الفعلية في Alpine.js
        foreach ($this->invoiceItems as $index => $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['price'] ?? 0);
            $discount = (float) ($item['discount'] ?? 0);
            $sub = ($qty * $price) - $discount;
            $this->invoiceItems[$index]['sub_value'] = round($sub, 2);
        }
    }

    public function calculateTotals()
    {
        // ✅ تبسيط: فقط للتحقق والتحسين - الحسابات الفعلية في Alpine.js
        $this->subtotal = collect($this->invoiceItems)->sum('sub_value');
        $discountPercentage = (float) ($this->discount_percentage ?? 0);
        $additionalPercentage = (float) ($this->additional_percentage ?? 0);
        
        $this->discount_value = round(($this->subtotal * $discountPercentage) / 100, 2);
        $this->additional_value = round(($this->subtotal * $additionalPercentage) / 100, 2);
        $this->total_after_additional = round($this->subtotal - $this->discount_value + $this->additional_value, 2);
    }

    public function createNewItem($name, $barcode = null)
    {
        $existingItem = Item::where('name', $name)->first();
        if ($existingItem) {
            return;
        }
        if ($barcode) {
            $existingBarcode = Barcode::where('barcode', $barcode)->exists();
            if ($existingBarcode) {
                $this->dispatch('alert', ['type' => 'error', 'message' => 'هذا الباركود مستخدم بالفعل لصنف آخر.']);

                return;
            }
        }
        $code = Item::max('code') + 1 ?? 1;
        $newItem = Item::create([
            'name' => $name,
            'code' => $code,
        ]);
        if ($barcode) {
            $newItem->barcodes()->create([
                'barcode' => $barcode,
                'unit_id' => 1,
            ]);
        }
        $this->updateSelectedItemData($newItem, 1, 0);
        $this->addItemFromSearch($newItem->id);
        $this->searchTerm = '';
        $this->barcodeTerm = '';
    }

    // ✅ تم نقل حسابات الخصم والإضافي إلى Alpine.js component (invoiceCalculations)
    // تم حذف: updatedDiscountPercentage, updatedDiscountValue, updatedAdditionalPercentage, updatedAdditionalValue
    // جميع الحسابات تتم في Alpine.js والمزامنة تتم عبر syncFromAlpine() قبل الحفظ

    public function handleKeyDown()
    {
        if ($this->searchResults->count() > 0) {
            $this->isCreateNewItemSelected = false;
            $this->selectedResultIndex = min(
                $this->selectedResultIndex + 1,
                $this->searchResults->count() - 1
            );
        } elseif (strlen($this->searchTerm) > 0) {
            $this->isCreateNewItemSelected = true;
        }
    }

    public function handleKeyUp()
    {
        if ($this->searchResults->count() > 0) {
            $this->isCreateNewItemSelected = false;
            $this->selectedResultIndex = max($this->selectedResultIndex - 1, -1);
        } elseif (strlen($this->searchTerm) > 0) {
            $this->isCreateNewItemSelected = false;
        }
    }

    public function handleEnter()
    {
        if ($this->selectedResultIndex >= 0) {
            $item = $this->searchResults->get($this->selectedResultIndex);
            $this->addItemFromSearch($item->id);
        } elseif ($this->isCreateNewItemSelected && strlen($this->searchTerm) > 0) {
            $this->createNewItem($this->searchTerm);
            $this->isCreateNewItemSelected = false;
        }
    }

    public function checkSearchResults()
    {
        $searchTerm = trim($this->searchTerm);
        if (! empty($searchTerm) && $this->searchResults->isEmpty()) {
            $this->searchedTerm = $searchTerm;

            return $this->dispatch('item-not-found', ['term' => $searchTerm, 'type' => 'search']);
        }
    }

    /**
     * ✅ استقبال البيانات من Alpine.js قبل الحفظ
     * يُستدعى من @submit.prevent="syncToLivewire()" في النموذج
     * 
     * @param array $alpineData البيانات المحسوبة في Alpine.js
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

    public function updateForm()
    {
        // تحقق من وجود العملية
        if (! $this->operation || ! $this->operationId) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'لا توجد فاتورة لتحريرها.',
            ]);
            return false;
        }

        // ✅ 1. إعادة حساب جميع الإجماليات للتأكد من صحتها
        $this->recalculateSubValues();
        $this->calculateTotals();
        
        // ✅ 2. Validation نهائي
        if (empty($this->invoiceItems)) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'يجب إضافة صنف واحد على الأقل.',
            ]);
            return false;
        }
        
        // التحقق من صحة الأصناف
        foreach ($this->invoiceItems as $index => $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['price'] ?? 0);
            
            if ($quantity <= 0) {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => "الكمية في الصف " . ($index + 1) . " يجب أن تكون أكبر من صفر.",
                ]);
                return false;
            }
            
            if ($price < 0) {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => "السعر في الصف " . ($index + 1) . " لا يمكن أن يكون سالباً.",
                ]);
                return false;
            }
        }
        
        if (($this->settings['allow_zero_invoice_total'] ?? '0') != '1' && $this->total_after_additional == 0) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'قيمة الفاتورة لا يمكن أن تكون صفرًا.',
            ]);
            return false;
        }

        // ✅ 3. الحفظ
        $service = new \App\Services\SaveInvoiceService;
        $result = $service->saveInvoice($this, true);

        if ($result) {
            $this->dispatch('alert', [
                'type' => 'success',
                'message' => 'تم تحديث الفاتورة بنجاح.',
            ]);
            $this->mount($this->operationId);
            return $result;
        }

        return false;
    }


    public function getPaymentBadgeClass()
    {
        $totalAmount = $this->total_after_additional;
        $paidAmount = $this->received_from_client;

        if ($paidAmount == 0) {
            return 'unpaid'; // غير مدفوع
        } elseif ($paidAmount >= $totalAmount) {
            return 'full'; // مدفوع كامل
        } else {
            return 'partial'; // مدفوع جزئي
        }
    }

    public function getPaymentBadgeText()
    {
        $totalAmount = $this->total_after_additional;
        $paidAmount = $this->received_from_client;

        if ($paidAmount == 0) {
            return 'غير مدفوع';
        } elseif ($paidAmount >= $totalAmount) {
            return 'مدفوع';
        } else {
            return 'جزئي';
        }
    }

    public function cancelUpdate()
    {
        // إعادة تحميل البيانات الأصلية
        $this->mount($this->operationId);

        $this->dispatch('alert', [
            'type' => 'info',
            'message' => 'تم إلغاء التعديلات وإرجاع البيانات الأصلية.',
        ]);
    }

    public function saveAndPrint()
    {
        $operationId = $this->updateForm();
        if ($operationId) {
            $printUrl = route('invoice.print', ['operation_id' => $operationId]);
            $this->dispatch('open-print-window', url: $printUrl);
        }
    }

    public function getAcc1OptionsProperty()
    {
        return collect($this->acc1List ?? [])->map(function ($account) {
            $id = is_array($account) ? ($account['id'] ?? null) : ($account->id ?? null);
            $name = is_array($account) ? ($account['aname'] ?? '') : ($account->aname ?? '');
            $code = is_array($account) ? ($account['code'] ?? '') : ($account->code ?? '');
            
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

    public function render()
    {
        return view('livewire.invoices.edit-invoice-form', [
            'acc1Options' => $this->acc1Options
        ]);
    }

    private function updateAccountsForNewType()
    {
        $clientsAccounts = $this->getAccountsByCode('122%');
        $suppliersAccounts = $this->getAccountsByCode('211%');
        $stores = $this->getAccountsByCode('123%');
        $employees = $this->getAccountsByCode('213%');
        $wasted = $this->getAccountsByCode('44001%');
        $accounts = $this->getAccountsByCode('%');
        $map = [
            10 => ['acc1' => 'clientsAccounts', 'acc1_role' => 'مدين', 'acc2_role' => 'دائن'],
            11 => ['acc1' => 'suppliersAccounts', 'acc1_role' => 'دائن', 'acc2_role' => 'مدين'],
            12 => ['acc1' => 'clientsAccounts', 'acc1_role' => 'دائن', 'acc2_role' => 'مدين'],
            13 => ['acc1' => 'suppliersAccounts', 'acc1_role' => 'مدين', 'acc2_role' => 'دائن'],
            14 => ['acc1' => 'clientsAccounts', 'acc1_role' => 'مدين', 'acc2_role' => 'دائن'],
            15 => ['acc1' => 'suppliersAccounts', 'acc1_role' => 'دائن', 'acc2_role' => 'مدين'],
            16 => ['acc1' => 'clientsAccounts', 'acc1_role' => 'مدين', 'acc2_role' => 'دائن'],
            17 => ['acc1' => 'suppliersAccounts', 'acc1_role' => 'دائن', 'acc2_role' => 'مدين'],
            18 => ['acc1' => 'wasted', 'acc1_role' => 'مدين', 'acc2_role' => 'دائن'],
            19 => ['acc1' => 'accounts', 'acc1_role' => 'مدين', 'acc2_role' => 'دائن'],
            20 => ['acc1' => 'accounts', 'acc1_role' => 'دائن', 'acc2_role' => 'مدين'],
            21 => ['acc1' => 'stores', 'acc1_role' => 'مدين', 'acc2_role' => 'دائن'],
            22 => ['acc1' => 'clientsAccounts', 'acc1_role' => 'مدين', 'acc2_role' => 'دائن'],
            25 => ['acc1' => 'suppliersAccounts', 'acc1_role' => 'دائن', 'acc2_role' => 'مدين'],
        ];
        $this->acc1List = isset($map[$this->type]) ? ${$map[$this->type]['acc1']} : collect();
        $this->acc2List = $stores;
        $this->acc1Role = $map[$this->type]['acc1_role'] ?? 'مدين';
        $this->acc2Role = $map[$this->type]['acc2_role'] ?? 'دائن';
    }

    private function updatePricesForNewType()
    {
        foreach ($this->invoiceItems as $index => $item) {
            $itemId = $item['item_id'];
            $unitId = $item['unit_id'];
            $foundItem = Item::find($itemId);
            if ($foundItem) {
                $newPrice = $this->calculateItemPrice($foundItem, $unitId, $this->selectedPriceType);
                $this->invoiceItems[$index]['price'] = $newPrice;
                $this->invoiceItems[$index]['sub_value'] = $newPrice * $item['quantity'];
            }
        }
    }
}
