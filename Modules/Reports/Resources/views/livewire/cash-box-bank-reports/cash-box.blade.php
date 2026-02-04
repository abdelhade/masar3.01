<?php

use Livewire\Volt\Component;

use Livewire\WithPagination;
use Modules\Accounts\Models\AccHead;
use App\Models\OperHead;
use App\Models\User;
use Carbon\Carbon;

new class extends Component {
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $boxsAccounts = [];
    public $selectedBox = null;
    public $fromDate = null;
    public $toDate = null;
    public $operheads = [];

    public function mount()
    {
        $this->fromDate = now()->startOfMonth()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
        $this->boxsAccounts = AccHead::where('code', 'like', '%1101%')->where('is_basic', 0)->where('isdeleted', 0)->pluck('aname', 'id');
        $this->selectedBox = $this->boxsAccounts->first();
    }

    public function search()
    {
        $this->operheads = OperHead::whereHas('journalHead.dets', function ($query) {
            $query->where('account_id', $this->selectedBox);
        })
            ->when($this->fromDate, function ($query) {
                $query->whereDate('pro_date', '>=', $this->fromDate);
            })
            ->when($this->toDate, function ($query) {
                $query->whereDate('pro_date', '<=', $this->toDate);
            })
            ->with([
                'journalHead.dets' => function ($query) {
                    $query->where('account_id', $this->selectedBox);
                },
                'type',
                'user',
                'acc1Head',
            ])
            ->orderBy('pro_date', 'desc')
            ->get();
    }
}; ?>

<div>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ __('Cashbox Movement Report') }}</h4>
        </div>
        <div class="card-body row">
            <div class="form-group col-md-3">
                <label for="box">{{ __('Account') }}</label>
                <select class="form-control" wire:model.live="selectedBox" id="box">
                    @foreach ($boxsAccounts as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="from_date">{{ __('From Date') }}</label>
                <input type="date" class="form-control" wire:model.live="fromDate" id="from_date">
            </div>
            <div class="form-group col-md-3">
                <label for="to_date">{{ __('To Date') }}</label>
                <input type="date" class="form-control" wire:model.live="toDate" id="to_date">
            </div>
            <div class="form-group col-md-3 mt-4">
                <button class="btn btn-primary" wire:click="search">
                    <i class="fas fa-search me-1"></i>{{ __('Search') }}
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Operation Date') }}</th>
                            <th>{{ __('Created Date') }}</th>
                            <th>{{ __('Operation Number') }}</th>
                            <th>{{ __('Operation Type') }}</th>
                            <th class="text-end">{{ __('Debit') }}</th>
                            <th class="text-end">{{ __('Credit') }}</th>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Record Date') }}</th>
                            <th>{{ __('Notes') }}</th>
                            <th>{{ __('Reviewed') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($operheads as $operhead)
                            @php
                                $journalDetails = $operhead->journalHead?->dets ?? collect();
                                $cashboxDetails = $journalDetails->where('account_id', $this->selectedBox);
                            @endphp
                            @if ($cashboxDetails->isNotEmpty())
                                @foreach ($cashboxDetails as $detail)
                                    <tr>
                                        <td>
                                            <span
                                                class="fw-semibold">{{ $operhead->accural_date ?? $operhead->pro_date }}</span>
                                        </td>
                                        <td>{{ $operhead->created_at?->format('Y-m-d H:i') }}</td>
                                        <td>{{ $operhead->pro_id }}</td>
                                        <td>
                                            <span class="badge {{ $detail->debit > 0 ? 'bg-danger' : 'bg-success' }}">
                                                {{ $operhead->type?->ptext ?? $operhead->pro_type }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            @if ($detail->debit > 0)
                                                <span
                                                    class="text-danger fw-bold fs-6">{{ number_format($detail->debit, 2) }}</span>
                                            @else
                                                <span class="text-muted">0.00</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if ($detail->credit > 0)
                                                <span
                                                    class="text-success fw-bold fs-6">{{ number_format($detail->credit, 2) }}</span>
                                            @else
                                                <span class="text-muted">0.00</span>
                                            @endif
                                        </td>
                                        <td>{{ AccHead::find($operhead->emp_id)?->aname ?? '-' }}</td>
                                        <td>{{ User::find($operhead->user)?->name ?? '-' }}</td>
                                        <td>{{ $detail->crtime ?? $operhead->created_at?->format('Y-m-d H:i') }}</td>
                                        <td>{{ $operhead->info }}</td>
                                        <td>
                                            <span class="badge {{ $operhead->closed ? 'bg-success' : 'bg-warning' }}">
                                                {{ $operhead->closed ? __('Yes') : __('No') }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ $operhead->getViewUrl() }}" class="btn btn-sm btn-info"
                                                target="_blank" title="{{ __('View Operation') }}">
                                                <i class="ti-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="table-warning">
                                    <td colspan="4" class="text-center text-muted">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        {{ $operhead->accural_date ?? $operhead->pro_date }} -
                                        {{ $operhead->pro_num }}
                                    </td>
                                    <td colspan="8" class="text-center text-muted">{{ __('No Details') }}</td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="12" class="text-center">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-inbox me-2"></i>{{ __('No Data Available') }}
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
