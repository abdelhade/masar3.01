<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Item;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Models\AccHead;
use App\Models\OperationItems;

new class extends Component {
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public ?int $itemId = null;
    public $warehouseId = 'all';
    public ?string $fromDate = null;
    public ?string $toDate = null;
    public string $itemName = '';
    public string $searchTerm = '';
    public int $highlightedIndex = -1;
    public bool $showDropdown = false;

    public Collection $warehouses;

    public function mount($itemId = null, $warehouseId = null): void
    {
        $this->warehouses = AccHead::where('code', 'like', '1104%')->where('is_basic', 0)->orderBy('id')->pluck('aname', 'id');
        $this->fromDate = now()->startOfMonth()->toDateString();
        $this->toDate = now()->endOfMonth()->toDateString();
        // Set from route if present
        if ($itemId) {
            $this->itemId = $itemId;
            $item = Item::find($itemId);
            if ($item) {
                $this->itemName = $item->name;
                $this->searchTerm = $item->name;
            }
        }
        if ($warehouseId && $warehouseId !== 'all') {
            $this->warehouseId = $warehouseId;
        }else{
            $this->warehouseId = 'all';
        }
    }

    public function updatedSearchTerm(): void
    {
        $this->highlightedIndex = -1;
        $this->showDropdown = true;
        if (empty($this->searchTerm)) {
            $this->itemId = null;
            $this->itemName = '';
        }
    }

    public function getSearchResultsProperty()
    {
        if (strlen($this->searchTerm) < 2 || $this->searchTerm === $this->itemName) {
            return collect();
        }

        return Item::where('name', 'like', '%' . $this->searchTerm . '%')
            ->select('id', 'name')
            ->limit(7)
            ->get();
    }

    public function selectItem(int $id, string $name): void
    {
        $this->itemId = $id;
        $this->itemName = $name;
        $this->searchTerm = $name;
        $this->highlightedIndex = -1;
        $this->showDropdown = false;
    }

    public function arrowDown(): void
    {
        $resultsCount = count($this->searchResults);
        if ($resultsCount > 0) {
            $this->highlightedIndex = ($this->highlightedIndex + 1) % $resultsCount;
        }
    }

    public function arrowUp(): void
    {
        $resultsCount = count($this->searchResults);
        if ($resultsCount > 0) {
            $this->highlightedIndex = ($this->highlightedIndex - 1 + $resultsCount) % $resultsCount;
        }
    }

    public function selectHighlightedItem(): void
    {
        $results = $this->searchResults;
        if ($this->highlightedIndex >= 0 && isset($results[$this->highlightedIndex])) {
            $item = $results[$this->highlightedIndex];
            $this->selectItem($item->id, $item->name);
        }
    }

    public function showResults(): void
    {
        $this->showDropdown = true;
    }

    public function hideDropdown(): void
    {
        $this->showDropdown = false;
    }

    public function getArabicReferenceName(int $referenceId): string
    {
        $baseId = $referenceId;
        $translations = [
            '10' => 'فاتورة مبيعات',
            '11' => 'فاتورة مشتريات',
            '12' => 'مردود مبيعات',
            '13' => 'مردود مشتريات',
            '14' => 'أمر بيع',
            '15' => 'أمر شراء',
            '16' => 'عرض سعر لعميل',
            '17' => 'عرض سعر من مورد',
            '18' => 'فاتورة تالف',
            '19' => 'أمر صرف',
            '20' => 'أمر إضافة',
            '21' => 'تحويل من مخزن لمخزن',
            '22' => 'أمر حجز',
            '23' => 'تحويل بين فروع',
            '35' => 'سند إتلاف مخزون',
            '56' => 'نموذج تصنيع',
            '57' => 'أمر تشغيل',
            '58' => 'تصنيع معياري',
            '59' => 'تصنيع حر',
            '60' => 'تسجيل الأرصدة الافتتاحية للمخازن',
        ];
        

        return $translations[$baseId] ?? 'N/A';
    }

    public function with(): array
    {
        return [
            'movements' => $this->getMovements(),
        ];
    }

    public function getMovements()
    {
        if (!$this->itemId) {
            return collect();
        }

        return OperationItems::where('item_id', $this->itemId)
            ->when($this->warehouseId !== 'all', function ($q) {
                $q->where('detail_store', $this->warehouseId);
            })
            ->when($this->fromDate, function ($q) {
                $q->whereDate('created_at', '>=', $this->fromDate);
            })
            ->when($this->toDate, function ($q) {
                $q->whereDate('created_at', '<=', $this->toDate);
            })
            ->orderBy('created_at', 'asc')
            ->paginate(100);
    }

    public function updated($property): void
    {
        if (in_array($property, ['itemId', 'warehouseId', 'fromDate', 'toDate'])) {
            $this->resetPage();
        }
    }

    // public function viewReference(int $movementId): void
    // {
    //     $this->selectedMovement = InventoryMovement::with('reference')->find($movementId);
    //     dd($this->selectedMovement);
    //     $this->dispatch('show-reference-modal');
    // }

    // public function closeModal(): void
    // {
    //     $this->selectedMovement = null;
    // }

    public function getTotalQuantityProperty()
    {
        if (!$this->itemId) {
            return 0;
        }

        $query = DB::table('operation_items')->where('item_id', $this->itemId);

        if ($this->warehouseId !== 'all') {
            $query->where('detail_store', $this->warehouseId);
        }

        return $query->sum('qty_in') - $query->sum('qty_out');
    }
}; ?>

<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title font-family-cairo fw-bold">{{ __('items.item_movement_report') }}</h4>
            </div>
        </div>
    </div>

    @if ($itemId)
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('item-movement.print', [
                        'itemId' => $itemId,
                        'warehouseId' => $warehouseId,
                        'fromDate' => $fromDate,
                        'toDate' => $toDate
                    ]) }}" target="_blank" class="btn btn-outline font-family-cairo fw-bold" style="text-decoration: none;">
                        <i class="fas fa-print"></i>
                        {{ __('items.print_report') }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="font-family-cairo fw-bold">{{ __('items.search_filters') }}</h4>
            @if ($itemId)
                <div class="d-flex align-items-center">
                    <span class="font-family-cairo fw-bold me-2">{{ __('items.current_balance_for_item', ['item' => $itemName]) }}:</span>
                    <span
                        class="bg-soft-primary font-family-cairo fw-bold font-16">{{ number_format($this->totalQuantity) }}
                        {{ Item::find($this->itemId)->units->first()->name }}</span>
                </div>
            @endif
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="item" class="form-label font-family-cairo fw-bold">{{ __('items.item') }}</label>
                        <div class="dropdown" wire:click.outside="hideDropdown">
                            <input type="text" class="form-control font-family-cairo fw-bold"
                                placeholder="{{ __('items.search_for_item') }}" wire:model.live.debounce.300ms="searchTerm"
                                wire:keydown.arrow-down.prevent="arrowDown" wire:keydown.arrow-up.prevent="arrowUp"
                                wire:keydown.enter.prevent="selectHighlightedItem" wire:focus="showResults"
                                onclick="this.select()">
                            @if ($showDropdown && $this->searchResults->isNotEmpty())
                                <ul class="dropdown-menu show" style="width: 100%;">
                                    @foreach ($this->searchResults as $index => $item)
                                        <li>
                                            <a class="font-family-cairo fw-bold dropdown-item {{ $highlightedIndex === $index ? 'active' : '' }}"
                                                href="#"
                                                wire:click.prevent="selectItem({{ $item->id }}, '{{ $item->name }}')">
                                                {{ $item->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @elseif($showDropdown && strlen($searchTerm) >= 2 && $searchTerm !== $itemName)
                                <ul class="dropdown-menu show" style="width: 100%;">
                                    <li><span class="dropdown-item-text font-family-cairo fw-bold text-danger">{{ __('items.no_results_for_search') }}</span></li>
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="warehouse" class="form-label font-family-cairo fw-bold">{{ __('items.warehouse') }}</label>
                        <select wire:model.live="warehouseId" id="warehouse"
                            class="form-select font-family-cairo fw-bold" style = "height: 50px;">
                            <option class="font-family-cairo fw-bold" value="all">{{ __('items.all_warehouses') }}</option>
                            @foreach ($warehouses as $id => $name)
                                <option class="font-family-cairo fw-bold" value="{{ $id }}">
                                    {{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label for="fromDate" class="form-label font-family-cairo fw-bold">{{ __('items.from_date') }}</label>
                        <input type="date" wire:model.live="fromDate" id="fromDate"
                            class="form-control font-family-cairo fw-bold">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label for="toDate" class="form-label font-family-cairo fw-bold">{{ __('items.to_date') }}</label>
                        <input type="date" wire:model.live="toDate" id="toDate"
                            class="form-control font-family-cairo fw-bold">
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($itemId)
        @php
            $currentItem = Item::find($this->itemId);
            $defaultUnitName = optional($currentItem?->units?->first())->name ?? '';
            $movementsCollection = $movements instanceof \Illuminate\Contracts\Pagination\Paginator ? collect($movements->items()) : collect($movements);
            $incomingMovements = $movementsCollection->filter(fn($movement) => $movement->qty_in > 0);
            $outgoingMovements = $movementsCollection->filter(fn($movement) => $movement->qty_out > 0);

            if ($this->warehouseId === 'all' || empty($this->warehouseId)) {
                $balanceBefore =
                    OperationItems::where('item_id', $this->itemId)
                        ->where('created_at', '<', $this->fromDate)
                        ->sum('qty_in') -
                    OperationItems::where('item_id', $this->itemId)
                        ->where('created_at', '<', $this->fromDate)
                        ->sum('qty_out');
            } else {
                $balanceBefore =
                    OperationItems::where('item_id', $this->itemId)
                        ->where('detail_store', $this->warehouseId)
                        ->where('created_at', '<', $this->fromDate)
                        ->sum('qty_in') -
                    OperationItems::where('item_id', $this->itemId)
                        ->where('detail_store', $this->warehouseId)
                        ->where('created_at', '<', $this->fromDate)
                        ->sum('qty_out');
            }

            $runningBalance = $balanceBefore;
            $movementBalances = [];
            foreach ($movementsCollection->sortBy('created_at') as $entry) {
                $movementBalances[$entry->id]['before'] = $runningBalance;
                if ($entry->qty_in > 0) {
                    $runningBalance += $entry->qty_in;
                } elseif ($entry->qty_out > 0) {
                    $runningBalance -= $entry->qty_out;
                }
                $movementBalances[$entry->id]['after'] = $runningBalance;
            }
        @endphp

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 font-family-cairo">{{ __('items.in') }} {{ __('items.movement_type') }}</p>
                            <h3 class="mb-0 font-family-cairo fw-bold text-success">{{ number_format($incomingMovements->count()) }}</h3>
                        </div>
                        <span class="avatar-sm rounded-circle bg-soft-success d-inline-flex align-items-center justify-content-center">
                            <i class="fas fa-arrow-down text-success"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 font-family-cairo">{{ __('items.out') }} {{ __('items.movement_type') }}</p>
                            <h3 class="mb-0 font-family-cairo fw-bold text-danger">{{ number_format($outgoingMovements->count()) }}</h3>
                        </div>
                        <span class="avatar-sm rounded-circle bg-soft-danger d-inline-flex align-items-center justify-content-center">
                            <i class="fas fa-arrow-up text-danger"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-soft-success border-0 d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 font-family-cairo fw-bold text-success">
                                <i class="fas fa-arrow-down me-2"></i>{{ __('items.in') }} - {{ __('items.movement_type') }}
                            </h5>
                            <small class="text-muted font-family-cairo">{{ __('items.stock_received_details') }}</small>
                        </div>
                        <span class="badge bg-success-subtle text-success font-family-cairo fw-bold">
                            {{ number_format($incomingMovements->sum('qty_in')) }} {{ $defaultUnitName }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-centered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="font-family-cairo fw-bold">{{ __('common.date') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('items.operation_source') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('items.warehouse') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('items.unit') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('common.quantity') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('items.balance_after_movement') }}</th>
                                        <th class="font-family-cairo fw-bold text-center">{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($incomingMovements as $movement)
                                        <tr>
                                            <td class="font-family-cairo fw-bold">{{ $movement->created_at->format('Y-m-d') }}</td>
                                            <td class="font-family-cairo fw-bold">
                                                {{ $movement->pro_id }}#_{{ $this->getArabicReferenceName($movement->pro_tybe) }}
                                            </td>
                                            <td class="font-family-cairo fw-bold">
                                                {{ AccHead::find($movement->detail_store)->aname ?? 'N/A' }}
                                            </td>
                                            <td class="font-family-cairo fw-bold">
                                                {{ optional(App\Models\Unit::find($movement->unit_id))->name ?? $defaultUnitName }}
                                            </td>
                                            <td class="font-family-cairo fw-bold text-success">
                                                {{ $movement->fat_quantity ?? $movement->qty_in }}
                                            </td>
                                            <td class="font-family-cairo fw-bold">
                                                {{ number_format($movementBalances[$movement->id]['after'] ?? 0) }}
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('invoice.view', $movement->pro_id) }}" class="btn btn-soft-success btn-sm font-family-cairo fw-bold" target="_blank">
                                                    <i class="fas fa-eye me-1"></i>{{ __('common.view') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center font-family-cairo fw-bold text-muted">
                                                {{ __('items.no_movements_for_criteria') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-soft-danger border-0 d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 font-family-cairo fw-bold text-danger">
                                <i class="fas fa-arrow-up me-2"></i>{{ __('items.out') }} - {{ __('items.movement_type') }}
                            </h5>
                            <small class="text-muted font-family-cairo">{{ __('items.stock_issued_details') }}</small>
                        </div>
                        <span class="badge bg-danger-subtle text-danger font-family-cairo fw-bold">
                            {{ number_format($outgoingMovements->sum('qty_out')) }} {{ $defaultUnitName }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-centered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="font-family-cairo fw-bold">{{ __('common.date') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('items.operation_source') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('items.warehouse') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('items.unit') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('common.quantity') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('items.balance_after_movement') }}</th>
                                        <th class="font-family-cairo fw-bold text-center">{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($outgoingMovements as $movement)
                                        <tr>
                                            <td class="font-family-cairo fw-bold">{{ $movement->created_at->format('Y-m-d') }}</td>
                                            <td class="font-family-cairo fw-bold">
                                                {{ $movement->pro_id }}#_{{ $this->getArabicReferenceName($movement->pro_tybe) }}
                                            </td>
                                            <td class="font-family-cairo fw-bold">
                                                {{ AccHead::find($movement->detail_store)->aname ?? 'N/A' }}
                                            </td>
                                            <td class="font-family-cairo fw-bold">
                                                {{ optional(App\Models\Unit::find($movement->unit_id))->name ?? $defaultUnitName }}
                                            </td>
                                            <td class="font-family-cairo fw-bold text-danger">
                                                {{ $movement->fat_quantity ?? $movement->qty_out }}
                                            </td>
                                            <td class="font-family-cairo fw-bold">
                                                {{ number_format($movementBalances[$movement->id]['after'] ?? 0) }}
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('invoice.view', $movement->pro_id) }}" class="btn btn-soft-danger btn-sm font-family-cairo fw-bold" target="_blank">
                                                    <i class="fas fa-eye me-1"></i>{{ __('common.view') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center font-family-cairo fw-bold text-muted">
                                                {{ __('items.no_movements_for_criteria') }}
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

        <div class="mt-4 d-flex justify-content-center">
            {{ $movements->links() }}
        </div>
    @endif

    <!-- Reference Details Modal -->
    {{-- <div wire:ignore.self class="modal fade" id="referenceModal" tabindex="-1" role="dialog" aria-labelledby="referenceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-family-cairo fw-bold" id="referenceModalLabel">ØªÙØ§ØµÙŠÙ„ Ø§Ù„Ù…Ø±Ø¬Ø¹</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    @if ($selectedMovement && $selectedMovement->reference)
                        <h4 class="font-family-cairo fw-bold">{{ $this->getArabicReferenceName($selectedMovement->reference_type) }} #{{ $selectedMovement->reference_id }}</h4>
                        <table class="table font-family-cairo fw-bold">
                            @foreach ($selectedMovement->reference->toArray() as $key => $value)
                                <tr>
                                    <th class="font-family-cairo fw-bold">{{ ucfirst(str_replace('_', ' ', $key)) }}</th>
                                    <td class="font-family-cairo fw-bold">
                                        @if (is_array($value))
                                            <pre>{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <p class="font-family-cairo fw-bold">Ù„Ø§ ÙŠÙˆØ¬Ø¯ ØªÙØ§ØµÙŠÙ„.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary font-family-cairo fw-bold" data-bs-dismiss="modal" wire:click="closeModal">Ø¥ØºÙ„Ø§Ù‚</button>
                </div>
            </div>
        </div>
    </div> --}}

    @push('scripts')
        <script>
            document.addEventListener('livewire:initialized', () => {
                const modalElement = document.getElementById('referenceModal');
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);

                    @this.on('show-reference-modal', () => {
                        modal.show();
                    });

                    modalElement.addEventListener('hidden.bs.modal', () => {
                        @this.call('closeModal');
                    })
                }
            });
        </script>
    @endpush
</div>

