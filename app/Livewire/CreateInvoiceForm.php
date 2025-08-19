<?php

namespace App\Livewire;

use App\Models\Barcode;
use Livewire\Component;
use App\Models\JournalDetail;
use App\Helpers\ItemViewModel;
use Illuminate\Support\Collection;
use App\Services\SaveInvoiceService;
use Illuminate\Support\Facades\Cache;
use Modules\Settings\Models\PublicSetting;
use App\Models\{OperHead, OperationItems, AccHead, Price, Item};

class CreateInvoiceForm extends Component
{
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

    public $isCreateNewItemSelected = false;

    public $currentBalance = 0;
    public $balanceAfterInvoice = 0;
    public $showBalance = false;

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

    // protected $listeners = ['addRow'];

    public function mount($type, $hash)
    {
        $this->type = (int) $type;
        if ($hash !== md5($this->type)) abort(403, 'نوع الفاتورة غير صحيح');

        $convertData = session()->get('convert_invoice_data');

        $this->nextProId = OperHead::max('pro_id') + 1 ?? 1;
        $this->pro_id = $this->nextProId;
        $this->pro_date = now()->format('Y-m-d');
        $this->cashAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('is_fund', 1)
            ->select('id', 'aname')
            ->get();

        $this->settings = PublicSetting::pluck('value', 'key')->toArray();

        // dd($this->settings);

        $clientsAccounts   = $this->getAccountsByCode('1103%');
        $suppliersAccounts = $this->getAccountsByCode('2101%');
        $stores            = $this->getAccountsByCode('1104%');
        // $accounts  = $this->getAccountsByCode('1104%');
        $employees         = $this->getAccountsByCode('2102%');
        $wasted         = $this->getAccountsByCode('55%');
        $accounts         = $this->getAccountsByCode('1108%');
        $map = [
            10 => ['acc1' => 'clientsAccounts', 'acc1_role' => 'مدين', 'acc2_role' => 'دائن'], // فاتورة مبيعات
            11 => ['acc1' => 'suppliersAccounts', 'acc1_role' => 'دائن', 'acc2_role' => 'مدين'], // فاتورة مشتريات
            12 => ['acc1' => 'clientsAccounts', 'acc1_role' => 'دائن', 'acc2_role' => 'مدين'], // مردود مبيعات
            13 => ['acc1' => 'suppliersAccounts', 'acc1_role' => 'مدين', 'acc2_role' => 'دائن'], // مردود مشتريات
            14 => ['acc1' => 'clientsAccounts', 'acc1_role' => 'مدين', 'acc2_role' => 'دائن'], // أمر بيع
            15 => ['acc1' => 'suppliersAccounts', 'acc1_role' => 'دائن', 'acc2_role' => 'مدين'], // أمر شراء
            16 => ['acc1' => 'clientsAccounts', 'acc1_role' => 'مدين', 'acc2_role' => 'دائن'],
            17 => ['acc1' => 'suppliersAccounts', 'acc1_role' => 'دائن', 'acc2_role' => 'مدين'],
            18 => ['acc1' => 'wasted', 'acc1_role' => 'مدين', 'acc2_role' => 'دائن'],
            19 => ['acc1' => 'accounts', 'acc1_role' => 'مدين', 'acc2_role' => 'دائن'],
            20 => ['acc1' => 'accounts', 'acc1_role' => 'دائن', 'acc2_role' => 'مدين'],
            21 => ['acc1' => 'stores', 'acc1_role' => 'مدين', 'acc2_role' => 'دائن'],
            22 => ['acc1' => 'clientsAccounts', 'acc1_role' => 'مدين', 'acc2_role' => 'دائن'],
        ];

        $this->acc1List = isset($map[$type]) ? ${$map[$type]['acc1']} : collect();
        $this->acc2List = $stores;
        $this->acc1Role = $map[$type]['acc1_role'] ?? 'مدين';
        $this->acc2Role = $map[$type]['acc2_role'] ?? 'دائن';
        $this->acc2_id = 62;
        $this->emp_id = 65;
        $this->cash_box_id = 59;

        if (in_array($this->type, [10, 12, 14, 16, 22])) {
            $this->acc1_id = 61;
        } elseif (in_array($this->type, [11, 13, 15, 17])) {
            $this->acc1_id = 64;
        } elseif (in_array($this->type, [18, 19, 20, 21])) {
            $this->acc1_id = 0;
        }

        if ($convertData && isset($convertData['invoice_data'])) {
            $invoiceData = $convertData['invoice_data'];

            $this->acc1_id = $invoiceData['client_id'] ?? $this->acc1_id;
            $this->acc2_id = $invoiceData['store_id'] ?? $this->acc2_id;
            $this->emp_id = $invoiceData['employee_id'] ?? $this->emp_id;
            $this->notes = $invoiceData['notes'] ?? '';
            $this->pro_date = $invoiceData['invoice_date'] ?? $this->pro_date;

            // تعبئة بيانات الخصم والإضافي
            $this->discount_percentage = $convertData['discount_percentage'] ?? 0;
            $this->additional_percentage = $convertData['additional_percentage'] ?? 0;
            $this->discount_value = $convertData['discount_value'] ?? 0;
            $this->additional_value = $convertData['additional_value'] ?? 0;
            $this->total_after_additional = $convertData['total_after_additional'] ?? 0;
            $this->subtotal = $convertData['subtotal'] ?? 0;

            if (isset($convertData['items_data']) && !empty($convertData['items_data'])) {
                $this->invoiceItems = $convertData['items_data'];
            }
            session()->forget('convert_invoice_data');

            $this->dispatch(
                'error',
                title: 'تم الحفظ!',
                text: 'تم تحميل بيانات الفاتورة الأصلية بنجاح. يمكنك التعديل عليها الآن.',
                icon: 'success'
            );
        } else {
            $this->invoiceItems = [];
        }

        $this->showBalance = in_array($this->type, [10, 11, 12, 13]);

        if ($this->showBalance) {
            $this->currentBalance = $this->getAccountBalance($this->acc1_id);
            $this->calculateBalanceAfterInvoice();
        }

        $this->employees = $employees;
        // $this->invoiceItems = [];
        $this->priceTypes = Price::pluck('name', 'id')->toArray();
        $this->searchResults = collect();
        $this->items = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices'])->take(20)->get();
        $this->barcodeSearchResults = collect();

        if ($this->type == 10 && $this->acc1_id) {
            $this->recommendedItems = $this->getRecommendedItems($this->acc1_id);
        }
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
        if ($this->type == 10 && $value) { // فقط لفواتير المبيعات
            $this->recommendedItems = $this->getRecommendedItems($value);
        } else {
            $this->recommendedItems = [];
        }
    }

    private function getRecommendedItems($clientId)
    {
        return OperationItems::whereHas('operhead', function ($query) use ($clientId) {
            $query->where('pro_type', 10) // فواتير المبيعات فقط
                ->where('acc1', $clientId); // العميل المحدد
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
            $quantity = $item['quantity'] ?? 0;
            $price = $item['price'] ?? 0;
            $subtotal += $quantity * $price;
        }

        $discountValue = $this->discount_value;
        $additionalValue = $this->additional_value;
        $netTotal = $subtotal - $discountValue + $additionalValue;

        $effect = 0;

        if ($this->type == 10) { // فاتورة مبيعات
            $effect = $netTotal;
        } elseif ($this->type == 11) { // فاتورة مشتريات
            $effect = -$netTotal;
        } elseif ($this->type == 12) { // مردود مبيعات
            $effect = -$netTotal;
        } elseif ($this->type == 13) { // مردود مشتريات
            $effect = $netTotal;
        }

        $this->balanceAfterInvoice = $this->currentBalance + $effect;
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

    public function updatedSearchTerm($value)
    {
        $this->searchResults = collect();
        $this->selectedResultIndex = -1;

        if (empty(trim($value))) {
            return;
        }

        // تحديد عدد النتائج بناءً على طول النص
        $limit = strlen(trim($value)) == 1 ? 10 : 20;

        // تنظيف مصطلح البحث
        $searchTerm = trim($value);

        // الكويري للبحث عن الأصناف
        $this->searchResults = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices'])
            ->where('name', 'like', '%' . $searchTerm . '%')
            ->orWhereHas('barcodes', function ($query) use ($searchTerm) {
                $query->where('barcode', 'like', '%' . $searchTerm . '%');
            })
            ->take($limit)
            ->get();
    }

    public function addItemFromSearch($itemId)
    {
        $item = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices'])->find($itemId);
        if (! $item) return;
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
        if ($this->type == 18) {
            $price = $item->average_cost ?? 0;
        }

        // التحقق من منع السعر صفر
        if (($this->settings['allow_zero_price_in_invoice'] ?? '0') != '1' && $price == 0) {
            $this->dispatch(
                'error',
                title: 'خطأ!',
                text: 'لا يمكن أن يكون السعر صفرًا في الفاتورة.',
                icon: 'error'
            );
            return;
        }

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
            'name' => $item->name, // 💡 أضف هذا السطر
            'quantity' => $quantity,
            'price' => $price,
            'sub_value' => $price * $quantity, // quantity * price
            'discount' => 0,
            'available_units' => $availableUnits,
        ];

        $this->updateSelectedItemData($item, $unitId, $price);

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
    }

    public function updatedAcc2Id()
    {
        // إذا كان هناك صنف مختار، قم بتحديث بياناته
        if ($this->currentSelectedItem) {
            $item = Item::with(['units', 'prices'])->find($this->currentSelectedItem);
            if ($item) {
                // البحث عن الصنف المختار في قائمة الفاتورة للحصول على الوحدة والسعر الحاليين
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

        if ($this->type == 11 && ($this->settings['allow_purchase_price_change'] ?? '0') != '1') {
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

        if ($field === 'quantity') {
            $this->quantityClickCount = 0; // إعادة تعيين عداد الضغطات
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
        } elseif ($field === 'price' && $this->type == 11 && ($this->settings['allow_purchase_price_change'] ?? '0') != '1') {
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
        $this->subtotal = collect($this->invoiceItems)->sum('sub_value');
        $discountPercentage = (float) ($this->discount_percentage ?? 0);
        $additionalPercentage = (float) ($this->additional_percentage ?? 0);
        $this->discount_value = ($this->subtotal * $discountPercentage) / 100;
        // $afterDiscount = round($this->subtotal - $this->discount_value, 2);
        $this->additional_value = ($this->subtotal *  $additionalPercentage) / 100;
        $this->total_after_additional = round($this->subtotal - $this->discount_value + $this->additional_value, 2);

        // dd($this->settings['allow_zero_invoice_total']);
        if (($this->settings['allow_zero_invoice_total'] ?? '0') != '1' && $this->total_after_additional == 0) {
            $this->dispatch(
                'error',
                title: 'خطأ!',
                text: 'قيمة الفاتورة لا يمكن أن تكون صفرًا.',
                icon: 'error'
            );
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

        $newItem->units()->attach([
            1 => [
                'u_val' => 1,
                'cost' => 0
            ]
        ]);
        if ($barcode) {
            $newItem->barcodes()->create([
                'barcode' => $barcode,
                'unit_id' => 1
            ]);
        } else {
            $newItem->barcodes()->create([
                'barcode' => $newItem->code,
                'unit_id' => 1,
            ]);
        }
        $this->updateSelectedItemData($newItem, 1, 0); // تحديث بيانات الصنف المختار
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
        return $service->saveInvoice($this);
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
