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
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="page-title font-family-cairo fw-bold mb-0">
                    @if ($itemId)
                        {{ __('items.item_movement_report') }} - {{ $itemName }}
                    @else
                        {{ __('items.item_movement_report') }}
                    @endif
                </h4>
                @if ($itemId)
                    <a href="{{ route('item-movement.print', [
                        'itemId' => $itemId,
                        'warehouseId' => $warehouseId,
                        'fromDate' => $fromDate,
                        'toDate' => $toDate,
                    ]) }}"
                        target="_blank" class="btn btn-outline-primary font-family-cairo fw-bold">
                        <i class="fas fa-print"></i>
                        {{ __('items.print_report') }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if ($itemId)
        @php
            $movementsCollection =
                $movements instanceof \Illuminate\Contracts\Pagination\Paginator
                    ? collect($movements->items())
                    : collect($movements);

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
            
            // حساب متوسط سعر الشراء لكل حركة بشكل محسّن
            $sortedMovements = $movementsCollection->sortBy('created_at');
            $averageCostsCache = [];
            
            // حساب التكلفة الإجمالية والكمية قبل بدء الحركات المحددة
            $baseQuery = OperationItems::where('item_id', $this->itemId)
                ->where('isdeleted', 0)
                ->where('qty_in', '>', 0);
            
            if ($this->warehouseId !== 'all' && !empty($this->warehouseId)) {
                $baseQuery->where('detail_store', $this->warehouseId);
            }
            
            $purchasesBeforePeriod = $baseQuery->where('created_at', '<', $this->fromDate)->get();
            $totalCost = $purchasesBeforePeriod->sum(function($item) {
                // استخدام item_price إذا كان cost_price = 0 (للمشتريات)
                $purchasePrice = ($item->cost_price ?? 0) > 0 ? ($item->cost_price ?? 0) : ($item->item_price ?? 0);
                return $purchasePrice * ($item->qty_in ?? 0);
            });
            $totalQuantity = $purchasesBeforePeriod->sum('qty_in');
            
            // حساب متوسط التكلفة التراكمي لكل حركة (قبل معالجة الحركة)
            foreach ($sortedMovements as $entry) {
                // حفظ المتوسط الحالي قبل معالجة هذه الحركة
                $fallbackPrice = ($entry->cost_price ?? 0) > 0 ? ($entry->cost_price ?? 0) : ($entry->item_price ?? 0);
                $calculatedAverage = $totalQuantity > 0 ? ($totalCost / $totalQuantity) : $fallbackPrice;
                $averageCostsCache[$entry->id] = (float)$calculatedAverage;
                
                // تحديث التكلفة والكمية بعد معالجة الحركة (للحركة القادمة)
                if ($entry->qty_in > 0) {
                    // استخدام item_price إذا كان cost_price = 0 (للمشتريات)
                    $purchasePrice = ($entry->cost_price ?? 0) > 0 ? ($entry->cost_price ?? 0) : ($entry->item_price ?? 0);
                    if ($purchasePrice > 0) {
                        $totalCost += (float)$purchasePrice * (float)$entry->qty_in;
                        $totalQuantity += (float)$entry->qty_in;
                    }
                }
                
                $movementBalances[$entry->id]['before'] = $runningBalance;
                if ($entry->qty_in > 0) {
                    $runningBalance += $entry->qty_in;
                } elseif ($entry->qty_out > 0) {
                    $runningBalance -= $entry->qty_out;
                }
                $movementBalances[$entry->id]['after'] = $runningBalance;
            }
        @endphp

        {{-- ✅ جدول الحركات --}}
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0 font-family-cairo fw-bold">تفاصيل الحركات</h5>
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
                                <th class="font-family-cairo fw-bold">سعر الفاتورة</th>
                                <th class="font-family-cairo fw-bold">سعر الشراء المتوسط</th>
                                <th class="font-family-cairo fw-bold">الربح</th>
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
                                    
                                    // سعر الفاتورة
                                    $invoicePrice = (float)($movement->item_price ?? 0);
                                    
                                    // سعر الشراء المتوسط: للحركات الواردة (مشتريات) استخدم item_price أو cost_price، للمنصرفة (مبيعات) استخدم المتوسط
                                    if ($movement->qty_in > 0) {
                                        // حركة شراء: استخدم item_price إذا كان cost_price = 0
                                        $costPrice = (float)($movement->cost_price ?? 0);
                                        $itemPrice = (float)($movement->item_price ?? 0);
                                        $averagePurchasePrice = $costPrice > 0 ? $costPrice : $itemPrice;
                                    } else {
                                        // حركة بيع: استخدم متوسط الشراء حتى وقت هذه الحركة من الـ cache
                                        if (isset($averageCostsCache[$movement->id])) {
                                            $averagePurchasePrice = (float)$averageCostsCache[$movement->id];
                                        } else {
                                            // Fallback: استخدم cost_price أو item_price
                                            $fallbackCostPrice = (float)($movement->cost_price ?? 0);
                                            $fallbackItemPrice = (float)($movement->item_price ?? 0);
                                            $averagePurchasePrice = $fallbackCostPrice > 0 ? $fallbackCostPrice : $fallbackItemPrice;
                                        }
                                    }
                                    
                                    // الربح: فقط للحركات المنصرفة (مبيعات)
                                    $profit = 0;
                                    if ($movement->qty_out > 0 && $invoicePrice > 0 && $averagePurchasePrice > 0) {
                                        // الربح = (سعر البيع - سعر الشراء المتوسط) × الكمية
                                        $profit = ($invoicePrice - $averagePurchasePrice) * (float)$movement->qty_out;
                                    } elseif (($movement->profit ?? 0) != 0) {
                                        // إذا كان الربح محسوب مسبقاً في قاعدة البيانات
                                        $profit = (float)($movement->profit ?? 0);
                                    }
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
        {{ $movement->qty_in > 0 ? number_format($movement->qty_in, 2) : '—' }}
    </td>
    <td class="font-family-cairo fw-bold text-danger">
        {{ $movement->qty_out > 0 ? number_format($movement->qty_out, 2) : '—' }}
    </td>
    <td class="font-family-cairo fw-bold">
        {{ $invoicePrice > 0 ? number_format($invoicePrice, 2) : '—' }}
    </td>
    <td class="font-family-cairo fw-bold">
        @if($averagePurchasePrice > 0)
            {{ number_format($averagePurchasePrice, 2) }}
        @elseif($averagePurchasePrice == 0)
            @if($movement->qty_in > 0 || $movement->qty_out > 0)
                0.00
            @else
                —
            @endif
        @else
            —
        @endif
    </td>
    <td class="font-family-cairo fw-bold {{ $profit > 0 ? 'text-success' : ($profit < 0 ? 'text-danger' : '') }}">
        {{ $profit != 0 ? number_format($profit, 2) : '—' }}
    </td>
    <td class="font-family-cairo">{{ number_format($before, 2) }}</td>
    <td class="font-family-cairo">{{ number_format($after, 2) }}</td>
    <td class="text-center">
        <a href="{{ route('invoice.view', $movement->pro_id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-eye"></i>
        </a>
    </td>
</tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted font-family-cairo">
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
