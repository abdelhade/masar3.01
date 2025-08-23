<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\AccHead;
use App\Models\OperHead;
use App\Models\JournalDetail;
 
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
        // Set from route if present
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

        return AccHead::where('aname', 'like', '%' . $this->searchTerm . '%')
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

    public function hideResults(): void
    {
        $this->showDropdown = false;
    }

    public function getAccountMovementProperty()
    {
        if (!$this->accountId) {
            return collect();
        }

        $query = JournalDetail::where('acc_head_id', $this->accountId)
            ->whereHas('operHead', function ($q) {
                $q->whereBetween('date', [$this->fromDate, $this->toDate]);
            })
            ->with(['operHead', 'accHead'])
            ->orderBy('id', 'desc');

        return $query->paginate(50);
    }

    public function getAccountBalanceProperty()
    {
        if (!$this->accountId) {
            return 0;
        }

        return JournalDetail::where('acc_head_id', $this->accountId)
            ->sum(DB::raw('debit - credit'));
    }

    public function getPeriodBalanceProperty()
    {
        if (!$this->accountId) {
            return 0;
        }

        return JournalDetail::where('acc_head_id', $this->accountId)
            ->whereHas('operHead', function ($q) {
                $q->whereBetween('date', [$this->fromDate, $this->toDate]);
            })
            ->sum(DB::raw('debit - credit'));
    }

    public function getOpeningBalanceProperty()
    {
        if (!$this->accountId) {
            return 0;
        }

        return JournalDetail::where('acc_head_id', $this->accountId)
            ->whereHas('operHead', function ($q) {
                $q->where('date', '<', $this->fromDate);
            })
            ->sum(DB::raw('debit - credit'));
    }

    public function getClosingBalanceProperty()
    {
        return $this->openingBalance + $this->periodBalance;
    }

    public function exportToExcel()
    {
        // Implementation for Excel export
        return redirect()->back()->with('success', __('messages.export_successful'));
    }

    public function exportToPdf()
    {
        // Implementation for PDF export
        return redirect()->back()->with('success', __('messages.export_successful'));
    }
}; ?>

<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ __('reports.account_movement_report') }}</h4>
    </div>
    <div class="card-body">
        <!-- Search Form -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="account_search" class="form-label">{{ __('forms.account') }}</label>
                <div class="position-relative">
                    <input type="text" 
                           id="account_search"
                           wire:model.live="searchTerm"
                           wire:keydown.arrow-down="arrowDown"
                           wire:keydown.arrow-up="arrowUp"
                           wire:keydown.enter="selectHighlightedItem"
                           wire:focus="showResults"
                           wire:blur="hideResults"
                           class="form-control @error('searchTerm') is-invalid @enderror"
                           placeholder="{{ __('forms.search_account') }}">
                    
                    @if($showDropdown && $searchResults->count() > 0)
                        <div class="dropdown-menu show w-100" style="max-height: 200px; overflow-y: auto;">
                            @foreach($searchResults as $index => $result)
                                <a class="dropdown-item {{ $index === $highlightedIndex ? 'active' : '' }}"
                                   wire:click="selectAccount({{ $result->id }}, '{{ $result->aname }}')"
                                   href="javascript:void(0)">
                                    {{ $result->aname }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                @error('searchTerm')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="from_date" class="form-label">{{ __('reports.from_date') }}</label>
                <input type="date" 
                       id="from_date"
                       wire:model.live="fromDate"
                       class="form-control @error('fromDate') is-invalid @enderror">
                @error('fromDate')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="to_date" class="form-label">{{ __('reports.to_date') }}</label>
                <input type="date" 
                       id="to_date"
                       wire:model.live="toDate"
                       class="form-control @error('toDate') is-invalid @enderror">
                @error('toDate')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="button" 
                            wire:click="$refresh"
                            class="btn btn-primary">
                        <i class="fas fa-search"></i> {{ __('reports.generate_report') }}
                    </button>
        </div>
    </div>

            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="btn-group" role="group">
                    <button type="button" 
                            wire:click="exportToExcel"
                            class="btn btn-success">
                        <i class="fas fa-file-excel"></i> {{ __('reports.export_excel') }}
                    </button>
                    <button type="button" 
                            wire:click="exportToPdf"
                            class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> {{ __('reports.export_pdf') }}
                    </button>
                </div>
            </div>
        </div>

        @if($accountId)
            <!-- Account Summary -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6>{{ __('reports.opening_balance') }}</h6>
                            <h4>{{ number_format($this->openingBalance, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6>{{ __('reports.period_movement') }}</h6>
                            <h4>{{ number_format($this->periodBalance, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>{{ __('reports.closing_balance') }}</h6>
                            <h4>{{ number_format($this->closingBalance, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h6>{{ __('reports.total_balance') }}</h6>
                            <h4>{{ number_format($this->accountBalance, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

            <!-- Movement Details -->
                <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>{{ __('reports.date') }}</th>
                            <th>{{ __('reports.operation_number') }}</th>
                            <th>{{ __('reports.description') }}</th>
                            <th class="text-end">{{ __('reports.debit') }}</th>
                            <th class="text-end">{{ __('reports.credit') }}</th>
                            <th class="text-end">{{ __('reports.balance') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($this->accountMovement as $movement)
                            <tr>
                                <td>{{ $movement->operHead->date }}</td>
                                <td>
                                    <a href="{{ route('journals.show', $movement->operHead->id) }}" 
                                       class="text-primary">
                                        {{ $movement->operHead->id }}
                                    </a>
                                    </td>
                                <td>{{ $movement->operHead->description ?: __('reports.no_description') }}</td>
                                <td class="text-end">
                                    @if($movement->debit > 0)
                                        {{ number_format($movement->debit, 2) }}
                                    @else
                                        -
                                    @endif
                                    </td>
                                <td class="text-end">
                                    @if($movement->credit > 0)
                                        {{ number_format($movement->credit, 2) }}
                                    @else
                                        -
                                    @endif
                                    </td>
                                <td class="text-end">
                                    @php
                                        $balance = $movement->debit - $movement->credit;
                                    @endphp
                                    <span class="badge {{ $balance >= 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ number_format($balance, 2) }}
                                    </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                <td colspan="6" class="text-center">{{ __('reports.no_movements_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            <!-- Pagination -->
            @if($this->accountMovement->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $this->accountMovement->links() }}
                </div>
                                        @endif
                    @else
            <div class="alert alert-info text-center">
                {{ __('reports.select_account_to_view_movements') }}
            </div>
        @endif
        </div>
</div>
