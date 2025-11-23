<?php

use Livewire\Volt\Component;
use Modules\Accounts\Models\AccHead;
use App\Models\JournalDetail;
use App\Models\JournalHead;
use App\Models\OperHead;
use App\Models\ProType;

use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $fromDate = '';
    public $toDate = '';
    public $accountId = '';
    public $operationType = '';

    public $search = '';
    public $perPage = 50;

    public function mount()
    {
        $this->fromDate = now()->startOfMonth()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
    }

    public function with(): array
    {
        $query = JournalDetail::with(['head', 'accHead', 'costCenter', 'operHead'])
            ->when($this->fromDate, function ($q) {
                $q->whereDate('crtime', '>=', $this->fromDate);
            })
            ->when($this->toDate, function ($q) {
                $q->whereDate('crtime', '<=', $this->toDate);
            })
            ->when($this->accountId, function ($q) {
                $q->where('account_id', $this->accountId);
            })
            ->when($this->operationType, function ($q) {
                $q->whereHas('head', function ($subQ) {
                    $subQ->where('pro_type', $this->operationType);
                });
            })
            // Cost center filtering removed as cost_center_id column doesn't exist in journal_details table
            ->when($this->search, function ($q) {
                $q->where(function ($subQ) {
                    $subQ->whereHas('accountHead', function ($accQ) {
                        $accQ->where('aname', 'like', '%' . $this->search . '%')
                              ->orWhere('code', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('head', function ($headQ) {
                        $headQ->where('info', 'like', '%' . $this->search . '%');
                    });
                });
            })
            ->orderBy('crtime', 'desc');

        $journalDetails = $query->paginate($this->perPage);

        // Calculate totals
        $totalDebit = $query->sum('debit');
        $totalCredit = $query->sum('credit');
        $totalBalance = $totalDebit - $totalCredit;

        return [
            'journalDetails' => $journalDetails,
            'accounts' => AccHead::where('isdeleted', 0)->where('is_basic', 0)->orderBy('code')->get(),

            'operationTypes' => ProType::select('id', 'ptext')
                ->whereIn('id', OperHead::select('pro_type')
                    ->whereNotNull('pro_type')
                    ->distinct()
                    ->pluck('pro_type')
                )
                ->where('isdeleted', 0)
                ->orderBy('ptext', 'asc')
                ->get(),
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'totalBalance' => $totalBalance,
        ];
    }

    public function resetFilters()
    {
        $this->reset(['fromDate', 'toDate', 'accountId', 'operationType', 'search']);
        $this->fromDate = now()->startOfMonth()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFromDate()
    {
        $this->resetPage();
    }

    public function updatedToDate()
    {
        $this->resetPage();
    }

    public function updatedAccountId()
    {
        $this->resetPage();
    }

    public function updatedOperationType()
    {
        $this->resetPage();
    }



    public function updatedPerPage()
    {
        $this->resetPage();
    }
}; ?>

<div style="font-family: 'Cairo', sans-serif; direction: rtl;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title font-family-cairo fw-bold">{{ __('reports.general_account_statement_details') }}</h3>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <label for="from_date" class="form-label font-family-cairo fw-bold">{{ __('reports.from_date') }}:</label>
                                <input type="date" id="from_date" class="form-control" wire:model.live="fromDate">
                            </div>
                            <div class="col-md-2">
                                <label for="to_date" class="form-label font-family-cairo fw-bold">{{ __('reports.to_date') }}:</label>
                                <input type="date" id="to_date" class="form-control" wire:model.live="toDate">
                            </div>
                            <div class="col-md-2">
                                <label for="account_id" class="form-label font-family-cairo fw-bold">{{ __('reports.account') }}:</label>
                                <select id="account_id" class="form-control" wire:model.live="accountId">
                                    <option value="">{{ __('reports.all_accounts') }}</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->aname }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="operation_type" class="form-label font-family-cairo fw-bold">{{ __('reports.operation_type') }}:</label>
                                <select id="operation_type" class="form-control" wire:model.live="operationType">
                                    <option value="">{{ __('reports.all_operations') }}</option>
                                    @foreach($operationTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->ptext }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="search" class="form-label font-family-cairo fw-bold">{{ __('reports.searching') }}:</label>
                                <input type="text" id="search" class="form-control" wire:model.live.debounce.300ms="search" placeholder="{{ __('reports.search_in_account_or_description') }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-2">
                                <label for="per_page" class="form-label font-family-cairo fw-bold">{{ __('reports.results_per_page') }}:</label>
                                <select id="per_page" class="form-control" wire:model.live="perPage">
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="200">200</option>
                                </select>
                            </div>
                            <div class="col-md-10 d-flex align-items-end">
                                <button class="btn btn-secondary me-2" wire:click="resetFilters">
                                    <i class="fas fa-refresh"></i> {{ __('reports.reset') }}
                                </button>
                            </div>
                        </div>

                        <!-- Summary Cards -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title font-family-cairo fw-bold">{{ __('reports.total_debit') }}</h5>
                                        <h4 class="font-family-cairo fw-bold">{{ number_format($totalDebit, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title font-family-cairo fw-bold">{{ __('reports.total_credit') }}</h5>
                                        <h4 class="font-family-cairo fw-bold">{{ number_format($totalCredit, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title font-family-cairo fw-bold">{{ __('reports.difference') }}</h5>
                                        <h4 class="font-family-cairo fw-bold">{{ number_format($totalBalance, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Results Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="">
                                    <tr class="text-center">
                                        <th class="font-family-cairo fw-bold">{{ __('reports.date') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('reports.operation_number') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('reports.operation_name') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('reports.journal_number') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('reports.account') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('reports.description') }}</th>

                                        <th class="font-family-cairo fw-bold">{{ __('reports.debit') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('reports.credit') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('reports.balance') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($journalDetails as $detail)
                                        <tr>
                                            <td class="text-center font-family-cairo">
                                                {{ $detail->crtime ? \Carbon\Carbon::parse($detail->crtime)->format('Y-m-d') : '---' }}
                                            </td>
                                            <td class="text-center font-family-cairo fw-bold">
                                                {{ $detail->op_id ?? '---' }}
                                            </td>
                                            <td class="text-center font-family-cairo fw-bold">
                                                @if($detail->operHead && $detail->op_id)
                                                    @php
                                                        $operationType = $detail->operHead->type->ptext ?? '---';
                                                        $editRoute = $detail->operHead->getEditRoute();
                                                    @endphp

                                                    @if(\Illuminate\Support\Facades\Route::has($editRoute))
                                                        <a href="{{ route($editRoute, $detail->op_id) }}"
                                                           class="text-decoration-underline text-primary"
                                                           title="{{ __('reports.edit_operation') }}">
                                                           {{ $operationType }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted" title="{{ __('reports.cannot_edit_operation_type') }}">
                                                            {{ $operationType }}
                                                        </span>
                                                    @endif
                                                @else
                                                    {{ $detail->operHead->pname ?? '---' }}
                                                @endif
                                            </td>

                                            <td class="text-center font-family-cairo fw-bold">
                                                {{ $detail->journal_id ?? '---' }}
                                            </td>
                                            <td class="font-family-cairo">
                                                <strong>{{ $detail->accHead->code ?? '---' }}</strong>
                                                <br>
                                                <small>{{ $detail->accHead->aname ?? '---' }}</small>
                                            </td>
                                            <td class="font-family-cairo">
                                                {{ $detail->info ?? '---' }}
                                            </td>

                                            <td class="text-end font-family-cairo fw-bold @if($detail->debit > 0) text-primary @endif">
                                                {{ $detail->debit > 0 ? number_format($detail->debit, 2) : '---' }}
                                            </td>
                                            <td class="text-end font-family-cairo fw-bold @if($detail->credit > 0) text-success @endif">
                                                {{ $detail->credit > 0 ? number_format($detail->credit, 2) : '---' }}
                                            </td>
                                            <td class="text-end font-family-cairo fw-bold @if(($detail->debit - $detail->credit) != 0) text-info @endif">
                                                {{ number_format($detail->debit - $detail->credit, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center font-family-cairo fw-bold">
                                                {{ __('reports.no_data_to_display') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($journalDetails->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $journalDetails->links() }}
                            </div>
                        @endif

                        <!-- Summary Footer -->
                        @if($journalDetails->count() > 0)
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <strong class="font-family-cairo fw-bold">{{ __('reports.report_summary') }}:</strong>
                                        <br>
                                        <span class="font-family-cairo">{{ __('reports.operations_count') }}: {{ $journalDetails->total() }}</span>
                                        <br>
                                        <span class="font-family-cairo">{{ __('reports.total_debit') }}: {{ number_format($totalDebit, 2) }}</span>
                                        <br>
                                        <span class="font-family-cairo">{{ __('reports.total_credit') }}: {{ number_format($totalCredit, 2) }}</span>
                                        <br>
                                        <span class="font-family-cairo">{{ __('reports.difference') }}: {{ number_format($totalBalance, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
