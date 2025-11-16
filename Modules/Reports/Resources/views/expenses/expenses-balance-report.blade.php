@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>{{ __('reports.expenses_balance_report') }}</h2>
            <div class="text-muted">{{ __('reports.as_of_date') }}: {{ $asOfDate ? \Carbon\Carbon::parse($asOfDate)->format('Y-m-d') : now()->format('Y-m-d') }}</div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="as_of_date">{{ __('reports.as_of_date') }}:</label>
                    <input type="date" id="as_of_date" class="form-control" wire:model="asOfDate">
                </div>
                <div class="col-md-3">
                    <label for="expense_category">{{ __('reports.expense_category') }}:</label>
                    <select id="expense_category" class="form-control" wire:model="expenseCategory">
                        <option value="">{{ __('reports.all') }}</option>
                        @foreach($expenseCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="cost_center">{{ __('reports.cost_center') }}:</label>
                    <select id="cost_center" class="form-control" wire:model="costCenter">
                        <option value="">{{ __('reports.all') }}</option>
                        @foreach($costCenters as $center)
                            <option value="{{ $center->id }}">{{ $center->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary mt-4" wire:click="generateReport">{{ __('reports.generate_report') }}</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('reports.account_code') }}</th>
                            <th>{{ __('reports.account_name') }}</th>
                            <th>{{ __('reports.expense_category') }}</th>
                            <th>{{ __('reports.cost_center') }}</th>
                            <th class="text-end">{{ __('reports.total_expenses') }}</th>
                            <th class="text-end">{{ __('reports.total_payments') }}</th>
                            <th class="text-end">{{ __('reports.balance') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenseBalances as $expense)
                        <tr>
                            <td>{{ $expense->code }}</td>
                            <td>{{ $expense->aname }}</td>
                            <td>{{ $expense->category->name ?? '---' }}</td>
                            <td>{{ $expense->costCenter->name ?? '---' }}</td>
                            <td class="text-end">{{ number_format($expense->total_expenses, 2) }}</td>
                            <td class="text-end">{{ number_format($expense->total_payments, 2) }}</td>
                            <td class="text-end">{{ number_format($expense->balance, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">{{ __('reports.no_data_available') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <th colspan="4">{{ __('reports.total') }}</th>
                            <th class="text-end">{{ number_format($totalExpenses, 2) }}</th>
                            <th class="text-end">{{ number_format($totalPayments, 2) }}</th>
                            <th class="text-end">{{ number_format($totalBalance, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($expenseBalances->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $expenseBalances->links() }}
                </div>
            @endif

            <!-- ملخص -->
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="alert alert-info">
                        <strong>{{ __('reports.total_expense_accounts') }}:</strong> {{ $totalAccounts }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-success">
                        <strong>{{ __('reports.highest_expense') }}:</strong> {{ $highestExpense ?? '---' }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning">
                        <strong>{{ __('reports.average_expense_per_account') }}:</strong> {{ number_format($averageExpensePerAccount, 2) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-primary">
                        <strong>{{ __('reports.net_expenses') }}:</strong> {{ number_format($netExpenses, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 