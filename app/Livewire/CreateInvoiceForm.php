<?php

namespace App\Livewire;

use Livewire\Component;
use App\Helpers\ItemViewModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\{OperHead, JournalHead, JournalDetail, OperationItems, AccHead, Price, Item};

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

    public $priceTypes = [];
    public $selectedPriceType = 1;
    public $selectedUnit = [];

    public $searchTerm = '';
    public $searchResults;
    public $selectedResultIndex = -1;

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
        // إذا لم يكن الهاش مطابقًا لنوع الفاتورة، أوقف التنفيذ
        if ($hash !== md5($this->type)) abort(403, 'نوع الفاتورة غير صحيح');

        // $this->items = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices'])->get();

        $this->nextProId = OperHead::max('pro_id') + 1 ?? 1;
        $this->pro_id = $this->nextProId;
        $this->pro_date = now()->format('Y-m-d');
        $this->cashAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('is_fund', 1)
            ->select('id', 'aname')
            ->get();

        $clientsAccounts   = $this->getAccountsByCode('122%');
        $suppliersAccounts = $this->getAccountsByCode('211%');
        $stores            = $this->getAccountsByCode('123%');
        $employees         = $this->getAccountsByCode('213%');
        $wasted         = $this->getAccountsByCode('44001%');
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
        $this->acc2_id = 27;
        $this->cash_box_id = 21;

        if (in_array($this->type, [10, 12, 14, 16, 22])) {
            $this->acc1_id = 148;
        } elseif (in_array($this->type, [11, 13, 15, 17])) {
            $this->acc1_id = 36;
        } elseif (in_array($this->type, [18, 19, 20, 21])) {
            $this->acc1_id = 0;
        }

        $this->employees = $employees;
        $this->invoiceItems = [];
        $this->priceTypes = Price::pluck('name', 'id')->toArray();
        $this->searchResults = collect();
    }

    private function getAccountsByCode(string $code)
    {
        return AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', $code)
            ->select('id', 'aname')
            ->get();
    }

    public function updateSelectedItemData($item, $unitId, $price)
    {
        $this->currentSelectedItem = $item->id;

        // dd($this->acc2_id);
        $availableQtyInSelectedStore = OperationItems::where('item_id', $item->id)
            ->where('detail_store', $this->acc2_id)
            ->selectRaw('SUM(qty_in - qty_out) as total')
            ->value('total') ?? 0;

        $totalAvailableQty = OperationItems::where('item_id', $item->id)
            ->selectRaw('SUM(qty_in - qty_out) as total')
            ->value('total') ?? 0;

        $unitName = $item->units->where('id', $unitId)->first()->name ?? '';

        $selectedStoreName = AccHead::where('id', $this->acc2_id)->value('aname') ?? '';

        $this->selectedItemData = [
            'name' => $item->name,
            'code' => $item->code ?? '',
            'available_quantity_in_store' => $availableQtyInSelectedStore,
            'total_available_quantity' => $totalAvailableQty,
            'selected_store_name' => $selectedStoreName,
            'unit_name' => $unitName,
            'price' => $price,
            'cost' => $item->average_cost ?? 0,
            'description' => $item->description ?? ''
        ];
    }

    public function removeRow($index)
    {
        unset($this->invoiceItems[$index]);
        $this->invoiceItems = array_values($this->invoiceItems);
        $this->calculateTotals();
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

    public function addItemFromSearch($itemId)
    {
        $item = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices'])->find($itemId);
        if (! $item) return;

        $firstUnit = $item->units->first();
        $unitId = $firstUnit?->id;

        $vm = new ItemViewModel(null, $item, $unitId);
        $salePrices = $vm->getUnitSalePrices();
        $price = $salePrices[$this->selectedPriceType]['price'] ?? 0;

        // حساب السعر للوحدة الافتراضية
        $price = 0;
        if ($unitId && $this->selectedPriceType) {
            $vm = new ItemViewModel(null, $item, $unitId);
            $salePrices = $vm->getUnitSalePrices();
            $price = $salePrices[$this->selectedPriceType]['price'] ?? 0;
        }

        // إضافة الصنف مع البيانات الكاملة
        $this->invoiceItems[] = [
            'item_id' => $item->id,
            'unit_id' => $unitId,
            'quantity' => 1,
            'price' => $price,
            'sub_value' => $price * 1, // quantity * price
            'discount' => 0,
            // 'available_units' => $availableUnits,
        ];
        $this->updateSelectedItemData($item, $unitId, $price);

        // تنظيف البحث
        $this->searchTerm = '';
        $this->searchResults = collect();
        $this->selectedResultIndex = -1;

        // حساب الإجماليات
        $this->calculateTotals();

        // التركيز على حقل الكمية
        $this->js('window.focusLastQuantityField()');
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

        // حساب السعر للوحدة المختارة
        $vm = new ItemViewModel(null, $item, $unitId);
        $salePrices = $vm->getUnitSalePrices();
        $price = $salePrices[$this->selectedPriceType]['price'] ?? 0;

        $this->invoiceItems[$index]['price'] = $price;

        // إعادة حساب القيمة الفرعية
        $this->recalculateSubValues();
        $this->calculateTotals();
    }

    public function updatedInvoiceItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) < 2) return;

        $rowIndex = (int) $parts[0];
        $field = $parts[1];

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
            $this->calculateQuantityFromSubValue($rowIndex);
        } elseif (in_array($field, ['quantity', 'price', 'discount'])) {
            $this->recalculateSubValues();
            $this->calculateTotals();
        }
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
        if (!isset($this->invoiceItems[$index])) return;

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
        $newQuantity = ($subValue + $discount) / $price;
        $this->invoiceItems[$index]['quantity'] = round($newQuantity, 3);
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
    }

    public function updatedDiscountPercentage()
    {
        $discountPercentage = (float) ($this->discount_percentage ?? 0);
        $this->discount_value = ($this->subtotal * $discountPercentage) / 100;
        $this->calculateTotals();
    }

    public function updatedDiscountValue()
    {
        if ($this->discount_value >= 0 && $this->subtotal > 0) {
            $this->discount_percentage = ($this->discount_value * 100) / $this->subtotal;
            $this->calculateTotals();
        }
    }

    public function updatedAdditionalPercentage()
    {
        $additionalPercentage = (float) ($this->additional_percentage ?? 0);
        $this->additional_value = ($this->subtotal * $additionalPercentage) / 100;
        $this->calculateTotals();
    }

    public function updatedAdditionalValue()
    {
        $afterDiscount = $this->subtotal - $this->discount_value;
        if ($this->additional_value >= 0 && $afterDiscount > 0) {
            $this->additional_percentage = ($this->additional_value * 100) / $afterDiscount;
            $this->calculateTotals();
        }
    }

    public function handleKeyDown()
    {
        $this->selectedResultIndex = min(
            $this->selectedResultIndex + 1,
            $this->searchResults->count() - 1
        );
    }

    public function handleKeyUp()
    {
        $this->selectedResultIndex = max($this->selectedResultIndex - 1, -1);
    }

    public function handleEnter()
    {
        if ($this->selectedResultIndex >= 0) {
            $item = $this->searchResults->get($this->selectedResultIndex);
            $this->addItemFromSearch($item->id);
        }
    }

    public function saveForm()
    {
        dd($this->all());
        if (empty($this->invoiceItems)) {
            Alert::toast('لا يمكن حفظ الفاتورة بدون أصناف.', 'error');
            return;
        }

        $this->validate([
            'acc1_id' => 'required|exists:acc_head,id',
            'acc2_id' => 'required|exists:acc_head,id',
            'pro_date' => 'required|date',
            'invoiceItems.*.item_id' => 'required|exists:items,id',
            'invoiceItems.*.unit_id' => 'required|exists:units,id',
            'invoiceItems.*.quantity' => 'required|numeric|min:0.001',
            'invoiceItems.*.price' => 'required|numeric|min:0',
            'discount_percentage' => 'numeric|min:0|max:100',
            'additional_percentage' => 'numeric|min:0|max:100',
            'received_from_client' => 'numeric|min:0',
        ], [
            'invoiceItems.*.quantity.min' => 'الكمية يجب أن تكون أكبر من الصفر',
            'invoiceItems.*.price.min' => 'السعر يجب أن يكون قيمة موجبة',
        ]);

        foreach ($this->invoiceItems as $index => $item) {
            $availableQty = OperationItems::where('item_id', $item['item_id'])
                ->where('detail_store', $this->acc2_id)
                ->selectRaw('SUM(qty_in - qty_out) as total')
                ->value('total') ?? 0;

            if (in_array($this->type, [10, 12, 18, 19])) { // عمليات صرف
                if ($availableQty < $item['quantity']) {
                    $itemName = Item::find($item['item_id'])->name;
                    Alert::toast("الكمية غير متوفرة للصنف: $itemName. المتاح: $availableQty", 'error');
                    return;
                }
            }
        }

        try {
            // dd($this->all());
            $isJournal = in_array($this->type, [10, 11, 12, 13, 18, 19, 20, 21, 23]) ? 1 : 0;
            $isManager = $isJournal ? 0 : 1;

            $isReceipt = in_array($this->type, [10, 22, 13]); // سند قبض
            $isPayment = in_array($this->type, [11, 12]); // سند دفع

            $operation = OperHead::create([
                'pro_id'         => $this->pro_id,
                'pro_type'       => $this->type,
                'acc1'           => $this->acc1_id,
                'acc2'           => $this->acc2_id,
                'emp_id'         => $this->emp_id,
                'is_manager'     => $isManager,
                'is_journal'     => $isJournal,
                'is_stock'       => 1,
                'pro_date'       => $this->pro_date,
                'op2'            => 0,
                'pro_value'      => $this->total_after_additional,
                'fat_net'        => $this->total_after_additional,
                'price_list'     => $this->selectedPriceType,
                'accural_date'   => $this->accural_date,
                'pro_serial'     => $this->serial_number,
                'fat_disc_per'   => $this->discount_percentage,
                'fat_disc'       => $this->discount_value,
                'fat_plus_per'   => $this->additional_percentage,
                'fat_plus'       => $this->additional_value,
                'fat_total'      => $this->subtotal,
                'info'           => $this->notes,
            ]);

            $totalProfit = 0;

            foreach ($this->invoiceItems as $invoiceItem) {
                $itemId    = $invoiceItem['item_id'];
                $quantity  = $invoiceItem['quantity'];
                $unitId    = $invoiceItem['unit_id'];
                $price     = $invoiceItem['price'];
                $subValue  = $invoiceItem['sub_value'] ?? $price * $quantity;
                $discount  = $invoiceItem['discount'] ?? 0;
                $itemCost  = Item::where('id', $itemId)->value('average_cost');

                $qty_in = $qty_out = 0;
                if (in_array($this->type, [11, 13, 20])) $qty_in = $quantity;
                if (in_array($this->type, [10, 12, 18, 19])) $qty_out = $quantity;

                if (in_array($this->type, [11, 20])) {
                    $oldQty = OperationItems::where('item_id', $itemId)
                        ->where('is_stock', 1)
                        ->selectRaw('SUM(qty_in - qty_out) as total')
                        ->value('total') ?? 0;
                    $oldCost = Item::where('id', $itemId)->value('average_cost') ?? 0;
                    $newQty = $oldQty + $quantity;
                    $newCost = $newQty > 0 ? (($oldQty * $oldCost) + $subValue) / $newQty : $oldCost;
                    Item::where('id', $itemId)->update(['average_cost' => $newCost]);
                }

                if (in_array($this->type, [10, 12, 18, 19])) {
                    $discountItem = ($this->discount_value - $this->additional_value) * $subValue / $this->subtotal;
                    $itemCostTotal = $quantity * ($itemCost - $discountItem);
                    $profit = $subValue - $itemCostTotal;
                    $totalProfit += $profit;
                } else {
                    $profit = 0;
                }

                OperationItems::create([
                    'pro_tybe'      => $this->type,
                    'detail_store'  => $this->acc2_id,
                    'pro_id'        => $operation->id,
                    'item_id'       => $itemId,
                    'unit_id'       => $unitId,
                    'qty_in'        => $qty_in,
                    'qty_out'       => $qty_out,
                    'item_price'    => $price,
                    'cost_price'    => $itemCost,
                    'item_discount' => $discount,
                    'detail_value'  => $subValue,
                    'notes'         => $invoiceItem['notes'] ?? null,
                    'is_stock'      => 1,
                    'profit'        => $profit,
                ]);
            }

            $operation->update(['profit' => $totalProfit]);

            if ($isJournal) {
                $journalId = JournalHead::max('journal_id') + 1;
                $debit = $credit = null;
                switch ($this->type) {
                    case 10:
                        $debit = $this->acc1_id;
                        $credit = 93; // حساب المبيعات
                        break;
                    case 11:
                        $debit = 4111; // حساب  المشتريات
                        $credit = $this->acc1_id;
                        break;
                    case 12:
                        $debit = 94; //حساب مردود المبيعات
                        $credit = $this->acc1_id;
                        break;
                    case 13:
                        $debit = $this->acc1_id;
                        $credit = 4112; // مردود مشتريات
                        break;
                    case 18:
                        $debit = $this->acc1_id;
                        $credit = $this->acc2_id;
                        break;
                    case 19:
                        $debit = $this->acc1_id;
                        $credit = $this->acc2_id;
                        break;
                    case 20:
                        $debit = $this->acc2_id;
                        $credit = $this->acc1_id;
                        break;
                    case 21:
                        $debit = $this->acc1_id;
                        $credit = $this->acc2_id;
                        break;
                }

                if ($debit) {
                    JournalDetail::create([
                        'journal_id' => $journalId,
                        'account_id' => $debit,
                        'debit'      => $this->total_after_additional,
                        'credit'     => 0,
                        'type'       => 1,
                        'info'       => $this->notes,
                        'op_id'      => $operation->id,
                        'isdeleted'  => 0,
                    ]);
                }

                if ($credit) {
                    JournalDetail::create([
                        'journal_id' => $journalId,
                        'account_id' => $credit,
                        'debit'      => 0,
                        'credit'     => $this->total_after_additional,
                        'type'       => 1,
                        'info'       => $this->notes,
                        'op_id'      => $operation->id,
                        'isdeleted'  => 0,
                    ]);
                }

                JournalHead::create([
                    'journal_id' => $journalId,
                    'total'      => $this->total_after_additional,
                    'op2'        => $operation->id,
                    'op_id'      => $operation->id,
                    'pro_type'   => $this->type,
                    'date'       => $this->pro_date,
                    'details'    => $this->notes,
                    'user'       => Auth::id(),
                ]);
            }
            if ($this->received_from_client > 0) {
                // إنشاء سند قبض أو دفع
                if ($isReceipt || $isPayment) {
                    $voucherValue = $this->received_from_client ?? $this->total_after_additional;
                    // Ensure cash_box_id is a valid integer, otherwise set to null or a default value (e.g., 0)
                    $cashBoxId = is_numeric($this->cash_box_id) && $this->cash_box_id > 0 ? (int)$this->cash_box_id : null;
                    if ($isReceipt) {
                        $proType = 1;
                    } elseif ($isPayment) {
                        $proType = 2;
                    }
                    $voucher = OperHead::create([
                        'pro_id'     => $this->pro_id,
                        'pro_type'   => $proType,
                        'acc1'       => $this->acc1_id,
                        'acc2'       => $cashBoxId,
                        'pro_value'  => $voucherValue,
                        'pro_date'   => $this->pro_date,
                        'info'       => 'سند آلي مرتبط بعملية رقم ' . $this->pro_id,
                        'op2'        => $operation->id,
                        'is_journal' => 1,
                        'is_stock'   => 0,
                    ]);

                    $voucherJournalId = JournalHead::max('journal_id') + 1;
                    JournalHead::create([
                        'journal_id' => $voucherJournalId,
                        'total'      => $voucherValue,
                        'op_id'      => $voucher->id,
                        'op2'        => $operation->id,
                        'pro_type'   => $this->type,
                        'date'       => $this->pro_date,
                        'details'    => 'قيد سند ' . ($isReceipt ? 'قبض' : 'دفع') . ' آلي',
                        'user'       => Auth::id(),
                    ]);

                    JournalDetail::create([
                        'journal_id' => $voucherJournalId,
                        'account_id' => $isReceipt ? $this->cash_box_id : $this->acc1_id,
                        'debit'      => $voucherValue,
                        'credit'     => 0,
                        'type'       => 1,
                        'info'       => 'سند ' . ($isReceipt ? 'قبض' : 'دفع'),
                        'op_id'      => $voucher->id,
                        'isdeleted'  => 0,
                    ]);

                    JournalDetail::create([
                        'journal_id' => $voucherJournalId,
                        'account_id' => $isReceipt ? $this->acc1_id : $this->cash_box_id,
                        'debit'      => 0,
                        'credit'     => $voucherValue,
                        'type'       => 1,
                        'info'       => 'سند ' . ($isReceipt ? 'قبض' : 'دفع'),
                        'op_id'      => $voucher->id,
                        'isdeleted'  => 0,
                    ]);
                }
            }
            Alert::toast('تم حفظ الفاتورة بنجاح', 'success');
            return redirect()->route('invoices.index');
        } catch (\Exception $e) {
            logger()->error('خطأ أثناء حفظ الفاتورة: ' . $e->getMessage());
            Alert::toast('حدث خطأ أثناء حفظ الفاتورة: ', 'error');
            return back()->withInput();
        }
    }

    public function render()
    {
        return view('livewire.invoices.create-invoice-form');
    }
}
