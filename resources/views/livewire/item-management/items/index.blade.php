<?php

use Livewire\Volt\Component;
use App\Models\Item;
use App\Models\Price;
use App\Models\Note;
use App\Models\NoteDetails;
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
    public $groups;
    public $selectedGroup = null;
    public $categories;
    public $selectedCategory = null;

    public function mount()
    {
        $this->priceTypes = Price::all()->pluck('name', 'id');
        $this->noteTypes = Note::all()->pluck('name', 'id');
        $this->warehouses = AccHead::where('code', 'like', '1104%')->where('is_basic', 0)->orderBy('id')->get();
        $this->groups = NoteDetails::where('note_id', 1)->pluck('name', 'id');
        $this->categories = NoteDetails::where('note_id', 2)->pluck('name', 'id');
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
            ->when($this->selectedGroup, function ($query) {
                $query->whereHas('notes', function ($q) {
                    $q->where('note_id', 1) // Groups have note_id = 1
                        ->where('note_detail_name', function ($subQuery) {
                            $subQuery->select('name')->from('note_details')->where('id', $this->selectedGroup);
                        });
                });
            })
            ->when($this->selectedCategory, function ($query) {
                $query->whereHas('notes', function ($q) {
                    $q->where('note_id', 2) // Categories have note_id = 2
                        ->where('note_detail_name', function ($subQuery) {
                            $subQuery->select('name')->from('note_details')->where('id', $this->selectedCategory);
                        });
                });
            })
            ->paginate(100);
    }

    public function getTotalQuantityProperty()
    {
        if (!$this->selectedPriceType) {
            return 0;
        }

        // Get all filtered items without pagination
        $allFilteredItems = Item::with([
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
            ->when($this->selectedGroup, function ($query) {
                $query->whereHas('notes', function ($q) {
                    $q->where('note_id', 1) // Groups have note_id = 1
                        ->where('note_detail_name', function ($subQuery) {
                            $subQuery->select('name')->from('note_details')->where('id', $this->selectedGroup);
                        });
                });
            })
            ->when($this->selectedCategory, function ($query) {
                $query->whereHas('notes', function ($q) {
                    $q->where('note_id', 2) // Categories have note_id = 2
                        ->where('note_detail_name', function ($subQuery) {
                            $subQuery->select('name')->from('note_details')->where('id', $this->selectedCategory);
                        });
                });
            })
            ->get();

        $total = 0;
        foreach ($allFilteredItems as $item) {
            // Get default unit for this item
            $defaultUnit = $item->units->sortBy('pivot.u_val')->first();
            $selectedUnitId = $defaultUnit ? $defaultUnit->id : null;

            // Create ItemViewModel for this item
            $viewModel = new ItemViewModel($this->selectedWarehouse, $item, $selectedUnitId);
            $formattedQuantity = $viewModel->getFormattedQuantity();

            if (isset($formattedQuantity['quantity']['integer'])) {
                $total += $formattedQuantity['quantity']['integer'];
            }
        }
        return $total;
    }

    public function getTotalAmountProperty()
    {
        if (!$this->selectedPriceType) {
            return 0;
        }

        // Get all filtered items without pagination
        $allFilteredItems = Item::with([
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
            ->when($this->selectedGroup, function ($query) {
                $query->whereHas('notes', function ($q) {
                    $q->where('note_id', 1) // Groups have note_id = 1
                        ->where('note_detail_name', function ($subQuery) {
                            $subQuery->select('name')->from('note_details')->where('id', $this->selectedGroup);
                        });
                });
            })
            ->when($this->selectedCategory, function ($query) {
                $query->whereHas('notes', function ($q) {
                    $q->where('note_id', 2) // Categories have note_id = 2
                        ->where('note_detail_name', function ($subQuery) {
                            $subQuery->select('name')->from('note_details')->where('id', $this->selectedCategory);
                        });
                });
            })
            ->get();

        $total = 0;
        foreach ($allFilteredItems as $item) {
            // Get default unit for this item
            $defaultUnit = $item->units->sortBy('pivot.u_val')->first();
            $selectedUnitId = $defaultUnit ? $defaultUnit->id : null;

            // Create ItemViewModel for this item
            $viewModel = new ItemViewModel($this->selectedWarehouse, $item, $selectedUnitId);
            $formattedQuantity = $viewModel->getFormattedQuantity();
            $quantity = $formattedQuantity['quantity']['integer'] ?? 0;

            // Get unit price based on selected price type
            if ($this->selectedPriceType === 'cost') {
                $unitPrice = $viewModel->getUnitCostPrice() ?? 0;
            } elseif ($this->selectedPriceType === 'average_cost') {
                $unitPrice = $viewModel->getUnitAverageCost() ?? 0;
            } else {
                $unitSalePrices = $viewModel->getUnitSalePrices();
                $unitPrice = $unitSalePrices[$this->selectedPriceType]['price'] ?? 0;
            }

            $total += $quantity * $unitPrice;
        }
        return $total;
    }

    public function getTotalItemsProperty()
    {
        if (!$this->selectedPriceType) {
            return 0;
        }

        // Get all filtered items without pagination
        $allFilteredItems = Item::with([
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
            ->when($this->selectedGroup, function ($query) {
                $query->whereHas('notes', function ($q) {
                    $q->where('note_id', 1) // Groups have note_id = 1
                        ->where('note_detail_name', function ($subQuery) {
                            $subQuery->select('name')->from('note_details')->where('id', $this->selectedGroup);
                        });
                });
            })
            ->when($this->selectedCategory, function ($query) {
                $query->whereHas('notes', function ($q) {
                    $q->where('note_id', 2) // Categories have note_id = 2
                        ->where('note_detail_name', function ($subQuery) {
                            $subQuery->select('name')->from('note_details')->where('id', $this->selectedCategory);
                        });
                });
            })
            ->get();

        $count = 0;
        foreach ($allFilteredItems as $item) {
            // Get default unit for this item
            $defaultUnit = $item->units->sortBy('pivot.u_val')->first();
            $selectedUnitId = $defaultUnit ? $defaultUnit->id : null;

            // Create ItemViewModel for this item
            $viewModel = new ItemViewModel($this->selectedWarehouse, $item, $selectedUnitId);
            $formattedQuantity = $viewModel->getFormattedQuantity();

            // Count items that have valid quantity data
            if (isset($formattedQuantity['quantity']['integer'])) {
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

    public function updatedSelectedGroup()
    {
        $this->resetPage();
    }

    public function updatedSelectedCategory()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->selectedWarehouse = null;
        $this->selectedGroup = null;
        $this->selectedCategory = null;
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

    public function printItems()
    {
        // This method will be used to trigger print functionality
        $this->dispatch('print-items');
    }
}; ?>

<div>
    @php
        include_once app_path('Helpers/FormatHelper.php');
    @endphp
    
    <style>
        .print-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
    </style>

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
                {{-- card title --}}
                <div class="text-center bg-dark text-white py-3">
                    <h5 class="card-title font-family-cairo fw-bold font-20 text-white">
                        {{ __('قائمه الأصناف مع الأرصده') }}
                    </h5>
                </div>
                

                
                <div class="card-header">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        {{-- Primary Action Button --}}
                        @can('إضافة الأصناف')
                            <a href="{{ route('items.create') }}"
                                class="btn btn-outline-primary btn-lg font-family-cairo fw-bold mt-4 d-flex justify-content-center align-items-center text-center"
                                style="min-height: 50px;">
                                <i class="fas fa-plus me-2"></i>
                                <span class="w-100 text-center">{{ __('إضافه صنف') }}</span>
                            </a>
                        @endcan

                        {{-- Print Button --}}
                        <a href="{{ route('items.print', [
                            'search' => $search,
                            'warehouse' => $selectedWarehouse,
                            'group' => $selectedGroup,
                            'category' => $selectedCategory,
                            'priceType' => $selectedPriceType
                        ]) }}" target="_blank" class="print-btn font-family-cairo fw-bold" style="text-decoration: none;">
                            <i class="fas fa-print"></i>
                            طباعة القائمة
                        </a>

                        {{-- Search and Filter Group --}}
                        <div class="d-flex flex-grow-1 flex-wrap align-items-center justify-content-end gap-2"
                            style="min-width: 300px;">
                            {{-- Clear Filters Button --}}
                            <div class="d-flex align-items-end mt-4">
                                <button type="button" wire:click="clearFilters" style="min-height: 50px;"
                                    class="btn btn-outline-secondary btn-lg font-family-cairo fw-bold">
                                    <i class="fas fa-times me-1"></i>
                                    مسح الفلاتر
                                </button>
                            </div>
                            {{-- Search Input --}}
                            <div class="flex-grow-1">
                                <label class="form-label font-family-cairo fw-bold font-12 mb-1">البحث:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" wire:model.live.debounce.300ms="search"
                                        class="form-control font-family-cairo"
                                        placeholder="بحث بالاسم, الكود, الباركود...">
                                </div>
                            </div>

                            {{-- Warehouse Filter --}}
                            <div class="flex-grow-1">
                                <label class="form-label font-family-cairo fw-bold font-12 mb-1">المخزن:</label>
                                <select wire:model.live="selectedWarehouse"
                                    class="form-select font-family-cairo fw-bold font-14">
                                    <option value="">كل المخازن</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->aname }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Group Filter --}}
                            <div class="flex-grow-1">
                                <label class="form-label font-family-cairo fw-bold font-12 mb-1">المجموعة:</label>
                                <select wire:model.live="selectedGroup"
                                    class="form-select font-family-cairo fw-bold font-14">
                                    <option value="">كل المجموعات</option>
                                    @foreach ($groups as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Category Filter --}}
                            <div class="flex-grow-1">
                                <label class="form-label font-family-cairo fw-bold font-12 mb-1">الفئة:</label>
                                <select wire:model.live="selectedCategory"
                                    class="form-select font-family-cairo fw-bold font-14">
                                    <option value="">كل الفئات</option>
                                    @foreach ($categories as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                

                
                <div class="card-body">
                    {{-- Active Filters Display --}}
                    @if ($search || $selectedWarehouse || $selectedGroup || $selectedCategory)
                        <div class="alert alert-info mb-3" x-data="{ show: true }" x-show="show">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="font-family-cairo fw-bold">
                                    <i class="fas fa-filter me-2"></i>
                                    الفلاتر النشطة:
                                    @if ($search)
                                        <span class="badge bg-primary me-1">البحث: {{ $search }}</span>
                                    @endif
                                    @if ($selectedWarehouse)
                                        @php $warehouse = $warehouses->firstWhere('id', $selectedWarehouse); @endphp
                                        <span class="badge bg-success me-1">المخزن:
                                            {{ $warehouse ? $warehouse->aname : 'غير محدد' }}</span>
                                    @endif
                                    @if ($selectedGroup)
                                        <span class="badge bg-warning me-1">المجموعة:
                                            {{ $groups[$selectedGroup] ?? 'غير محدد' }}</span>
                                    @endif
                                    @if ($selectedCategory)
                                        <span class="badge bg-info me-1">الفئة:
                                            {{ $categories[$selectedCategory] ?? 'غير محدد' }}</span>
                                    @endif
                                </div>
                                <button type="button" class="btn-close" @click="show = false"></button>
                            </div>
                        </div>
                    @endif

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-striped mb-0 table-hover"
                            style="direction: rtl; font-family: 'Cairo', sans-serif;">
                            <style>
                                /* تخصيص لون الهوفر للصفوف */
                                .table-hover tbody tr:hover {
                                    background-color: #ffc107 !important;
                                    /* لون warning */
                                }
                            </style>
                            <thead class="table-light text-center align-middle">

                                <tr>
                                    <th class="font-family-cairo text-center fw-bold">#</th>
                                    <th class="font-family-cairo text-center fw-bold">الكود</th>
                                    <th class="font-family-cairo text-center fw-bold">الاسم</th>
                                    <th class="font-family-cairo text-center fw-bold" style="min-width: 130px;">الوحدات
                                    </th>
                                    <th class="font-family-cairo text-center fw-bold" style="min-width: 100px;">الكميه
                                    </th>
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
                                                    <i class="las la-eye fa-lg text-primary"
                                                        title="عرض حركات الصنف"></i>
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
                                    <h6 class="font-family-cairo fw-bold mb-0 text-white">
                                        <i class="fas fa-calculator me-2"></i>
                                        تقيم المخزون
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label class="form-label font-family-cairo fw-bold">اختر نوع السعر:</label>
                                            <select wire:model.live="selectedPriceType"
                                                class="form-select font-family-cairo fw-bold font-14">
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
                                                @if ($selectedWarehouse)
                                                    @php
                                                        $warehouse = $warehouses->firstWhere('id', $selectedWarehouse);
                                                    @endphp
                                                    {{ $warehouse ? $warehouse->aname : 'غير محدد' }}
                                                @else
                                                    جميع المخازن
                                                @endif
                                            </div>
                                        </div>
                                        @if ($selectedPriceType)
                                            <div class="col-md-3">
                                                <h6 class="font-family-cairo fw-bold text-primary mb-1"
                                                    style="font-size: 0.95rem;">إجمالي الكمية</h6>
                                                <h4 class="font-family-cairo fw-bold text-success mb-0"
                                                    style="font-size: 1.2rem;">{{ $this->totalQuantity }}</h4>
                                            </div>
                                            <div class="col-md-3">
                                                <h6 class="font-family-cairo fw-bold text-primary">إجمالي القيمة</h6>
                                                <h4 class="font-family-cairo fw-bold text-success">
                                                    {{ formatCurrency($this->totalAmount) }}</h4>
                                            </div>
                                            <div class="col-md-2">
                                                <h6 class="font-family-cairo fw-bold text-primary">عدد الأصناف</h6>
                                                <h4 class="font-family-cairo fw-bold text-success">
                                                    {{ $this->totalItems }}</h4>
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
