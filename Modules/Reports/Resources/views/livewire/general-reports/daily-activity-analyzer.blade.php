<?php

use App\Models\OperHead;
use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $fromDate = '';

    public $toDate = '';

    public $userId = '';

    public $operationType = '';

    public function mount()
    {
        $this->fromDate = now()->startOfMonth()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
    }

    public function with(): array
    {
        $query = OperHead::with('user')
            ->where('isdeleted', 0)
            ->when($this->fromDate, function ($q) {
                $q->whereDate('pro_date', '>=', $this->fromDate);
            })
            ->when($this->toDate, function ($q) {
                $q->whereDate('pro_date', '<=', $this->toDate);
            })
            ->when($this->userId, function ($q) {
                $q->where('user', $this->userId);
            })
            ->when($this->operationType, function ($q) {
                $q->where('pro_type', $this->operationType);
            })
            ->orderBy('created_at', 'desc');

        $operations = $query->paginate(50);

        return [
            'operations' => $operations,
            'users' => User::all(),
        ];
    }

    public function resetFilters()
    {
        $this->reset(['fromDate', 'toDate', 'userId', 'operationType']);
        $this->fromDate = now()->startOfMonth()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
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

    public function updatedUserId()
    {
        $this->resetPage();
    }

    public function updatedOperationType()
    {
        $this->resetPage();
    }
}; ?>

<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>{{ __('reports.daily_activity_analyzer') }}</h2>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="from_date">{{ __('reports.from_date') }}:</label>
                    <input type="date" id="from_date" class="form-control" wire:model.live="fromDate">
                </div>
                <div class="col-md-3">
                    <label for="to_date">{{ __('reports.to_date') }}:</label>
                    <input type="date" id="to_date" class="form-control" wire:model.live="toDate">
                </div>
                <div class="col-md-3">
                    <label for="user_id">{{ __('reports.user') }}:</label>
                    <select id="user_id" class="form-control" wire:model.live="userId">
                        <option value="">{{ __('reports.all') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="operation_type">{{ __('reports.operation_type') }}:</label>
                    <select id="operation_type" class="form-control" wire:model.live="operationType">
                        <option value="">{{ __('reports.all') }}</option>
                        <option value="10">{{ __('reports.sales_invoice') }}</option>
                        <option value="11">{{ __('reports.purchase_invoice') }}</option>
                        <option value="12">{{ __('reports.sales_return') }}</option>
                        <option value="13">{{ __('reports.purchase_return') }}</option>
                        <option value="7">{{ __('reports.journal_entry') }}</option>
                        <option value="8">{{ __('reports.account_journal_entry') }}</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12 text-end">
                    <button class="btn btn-secondary" wire:click="resetFilters">
                        <i class="fas fa-redo"></i> {{ __('reports.reset') }}
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('reports.date') }}</th>
                            <th>{{ __('reports.time') }}</th>
                            <th>{{ __('reports.user') }}</th>
                            <th>{{ __('reports.operation_type') }}</th>
                            <th>{{ __('reports.operation_number') }}</th>
                            <th>{{ __('reports.amount') }}</th>
                            <th>{{ __('reports.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($operations as $operation)
                        <tr>
                            <td>{{ $operation->pro_date ? \Carbon\Carbon::parse($operation->pro_date)->format('Y-m-d') : '---' }}</td>
                            <td>{{ $operation->created_at ? $operation->created_at->format('H:i') : '---' }}</td>
                            <td>{{ $operation->user->name ?? '---' }}</td>
                            <td>{{ $operation->getOperationTypeText() }}</td>
                            <td>{{ $operation->pro_num ?? '---' }}</td>
                            <td>{{ number_format($operation->pro_value ?? 0, 2) }}</td>
                            <td>{{ $operation->details ?? '---' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">{{ __('reports.no_data_available') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($operations->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $operations->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

