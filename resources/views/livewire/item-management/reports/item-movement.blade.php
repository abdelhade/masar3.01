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
                        'toDate' => $toDate,
                    ]) }}"
                        target="_blank" class="btn btn-outline font-family-cairo fw-bold" style="text-decoration: none;">
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
                    <span
                        class="font-family-cairo fw-bold me-2">{{ __('items.current_balance_for_item', ['item' => $itemName]) }}:</span>
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
                        <label for="item"
                            class="form-label font-family-cairo fw-bold">{{ __('items.item') }}</label>
                        <div class="dropdown" wire:click.outside="hideDropdown">
                            <input type="text" class="form-control font-family-cairo fw-bold"
                                placeholder="{{ __('items.search_for_item') }}"
                                wire:model.live.debounce.300ms="searchTerm" wire:keydown.arrow-down.prevent="arrowDown"
                                wire:keydown.arrow-up.prevent="arrowUp"
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
                                    <li><span
                                            class="dropdown-item-text font-family-cairo fw-bold text-danger">{{ __('items.no_results_for_search') }}</span>
                                    </li>
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="warehouse"
                            class="form-label font-family-cairo fw-bold">{{ __('items.warehouse') }}</label>
                        <select wire:model.live="warehouseId" id="warehouse"
                            class="form-select font-family-cairo fw-bold" style = "height: 50px;">
                            <option class="font-family-cairo fw-bold" value="all">{{ __('items.all_warehouses') }}
                            </option>
                            @foreach ($warehouses as $id => $name)
                                <option class="font-family-cairo fw-bold" value="{{ $id }}">
                                    {{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label for="fromDate"
                            class="form-label font-family-cairo fw-bold">{{ __('items.from_date') }}</label>
                        <input type="date" wire:model.live="fromDate" id="fromDate"
                            class="form-control font-family-cairo fw-bold">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label for="toDate"
                            class="form-label font-family-cairo fw-bold">{{ __('items.to_date') }}</label>
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
            $movementsCollection =
                $movements instanceof \Illuminate\Contracts\Pagination\Paginator
                    ? collect($movements->items())
                    : collect($movements);
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

        {{-- ✅ كروت الإحصائيات --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <i class="bi bi-box-seam fs-1 text-success"></i>
                        <h5 class="mt-2 font-family-cairo fw-bold">إجمالي الوارد</h5>
                        <h3 class="text-success font-family-cairo fw-bold">
                            {{ number_format($incomingMovements->sum('qty_in')) }}</h3>
                        <small class="text-muted">{{ $defaultUnitName }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <i class="bi bi-box-arrow-up fs-1 text-danger"></i>
                        <h5 class="mt-2 font-family-cairo fw-bold">إجمالي المنصرف</h5>
                        <h3 class="text-danger font-family-cairo fw-bold">
                            {{ number_format($outgoingMovements->sum('qty_out')) }}</h3>
                        <small class="text-muted">{{ $defaultUnitName }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <i class="bi bi-calendar fs-1 text-primary"></i>
                        <h5 class="mt-2 font-family-cairo fw-bold">عدد الحركات</h5>
                        <h3 class="text-primary font-family-cairo fw-bold">{{ $movements->total() }}</h3>
                        <small class="text-muted">حركة</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <i class="bi bi-speedometer2 fs-1 text-warning"></i>
                        <h5 class="mt-2 font-family-cairo fw-bold">الرصيد الحالي</h5>
                        <h3 class="text-warning font-family-cairo fw-bold">{{ number_format($this->totalQuantity) }}
                        </h3>
                        <small class="text-muted">{{ $defaultUnitName }}</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- ✅ جدول الحركات --}}
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0 font-family-cairo fw-bold">تفاصيل الحركات</h5>
                <a href="{{ route('item-movement.print', [
                    'itemId' => $itemId,
                    'warehouseId' => $warehouseId,
                    'fromDate' => $fromDate,
                    'toDate' => $toDate,
                ]) }}"
                    target="_blank" class="btn btn-outline-primary btn-sm font-family-cairo">
                    <i class="fas fa-print"></i> طباعة
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="font-family-cairo fw-bold">التاريخ</th>
                                <th class="font-family-cairo fw-bold">نوع العملية</th>
                                <th class="font-family-cairo fw-bold">المخزن</th>
                                <th class="font-family-cairo fw-bold">الكمية الواردة</th>
                                <th class="font-family-cairo fw-bold">الكمية المنصرفة</th>
                                <th class="font-family-cairo fw-bold">الرصيد قبل</th>
                                <th class="font-family-cairo fw-bold">الرصيد بعد</th>
                                <th class="font-family-cairo fw-bold text-center">عرض</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($movements as $movement)
                                @php
                                    $before = $movementBalances[$movement->id]['before'] ?? 0;
                                    $after = $movementBalances[$movement->id]['after'] ?? 0;
                                    $isIn = $movement->qty_in > 0;
                                @endphp
<tr>
    <td class="font-family-cairo">{{ $movement->created_at->format('Y-m-d') }}</td>
    <td class="font-family-cairo">
        {{ $this->getArabicReferenceName($movement->pro_tybe) }}
        <br>
        <small class="text-muted">#{{ $movement->pro_id }}</small>
    </td>
    <td class="font-family-cairo">
        {{ optional(\Modules\Accounts\Models\AccHead::find($movement->detail_store))->aname ?? '—' }}
    </td>
    <td class="font-family-cairo fw-bold text-success">
        {{ $movement->qty_in > 0 ? number_format($movement->qty_in) : '—' }}
    </td>
    <td class="font-family-cairo fw-bold text-danger">
        {{ $movement->qty_out > 0 ? number_format($movement->qty_out) : '—' }}
    </td>
    <td class="font-family-cairo">{{ number_format($before) }}</td>
    <td class="font-family-cairo">{{ number_format($after) }}</td>
    <td class="text-center">
        <a href="{{ route('invoice.view', $movement->pro_id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-eye"></i>
        </a>
    </td>
</tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted font-family-cairo">
                                        لا توجد حركات لهذا الصنف في الفترة المحددة.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $movements->links() }}
                </div>
            </div>
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
