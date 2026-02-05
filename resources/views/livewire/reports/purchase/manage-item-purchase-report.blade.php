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
        $this->warehouses = AccHead::where('is_stock', 1)->orderBy('id')->pluck('aname', 'id');
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
        $translations = [
            '10' => 'فاتورة مبيعات',
            '11' => 'فاتورة مشتريات',
            '12' => 'مردود مبيعات',
            '13' => 'مردود مشتريات',
            '14' => 'أمر بيع',
            '15' => 'أمر شراء',
            '16' => 'عرض سعر لعميل',
            '17' => 'عرض سعر من مورد',
            '18' => 'فاتورة توالف',
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

        return $translations[$referenceId] ?? 'N/A';
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
            ->whereIn('pro_tybe', [11, 13])
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
                <h4 class="page-title font-hold fw-bold">{{ __('Item Purchase Report') }}</h4>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="font-hold fw-bold">{{ __('Search Filters') }}</h4>
            @if ($itemId)
                <div class="d-flex align-items-center">
                    <span class="font-hold fw-bold me-2">
                        {{ __('Current Stock for') }} {{ $itemName }} {{ __('in Selected Warehouses') }}:
                    </span>
                    <span class="bg-soft-primary font-bold fw-bold font-16">
                        {{ number_format($this->totalQuantity) }}
                        {{ Item::find($this->itemId)->units->first()->name ?? '' }}
                    </span>
                </div>
            @endif
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="item" class="form-label font-hold fw-bold">{{ __('Item') }}</label>
                        <div class="dropdown" wire:click.outside="hideDropdown">
                            <input type="text" class="form-control font-hold fw-bold"
                                placeholder="{{ __('Search for an item...') }}"
                                wire:model.live.debounce.300ms="searchTerm" wire:keydown.arrow-down.prevent="arrowDown"
                                wire:keydown.arrow-up.prevent="arrowUp"
                                wire:keydown.enter.prevent="selectHighlightedItem" wire:focus="showResults"
                                onclick="this.select()">

                            @if ($showDropdown && $this->searchResults->isNotEmpty())
                                <ul class="dropdown-menu show" style="width: 100%;">
                                    @foreach ($this->searchResults as $index => $item)
                                        <li>
                                            <a class="font-hold fw-bold dropdown-item {{ $highlightedIndex === $index ? 'active' : '' }}"
                                                href="#"
                                                wire:click.prevent="selectItem({{ $item->id }}, '{{ $item->name }}')">
                                                {{ $item->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @elseif($showDropdown && strlen($searchTerm) >= 2 && $searchTerm !== $itemName)
                                <ul class="dropdown-menu show" style="width: 100%;">
                                    <li>
                                        <span class="dropdown-item-text font-hold fw-bold text-danger">
                                            {{ __('No results found for this search') }}
                                        </span>
                                    </li>
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="warehouse" class="form-label font-hold fw-bold">{{ __('Warehouse') }}</label>
                        <select wire:model.live="warehouseId" id="warehouse" class="form-select font-hold fw-bold"
                            style="height: 50px;">
                            <option class="font-hold fw-bold" value="all">{{ __('All Warehouses') }}</option>
                            @foreach ($warehouses as $id => $name)
                                <option class="font-bold fw-bold" value="{{ $id }}">{{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label for="fromDate" class="form-label font-hold fw-bold">{{ __('From Date') }}</label>
                        <input type="date" wire:model.live="fromDate" id="fromDate"
                            class="form-control font-hold fw-bold">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label for="toDate" class="form-label font-hold fw-bold">{{ __('To Date') }}</label>
                        <input type="date" wire:model.live="toDate" id="toDate"
                            class="form-control font-hold fw-bold">
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($itemId)
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-centered mb-0">
                        <thead>
                            <tr>
                                <th class="font-hold fw-bold">{{ __('Date') }}</th>
                                <th class="font-hold fw-bold">{{ __('Operation Source') }}</th>
                                <th class="font-hold fw-bold">{{ __('Movement Type') }}</th>
                                <th class="font-hold fw-bold">{{ __('Warehouse') }}</th>
                                <th class="font-hold fw-bold">{{ __('Unit') }}</th>
                                <th class="font-hold fw-bold">{{ __('Balance Before') }}</th>
                                <th class="font-hold fw-bold">{{ __('Quantity') }}</th>
                                <th class="font-hold fw-bold">{{ __('Balance After') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Calculate opening balance before the selected period
                                $balanceQuery = OperationItems::where('item_id', $this->itemId)->where(
                                    'created_at',
                                    '<',
                                    $this->fromDate,
                                );

                                if ($this->warehouseId !== 'all') {
                                    $balanceQuery->where('detail_store', $this->warehouseId);
                                }

                                $balanceBefore = $balanceQuery->sum('qty_in') - $balanceQuery->sum('qty_out');
                                $balanceAfter = 0;
                            @endphp

                            @forelse($movements as $movement)
                                <tr>
                                    <td class="font-hold fw-bold">{{ $movement->created_at->format('Y-m-d') }}</td>
                                    <td class="font-hold fw-bold">
                                        {{-- Note: ensure getArabicReferenceName returns English if needed, or translate its output --}}
                                        {{ $movement->pro_id }}#_{{ $this->getArabicReferenceName($movement->pro_tybe) }}
                                    </td>
                                    <td class="font-hold fw-bold">
                                        <span
                                            class="badge {{ $movement->qty_in != 0 ? 'badge-soft-success' : 'badge-soft-danger' }} font-hold fw-bold">
                                            {{ $movement->qty_in != 0 ? __('In') : __('Out') }}
                                        </span>
                                    </td>
                                    <td class="font-hold fw-bold">
                                        {{ AccHead::find($movement->detail_store)->aname ?? 'N/A' }}
                                    </td>
                                    <td class="font-hold fw-bold">
                                        {{ Item::find($this->itemId)->units->first()->name ?? '' }}
                                    </td>
                                    <td class="font-hold fw-bold">{{ number_format($balanceBefore, 2) }}</td>
                                    <td
                                        class="font-hold fw-bold {{ $movement->qty_in != 0 ? 'bg-soft-success' : 'bg-soft-danger' }}">
                                        {{ number_format($movement->qty_in != 0 ? $movement->qty_in : $movement->qty_out, 2) }}
                                    </td>
                                    @php
                                        if ($movement->qty_in != 0) {
                                            $balanceAfter = $balanceBefore + $movement->qty_in;
                                        } elseif ($movement->qty_out != 0) {
                                            $balanceAfter = $balanceBefore - $movement->qty_out;
                                        }
                                        $displayBalanceAfter = $balanceAfter;
                                        $balanceBefore = $balanceAfter; // Update for next iteration
                                    @endphp
                                    <td class="font-hold fw-bold">{{ number_format($displayBalanceAfter, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center font-hold fw-bold">
                                        {{ __('No movements found for the selected criteria.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex justify-content-center">
                    {{ $movements->links() }}
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            // Script content if needed
        </script>
    @endpush
</div>
