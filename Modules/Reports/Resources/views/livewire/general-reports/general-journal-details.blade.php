<?php

use Livewire\Volt\Component;
use Modules\\Accounts\\Models\\AccHead;
use App\Models\JournalDetail;
use App\Models\JournalHead;
use App\Models\OperHead;

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

            'operationTypes' => OperHead::select('pro_type')
                ->distinct()
                ->whereNotNull('pro_type')
                ->orderBy('pro_type')
                ->pluck('pro_type'),
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
                        <h3 class="card-title font-family-cairo fw-bold">ÙƒØ´Ù Ø­Ø³Ø§Ø¨ Ø¹Ø§Ù… - ØªÙØ§ØµÙŠÙ„ Ø§Ù„ÙŠÙˆÙ…ÙŠØ©</h3>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <label for="from_date" class="form-label font-family-cairo fw-bold">Ù…Ù† ØªØ§Ø±ÙŠØ®:</label>
                                <input type="date" id="from_date" class="form-control" wire:model.live="fromDate">
                            </div>
                            <div class="col-md-2">
                                <label for="to_date" class="form-label font-family-cairo fw-bold">Ø¥Ù„Ù‰ ØªØ§Ø±ÙŠØ®:</label>
                                <input type="date" id="to_date" class="form-control" wire:model.live="toDate">
                            </div>
                            <div class="col-md-2">
                                <label for="account_id" class="form-label font-family-cairo fw-bold">Ø§Ù„Ø­Ø³Ø§Ø¨:</label>
                                <select id="account_id" class="form-control" wire:model.live="accountId">
                                    <option value="">Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø­Ø³Ø§Ø¨Ø§Øª</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->aname }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="operation_type" class="form-label font-family-cairo fw-bold">Ù†ÙˆØ¹ Ø§Ù„Ø¹Ù…Ù„ÙŠØ©:</label>
                                <select id="operation_type" class="form-control" wire:model.live="operationType">
                                    <option value="">Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø¹Ù…Ù„ÙŠØ§Øª</option>
                                    @foreach($operationTypes as $type)
                                        <option value="{{ $type }}">{{ $type}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="search" class="form-label font-family-cairo fw-bold">Ø¨Ø­Ø«:</label>
                                <input type="text" id="search" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Ø¨Ø­Ø« ÙÙŠ Ø§Ù„Ø­Ø³Ø§Ø¨ Ø£Ùˆ Ø§Ù„Ø¨ÙŠØ§Ù†">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-2">
                                <label for="per_page" class="form-label font-family-cairo fw-bold">Ø¹Ø¯Ø¯ Ø§Ù„Ù†ØªØ§Ø¦Ø¬:</label>
                                <select id="per_page" class="form-control" wire:model.live="perPage">
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="200">200</option>
                                </select>
                            </div>
                            <div class="col-md-10 d-flex align-items-end">
                                <button class="btn btn-secondary me-2" wire:click="resetFilters">
                                    <i class="fas fa-refresh"></i> Ø¥Ø¹Ø§Ø¯Ø© ØªØ¹ÙŠÙŠÙ†
                                </button>
                            </div>
                        </div>

                        <!-- Summary Cards -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title font-family-cairo fw-bold">Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ù…Ø¯ÙŠÙ†</h5>
                                        <h4 class="font-family-cairo fw-bold">{{ number_format($totalDebit, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title font-family-cairo fw-bold">Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø¯Ø§Ø¦Ù†</h5>
                                        <h4 class="font-family-cairo fw-bold">{{ number_format($totalCredit, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title font-family-cairo fw-bold">Ø§Ù„ÙØ±Ù‚</h5>
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
                                        <th class="font-family-cairo fw-bold">Ø§Ù„ØªØ§Ø±ÙŠØ®</th>
                                        <th class="font-family-cairo fw-bold">Ø±Ù‚Ù… Ø§Ù„Ø¹Ù…Ù„ÙŠØ©</th>
                                        <th class="font-family-cairo fw-bold">Ø§Ø³Ù… Ø§Ù„Ø¹Ù…Ù„ÙŠØ©</th>
                                        <th class="font-family-cairo fw-bold">Ø±Ù‚Ù… Ø§Ù„ÙŠÙˆÙ…ÙŠØ©</th>
                                        <th class="font-family-cairo fw-bold">Ø§Ù„Ø­Ø³Ø§Ø¨</th>
                                        <th class="font-family-cairo fw-bold">Ø§Ù„Ø¨ÙŠØ§Ù†</th>

                                        <th class="font-family-cairo fw-bold">Ù…Ø¯ÙŠÙ†</th>
                                        <th class="font-family-cairo fw-bold">Ø¯Ø§Ø¦Ù†</th>
                                        <th class="font-family-cairo fw-bold">Ø§Ù„Ø±ØµÙŠØ¯</th>
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
                                                           title="{{ __('ØªØ¹Ø¯ÙŠÙ„ Ø§Ù„Ø¹Ù…Ù„ÙŠØ©') }}">
                                                           {{ $operationType }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted" title="{{ __('Ù„Ø§ ÙŠÙ…ÙƒÙ† ØªØ¹Ø¯ÙŠÙ„ Ù‡Ø°Ø§ Ø§Ù„Ù†ÙˆØ¹ Ù…Ù† Ø§Ù„Ø¹Ù…Ù„ÙŠØ§Øª') }}">
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
                                                Ù„Ø§ ØªÙˆØ¬Ø¯ Ø¨ÙŠØ§Ù†Ø§Øª Ù…ØªØ§Ø­Ø© Ù„Ù„Ø¹Ø±Ø¶
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
                                        <strong class="font-family-cairo fw-bold">Ù…Ù„Ø®Øµ Ø§Ù„ØªÙ‚Ø±ÙŠØ±:</strong>
                                        <br>
                                        <span class="font-family-cairo">Ø¹Ø¯Ø¯ Ø§Ù„Ø¹Ù…Ù„ÙŠØ§Øª: {{ $journalDetails->total() }}</span>
                                        <br>
                                        <span class="font-family-cairo">Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ù…Ø¯ÙŠÙ†: {{ number_format($totalDebit, 2) }}</span>
                                        <br>
                                        <span class="font-family-cairo">Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø¯Ø§Ø¦Ù†: {{ number_format($totalCredit, 2) }}</span>
                                        <br>
                                        <span class="font-family-cairo">Ø§Ù„ÙØ±Ù‚: {{ number_format($totalBalance, 2) }}</span>
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

