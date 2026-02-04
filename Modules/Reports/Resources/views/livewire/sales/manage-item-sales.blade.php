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
        $this->warehouses = AccHead::where('code', 'like', '1104%')->where('is_basic', 0)->where('is_stock', 1)->orderBy('id')->pluck('aname', 'id');
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
        } else {
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
            '10' => __('Sales Invoice'),
            // '11' => 'ÙØ§ØªÙˆØ±Ø© Ù…Ø´ØªØ±ÙŠØ§Øª',
            '12' => __('Sales Return'),
            // '13' => 'Ù…Ø±Ø¯ÙˆØ¯ Ù…Ø´ØªØ±ÙŠØ§Øª',
            // '14' => 'Ø§Ù…Ø± Ø¨ÙŠØ¹',
            // '15' => 'Ø§Ù…Ø± Ø´Ø±Ø§Ø¡',
            // '16' => 'Ø¹Ø±Ø¶ Ø³Ø¹Ø± Ù„Ø¹Ù…ÙŠÙ„',
            // '17' => 'Ø¹Ø±Ø¶ Ø³Ø¹Ø± Ù…Ù† Ù…ÙˆØ±Ø¯',
            // '18' => 'ÙØ§ØªÙˆØ±Ø© ØªÙˆØ§Ù„Ù',
            // '19' => 'Ø§Ù…Ø± ØµØ±Ù',
            // '20' => 'Ø§Ù…Ø± Ø§Ø¶Ø§ÙØ©',
            // '21' => 'ØªØ­ÙˆÙŠÙ„ Ù…Ù† Ù…Ø®Ø²Ù† Ù„Ù…Ø®Ø²Ù†',
            // '22' => 'Ø§Ù…Ø± Ø­Ø¬Ø²',
            // '23' => 'ØªØ­ÙˆÙŠÙ„ Ø¨ÙŠÙ† ÙØ±ÙˆØ¹',
            // '35' => 'Ø³Ù†Ø¯ Ø¥ØªÙ„Ø§Ù Ù…Ø®Ø²ÙˆÙ†',
            // '56' => 'Ù†Ù…ÙˆØ°Ø¬ ØªØµÙ†ÙŠØ¹',
            // '57' => 'Ø§Ù…Ø± ØªØ´ØºÙŠÙ„',
            // '58' => 'ØªØµÙ†ÙŠØ¹ Ù…Ø¹ÙŠØ§Ø±ÙŠ',
            // '59' => 'ØªØµÙ†ÙŠØ¹ Ø­Ø±',
            // '60' => 'ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø§Ø±ØµØ¯Ù‡ Ø§Ù„Ø§ÙØªØªØ§Ø­ÙŠÙ‡ Ù„Ù„Ù…Ø®Ø§Ø²Ù†',
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
            ->whereIn('pro_tybe', [10, 12])
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
                <h4 class="page-title font-bold fw-bold">{{ __('Item Sales Report') }}</h4>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="font-bold fw-bold">{{ __('Item Sales Report') }}</h4>
            @if ($itemId)
                <div class="d-flex align-items-center">
                    <span class="font-bold fw-bold me-2">{{ __('Current Balance') }} {{ $itemName }}:</span>
                    <span class="badge bg-primary fs-6 px-3 py-2">
                        {{ number_format($totalQuantity ?? 0, 3) }}
                        {{ \App\Models\Item::find($itemId)?->units->first()->name ?? __('Unit') }}
                    </span>
                </div>
            @endif
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="item" class="form-label font-bold fw-bold">{{ __('Item') }}</label>
                    <div class="dropdown position-relative" x-data="{ open: false }" @click.outside="open = false">
                        <input type="text" class="form-control font-bold"
                            placeholder="{{ __('Search for item...') }}" wire:model.live.debounce.300ms="searchTerm"
                            wire:keydown.arrow-down.prevent="arrowDown()" wire:keydown.arrow-up.prevent="arrowUp()"
                            wire:keydown.enter.prevent="selectHighlightedItem()" x-ref="searchInput">

                        @if ($showDropdown)
                            @if ($searchResults->isNotEmpty())
                                <ul class="dropdown-menu show position-absolute w-100 border shadow"
                                    style="z-index: 1050; top: 100%; left: 0;">
                                    @foreach ($searchResults as $index => $item)
                                        <li>
                                            <a class="dropdown-item font-bold {{ $highlightedIndex === $index ? 'bg-primary text-white' : '' }}"
                                                href="#"
                                                wire:click.prevent="selectItem({{ $item->id }}, '{{ addslashes($item->name) }}')"
                                                wire:keydown.enter.prevent="selectItem({{ $item->id }}, '{{ addslashes($item->name) }}')">
                                                {{ $item->code ?? '' }} - {{ $item->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @elseif(strlen($searchTerm) >= 2 && $searchTerm !== $itemName)
                                <ul class="dropdown-menu show position-absolute w-100 border shadow"
                                    style="z-index: 1050; top: 100%; left: 0;">
                                    <li>
                                        <span class="dropdown-item-text text-danger fw-bold p-3 text-center w-100">
                                            <i class="fas fa-search me-2"></i>{{ __('No results found') }}
                                        </span>
                                    </li>
                                </ul>
                            @endif
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="warehouse" class="form-label font-bold fw-bold">{{ __('Warehouse') }}</label>
                    <select wire:model.live="warehouseId" id="warehouse" class="form-select font-bold"
                        style="height: 50px;">
                        <option value="all" class="font-bold">{{ __('All Warehouses') }}</option>
                        @foreach ($warehouses as $id => $name)
                            <option value="{{ $id }}" class="font-bold">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="fromDate" class="form-label font-bold fw-bold">{{ __('From Date') }}</label>
                    <input type="date" wire:model.live="fromDate" id="fromDate" class="form-control font-bold">
                </div>
                <div class="col-md-2">
                    <label for="toDate" class="form-label font-bold fw-bold">{{ __('To Date') }}</label>
                    <input type="date" wire:model.live="toDate" id="toDate" class="form-control font-bold">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-outline-primary h-100" wire:click="generateReport"
                        title="{{ __('Generate Report') }}">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if ($itemId)
        <div class="card">
            <div class="card-header bg-gradient-primary text-white">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-box me-2"></i>{{ __('Movement History') }} - {{ $itemName }}
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center">{{ __('Date') }}</th>
                                <th class="text-center">{{ __('Operation Source') }}</th>
                                <th class="text-center">{{ __('Movement Type') }}</th>
                                <th>{{ __('Warehouse') }}</th>
                                <th>{{ __('Unit') }}</th>
                                <th class="text-end">{{ __('Balance Before') }}</th>
                                <th class="text-end">{{ __('Quantity') }}</th>
                                <th class="text-end">{{ __('Balance After') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $runningBalance =
                                    $this->warehouseId === 'all' || empty($this->warehouseId)
                                        ? OperationItems::where('item_id', $this->itemId)
                                            ->where('created_at', '<', $this->fromDate ?? now())
                                            ->sum(DB::raw('qty_in - qty_out'))
                                        : OperationItems::where('item_id', $this->itemId)
                                            ->where('detail_store', $this->warehouseId)
                                            ->where('created_at', '<', $this->fromDate ?? now())
                                            ->sum(DB::raw('qty_in - qty_out'));
                            @endphp
                            @forelse($movements as $movement)
                                @php
                                    $quantity = $movement->qty_in ?: -$movement->qty_out;
                                    $isInbound = $movement->qty_in > 0;
                                    $runningBalance += $quantity;
                                @endphp
                                <tr class="{{ $isInbound ? 'table-success' : 'table-danger' }}">
                                    <td class="text-center fw-bold">
                                        {{ \Carbon\Carbon::parse($movement->created_at)->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="fw-bold">
                                        <span class="badge bg-info">
                                            {{ $movement->pro_id }}#{{ $this->getArabicReferenceName($movement->pro_tybe) ?? __('Unknown') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $isInbound ? 'bg-success' : 'bg-danger' }} fs-6">
                                            {{ $isInbound ? __('Inbound') : __('Outbound') }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold">
                                        {{ \Modules\Accounts\Models\AccHead::find($movement->detail_store)?->aname ?? __('N/A') }}
                                    </td>
                                    <td class="fw-bold text-muted small">
                                        {{ \App\Models\Item::find($itemId)?->units->first()->name ?? __('Unit') }}
                                    </td>
                                    <td class="text-end fw-bold text-muted">
                                        {{ number_format($runningBalance - abs($quantity), 3) }}
                                    </td>
                                    <td
                                        class="text-end fw-bolder fs-6 {{ $isInbound ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($quantity, 3) }}
                                    </td>
                                    <td class="text-end fw-bold bg-light rounded px-2 py-1">
                                        {{ number_format($runningBalance, 3) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-75"></i>
                                            <h5>{{ __('No movements found for the selected criteria') }}</h5>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($movements->hasPages())
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $movements->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Reference Details Modal -->
    {{-- <div wire:ignore.self class="modal fade" id="referenceModal" tabindex="-1" role="dialog" aria-labelledby="referenceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-hold fw-bold" id="referenceModalLabel">ØªÙØ§ØµÙŠÙ„ Ø§Ù„Ù…Ø±Ø¬Ø¹</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    @if ($selectedMovement && $selectedMovement->reference)
                        <h4 class="font-hold fw-bold">{{ $this->getArabicReferenceName($selectedMovement->reference_type) }} #{{ $selectedMovement->reference_id }}</h4>
                        <table class="table font-hold fw-bold">
                            @foreach ($selectedMovement->reference->toArray() as $key => $value)
                                <tr>
                                    <th class="font-hold fw-bold">{{ ucfirst(str_replace('_', ' ', $key)) }}</th>
                                    <td class="font-hold fw-bold">
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
                        <p class="font-hold fw-bold">Ù„Ø§ ÙŠÙˆØ¬Ø¯ ØªÙØ§ØµÙŠÙ„.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary font-hold fw-bold" data-bs-dismiss="modal" wire:click="closeModal">Ø¥ØºÙ„Ø§Ù‚</button>
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
