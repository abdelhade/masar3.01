<?php

use Livewire\Volt\Component;
use App\Models\Item;
use App\Models\Price;
use App\Models\Note;
use App\Helpers\ItemViewModel;
use App\Models\AccHead;
use App\Models\OperationItems;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $selectedUnit = [];
    public $displayItemData = [];
    public $priceTypes;
    public $noteTypes;
    public $search = '';
    public $warehouses;
    public $selectedWarehouse = null;
    public $selectedPriceType = '';

    public function mount()
    {
        $this->priceTypes = Price::all()->pluck('name', 'id');
        $this->noteTypes = Note::all()->pluck('name', 'id');
        $this->warehouses = AccHead::where('code', 'like', '1104%')->where('is_basic', 0)->orderBy('id')->get();
    }

    public function getItemsProperty()
    {
        return Item::with([
            'units' => function ($query) {
                $query->orderBy('pivot_u_val');
            },
            'prices',
            'barcodes',
            'notes',
        ])
            ->when($this->search, function ($query) {
                $query
                    ->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%')
                    ->orWhereHas('barcodes', function ($q) {
                        $q->where('barcode', 'like', '%' . $this->search . '%');
                    });
            })
            ->paginate(100);
    }

    public function getTotalQuantityProperty()
    {
        if (!$this->selectedPriceType) {
            return 0;
        }

        $total = 0;
        foreach ($this->items as $item) {
            if (!isset($this->selectedUnit[$item->id])) {
                $defaultUnit = $item->units->sortBy('pivot.u_val')->first();
                $this->selectedUnit[$item->id] = $defaultUnit ? $defaultUnit->id : null;
            }
            
            $this->calculateAndStoreDisplayData($item->id);
            $itemData = $this->displayItemData[$item->id] ?? [];
            
            if (!empty($itemData) && isset($itemData['formattedQuantity']['quantity']['integer'])) {
                $total += $itemData['formattedQuantity']['quantity']['integer'];
            }
        }
        return $total;
    }

    public function getTotalAmountProperty()
    {
        if (!$this->selectedPriceType) {
            return 0;
        }

        $total = 0;
        foreach ($this->items as $item) {
            if (!isset($this->selectedUnit[$item->id])) {
                $defaultUnit = $item->units->sortBy('pivot.u_val')->first();
                $this->selectedUnit[$item->id] = $defaultUnit ? $defaultUnit->id : null;
            }
            
            $this->calculateAndStoreDisplayData($item->id);
            $itemData = $this->displayItemData[$item->id] ?? [];
            
            if (!empty($itemData)) {
                $quantity = $itemData['formattedQuantity']['quantity']['integer'] ?? 0;
                
                if ($this->selectedPriceType === 'cost') {
                    $unitPrice = $itemData['unitCostPrice'] ?? 0;
                } elseif ($this->selectedPriceType === 'average_cost') {
                    $unitPrice = $itemData['unitAverageCost'] ?? 0;
                } else {
                    $unitPrice = $itemData['unitSalePrices'][$this->selectedPriceType]['price'] ?? 0;
                }
                
                $total += $quantity * $unitPrice;
            }
        }
        return $total;
    }

    public function getTotalItemsProperty()
    {
        if (!$this->selectedPriceType) {
            return 0;
        }

        $count = 0;
        foreach ($this->items as $item) {
            if (!isset($this->selectedUnit[$item->id])) {
                $defaultUnit = $item->units->sortBy('pivot.u_val')->first();
                $this->selectedUnit[$item->id] = $defaultUnit ? $defaultUnit->id : null;
            }
            
            $this->calculateAndStoreDisplayData($item->id);
            $itemData = $this->displayItemData[$item->id] ?? [];
            
            if (!empty($itemData)) {
                $count++;
            }
        }
        return $count;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedWarehouse()
    {
        $this->resetPage();
    }

    public function calculateAndStoreDisplayData($itemId)
    {
        $item = $this->items->firstWhere('id', $itemId);
        if (!$item) {
            $this->displayItemData[$itemId] = [];
            return;
        }

        $selectedUnitId = $this->selectedUnit[$itemId];
        $viewModel = new ItemViewModel($this->selectedWarehouse, $item, $selectedUnitId);

        $unitSalePricesData = [];
        if ($selectedUnitId) {
            $rawPrices = $viewModel->getUnitSalePrices();
            foreach ($this->priceTypes as $priceTypeId => $priceTypeName) {
                $unitSalePricesData[$priceTypeId] = $rawPrices[$priceTypeId] ?? ['name' => $priceTypeName, 'price' => null];
            }
        }

        $this->displayItemData[$itemId] = [
            'id' => $item->id,
            'code' => $item->code,
            'name' => $item->name,
            'unitOptions' => $viewModel->getUnitOptions(),
            'formattedQuantity' => $viewModel->getFormattedQuantity(),
            'unitCostPrice' => $viewModel->getUnitCostPrice(),
            'unitAverageCost' => $viewModel->getUnitAverageCost(),
            'quantityCost' => $viewModel->getQuantityCost(),
            'quantityAverageCost' => $viewModel->getQuantityAverageCost(),
            'unitSalePrices' => $unitSalePricesData,
            'unitBarcodes' => $selectedUnitId ? $viewModel->getUnitBarcode() : [],
            'itemNotes' => $item->notes
                ->mapWithKeys(function ($note) {
                    return [$note->id => $note->pivot->note_detail_name];
                })
                ->all(),
        ];
    }

    public function updated($propertyName, $value)
    {
        if (str_starts_with($propertyName, 'selectedUnit.')) {
            $parts = explode('.', $propertyName);
            $itemId = (int) $parts[1];

            if (isset($this->selectedUnit[$itemId])) {
                $this->calculateAndStoreDisplayData($itemId);
            }
        }
    }

    public function getComputedKey($itemId)
    {
        return 'item-' . $itemId . '-' . ($this->selectedUnit[$itemId] ?? 'no-unit');
    }

    public function edit($itemId)
    {
        redirect()->route('items.edit', $itemId);
    }

    public function delete($itemId)
    {
        // check if the item is used in any operation
        $operationItems = OperationItems::where('item_id', $itemId)->get();
        if ($operationItems->count() > 0) {
            session()->flash('error', 'لا يمكن حذف الصنف لأنه مستخدم في عمليات أخرى');
            return;
        }
        $item = Item::with('units', 'prices', 'notes', 'barcodes')->find($itemId);
        $item->units()->detach();
        $item->prices()->detach();
        $item->notes()->detach();
        $item->barcodes()->delete();
        $item->delete();
        session()->flash('success', 'تم حذف الصنف بنجاح');
    }

    public function viewItemMovement($itemId, $warehouseId = 'all')
    {
        // redirect to item movement page
        return redirect()->route('item-movement', ['itemId' => $itemId, 'warehouseId' => $warehouseId]);
    }
}; ?>

<div>
    @php
        include_once app_path('Helpers/FormatHelper.php');
    @endphp
    <div class="row">
        <div class="col-lg-12">
            @if (session()->has('success'))
                <div class="alert alert-success font-family-cairo fw-bold font-12 mt-2" x-data="{ show: true }"
                    x-show="show" x-init="setTimeout(() => show = false, 3000)">
                    {{ session('success') }}
                </div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger font-family-cairo fw-bold font-12 mt-2" x-data="{ show: true }"
                    x-show="show" x-init="setTimeout(() => show = false, 3000)">
                    {{ session('error') }}
                </div>
            @endif
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        {{-- Primary Action Button --}}
                        @can('إضافة الأصناف')
                            <a href="{{ route('items.create') }}" class="btn btn-primary font-family-cairo fw-bold">
                                <i class="fas fa-plus me-2"></i>
                                {{ __('Add New') }}
                            </a>
                        @endcan

                        {{-- Search and Filter Group --}}
                        <div class="d-flex flex-grow-1 flex-wrap align-items-center justify-content-end gap-2" style="min-width: 300px;">
                            {{-- Search Input --}}
                            <div class="flex-grow-1">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" wire:model.live.debounce.300ms="search"
                                        class="form-control font-family-cairo"
                                        placeholder="بحث بالاسم, الكود, الباركود...">
                                </div>
                            </div>

                            {{-- Warehouse Filter --}}
                            <div class="flex-grow-1">
                                <select wire:model.live="selectedWarehouse" class="form-select font-family-cairo fw-bold font-14">
                                    <option value="">كل المخازن</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->aname }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">

                                <tr>
                                    <th class="font-family-cairo text-center fw-bold">#</th>
                                    <th class="font-family-cairo text-center fw-bold">الكود</th>
                                    <th class="font-family-cairo text-center fw-bold">الاسم</th>
                                    <th class="font-family-cairo text-center fw-bold">الوحدات</th>
                                    <th class="font-family-cairo text-center fw-bold">الكميه</th>
                                    <th class="font-family-cairo text-center fw-bold">متوسط التكلفه</th>
                                    <th class="font-family-cairo text-center fw-bold">تكلفه المتوسطه للكميه</th>
                                    <th class="font-family-cairo text-center fw-bold">التكلفه الاخيره</th>
                                    <th class="font-family-cairo text-center fw-bold">تكلفه الكميه</th>
                                    @foreach ($this->priceTypes as $priceId => $priceName)
                                        <th class="font-family-cairo text-center fw-bold">{{ $priceName }}</th>
                                    @endforeach
                                    <th class="font-family-cairo text-center fw-bold">الباركود</th>
                                    @foreach ($this->noteTypes as $noteId => $noteName)
                                        <th class="font-family-cairo text-center fw-bold">{{ $noteName }}</th>
                                    @endforeach
                                    @canany(['تعديل الأصناف', 'حذف الأصناف'])
                                        <th class="font-family-cairo fw-bold">العمليات</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->items as $item)
                                    @php
                                        if (!isset($this->selectedUnit[$item->id])) {
                                            $defaultUnit = $item->units->sortBy('pivot.u_val')->first();
                                            $this->selectedUnit[$item->id] = $defaultUnit ? $defaultUnit->id : null;
                                        }
                                        $this->calculateAndStoreDisplayData($item->id);
                                        $itemData = $this->displayItemData[$item->id] ?? [];
                                        // dd($itemData);
                                    @endphp
                                    @if (!empty($itemData))
                                        <tr wire:key="{{ $this->getComputedKey($item->id) }}">
                                            <td class="font-family-cairo text-center fw-bold">{{ $loop->iteration }}
                                            </td>
                                            <td class="font-family-cairo text-center fw-bold">{{ $itemData['code'] }}
                                            </td>
                                            <td class="font-family-cairo text-center fw-bold">{{ $itemData['name'] }}
                                                <a href="{{ route('item-movement', ['itemId' => $item->id]) }}">
                                                    <i class="las la-eye fa-lg" title="عرض حركات الصنف"></i>
                                                </a>
                                            </td>
                                            <td class="font-family-cairo text-center fw-bold">
                                                @if (!empty($itemData['unitOptions']))
                                                    <select class="form-select font-family-cairo fw-bold font-14"
                                                        wire:model.live="selectedUnit.{{ $item->id }}"
                                                        style="min-width: 105px;">
                                                        @foreach ($itemData['unitOptions'] as $option)
                                                            <option value="{{ $option['value'] }}">
                                                                {{ $option['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <span class="font-family-cairo fw-bold font-14">لا يوجد
                                                        وحدات</span>
                                                @endif
                                            </td>
                                            <td class="text-center fw-bold">
                                                @php $fq = $itemData['formattedQuantity']; @endphp
                                                {{ $fq['quantity']['integer'] }}
                                                @if (isset($fq['quantity']['remainder']) &&
                                                        $fq['quantity']['remainder'] > 0 &&
                                                        $fq['unitName'] !== $fq['smallerUnitName']
                                                )
                                                    [{{ $fq['quantity']['remainder'] }} {{ $fq['smallerUnitName'] }}]
                                                @endif
                                            </td>
                                            <td class="font-family-cairo text-center fw-bold">
                                                {{-- average cost * unit value --}}
                                                {{ formatCurrency($itemData['unitAverageCost']) }}
                                            </td>
                                            <td class="font-family-cairo text-center fw-bold">
                                                {{ formatCurrency($itemData['quantityAverageCost']) }}
                                            </td>
                                            <td class="text-center fw-bold">
                                                {{ formatCurrency($itemData['unitCostPrice']) }}
                                            </td>
                                            <td class="text-center fw-bold">
                                                {{ formatCurrency($itemData['quantityCost']) }}
                                            </td>

                                            {{-- Prices --}}
                                            @foreach ($this->priceTypes as $priceTypeId => $priceTypeName)
                                                <td class="font-family-cairo text-center fw-bold">
                                                    {{ isset($itemData['unitSalePrices'][$priceTypeId]['price']) ? formatCurrency($itemData['unitSalePrices'][$priceTypeId]['price']) : 'N/A' }}
                                                </td>
                                            @endforeach

                                            <td class="font-family-cairo fw-bold text-center">
                                                @if (!empty($itemData['unitBarcodes']))
                                                    <select class="form-select font-family-cairo fw-bold font-14"
                                                        style="min-width: 100px;">
                                                        @foreach ($itemData['unitBarcodes'] as $barcode)
                                                            <option value="{{ formatBarcode($barcode['barcode']) }}">
                                                                {{ formatBarcode($barcode['barcode']) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <span class="font-family-cairo fw-bold font-14">لا يوجد</span>
                                                @endif
                                            </td>

                                            {{-- Notes --}}
                                            @foreach ($this->noteTypes as $noteTypeId => $noteTypeName)
                                                <td class="font-family-cairo fw-bold text-center">
                                                    {{ $itemData['itemNotes'][$noteTypeId] ?? '' }}
                                                </td>
                                            @endforeach
                                            @canany(['تعديل الأصناف', 'حذف الأصناف'])
                                                <td>
                                                    @can('تعديل الأصناف')
                                                        <button type="button" class="btn btn-success btn-sm"
                                                            wire:click="edit({{ $item->id }})"><i
                                                                class="las la-edit fa-lg"></i></button>
                                                    @endcan
                                                    @can('حذف الأصناف')
                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            wire:click="delete({{ $item->id }})"
                                                            onclick="confirm('هل أنت متأكد من حذف هذا الصنف؟') || event.stopImmediatePropagation()">
                                                            <i class="las la-trash fa-lg"></i>
                                                        </button>
                                                    @endcan
                                                </td>
                                            @endcanany
                                        </tr>
                                    @endif
                                @empty
                                    @php
                                        $colspan = 7 + count($this->priceTypes) + 1 + count($this->noteTypes) + 1;
                                    @endphp
                                    <tr>
                                        <td colspan="{{ $colspan }}"
                                            class="text-center font-family-cairo fw-bold">لا يوجد سجلات
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{-- table footer to appear the total items quantity and the total cost or any selected price --}}
                    </div>
                    
                    {{-- Price Selector and Totals Section --}}
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="font-family-cairo fw-bold mb-0">
                                        <i class="fas fa-calculator me-2"></i>
                                        حساب المجاميع حسب السعر المحدد
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label class="form-label font-family-cairo fw-bold">اختر نوع السعر:</label>
                                            <select wire:model.live="selectedPriceType" class="form-select font-family-cairo fw-bold font-14">
                                                <option value="">اختر نوع السعر</option>
                                                <option value="cost">التكلفة</option>
                                                <option value="average_cost">متوسط التكلفة</option>
                                                @foreach ($this->priceTypes as $priceId => $priceName)
                                                    <option value="{{ $priceId }}">{{ $priceName }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label font-family-cairo fw-bold">المخزن المحدد:</label>
                                            <div class="form-control-plaintext font-family-cairo fw-bold">
                                                @if($selectedWarehouse)
                                                    @php
                                                        $warehouse = $warehouses->firstWhere('id', $selectedWarehouse);
                                                    @endphp
                                                    {{ $warehouse ? $warehouse->aname : 'غير محدد' }}
                                                @else
                                                    جميع المخازن
                                                @endif
                                            </div>
                                        </div>
                                        @if($selectedPriceType)
                                            <div class="col-md-3">
                                                {{-- <div class="card bg-light" style="max-width: 180px; margin: 0 auto;"> --}}
                                                    {{-- <div class="card-body text-center p-2"> --}}
                                                        <h6 class="font-family-cairo fw-bold text-primary mb-1" style="font-size: 0.95rem;">إجمالي الكمية</h6>
                                                        <h4 class="font-family-cairo fw-bold text-success mb-0" style="font-size: 1.2rem;">{{ $this->totalQuantity }}</h4>
                                                    {{-- </div> --}}
                                                {{-- </div> --}}
                                            </div>
                                            <div class="col-md-3">
                                                {{-- <div class="card bg-light style="max-width: 180px; margin: 0 auto;"> --}}
                                                    {{-- <div class="card-body text-center"> --}}
                                                        <h6 class="font-family-cairo fw-bold text-primary">إجمالي القيمة</h6>
                                                        <h4 class="font-family-cairo fw-bold text-success">{{ formatCurrency($this->totalAmount) }}</h4>
                                                    {{-- </div> --}}
                                                {{-- </div> --}}
                                            </div>
                                            <div class="col-md-2">
                                                {{-- <div class="card bg-light style="max-width: 180px; margin: 0 auto;"> --}}
                                                    {{-- <div class="card-body text-center"> --}}
                                                        <h6 class="font-family-cairo fw-bold text-primary">عدد الأصناف</h6>
                                                        <h4 class="font-family-cairo fw-bold text-success">{{ $this->totalItems }}</h4>
                                                    {{-- </div> --}}
                                                {{-- </div> --}}
                                            </div>
                                    @endif
                                    </div>
                                    
                                   
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-center">
                        {{ $this->items->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

