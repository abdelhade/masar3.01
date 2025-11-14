<?php

namespace Modules\POS\app\Livewire;

use App\Helpers\ItemViewModel;
use Modules\Accounts\Models\AccHead;
use App\Models\Item;
use App\Models\JournalDetail;
use App\Models\OperationItems;
use App\Models\OperHead;
use App\Models\Price;
use App\Services\SaveInvoiceService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Modules\Settings\Models\PublicSetting;

class CreatePosTransactionForm extends Component
{
    // نفس المتغيرات من CreateInvoiceForm مع تخصيص POS
    public $type = 10; // ثابت للمبيعات في POS

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

    public $isCreateNewItemSelected = false;

    public $currentBalance = 0;

    public $balanceAfterInvoice = 0;

    public $showBalance = true; // دائماً نعرض الرصيد في POS

    public $priceTypes = [];

    public $selectedPriceType = 1;

    public $status = 0;

    public $selectedUnit = [];

    public $searchTerm = '';

    public $searchResults = [];

    public $selectedResultIndex = -1;

    public int $quantityClickCount = 0;

    public $lastQuantityFieldIndex = null;

    public $acc1List = [];

    public $acc2List = [];

    public $employees = [];

    public $deliverys = [];

    public $statues = [];

    public $delivery_id = null;

    public $nextProId;

    public $acc1Role = 'مدين';

    public $acc2Role = 'دائن';

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

    // POS specific properties
    public $paymentMethod = 'cash'; // cash, card, mixed

    public $cardAmount = 0;

    public $cashAmount = 0;

    public $changeAmount = 0;

    public $customerDisplay = true; // عرض للعميل

    public $categories = []; // التصنيفات

    public $selectedCategory = null; // التصنيف المختار

    public $categoryItems = []; // الأصناف في التصنيف المختار

    public function mount()
    {
        $this->type = 10; // فاتورة مبيعات

        $this->nextProId = OperHead::max('pro_id') + 1 ?? 1;
        $this->pro_id = $this->nextProId;
        $this->pro_date = now()->format('Y-m-d');
        $this->accural_date = now()->format('Y-m-d');
        $this->deliverys = $this->getAccountsByCode('2102%');

        $this->cashAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('is_fund', 1)
            ->select('id', 'aname')
            ->get();

        $this->settings = Cache::remember(
            'pos.settings',
            now()->addHour(),
            function () {
                return PublicSetting::pluck('value', 'key')->toArray();
            }
        );

        $clientsAccounts = $this->getAccountsByCode('1103%');
        $stores = $this->getAccountsByCode('1104%');
        $employees = $this->getAccountsByCode('2102%');

        // إعدادات افتراضية للPOS
        $this->acc1List = $clientsAccounts;
        $this->acc2List = $stores;
        $this->employees = $employees;

        // القيم الافتراضية للPOS
        $this->acc1_id = 61; // عميل افتراضي
        $this->acc2_id = 62; // مخزن افتراضي
        $this->emp_id = 65; // موظف افتراضي
        $this->cash_box_id = 59; // صندوق افتراضي
        $this->delivery_id = 65;

        $this->showBalance = true;
        $this->currentBalance = $this->getAccountBalance($this->acc1_id);
        $this->calculateBalanceAfterInvoice();

        $this->invoiceItems = [];
        $this->priceTypes = Price::pluck('name', 'id')->toArray();
        $this->searchResults = [];
        $this->items = Item::with(['units' => fn ($q) => $q->orderBy('pivot_u_val'), 'prices'])->take(20)->get();
        $this->barcodeSearchResults = collect();

        // جلب أصناف سريعة الوصول (الأكثر مبيعاً)
        // جلب التصنيفات
        $this->loadCategories();

        // حساب القيم الأولية
        $this->calculateTotals();
    }

    private function getAccountsByCode(string $code)
    {
        return AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', $code)
            ->select('id', 'aname')
            ->get();
    }

    protected function getAccountBalance($accountId)
    {
        $balance = Cache::remember(
            "account.balance.{$accountId}",
            now()->addMinutes(10),
            function () use ($accountId) {
                return JournalDetail::where('account_id', $accountId)
                    ->where('isdeleted', 0)
                    ->selectRaw('SUM(debit) - SUM(credit) as balance')
                    ->value('balance') ?? 0;
            }
        );

        if (($this->settings['allow_zero_opening_balance'] ?? '0') != '1' && $balance == 0 && $accountId) {
            $this->dispatch(
                'error',
                title: 'خطأ!',
                text: 'الرصيد الافتتاحي لا يمكن أن يكون صفرًا.',
                icon: 'error'
            );
        }

        return $balance;
    }

    public function updatedAcc1Id($value)
    {
        if ($this->showBalance) {
            $this->currentBalance = $this->getAccountBalance($value);
            $this->calculateBalanceAfterInvoice();
        }
    }

    public function calculateBalanceAfterInvoice()
    {
        $discountValue = $this->discount_value;
        $additionalValue = $this->additional_value;
        $netTotal = $this->subtotal - $discountValue + $additionalValue;

        // في POS دائماً مبيعات (مدين)
        $effect = $netTotal;
        $this->balanceAfterInvoice = $this->currentBalance + $effect;
    }

    // نسخ جميع الدوال من CreateInvoiceForm مع نفس المنطق
    public function updateSelectedItemData($item, $unitId, $price)
    {
        $this->currentSelectedItem = $item->id;

        // تحميل البيانات الأساسية فوراً
        $unitName = $item->units->where('id', $unitId)->first()->name ?? '';
        $selectedStoreName = AccHead::where('id', $this->acc2_id)->value('aname') ?? '';

        $this->selectedItemData = [
            'name' => $item->name,
            'code' => $item->code ?? '',
            'unit_name' => $unitName,
            'price' => $price,
            'average_cost' => $item->average_cost ?? 0,
            'description' => $item->description ?? '',
            'selected_store_name' => $selectedStoreName,
            // البيانات الثقيلة ستُحمّل lazy
            'available_quantity_in_store' => 0,
            'total_available_quantity' => 0,
            'last_purchase_price' => 0,
        ];

        // تحميل البيانات الثقيلة في الخلفية
        $this->loadItemDetails($item->id);
    }

    public function loadItemDetails($itemId)
    {
        $availableQtyInSelectedStore = Cache::remember(
            "item.qty.{$itemId}.{$this->acc2_id}",
            now()->addMinutes(2),
            function () use ($itemId) {
                return OperationItems::where('item_id', $itemId)
                    ->where('detail_store', $this->acc2_id)
                    ->selectRaw('SUM(qty_in - qty_out) as total')
                    ->value('total') ?? 0;
            }
        );

        $totalAvailableQty = Cache::remember(
            "item.total_qty.{$itemId}",
            now()->addMinutes(2),
            function () use ($itemId) {
                return OperationItems::where('item_id', $itemId)
                    ->selectRaw('SUM(qty_in - qty_out) as total')
                    ->value('total') ?? 0;
            }
        );

        $lastPurchasePrice = Cache::remember(
            "item.last_price.{$itemId}",
            now()->addMinutes(5),
            function () use ($itemId) {
                return OperationItems::where('item_id', $itemId)
                    ->where('is_stock', 1)
                    ->whereIn('pro_tybe', [11, 20])
                    ->where('qty_in', '>', 0)
                    ->orderBy('created_at', 'desc')
                    ->value('item_price') ?? 0;
            }
        );

        $this->selectedItemData['available_quantity_in_store'] = $availableQtyInSelectedStore;
        $this->selectedItemData['total_available_quantity'] = $totalAvailableQty;
        $this->selectedItemData['last_purchase_price'] = $lastPurchasePrice;
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

        $item = Item::with(['units' => fn ($q) => $q->orderBy('pivot_u_val'), 'prices'])
            ->whereHas('barcodes', function ($query) use ($barcode) {
                $query->where('barcode', $barcode);
            })
            ->first();

        if (! $item) {
            return $this->dispatch('prompt-create-item-from-barcode', barcode: $barcode);
        }

        $this->addedFromBarcode = true;

        // البحث عن الصنف في السلة الحالية
        $existingItemIndex = null;
        foreach ($this->invoiceItems as $index => $invoiceItem) {
            if ($invoiceItem['item_id'] === $item->id) {
                $existingItemIndex = $index;
                break;
            }
        }

        // إذا كان الصنف موجود في السلة، زيادة الكمية
        if ($existingItemIndex !== null) {
            $this->invoiceItems[$existingItemIndex]['quantity']++;
            $this->recalculateSubValues();
            $this->calculateTotals();
            $this->barcodeTerm = '';
            $this->updatePaymentCalculations();
            $this->dispatch('alert', ['type' => 'success', 'message' => 'تم زيادة كمية الصنف في السلة.']);

            return;
        }

        $this->addItemFromSearch($item->id);
        $this->barcodeTerm = '';
        $this->barcodeSearchResults = collect();
        $this->selectedBarcodeResultIndex = -1;
        $this->lastQuantityFieldIndex = count($this->invoiceItems) - 1;

        $this->dispatch('alert', ['type' => 'success', 'message' => 'تم إضافة الصنف بنجاح.']);
        $this->updatePaymentCalculations();
    }

    public function updatedBarcodeTerm($value)
    {
        $this->selectedBarcodeResultIndex = -1;
        $this->barcodeSearchResults = collect();
    }

    public function handleQuantityEnter($index)
    {
        if (! isset($this->invoiceItems[$index])) {
            return;
        }

        $this->quantityClickCount++;
        $this->lastQuantityFieldIndex = $index;

        if (($this->settings['default_quantity_greater_than_zero'] ?? '0') == '1') {
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
        $this->updatePaymentCalculations();

        if ($this->quantityClickCount === 1) {
            $this->js('window.focusBarcodeField()');
        }
    }

    public function removeRow($index)
    {
        unset($this->invoiceItems[$index]);
        $this->invoiceItems = array_values($this->invoiceItems);
        $this->calculateTotals();
        $this->updatePaymentCalculations();
    }

    public function updatedSearchTerm($value)
    {
        // حماية إضافية من القيم الخطيرة
        if (! is_string($value) || strlen($value) > 100) {
            $this->searchResults = [];

            return;
        }

        try {
            $this->searchResults = [];
            $this->selectedResultIndex = -1;

            // تنظيف النص من الأحرف الخاصة أولاً
            $cleanValue = preg_replace('/[^\p{L}\p{N}\s]/u', '', $value);
            $cleanValue = trim($cleanValue);

            if (strlen($cleanValue) < 2) {
                return;
            }

            $limit = strlen($cleanValue) == 2 ? 10 : 20;

            $this->searchResults = Item::select('items.id', 'items.name', 'items.code')
                ->where(function ($query) use ($cleanValue) {
                    $query->where('items.name', 'like', "{$cleanValue}%")
                        ->orWhere('items.code', 'like', "{$cleanValue}%");
                })
                ->where('items.is_active', 1)
                ->orderBy('items.name')
                ->take($limit)
                ->get()
                ->toArray();

        } catch (\Throwable $e) {
            // في حالة حدوث أي خطأ، إرجاع مصفوفة فارغة
            $this->searchResults = [];
            $this->selectedResultIndex = -1;
            \Illuminate\Support\Facades\Log::error('Search error: '.$e->getMessage());
        }
    }

    // نسخ جميع دوال المعالجة من CreateInvoiceForm
    public function addItemFromSearch($itemId)
    {
        $item = Item::with(['units' => fn ($q) => $q->orderBy('pivot_u_val'), 'prices'])->find($itemId);
        if (! $item) {
            return;
        }

        // نفس المنطق من CreateInvoiceForm
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
            $this->searchResults = [];
            $this->selectedResultIndex = -1;
            $this->barcodeTerm = '';
            $this->barcodeSearchResults = collect();
            $this->selectedBarcodeResultIndex = -1;

            $this->lastQuantityFieldIndex = $existingItemIndex;
            $this->updatePaymentCalculations();

            return;
        }

        $firstUnit = $item->units->first();
        $unitId = $firstUnit?->id;

        $vm = new ItemViewModel(null, $item, $unitId);
        $salePrices = $vm->getUnitSalePrices();
        $price = $salePrices[$this->selectedPriceType]['price'] ?? 0;

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

        $unitOptions = $vm->getUnitOptions();
        $availableUnits = collect($unitOptions)->map(function ($unit) {
            return (object) [
                'id' => $unit['value'],
                'name' => $unit['label'],
            ];
        });

        $quantity = ($this->settings['default_quantity_greater_than_zero'] ?? '0') == '1' ? 1 : 1;

        $this->invoiceItems[] = [
            'item_id' => $item->id,
            'unit_id' => $unitId,
            'name' => $item->name,
            'quantity' => $quantity,
            'price' => $price,
            'sub_value' => $price * $quantity,
            'discount' => 0,
            'available_units' => $availableUnits,
        ];

        $this->updateSelectedItemData($item, $unitId, $price);

        $this->barcodeTerm = '';
        $this->barcodeSearchResults = collect();
        $this->selectedBarcodeResultIndex = -1;
        $this->lastQuantityFieldIndex = count($this->invoiceItems) - 1;

        $this->searchTerm = '';
        $this->searchResults = [];
        $this->selectedResultIndex = -1;

        $this->calculateTotals();
        $this->updatePaymentCalculations();
    }

    // نسخ جميع دوال الحساب من CreateInvoiceForm
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
        // حساب المجموع الفرعي
        $this->subtotal = collect($this->invoiceItems)->sum('sub_value');

        // حساب الخصم
        $discountPercentage = (float) ($this->discount_percentage ?? 0);
        $this->discount_value = ($this->subtotal * $discountPercentage) / 100;

        // حساب الإضافي
        $additionalPercentage = (float) ($this->additional_percentage ?? 0);
        $this->additional_value = ($this->subtotal * $additionalPercentage) / 100;

        // حساب الإجمالي النهائي
        $this->total_after_additional = round($this->subtotal - $this->discount_value + $this->additional_value, 2);

        if (($this->settings['allow_zero_invoice_total'] ?? '0') != '1' && $this->total_after_additional == 0) {
            $this->dispatch(
                'error',
                title: 'خطأ!',
                text: 'قيمة الفاتورة لا يمكن أن تكون صفرًا.',
                icon: 'error'
            );
        }

        $this->calculateBalanceAfterInvoice();
    }

    // POS specific functions
    public function updatePaymentCalculations()
    {
        if ($this->paymentMethod === 'cash') {
            $this->cashAmount = $this->total_after_additional;
            $this->cardAmount = 0;
        } elseif ($this->paymentMethod === 'card') {
            $this->cardAmount = $this->total_after_additional;
            $this->cashAmount = 0;
        }

        $this->calculateChange();
    }

    public function calculateChange()
    {
        $totalPaid = $this->cashAmount + $this->cardAmount;
        $this->changeAmount = max(0, $totalPaid - $this->total_after_additional);
    }

    public function updatedCashAmount($value)
    {
        $this->calculateChange();
    }

    public function updatedCardAmount($value)
    {
        $this->calculateChange();
    }

    public function setPaymentMethod($method)
    {
        $this->paymentMethod = $method;
        $this->updatePaymentCalculations();
    }

    public function updatedPaymentMethod($value)
    {
        $this->updatePaymentCalculations();
    }

    public function updatedDiscountValue($value)
    {
        $this->calculateTotals();
    }

    public function updatedAdditionalValue($value)
    {
        $this->calculateTotals();
    }

    // دوال إدارة الكميات
    public function incrementQuantity($index)
    {
        if (isset($this->invoiceItems[$index])) {
            $this->invoiceItems[$index]['quantity']++;
            $this->recalculateSubValues();
            $this->calculateTotals();
            $this->updatePaymentCalculations();
        }
    }

    public function decrementQuantity($index)
    {
        if (isset($this->invoiceItems[$index]) && $this->invoiceItems[$index]['quantity'] > 1) {
            $this->invoiceItems[$index]['quantity']--;
            $this->recalculateSubValues();
            $this->calculateTotals();
            $this->updatePaymentCalculations();
        }
    }

    public function updateQuantity($index, $value)
    {
        if (isset($this->invoiceItems[$index])) {
            $this->invoiceItems[$index]['quantity'] = max(0, (float) $value);
            $this->recalculateSubValues();
            $this->calculateTotals();
            $this->updatePaymentCalculations();
        }
    }

    public function updateUnit($index, $unitId)
    {
        if (! isset($this->invoiceItems[$index])) {
            return;
        }

        $itemId = $this->invoiceItems[$index]['item_id'];
        $item = Item::with(['units' => fn ($q) => $q->orderBy('pivot_u_val'), 'prices'])->find($itemId);

        if (! $item) {
            return;
        }

        // تحديث الوحدة
        $this->invoiceItems[$index]['unit_id'] = $unitId;

        // حساب السعر الجديد للوحدة المختارة
        $vm = new ItemViewModel(null, $item, $unitId);
        $salePrices = $vm->getUnitSalePrices();
        $newPrice = $salePrices[$this->selectedPriceType]['price'] ?? 0;

        // التحقق من صحة السعر
        if (($this->settings['allow_zero_price_in_invoice'] ?? '0') != '1' && $newPrice == 0) {
            $this->dispatch(
                'error',
                title: 'تحذير!',
                text: 'سعر الوحدة المختارة صفر. سيتم الاحتفاظ بالسعر السابق.',
                icon: 'warning'
            );

            return;
        }

        if (($this->settings['prevent_negative_invoice'] ?? '0') == '1' && $newPrice < 0) {
            $this->dispatch(
                'error',
                title: 'تحذير!',
                text: 'سعر الوحدة المختارة سالب. سيتم الاحتفاظ بالسعر السابق.',
                icon: 'warning'
            );

            return;
        }

        // تحديث السعر
        $this->invoiceItems[$index]['price'] = $newPrice;

        // إعادة حساب القيم
        $this->recalculateSubValues();
        $this->calculateTotals();
        $this->updatePaymentCalculations();

        // تحديث بيانات الصنف المختار
        $this->updateSelectedItemData($item, $unitId, $newPrice);

        // إرسال رسالة نجاح
        $this->dispatch('alert', ['type' => 'success', 'message' => 'تم تحديث الوحدة والسعر بنجاح.']);
    }

    // دوال التصنيفات
    private function loadCategories()
    {
        // إزالة الكاش مؤقتاً لحل مشكلة التصنيفات الجديدة
        $this->categories = DB::table('note_details')
            ->join('notes', 'note_details.note_id', '=', 'notes.id')
            ->select('note_details.id', 'note_details.name', 'notes.name as parent_name')
            ->where('note_details.note_id', '=', 2)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name, // اسم التصنيف الفرعي (مثل "التصنيفات 1")
                    'parent_name' => $category->parent_name, // اسم التصنيف الرئيسي (مثل "التصنيفات")
                ];
            });
    }

    public function selectCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;
        $this->loadCategoryItems();
        $this->dispatch('$refresh');
    }

    public function refreshCategoryItems()
    {
        $this->loadCategoryItems();
        $this->dispatch('$refresh');
    }

    public function getCategoryItems($categoryId)
    {
        if ($categoryId) {
            // جلب اسم التصنيف المختار أولاً
            $selectedCategoryName = DB::table('note_details')
                ->where('id', $categoryId)
                ->value('name');

            if ($selectedCategoryName) {
                // جلب جميع الأصناف من التصنيف المختار
                return DB::table('item_notes')
                    ->join('items', 'item_notes.item_id', '=', 'items.id')
                    ->where('item_notes.note_detail_name', $selectedCategoryName)
                    ->where('items.is_active', 1)
                    ->select('items.id', 'items.name', 'items.code')
                    ->orderBy('items.name')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'code' => $item->code,
                        ];
                    })
                    ->toArray();
            }
        }

        return [];
    }

    public function updatedSelectedCategory($value)
    {
        $this->selectedCategory = $value;
        $this->loadCategoryItems();
        $this->dispatch('$refresh');
    }

    public function clearCategoryFilter()
    {
        $this->selectedCategory = null;
        $this->loadCategoryItems();
        $this->dispatch('$refresh');
    }

    public function refreshCategories()
    {
        // دالة لتحديث التصنيفات عند إضافة تصنيف جديد
        $this->loadCategories();
        $this->dispatch('$refresh');
    }

    private function loadCategoryItems()
    {
        if ($this->selectedCategory) {
            // جلب اسم التصنيف المختار أولاً
            $selectedCategoryName = DB::table('note_details')
                ->where('id', $this->selectedCategory)
                ->value('name');

            if ($selectedCategoryName) {
                // جلب جميع الأصناف من التصنيف المختار
                $this->categoryItems = DB::table('item_notes')
                    ->join('items', 'item_notes.item_id', '=', 'items.id')
                    ->where('item_notes.note_detail_name', $selectedCategoryName)
                    ->where('items.is_active', 1)
                    ->select('items.id', 'items.name', 'items.code')
                    ->orderBy('items.name')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'code' => $item->code,
                        ];
                    })
                    ->toArray();
            } else {
                $this->categoryItems = [];
            }
        } else {
            $this->categoryItems = [];
        }
    }

    // نسخ دوال الحفظ والطباعة
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

        // تحديث قيمة المدفوع حسب طريقة الدفع
        $this->received_from_client = $this->cashAmount + $this->cardAmount;

        $service = new SaveInvoiceService;

        return $service->saveInvoice($this);
    }

    public function saveAndPrint()
    {
        $operationId = $this->saveForm();
        if ($operationId) {
            $printUrl = route('pos.print', ['operation_id' => $operationId]);
            $this->dispatch('open-print-window', url: $printUrl);

            // إعادة تعيين النموذج لمعاملة جديدة
            $this->resetForm();
        }
    }

    public function resetForm()
    {
        $this->invoiceItems = [];
        $this->subtotal = 0;
        $this->discount_percentage = 0;
        $this->discount_value = 0;
        $this->additional_percentage = 0;
        $this->additional_value = 0;
        $this->total_after_additional = 0;
        $this->received_from_client = 0;
        $this->cashAmount = 0;
        $this->cardAmount = 0;
        $this->changeAmount = 0;
        $this->notes = '';
        $this->searchTerm = '';
        $this->barcodeTerm = '';

        // تحديث رقم الفاتورة
        $this->nextProId = OperHead::max('pro_id') + 1 ?? 1;
        $this->pro_id = $this->nextProId;

        // إعادة حساب الرصيد بعد إعادة تعيين النموذج
        $this->currentBalance = $this->getAccountBalance($this->acc1_id);
        $this->calculateBalanceAfterInvoice();

        // إعادة تعيين طريقة الدفع
        $this->paymentMethod = 'cash';
        $this->updatePaymentCalculations();

        $this->dispatch('alert', ['type' => 'success', 'message' => 'تم إعادة تعيين النموذج بنجاح. جاهز لمعاملة جديدة.']);
    }

    // نسخ باقي الدوال المطلوبة من CreateInvoiceForm...
    // (يمكنني إضافة المزيد حسب الحاجة)

    public function dehydrate()
    {
        // تنظيف البيانات المؤقتة لتحسين الأداء
        $this->searchResults = [];
        $this->barcodeSearchResults = collect();
    }

    public function render()
    {
        return view('pos::livewire.create-pos-transaction-form');
    }
}
