<?php

use Livewire\Volt\Component;
use App\Models\Item;
use App\Models\Price;
use App\Models\Note;
use App\Models\NoteDetails;
use App\Support\ItemDataTransformer;
use App\Models\AccHead;
use App\Models\OperationItems;
use Livewire\WithPagination;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;
use App\Services\ItemsQueryService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

new class extends Component {
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $selectedUnit = [];
    public $displayItemData = [];
    public $perPage = 100; // Flexible pagination
    
    // Lazy-loaded data caches
    public $loadedPriceData = [];
    public $loadedNoteData = [];
    
    // Base quantities cache for current page
    public $baseQuantities = [];
    
    #[Locked]
    public $priceTypes;
    #[Locked]
    public $noteTypes;
    public $search = '';
    #[Locked]
    public $warehouses;
    public $selectedWarehouse = null;
    public $selectedPriceType = '';
    #[Locked]
    public $groups;
    public $selectedGroup = null;
    #[Locked]
    public $categories;
    public $selectedCategory = null;
    
    // Column visibility settings
    public $visibleColumns = [
        'code' => true,
        'name' => true,
        'units' => true,
        'quantity' => true,
        'average_cost' => true,
        'quantity_average_cost' => true,
        'last_cost' => true,
        'quantity_cost' => true,
        'barcode' => true,
        'actions' => true,
    ];
    
    // Individual note visibility settings
    public $visibleNotes = [];
    
    // Individual price visibility settings
    public $visiblePrices = [];

    public function mount()
    {
        // Cache static data for 60 minutes
        $this->priceTypes = Cache::remember('price_types', 3600, function () {
            return Price::all()->pluck('name', 'id');
        });
        
        $this->noteTypes = Cache::remember('note_types', 3600, function () {
            return Note::all()->pluck('name', 'id');
        });
        
        $this->warehouses = Cache::remember('warehouses_1104', 3600, function () {
            return AccHead::where('code', 'like', '1104%')
                ->where('is_basic', 0)
                ->orderBy('id')
                ->get();
        });
        
        $this->groups = Cache::remember('note_groups', 3600, function () {
            return NoteDetails::where('note_id', 1)->pluck('name', 'id');
        });
        
        $this->categories = Cache::remember('note_categories', 3600, function () {
            return NoteDetails::where('note_id', 2)->pluck('name', 'id');
        });
        
        // Initialize note visibility - all notes visible by default
        foreach ($this->noteTypes as $noteId => $noteName) {
            $this->visibleNotes[$noteId] = true;
        }
        
        // Initialize price visibility - all prices visible by default
        foreach ($this->priceTypes as $priceId => $priceName) {
            $this->visiblePrices[$priceId] = true;
        }
    }

    #[Computed]
    public function items()
    {
        $queryService = new ItemsQueryService();
        $items = $queryService->buildFilteredQuery($this->search, (int)$this->selectedGroup, (int)$this->selectedCategory)
            ->paginate($this->perPage);
        
        // Load base quantities for all items in current page
        $this->baseQuantities = $queryService->getBaseQuantitiesForItems(
            $items->pluck('id')->all(), 
            (int)$this->selectedWarehouse
        );
        
        // Pre-calculate display data for all items in current page
        $this->prepareDisplayData($items);
        
        return $items;
    }
    
    protected function prepareDisplayData($items)
    {
        foreach ($items as $item) {
            if (!isset($this->selectedUnit[$item->id])) {
                $defaultUnit = $item->units->sortBy('pivot.u_val')->first();
                $this->selectedUnit[$item->id] = $defaultUnit ? $defaultUnit->id : null;
            }
            
            // Prepare Alpine.js data for client-side calculations
            $itemId = $item->id;
            $baseQty = $this->baseQuantities[$itemId] ?? 0;
            $this->displayItemData[$itemId] = ItemDataTransformer::getItemDataForAlpine(
                $item, 
                (int)$this->selectedWarehouse, 
                $baseQty
            );
        }
    }

    public function getTotalQuantityProperty()
    {
        if (!$this->selectedPriceType) {
            return 0;
        }

        $queryService = new ItemsQueryService();
        return $queryService->getTotalQuantity(
            $this->search, 
            (int)$this->selectedGroup, 
            (int)$this->selectedCategory,
            (int)$this->selectedWarehouse
        );
    }

    public function getTotalAmountProperty()
    {
        if (!$this->selectedPriceType) {
            return 0;
        }

        $queryService = new ItemsQueryService();
        return $queryService->getTotalAmount(
            $this->search, 
            (int)$this->selectedGroup, 
            (int)$this->selectedCategory, 
            $this->selectedPriceType,
            (int)$this->selectedWarehouse
        );
    }


    public function getTotalItemsProperty()
    {
        if (!$this->selectedPriceType) {
            return 0;
        }

        $queryService = new ItemsQueryService();
        return $queryService->getTotalItems(
            $this->search, 
            (int)$this->selectedGroup, 
            (int)$this->selectedCategory,
            (int)$this->selectedWarehouse
        );
    }


    public function updatedSearch()
    {
        $this->resetPage();
        $this->clearLazyLoadedData();
    }

    public function updatedSelectedWarehouse()
    {
        $this->resetPage();
        $this->clearLazyLoadedData();
    }

    public function updatedSelectedGroup()
    {
        $this->resetPage();
        $this->clearLazyLoadedData();
    }

    public function updatedSelectedCategory()
    {
        $this->resetPage();
        $this->clearLazyLoadedData();
    }
    
    public function updatedPerPage()
    {
        $this->resetPage();
        $this->clearLazyLoadedData();
    }
    
    /**
     * Clear lazy-loaded data cache
     * Called when filters change or page changes
     */
    protected function clearLazyLoadedData()
    {
        $this->loadedPriceData = [];
        $this->loadedNoteData = [];
        $this->baseQuantities = [];
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->selectedWarehouse = null;
        $this->selectedGroup = null;
        $this->selectedCategory = null;
        $this->resetPage();
    }
    
    public function setPerPage($value)
    {
        $this->perPage = in_array($value, [25, 50, 100, 200]) ? $value : 50;
        $this->resetPage();
    }
    
    /**
     * Lazy load price data for a specific price type
     * Only loads data for items on current page
     */
    public function loadPriceColumn($priceId)
    {
        if (isset($this->loadedPriceData[$priceId])) {
            return; // Already loaded
        }
        
        $itemIds = $this->items->pluck('id');
        
        if ($itemIds->isEmpty()) {
            $this->loadedPriceData[$priceId] = [];
            return;
        }
        
        $prices = DB::table('item_prices')
            ->whereIn('item_id', $itemIds)
            ->where('price_id', $priceId)
            ->get()
            ->keyBy('item_id');
        
        $this->loadedPriceData[$priceId] = $prices;
    }
    
    /**
     * Lazy load note data for a specific note type
     * Only loads data for items on current page
     */
    public function loadNoteColumn($noteId)
    {
        if (isset($this->loadedNoteData[$noteId])) {
            return; // Already loaded
        }
        
        $itemIds = $this->items->pluck('id');
        
        if ($itemIds->isEmpty()) {
            $this->loadedNoteData[$noteId] = [];
            return;
        }
        
        $notes = DB::table('item_notes')
            ->whereIn('item_id', $itemIds)
            ->where('note_id', $noteId)
            ->get()
            ->keyBy('item_id');
        
        $this->loadedNoteData[$noteId] = $notes;
    }


    public function getVisibleColumnsCountProperty()
    {
        $count = 1; // # column
        $count += $this->visibleColumns['code'] ? 1 : 0;
        $count += $this->visibleColumns['name'] ? 1 : 0;
        $count += $this->visibleColumns['units'] ? 1 : 0;
        $count += $this->visibleColumns['quantity'] ? 1 : 0;
        $count += $this->visibleColumns['average_cost'] ? 1 : 0;
        $count += $this->visibleColumns['quantity_average_cost'] ? 1 : 0;
        $count += $this->visibleColumns['last_cost'] ? 1 : 0;
        $count += $this->visibleColumns['quantity_cost'] ? 1 : 0;
        // Count visible prices individually
        foreach ($this->priceTypes as $priceId => $priceName) {
            if (isset($this->visiblePrices[$priceId]) && $this->visiblePrices[$priceId]) {
                $count += 1;
            }
        }
        $count += $this->visibleColumns['barcode'] ? 1 : 0;
        
        // Count visible notes individually
        foreach ($this->noteTypes as $noteId => $noteName) {
            if (isset($this->visibleNotes[$noteId]) && $this->visibleNotes[$noteId]) {
                $count += 1;
            }
        }
        
        $count += $this->visibleColumns['actions'] ? 1 : 0;
        
        return $count;
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


    public function updateVisibility($columns, $prices, $notes)
    {
        // Update columns
        $this->visibleColumns = $columns;
        
        // Update prices and lazy load newly visible ones
        $previousPrices = $this->visiblePrices;
        $this->visiblePrices = $prices;
        foreach ($prices as $priceId => $isVisible) {
            if ($isVisible && !($previousPrices[$priceId] ?? false)) {
                // Newly visible - lazy load
                $this->loadPriceColumn($priceId);
            }
        }
        
        // Update notes and lazy load newly visible ones
        $previousNotes = $this->visibleNotes;
        $this->visibleNotes = $notes;
        foreach ($notes as $noteId => $isVisible) {
            if ($isVisible && !($previousNotes[$noteId] ?? false)) {
                // Newly visible - lazy load
                $this->loadNoteColumn($noteId);
            }
        }
        
        // Clear display data to force recalculation
        $this->displayItemData = [];
        
        // Reset page to ensure proper display
        $this->resetPage();
        
        // Force refresh the component to apply all changes
        $this->dispatch('$refresh');
        
        // Show success message
        session()->flash('success', 'تم تطبيق التغييرات بنجاح');
        
        // Close modal after applying changes
        $this->dispatch('close-modal');
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
                        <div class = "mt-4">
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
                        </div>

                        {{-- Column Visibility Button --}}
                        <div class="mt-4">
                            <button type="button" class="btn btn-outline-info btn-lg font-family-cairo fw-bold" 
                                    data-bs-toggle="modal" data-bs-target="#columnVisibilityModal"
                                    style="min-height: 50px;">
                                <i class="fas fa-columns me-2"></i>
                                خيارات العرض
                            </button>
                        </div>

                        {{-- Search and Filter Group --}}
                        <div class="d-flex flex-grow-1 flex-wrap align-items-center justify-content-end gap-2"
                            style="min-width: 300px;" 
                            x-data="filtersComponent()"
                            x-init="
                                searchValue = @js($this->search);
                                warehouseValue = @js($this->selectedWarehouse);
                                groupValue = @js($this->selectedGroup);
                                categoryValue = @js($this->selectedCategory);
                            ">
                            {{-- Clear Filters Button --}}
                            <div class="d-flex align-items-end mt-4">
                                <button type="button" @click="clearFilters()" style="min-height: 50px;"
                                    class="btn btn-outline-secondary btn-lg font-family-cairo fw-bold"
                                    wire:loading.attr="disabled" wire:target="clearFilters">
                                    <span wire:loading.remove wire:target="clearFilters">
                                    <i class="fas fa-times me-1"></i>
                                    مسح الفلاتر
                                    </span>
                                    <span wire:loading wire:target="clearFilters">
                                        <div class="spinner-border spinner-border-sm me-1" role="status">
                                            <span class="visually-hidden">جاري التحميل...</span>
                                        </div>
                                        جاري التحميل...
                                    </span>
                                </button>
                            </div>
                            {{-- Search Input --}}
                            <div class="flex-grow-1">
                                <label class="form-label font-family-cairo fw-bold font-12 mb-1">البحث:</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search" wire:loading.remove wire:target="search"></i>
                                        <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="search">
                                            <span class="visually-hidden">جاري التحميل...</span>
                                        </div>
                                    </span>
                                    <input type="text" x-model="searchValue" @input="updateSearch()"
                                        class="form-control font-family-cairo"
                                        placeholder="بحث بالاسم, الكود, الباركود..."
                                        wire:loading.attr="disabled" wire:target="search">
                                </div>
                            </div>

                            {{-- Warehouse Filter --}}
                            <div class="flex-grow-1">
                                <label class="form-label font-family-cairo fw-bold font-12 mb-1">المخزن:</label>
                                <div class="input-group">
                                    <select x-model="warehouseValue" @change="updateWarehouse()"
                                        class="form-select font-family-cairo fw-bold font-14"
                                        wire:loading.attr="disabled" wire:target="selectedWarehouse">
                                    <option value="">كل المخازن</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->aname }}</option>
                                    @endforeach
                                </select>
                                    <span class="input-group-text">
                                        <i class="fas fa-warehouse" wire:loading.remove wire:target="selectedWarehouse"></i>
                                        <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="selectedWarehouse">
                                            <span class="visually-hidden">جاري التحميل...</span>
                                        </div>
                                    </span>
                                </div>
                            </div>

                            {{-- Group Filter --}}
                            <div class="flex-grow-1">
                                <label class="form-label font-family-cairo fw-bold font-12 mb-1">المجموعة:</label>
                                <div class="input-group">
                                    <select x-model="groupValue" @change="updateGroup()"
                                        class="form-select font-family-cairo fw-bold font-14"
                                        wire:loading.attr="disabled" wire:target="selectedGroup">
                                    <option value="">كل المجموعات</option>
                                    @foreach ($groups as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                    <span class="input-group-text">
                                        <i class="fas fa-layer-group" wire:loading.remove wire:target="selectedGroup"></i>
                                        <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="selectedGroup">
                                            <span class="visually-hidden">جاري التحميل...</span>
                                        </div>
                                    </span>
                                </div>
                            </div>

                            {{-- Category Filter --}}
                            <div class="flex-grow-1">
                                <label class="form-label font-family-cairo fw-bold font-12 mb-1">التصنيف:</label>
                                <div class="input-group">
                                    <select x-model="categoryValue" @change="updateCategory()"
                                        class="form-select font-family-cairo fw-bold font-14"
                                        wire:loading.attr="disabled" wire:target="selectedCategory">
                                    <option value="">كل التصنيفات</option>
                                    @foreach ($categories as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                    <span class="input-group-text">
                                        <i class="fas fa-tags" wire:loading.remove wire:target="selectedCategory"></i>
                                        <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="selectedCategory">
                                            <span class="visually-hidden">جاري التحميل...</span>
                                        </div>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                

                
                <div class="card-body">
                    {{-- Pagination Control --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <label class="form-label font-family-cairo fw-bold mb-0">عرض:</label>
                            <div class="input-group" style="width: auto;">
                                <select wire:model.live="perPage" class="form-select form-select-sm font-family-cairo fw-bold"
                                    wire:loading.attr="disabled" wire:target="perPage">
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                            </select>
                                <span class="input-group-text">
                                    <i class="fas fa-list" wire:loading.remove wire:target="perPage"></i>
                                    <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="perPage">
                                        <span class="visually-hidden">جاري التحميل...</span>
                                    </div>
                                </span>
                            </div>
                            <span class="font-family-cairo fw-bold">سجل</span>
                        </div>
                        <div class="font-family-cairo fw-bold text-muted">
                            <i class="fas fa-list me-1"></i>
                            إجمالي النتائج: <span class="text-primary">{{ $this->items->total() }}</span>
                        </div>
                    </div>
                    
                    {{-- Active Filters Display --}}
                    @if ($search || $selectedWarehouse || $selectedGroup || $selectedCategory)
                        <div class="alert alert-info mb-3" 
                             x-data="{ show: true }" 
                             x-show="show"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform translate-y-2">
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
                                        <span class="badge bg-info me-1">التصنيف:
                                            {{ $categories[$selectedCategory] ?? 'غير محدد' }}</span>
                                    @endif
                                </div>
                                <button type="button" class="btn-close" @click="show = false"></button>
                            </div>
                        </div>
                    @endif

                    <div class="table-responsive" style="overflow-x: auto; max-height: 70vh; overflow-y: auto;">
                        
                        <table class="table table-striped mb-0 table-hover"
                            style="direction: rtl; font-family: 'Cairo', sans-serif;">
                            <style>
                                /* تخصيص لون الهوفر للصفوف */
                                .table-hover tbody tr:hover {
                                    background-color: #ffc107 !important;
                                    /* لون warning */
                                }
                                
                                /* Fixed header styles */
                                .table-responsive {
                                    position: relative;
                                }
                                
                                .table thead th {
                                    position: sticky;
                                    top: 0;
                                    background-color: #f8f9fa !important;
                                    z-index: 10;
                                    border-bottom: 2px solid #dee2e6;
                                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                                }
                                
                                /* Ensure proper stacking context */
                                .table-responsive {
                                    z-index: 1;
                                }
                            </style>
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th class="font-family-cairo text-center fw-bold">#</th>
                                    @if($visibleColumns['code'])
                                        <th class="font-family-cairo text-center fw-bold">الكود</th>
                                    @endif
                                    @if($visibleColumns['name'])
                                        <th class="font-family-cairo text-center fw-bold">الاسم</th>
                                    @endif
                                    @if($visibleColumns['units'])
                                        <th class="font-family-cairo text-center fw-bold" style="min-width: 130px;">الوحدات</th>
                                    @endif
                                    @if($visibleColumns['quantity'])
                                        <th class="font-family-cairo text-center fw-bold" style="min-width: 100px;">الكميه</th>
                                    @endif
                                    @if($visibleColumns['average_cost'])
                                        <th class="font-family-cairo text-center fw-bold">متوسط التكلفه</th>
                                    @endif
                                    @if($visibleColumns['quantity_average_cost'])
                                        <th class="font-family-cairo text-center fw-bold">تكلفه المتوسطه للكميه</th>
                                    @endif
                                    @if($visibleColumns['last_cost'])
                                        <th class="font-family-cairo text-center fw-bold">التكلفه الاخيره</th>
                                    @endif
                                    @if($visibleColumns['quantity_cost'])
                                        <th class="font-family-cairo text-center fw-bold">تكلفه الكميه</th>
                                    @endif
                                    @foreach ($this->priceTypes as $priceId => $priceName)
                                        @if(isset($visiblePrices[$priceId]) && $visiblePrices[$priceId])
                                            <th class="font-family-cairo text-center fw-bold">{{ $priceName }}</th>
                                        @endif
                                    @endforeach
                                    @if($visibleColumns['barcode'])
                                        <th class="font-family-cairo text-center fw-bold">الباركود</th>
                                    @endif
                                    @foreach ($this->noteTypes as $noteId => $noteName)
                                        @if(isset($visibleNotes[$noteId]) && $visibleNotes[$noteId])
                                            <th class="font-family-cairo text-center fw-bold">{{ $noteName }}</th>
                                        @endif
                                    @endforeach
                                    @canany(['تعديل الأصناف', 'حذف الأصناف'])
                                        @if($visibleColumns['actions'])
                                            <th class="font-family-cairo fw-bold">العمليات</th>
                                        @endif
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->items as $item)
                                    @php
                                        // Data already prepared in getItemsProperty()
                                        $itemData = $this->displayItemData[$item->id] ?? [];
                                        $selectedUnitId = $this->selectedUnit[$item->id] ?? null;
                                    @endphp
                                    @if (!empty($itemData))
                                         <tr wire:key="item-{{ $item->id }}-{{ $selectedUnitId ?? 'no-unit' }}" 
                                             x-data="itemRow({{ json_encode($itemData) }}, {{ $selectedUnitId }})"
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 transform scale-95"
                                             x-transition:enter-end="opacity-100 transform scale-100"
                                             x-transition:leave="transition ease-in duration-150"
                                             x-transition:leave-start="opacity-100 transform scale-100"
                                             x-transition:leave-end="opacity-0 transform scale-95">
                                            <td class="font-family-cairo text-center fw-bold">{{ $loop->iteration }}</td>
                                            
                                            @if($visibleColumns['code'])
                                                <td class="font-family-cairo text-center fw-bold" x-text="itemData.code"></td>
                                            @endif
                                            
                                            @if($visibleColumns['name'])
                                                <td class="font-family-cairo text-center fw-bold">
                                                    <span x-text="itemData.name"></span>
                                                    <a href="{{ route('item-movement', ['itemId' => $item->id]) }}">
                                                        <i class="las la-eye fa-lg text-primary" title="عرض حركات الصنف"></i>
                                                    </a>
                                                </td>
                                            @endif
                                            
                                            @if($visibleColumns['units'])
                                                <td class="font-family-cairo text-center fw-bold">
                                                     <template x-if="Object.keys(itemData.units).length > 0">
                                                         <div>
                                                            <select class="form-select font-family-cairo fw-bold font-14"
                                                                 x-model="selectedUnitId"
                                                                style="min-width: 105px;">
                                                                 <template x-for="[unitId, unit] in Object.entries(itemData.units)" :key="unitId">
                                                                     <option :value="unitId" x-text="unit.name + ' [' + formatNumber(unit.u_val) + ']'"></option>
                                                                 </template>
                                                            </select>
                                                        </div>
                                                     </template>
                                                    <template x-if="Object.keys(itemData.units).length === 0">
                                                        <span class="font-family-cairo fw-bold font-14">لا يوجد وحدات</span>
                                                    </template>
                                                </td>
                                            @endif
                                            
                                            @if($visibleColumns['quantity'])
                                                <td class="text-center fw-bold">
                                                    <span x-text="formattedQuantity.integer"></span>
                                                    <template x-if="formattedQuantity.remainder > 0 && formattedQuantity.unitName !== formattedQuantity.smallerUnitName">
                                                        <span x-text="'[' + formattedQuantity.remainder + ' ' + formattedQuantity.smallerUnitName + ']'"></span>
                                                    </template>
                                                </td>
                                            @endif
                                            
                                            @if($visibleColumns['average_cost'])
                                                <td class="font-family-cairo text-center fw-bold">
                                                    <span x-text="formatCurrency(unitAverageCost)"></span>
                                                </td>
                                            @endif
                                            
                                            @if($visibleColumns['quantity_average_cost'])
                                                <td class="font-family-cairo text-center fw-bold">
                                                    <span x-text="formatCurrency(quantityAverageCost)"></span>
                                                </td>
                                            @endif
                                            
                                            @if($visibleColumns['last_cost'])
                                                <td class="text-center fw-bold">
                                                    <span x-text="formatCurrency(unitCostPrice)"></span>
                                                </td>
                                            @endif
                                            
                                            @if($visibleColumns['quantity_cost'])
                                                <td class="text-center fw-bold">
                                                    <span x-text="formatCurrency(quantityCost)"></span>
                                                </td>
                                            @endif

                                            {{-- Prices --}}
                                            @foreach ($this->priceTypes as $priceTypeId => $priceTypeName)
                                                @if(isset($visiblePrices[$priceTypeId]) && $visiblePrices[$priceTypeId])
                                                    <td class="font-family-cairo text-center fw-bold">
                                                        <span x-text="getPriceForType({{ $priceTypeId }})"></span>
                                                    </td>
                                                @endif
                                            @endforeach

                                            @if($visibleColumns['barcode'])
                                                <td class="font-family-cairo fw-bold text-center">
                                                    <template x-if="currentBarcodes.length > 0">
                                                        <select class="form-select font-family-cairo fw-bold font-14"
                                                            style="min-width: 100px;">
                                                            <template x-for="barcode in currentBarcodes" :key="barcode.id">
                                                                <option :value="barcode.barcode" x-text="barcode.barcode"></option>
                                                            </template>
                                                        </select>
                                                    </template>
                                                    <template x-if="currentBarcodes.length === 0">
                                                        <span class="font-family-cairo fw-bold font-14">لا يوجد</span>
                                                    </template>
                                                </td>
                                            @endif

                                            {{-- Notes --}}
                                            @foreach ($this->noteTypes as $noteTypeId => $noteTypeName)
                                                @if(isset($visibleNotes[$noteTypeId]) && $visibleNotes[$noteTypeId])
                                                    <td class="font-family-cairo fw-bold text-center">
                                                        <span x-text="itemData.notes[{{ $noteTypeId }}] || ''"></span>
                                                    </td>
                                                @endif
                                            @endforeach
                                            
                                            @canany(['تعديل الأصناف', 'حذف الأصناف'])
                                                @if($visibleColumns['actions'])
                                                    <td class="d-flex justify-content-center align-items-center gap-2 mt-2">
                                                        @can('تعديل الأصناف')
                                                            <button type="button" title="تعديل الصنف" class="btn btn-success btn-sm"
                                                                wire:click="edit({{ $item->id }})">
                                                                <i class="las la-edit fa-lg"></i>
                                                            </button>
                                                        @endcan
                                                        @can('حذف الأصناف')
                                                            <button type="button" title="حذف الصنف" class="btn btn-danger btn-sm"
                                                                wire:click="delete({{ $item->id }})"
                                                                onclick="confirm('هل أنت متأكد من حذف هذا الصنف؟') || event.stopImmediatePropagation()">
                                                                <i class="las la-trash fa-lg"></i>
                                                            </button>
                                                        @endcan
                                                    </td>
                                                @endif
                                            @endcanany
                                        </tr>
                                    @endif
                                @empty
                                    @php
                                        $colspan = $this->visibleColumnsCount;
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
                    

                    
                    {{-- Pagination Links --}}
                    <div class="mt-4 d-flex justify-content-center">
                        <div class="font-family-cairo">
                        {{ $this->items->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Column Visibility Modal --}}
    <div class="modal fade" id="columnVisibilityModal" tabindex="-1" aria-labelledby="columnVisibilityModalLabel" aria-hidden="true" 
         x-data="columnVisibilityModal()" 
         x-init="
            columns = @js($this->visibleColumns);
            prices = @js($this->visiblePrices);
            notes = @js($this->visibleNotes);
         "
         @close-modal.window="$el.querySelector('.btn-close').click()">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title font-family-cairo fw-bold" id="columnVisibilityModalLabel">
                        <i class="fas fa-columns me-2"></i>
                        خيارات عرض الأعمدة
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Global Controls --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex gap-2 justify-content-center">
                                <button type="button" @click="showAllColumns()" class="btn btn-success btn-sm font-family-cairo fw-bold">
                                    <i class="fas fa-eye me-1"></i>
                                    إظهار الكل
                                </button>
                                <button type="button" @click="hideAllColumns()" class="btn btn-secondary btn-sm font-family-cairo fw-bold">
                                    <i class="fas fa-eye-slash me-1"></i>
                                    إخفاء الكل
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Columns Section --}}
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-family-cairo fw-bold text-primary mb-3">
                                <i class="fas fa-list me-2"></i>
                                الأعمدة الأساسية:
                            </h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" x-model="columns.code">
                                <label class="form-check-label font-family-cairo fw-bold">
                                    الكود
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" x-model="columns.name">
                                <label class="form-check-label font-family-cairo fw-bold">
                                    الاسم
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" x-model="columns.units">
                                <label class="form-check-label font-family-cairo fw-bold">
                                    الوحدات
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" x-model="columns.quantity">
                                <label class="form-check-label font-family-cairo fw-bold">
                                    الكمية
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" x-model="columns.barcode">
                                <label class="form-check-label font-family-cairo fw-bold">
                                    الباركود
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="font-family-cairo fw-bold text-primary mb-3">
                                <i class="fas fa-dollar-sign me-2"></i>
                                أعمدة التكلفة والأسعار:
                            </h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" x-model="columns.average_cost">
                                <label class="form-check-label font-family-cairo fw-bold">
                                    متوسط التكلفة
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" x-model="columns.quantity_average_cost">
                                <label class="form-check-label font-family-cairo fw-bold">
                                    تكلفة المتوسطة للكمية
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" x-model="columns.last_cost">
                                <label class="form-check-label font-family-cairo fw-bold">
                                    التكلفة الأخيرة
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" x-model="columns.quantity_cost">
                                <label class="form-check-label font-family-cairo fw-bold">
                                    تكلفة الكمية
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Prices Section --}}
                    @if(count($this->priceTypes) > 0)
                        <hr class="my-4">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <h6 class="font-family-cairo fw-bold text-info mb-3">
                                    <i class="fas fa-tags me-2"></i>
                                    أسعار البيع:
                                </h6>
                                <div class="d-flex gap-2 mb-3">
                                    <button type="button" @click="showAllPrices()" class="btn btn-info btn-sm font-family-cairo fw-bold">
                                        <i class="fas fa-eye me-1"></i>
                                        إظهار جميع الأسعار
                                    </button>
                                    <button type="button" @click="hideAllPrices()" class="btn btn-secondary btn-sm font-family-cairo fw-bold">
                                        <i class="fas fa-eye-slash me-1"></i>
                                        إخفاء جميع الأسعار
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                @foreach ($this->priceTypes as $priceId => $priceName)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" x-model="prices['{{ $priceId }}']">
                                        <label class="form-check-label font-family-cairo fw-bold">
                                            {{ $priceName }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    {{-- Actions Section --}}
                    @canany(['تعديل الأصناف', 'حذف الأصناف'])
                        <hr class="my-4">
                        <div class="row">
                            <div class="col-12">
                                <h6 class="font-family-cairo fw-bold text-warning mb-3">
                                    <i class="fas fa-cogs me-2"></i>
                                    العمليات:
                                </h6>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" x-model="columns.actions">
                                    <label class="form-check-label font-family-cairo fw-bold">
                                        العمليات (تعديل/حذف)
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endcanany
                    
                    {{-- Notes Section --}}
                    @if(count($this->noteTypes) > 0)
                        <hr class="my-4">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <h6 class="font-family-cairo fw-bold text-success mb-3">
                                    <i class="fas fa-sticky-note me-2"></i>
                                    الملاحظات:
                                </h6>
                                <div class="d-flex gap-2 mb-3">
                                    <button type="button" @click="showAllNotes()" class="btn btn-success btn-sm font-family-cairo fw-bold">
                                        <i class="fas fa-eye me-1"></i>
                                        إظهار جميع الملاحظات
                                    </button>
                                    <button type="button" @click="hideAllNotes()" class="btn btn-secondary btn-sm font-family-cairo fw-bold">
                                        <i class="fas fa-eye-slash me-1"></i>
                                        إخفاء جميع الملاحظات
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                @foreach ($this->noteTypes as $noteId => $noteName)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" x-model="notes['{{ $noteId }}']">
                                        <label class="form-check-label font-family-cairo fw-bold">
                                            {{ $noteName }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary font-family-cairo fw-bold" @click="applyChanges()"
                            wire:loading.attr="disabled" wire:target="updateVisibility">
                        <span wire:loading.remove wire:target="updateVisibility">
                            <i class="fas fa-check me-2"></i>
                            تطبيق التغييرات
                        </span>
                        <span wire:loading wire:target="updateVisibility">
                            <div class="spinner-border spinner-border-sm me-2" role="status">
                                <span class="visually-hidden">جاري التطبيق...</span>
                            </div>
                            جاري التطبيق...
                        </span>
                    </button>
                    <button type="button" class="btn btn-secondary font-family-cairo fw-bold" data-bs-dismiss="modal">
                        إغلاق
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Alpine.js component for item row calculations
function itemRow(itemData, initialUnitId) {
    return {
        itemData: itemData,
        selectedUnitId: initialUnitId,
        
        get selectedUnit() {
            return this.itemData.units[this.selectedUnitId] || null;
        },
        
        get selectedUVal() {
            return this.selectedUnit?.u_val || 1;
        },
        
        get currentUnitQuantity() {
            return this.selectedUVal > 0 ? this.itemData.base_quantity / this.selectedUVal : 0;
        },
        
        get formattedQuantity() {
            const integer = this.selectedUVal > 0 ? Math.floor(this.itemData.base_quantity / this.selectedUVal) : 0;
            const remainder = this.selectedUVal > 0 ? this.itemData.base_quantity % this.selectedUVal : 0;
            
            // Find smaller unit
            const units = Object.values(this.itemData.units);
            const smallerUnit = units.length > 0 ? units.reduce((min, unit) => 
                unit.u_val < min.u_val ? unit : min
            ) : null;
            
            return {
                integer: integer,
                remainder: remainder,
                unitName: this.selectedUnit?.name || '',
                smallerUnitName: smallerUnit?.name || ''
            };
        },
        
        get unitCostPrice() {
            return this.selectedUnit?.cost || 0;
        },
        
        get quantityCost() {
            return this.currentUnitQuantity * this.unitCostPrice;
        },
        
        get unitAverageCost() {
            return this.itemData.average_cost * this.selectedUVal;
        },
        
        get quantityAverageCost() {
            return this.currentUnitQuantity * this.unitAverageCost;
        },
        
        get currentBarcodes() {
            return this.itemData.barcodes[this.selectedUnitId] || [];
        },
        
        getPriceForType(priceTypeId) {
            const unitPrices = this.itemData.prices[this.selectedUnitId] || {};
            const price = unitPrices[priceTypeId];
            return price ? this.formatCurrency(price.price) : 'N/A';
        },
        
        formatCurrency(value) {
            if (value === null || value === undefined) return '0.00';
            return new Intl.NumberFormat('ar-SA', {
                style: 'currency',
                currency: 'SAR',
                minimumFractionDigits: 2
            }).format(value);
        },
        
        formatNumber(value) {
            if (value === null || value === undefined) return '0';
            // Remove trailing zeros and decimal point if not needed
            return parseFloat(value).toString();
        }
    }
}

// Alpine.js component for filters with debouncing
function filtersComponent() {
    return {
        searchValue: '',
        warehouseValue: '',
        groupValue: '',
        categoryValue: '',
        searchTimeout: null,
        
        updateSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.$wire.set('search', this.searchValue);
            }, 500);
        },
        
        updateWarehouse() {
            this.$wire.set('selectedWarehouse', this.warehouseValue);
        },
        
        updateGroup() {
            this.$wire.set('selectedGroup', this.groupValue);
        },
        
        updateCategory() {
            this.$wire.set('selectedCategory', this.categoryValue);
        },
        
        clearFilters() {
            this.searchValue = '';
            this.warehouseValue = '';
            this.groupValue = '';
            this.categoryValue = '';
            this.$wire.call('clearFilters');
        }
    }
}

// Alpine.js component for column visibility modal
function columnVisibilityModal() {
    return {
        columns: {},
        prices: {},
        notes: {},
        
        showAllColumns() {
            console.log('showAllColumns called', { columns: this.columns, prices: this.prices, notes: this.notes });
            Object.keys(this.columns).forEach(key => this.columns[key] = true);
            Object.keys(this.prices).forEach(key => this.prices[key] = true);
            Object.keys(this.notes).forEach(key => this.notes[key] = true);
        },
        
        hideAllColumns() {
            console.log('hideAllColumns called', { columns: this.columns, prices: this.prices, notes: this.notes });
            Object.keys(this.columns).forEach(key => this.columns[key] = false);
            Object.keys(this.prices).forEach(key => this.prices[key] = false);
            Object.keys(this.notes).forEach(key => this.notes[key] = false);
        },
        
        showAllPrices() {
            console.log('showAllPrices called', { prices: this.prices });
            Object.keys(this.prices).forEach(key => this.prices[key] = true);
        },
        
        hideAllPrices() {
            console.log('hideAllPrices called', { prices: this.prices });
            Object.keys(this.prices).forEach(key => this.prices[key] = false);
        },
        
        showAllNotes() {
            console.log('showAllNotes called', { notes: this.notes });
            Object.keys(this.notes).forEach(key => this.notes[key] = true);
        },
        
        hideAllNotes() {
            console.log('hideAllNotes called', { notes: this.notes });
            Object.keys(this.notes).forEach(key => this.notes[key] = false);
        },
        
        applyChanges() {
            console.log('applyChanges called', { columns: this.columns, prices: this.prices, notes: this.notes });
            this.$wire.call('updateVisibility', this.columns, this.prices, this.notes);
        }
    }
}

</script>
