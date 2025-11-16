@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>{{ __('reports.general_account_statement') }}</h2>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="account_id">{{ __('reports.account') }}:</label>
                    <select id="account_id" class="form-control" wire:model="accountId">
                        <option value="">{{ __('reports.select_account') }}</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->aname }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="from_date">{{ __('reports.from_date') }}:</label>
                    <input type="date" id="from_date" class="form-control" wire:model="fromDate">
                </div>
                <div class="col-md-3">
                    <label for="to_date">{{ __('reports.to_date') }}:</label>
                    <input type="date" id="to_date" class="form-control" wire:model="toDate">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary mt-4" wire:click="generateReport">{{ __('reports.generate_report') }}</button>
                </div>
            </div>

            @if($selectedAccount)
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <strong>{{ __('reports.selected_account') }}:</strong> {{ $selectedAccount->code }} - {{ $selectedAccount->aname }}
                        <br>
                        <strong>{{ __('reports.opening_balance') }}:</strong> {{ number_format($openingBalance, 2) }}
                    </div>
                </div>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('reports.date') }}</th>
                            <th>{{ __('reports.operation_number') }}</th>
                            <th>{{ __('reports.description') }}</th>
                            <th>{{ __('reports.debit') }}</th>
                            <th>{{ __('reports.credit') }}</th>
                            <th>{{ __('reports.balance') }}</th>
                            <th>{{ __('reports.cost_center') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $movement)
                        <tr>
                            <td>{{ $movement->crtime ? \Carbon\Carbon::parse($movement->crtime)->format('Y-m-d') : '---' }}</td>
                            <td>{{ $movement->head->journal_id ?? '---' }}</td>
                            <td>{{ $movement->info ?? '---' }}</td>
                            <td class="text-end">{{ $movement->debit > 0 ? number_format($movement->debit, 2) : '---' }}</td>
                            <td class="text-end">{{ $movement->credit > 0 ? number_format($movement->credit, 2) : '---' }}</td>
                            <td class="text-end">{{ number_format($movement->running_balance, 2) }}</td>
                            <td>{{ $movement->costCenter->name ?? '---' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">{{ __('reports.no_data_available') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($movements->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $movements->links() }}
                </div>
            @endif

            @if($selectedAccount)
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert alert-success">
                        <strong>{{ __('reports.closing_balance') }}:</strong> {{ number_format($closingBalance, 2) }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection 