<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Models\AccHead;
use App\Models\OperHead;
use App\Models\JournalDetail;
use App\Enums\OperationTypeEnum;

new class extends Component {
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $accountId = null;
    public ?string $fromDate = null;
    public ?string $toDate = null;
    public string $accountName = '';
    public string $searchTerm = '';
    public int $highlightedIndex = -1;
    public bool $showDropdown = false;

    public Collection $warehouses;

    public function mount($accountId = null): void
    {
        $this->warehouses = AccHead::where('is_stock', 1)->orderBy('id')->pluck('aname', 'id');
        $this->fromDate = now()->startOfMonth()->toDateString();
        $this->toDate = now()->endOfMonth()->toDateString();
        if ($accountId) {
            $this->accountId = $accountId;
            $account = AccHead::find($accountId);
            if ($account) {
                $this->accountName = $account->aname;
                $this->searchTerm = $account->aname;
            }
        }
    }

    public function updatedSearchTerm(): void
    {
        $this->highlightedIndex = -1;
        $this->showDropdown = true;
        if (empty($this->searchTerm)) {
            $this->accountId = null;
            $this->accountName = '';
        }
    }

    public function getSearchResultsProperty()
    {
        if (strlen($this->searchTerm) < 2 || $this->searchTerm === $this->accountName) {
            return collect();
        }

        return AccHead::where('is_basic', 0)
            ->where('aname', 'like', '%' . $this->searchTerm . '%')
            ->select('id', 'aname')
            ->limit(7)
            ->get();
    }

    public function selectAccount(int $id, string $name): void
    {
        $this->accountId = $id;
        $this->accountName = $name;
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
            $account = $results[$this->highlightedIndex];
            $this->selectAccount($account->id, $account->aname);
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
        $operationType = OperationTypeEnum::fromValue($referenceId);
        return $operationType?->getArabicName() ?? 'عملية غير محددة';
    }

    public function with(): array
    {
        return [
            'movements' => $this->getMovements(),
        ];
    }

    public function getMovements()
    {
        if (!$this->accountId) {
            return collect();
        }

        return JournalDetail::where('account_id', $this->accountId)
            ->when($this->fromDate, function ($q) {
                $q->whereDate('crtime', '>=', $this->fromDate);
            })
            ->when($this->toDate, function ($q) {
                $q->whereDate('crtime', '<=', $this->toDate);
            })
            ->orderBy('crtime', 'asc')
            ->paginate(100);
    }

    public function updated($property): void
    {
        if (in_array($property, ['accountId', 'fromDate', 'toDate'])) {
            $this->resetPage();
        }
    }

    public function getRunningBalanceProperty()
    {
        if (!$this->accountId) {
            return 0;
        }

        $query = DB::table('acc_head')->where('id', $this->accountId)->first();

        return $query->balance;
    }
};
?>

<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title font-hold fw-bold">{{ __('Account Movement Report') }}</h4>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="font-hold fw-bold">{{ __('Account Movement Report') }}</h4>
            @if ($accountId)
                <div class="d-flex align-items-center">
                    <span class="font-hold fw-bold me-2">
                        {{ __('Current Balance for Account') }} {{ $accountName }}:
                    </span>
                    <span
                        class="font-hold fw-bold font-16 @if ($this->runningBalance < 0) bg-soft-danger @else bg-soft-primary @endif">
                        {{ number_format($this->runningBalance, 2) }}
                    </span>
                </div>
            @endif
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="account" class="form-label font-hold fw-bold">{{ __('Account') }}</label>
                        <div class="dropdown" wire:click.outside="hideDropdown">
                            <input type="text" class="form-control font-hold fw-bold"
                                placeholder="{{ __('Search for an account...') }}"
                                wire:model.live.debounce.300ms="searchTerm" wire:keydown.arrow-down.prevent="arrowDown"
                                wire:keydown.arrow-up.prevent="arrowUp"
                                wire:keydown.enter.prevent="selectHighlightedItem" wire:focus="showResults"
                                onclick="this.select()">
                            @if ($showDropdown && $this->searchResults->isNotEmpty())
                                <ul class="dropdown-menu show" style="width: 100%;">
                                    @foreach ($this->searchResults as $index => $account)
                                        <li>
                                            <a class="font-hold fw-bold dropdown-item {{ $highlightedIndex === $index ? 'active' : '' }}"
                                                href="#"
                                                wire:click.prevent="selectAccount({{ $account->id }}, '{{ $account->aname }}')">
                                                {{ $account->aname }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @elseif($showDropdown && strlen($searchTerm) >= 2 && $searchTerm !== $accountName)
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

    @if ($accountId)
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-centered mb-0">
                        <thead>
                            <tr>
                                <th class="font-hold fw-bold">{{ __('Date') }}</th>
                                <th class="font-hold fw-bold">{{ __('Operation Source') }}</th>
                                <th class="font-hold fw-bold">{{ __('Movement Type') }}</th>
                                <th class="font-hold fw-bold">{{ __('Balance Before') }}</th>
                                <th class="font-hold fw-bold">{{ __('Debit') }}</th>
                                <th class="font-hold fw-bold">{{ __('Credit') }}</th>
                                <th class="font-hold fw-bold">{{ __('Balance After') }}</th>
                                <th class="font-hold fw-bold">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $balanceBefore =
                                    JournalDetail::where('account_id', $this->accountId)
                                        ->where('crtime', '<', $this->fromDate)
                                        ->sum('debit') -
                                    JournalDetail::where('account_id', $this->accountId)
                                        ->where('crtime', '<', $this->fromDate)
                                        ->sum('credit');
                                $balanceAfter = 0;
                            @endphp
                            @forelse($movements as $movement)
                                <tr>
                                    <td class="font-hold fw-bold">{{ $movement->crtime->format('Y-m-d') }}</td>
                                    <td class="font-hold fw-bold">
                                        {{ $movement->op_id }}#_{{ $this->getArabicReferenceName(OperHead::find($movement->op_id)->pro_type) }}
                                    </td>
                                    <td class="font-hold fw-bold">
                                        @if ($movement->debit > 0)
                                            <span class="badge bg-primary">{{ __('Debit') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ __('Credit') }}</span>
                                        @endif
                                    </td>
                                    <td class="font-hold fw-bold">{{ number_format($balanceBefore, 2) }}</td>
                                    <td class="font-hold fw-bold">
                                        @if ($movement->debit > 0)
                                            <span
                                                class="badge bg-primary">{{ number_format($movement->debit, 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="font-hold fw-bold">
                                        @if ($movement->credit > 0)
                                            <span
                                                class="badge bg-danger">{{ number_format($movement->credit, 2) }}</span>
                                        @endif
                                    </td>
                                    @php
                                        $balanceAfter = $balanceBefore + $movement->debit - $movement->credit;
                                    @endphp
                                    <td
                                        class="font-hold fw-bold {{ $balanceAfter < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($balanceAfter, 2) }}
                                    </td>
                                    <td class="font-hold fw-bold">
                                        @php
                                            $operation = OperHead::find($movement->op_id);
                                        @endphp
                                        @if ($operation && in_array($operation->pro_type, [10, 11, 12, 13]))
                                            <a href="{{ route('invoice.view', $movement->op_id) }}"
                                                class="btn btn-sm btn-info" target="_blank"
                                                title="{{ __('View Invoice') }}">
                                                <i class="fas fa-eye"></i> {{ __('View') }}
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @php
                                    $balanceBefore = $balanceAfter;
                                @endphp
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
            document.addEventListener('livewire:initialized', () => {
                const modalElement = document.getElementById('referenceModal');
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);

                    @this.on('show-reference-modal', () => {
                        modal.show();
                    });

                    modalElement.addEventListener('hidden.bs.modal', () => {
                        @this.call('closeModal');
                    });
                }
            });
        </script>
    @endpush
</div>
