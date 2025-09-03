<?php

namespace Modules\POS\Http\Livewire;

use App\Models\Barcode;
use Livewire\Component;
use App\Enums\InvoiceStatus;
use App\Models\JournalDetail;
use App\Helpers\ItemViewModel;
use Illuminate\Support\Collection;
use App\Services\SaveInvoiceService;
use Illuminate\Support\Facades\Cache;
use Modules\Settings\Models\PublicSetting;
use App\Models\{OperHead, OperationItems, AccHead, Price, Item, Note};

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
    public $searchResults;
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
        'description' => ''
    ];

    public $recommendedItems = [];

    // POS specific properties
    public $paymentMethod = 'cash'; // cash, card, mixed
    public $cardAmount = 0;
    public $cashAmount = 0;
    public $changeAmount = 0;
    public $customerDisplay = true; // عرض للعميل
    public $quickAccessItems = []; // أصناف سريعة الوصول
    
    // متغيرات التصنيفات والمجموعات
    public $categories = []; // قائمة التصنيفات (notes)
    public $selectedCategory = null; // التصنيف المختار
    public $filteredQuickItems = []; // الأصناف المفلترة حسب التصنيف

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

        $this->settings = PublicSetting::pluck('value', 'key')->toArray();

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
        $this->searchResults = collect();
        $this->items = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices'])->take(20)->get();
        $this->barcodeSearchResults = collect();

        // جلب أصناف سريعة الوصول (الأكثر مبيعاً)
        $this->loadQuickAccessItems();
        
        // جلب التصنيفات
        $this->categories = Note::orderBy('name')->get();
        $this->filteredQuickItems = $this->quickAccessItems;
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
        $balance = JournalDetail::where('account_id', $accountId)
            ->where('isdeleted', 0)
            ->selectRaw('SUM(debit) - SUM(credit) as balance')
            ->value('balance') ?? 0;

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

    private function loadQuickAccessItems()
    {
        // جلب أكثر 20 صنف مبيعاً في آخر شهر
        $this->quickAccessItems = OperationItems::whereHas('operhead', function ($query) {
            $query->where('pro_type', 10) // فواتير المبيعات
                ->where('created_at', '>=', now()->subMonth());
        })
            ->groupBy('item_id')
            ->selectRaw('item_id, SUM(qty_out) as total_quantity')
            ->with(['item' => function ($query) {
                $query->select('id', 'name', 'code');
            }])
            ->orderByDesc('total_quantity')
            ->take(20)
            ->get()
            ->map(function ($operationItem) {
                return [
                    'id' => $operationItem->item_id,
                    'name' => $operationItem->item->name,
                    'code' => $operationItem->item->code,
                    'total_quantity' => $operationItem->total_quantity,
                ];
            })
            ->toArray();
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
        $subtotal = 0;
        foreach ($this->invoiceItems as $item) {
            $quantity = $item['quantity'] ?? 0;
            $price = $item['price'] ?? 0;
            $subtotal += $quantity * $price;
        }

        $discountValue = $this->discount_value;
        $additionalValue = $this->additional_value;
        $netTotal = $subtotal - $discountValue + $additionalValue;

        // في POS دائماً مبيعات (مدين)
        $effect = $netTotal;
        $this->balanceAfterInvoice = $this->currentBalance + $effect;
    }

    // نسخ جميع الدوال من CreateInvoiceForm مع نفس المنطق
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
            return $this->dispatch('prompt-create-item-from-barcode', barcode: $barcode);
        }

        $this->addedFromBarcode = true;

        $lastIndex = count($this->invoiceItems) - 1;
        if ($lastIndex >= 0 && $this->invoiceItems[$lastIndex]['item_id'] === $item->id) {
            $this->invoiceItems[$lastIndex]['quantity']++;
            $this->recalculateSubValues();
            $this->calculateTotals();
            $this->barcodeTerm = '';
            $this->updatePaymentCalculations();
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
        if (!isset($this->invoiceItems[$index])) {
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
        $this->searchResults = collect();
        $this->selectedResultIndex = -1;

        if (empty(trim($value))) {
            return;
        }

        $limit = strlen(trim($value)) == 1 ? 10 : 20;
        $searchTerm = trim($value);

        $this->searchResults = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices'])
            ->where('name', 'like', '%' . $searchTerm . '%')
            ->orWhereHas('barcodes', function ($query) use ($searchTerm) {
                $query->where('barcode', 'like', '%' . $searchTerm . '%');
            })
            ->take($limit)
            ->get();
    }

    // نسخ جميع دوال المعالجة من CreateInvoiceForm
    public function addItemFromSearch($itemId)
    {
        $item = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices'])->find($itemId);
        if (!$item) return;

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
            $this->searchResults = collect();
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
        $this->searchResults = collect();
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
        $this->subtotal = collect($this->invoiceItems)->sum('sub_value');
        $discountPercentage = (float) ($this->discount_percentage ?? 0);
        $additionalPercentage = (float) ($this->additional_percentage ?? 0);
        $this->discount_value = ($this->subtotal * $discountPercentage) / 100;
        $this->additional_value = ($this->subtotal * $additionalPercentage) / 100;
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

    public function updatedCashAmount()
    {
        $this->calculateChange();
    }

    public function updatedCardAmount()
    {
        $this->calculateChange();
    }

    public function setPaymentMethod($method)
    {
        $this->paymentMethod = $method;
        $this->updatePaymentCalculations();
    }

    public function addQuickItem($itemId)
    {
        $this->addItemFromSearch($itemId);
    }
    
    public function selectCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;
        $this->filterItemsByCategory();
    }
    
    public function clearCategoryFilter()
    {
        $this->selectedCategory = null;
        $this->filteredQuickItems = $this->quickAccessItems;
    }
    
    private function filterItemsByCategory()
    {
        if ($this->selectedCategory) {
            // فلترة الأصناف حسب التصنيف المختار
            $categoryItemIds = Item::whereHas('notes', function($query) {
                $query->where('note_id', $this->selectedCategory);
            })->pluck('id')->toArray();
            
            $this->filteredQuickItems = collect($this->quickAccessItems)->filter(function($item) use ($categoryItemIds) {
                return in_array($item['id'], $categoryItemIds);
            })->values()->toArray();
        } else {
            $this->filteredQuickItems = $this->quickAccessItems;
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

        $service = new SaveInvoiceService();
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

    public function updateQuantity($index, $quantity)
    {
        if (isset($this->invoiceItems[$index])) {
            $quantity = max(1, (float) $quantity);
            $this->invoiceItems[$index]['quantity'] = $quantity;
            $this->recalculateSubValues();
            $this->calculateTotals();
            $this->updatePaymentCalculations();
        }
    }

    public function resetForm()
    {
        // إعادة تعيين جميع البيانات
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
        $this->paymentMethod = 'cash';
        
        // إعادة تعيين البيانات المختارة
        $this->currentSelectedItem = null;
        $this->selectedItemData = [
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
        
        // إعادة تعيين نتائج البحث
        $this->searchResults = collect();
        $this->barcodeSearchResults = collect();
        $this->selectedResultIndex = -1;
        $this->selectedBarcodeResultIndex = -1;
        
        // تحديث رقم الفاتورة
        $this->nextProId = OperHead::max('pro_id') + 1 ?? 1;
        $this->pro_id = $this->nextProId;
        
        // إعادة حساب الرصيد
        $this->currentBalance = $this->getAccountBalance($this->acc1_id);
        $this->calculateBalanceAfterInvoice();
        
        $this->dispatch('alert', ['type' => 'success', 'message' => 'تم حفظ المعاملة بنجاح. جاهز لمعاملة جديدة.']);
    }

    // نسخ باقي الدوال المطلوبة من CreateInvoiceForm...
    // (يمكنني إضافة المزيد حسب الحاجة)

    public function render()
    {
        return view('pos::livewire.create-pos-transaction-form');
    }
}
