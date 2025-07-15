<?php

use Livewire\Volt\Component;
use App\Models\Item;
use App\Models\Price;
use App\Models\Note;
use App\Helpers\ItemViewModel;
use App\Models\AccHead;
use App\Models\OperationItems;

new class extends Component {
    public $items;
    public $selectedUnit = [];
    public $displayItemData = [];
    public $priceTypes;
    public $noteTypes;
    public $search = '';
    public $warehouses;
    public $selectedWarehouse = null;

    public function mount()
    {
        $this->priceTypes = Price::all()->pluck('name', 'id');
        $this->noteTypes = Note::all()->pluck('name', 'id');
        $this->warehouses = AccHead::where('is_stock', 1)->get();
        $this->loadItems();
    }

    public function loadItems()
    {
        $this->items = Item::with([
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
            ->get();

        $this->displayItemData = [];
        foreach ($this->items as $item) {
            if (!isset($this->selectedUnit[$item->id])) {
                $defaultUnit = $item->units->sortBy('pivot.u_val')->first();
                $this->selectedUnit[$item->id] = $defaultUnit ? $defaultUnit->id : null;
            }
            $this->calculateAndStoreDisplayData($item->id);
        }
    }

    public function updatedSearch()
    {
        $this->loadItems();
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
            'quantityCost' => $viewModel->getQuantityCost(),
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
        // refresh the items
        $this->loadItems();
    }

    public function updatedSelectedWarehouse()
    {
        $this->loadItems();
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
                <div class="card-header d-flex justify-content-between align-items-center">
                    {{-- @can('بحث الأصناف ') --}}
                        <div class="w-25">
                            <select class="form-select font-family-cairo fw-bold font-14"
                                wire:model.live="selectedWarehouse" style="min-width: 105px;">
                                <option value="">كل المخازن</option>
                                @foreach ($this->warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->aname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-25">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                class="form-control font-family-cairo" placeholder="بحث بالاسم أو الكود أو الباركود...">
                        </div>
                    {{-- @endcan --}}
                </div>
                <div class="card-header">
                    <a href="{{ route('items.create') }}" class="btn btn-primary font-family-cairo fw-bold">
                        {{ __('Add New Item') }}
                        <i class="fas fa-plus me-2"></i>
                    </a>
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
                                    <th class="font-family-cairo text-center fw-bold">التكلفه</th>
                                    <th class="font-family-cairo text-center fw-bold">تكلفه الكميه</th>
                                    @foreach ($this->priceTypes as $priceId => $priceName)
                                        <th class="font-family-cairo text-center fw-bold">{{ $priceName }}</th>
                                    @endforeach
                                    <th class="font-family-cairo text-center fw-bold">الباركود</th>
                                    @foreach ($this->noteTypes as $noteId => $noteName)
                                        <th class="font-family-cairo text-center fw-bold">{{ $noteName }}</th>
                                    @endforeach
                                    <th class="font-family-cairo text-center fw-bold">العمليات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($displayItemData as $itemId => $itemData)
                                    @if (!empty($itemData))
                                        <tr wire:key="{{ $this->getComputedKey($itemId) }}">
                                            <td class="font-family-cairo text-center fw-bold">{{ $loop->iteration }}
                                            </td>
                                            <td class="font-family-cairo text-center fw-bold">{{ $itemData['code'] }}
                                            </td>
                                            <td class="font-family-cairo text-center fw-bold">{{ $itemData['name'] }}
                                            </td>
                                            <td class="font-family-cairo text-center fw-bold">
                                                @if (!empty($itemData['unitOptions']))
                                                    <select class="form-select font-family-cairo fw-bold font-14"
                                                        wire:model.live="selectedUnit.{{ $itemId }}"
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


                                            <td>
                                                @can('تعديل الأصناف')
                                                    <button type="button" class="btn btn-success btn-sm"
                                                        wire:click="edit({{ $itemId }})"
                                                        title="تعديل الصنف">
                                                        <i class="las la-edit fa-lg"></i>
                                                    </button>
                                                @endcan
                                                {{-- view item movement --}}
                                                <button type="button" class="btn btn-info btn-sm"
                                                    wire:click="viewItemMovement({{ $itemId }}, {{ $selectedWarehouse }})"
                                                    title="عرض حركات الصنف">
                                                    <i class="las la-chart-bar fa-lg"></i>
                                                </button>

                                                @can('حذف الأصناف')
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        wire:click="delete({{ $itemId }})"
                                                        title="حذف الصنف"
                                                        onclick="confirm('هل أنت متأكد من حذف هذا الصنف؟') || event.stopImmediatePropagation()">
                                                        <i class="las la-trash fa-lg"></i>
                                                    </button>
                                                @endcan

                                            </td>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
