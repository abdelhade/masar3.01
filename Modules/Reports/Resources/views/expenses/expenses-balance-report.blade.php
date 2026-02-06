@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-head">
                <h2>{{ __('Expenses Balance Report') }}</h2>
                <div class="text-muted">{{ __('As Of Date') }}:
                    {{ $asOfDate ? \Carbon\Carbon::parse($asOfDate)->format('Y-m-d') : now()->format('Y-m-d') }}</div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="as_of_date">{{ __('As Of Date') }}:</label>
                        <input type="date" id="as_of_date" class="form-control" wire:model="asOfDate">
                    </div>
                    <div class="col-md-3">
                        <label for="expense_category">{{ __('Expense Category') }}:</label>
                        <select id="expense_category" class="form-control" wire:model="expenseCategory">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($expenseCategories as $category)
                                <option value=$category->id>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="cost_center">{{ __('Cost Center') }}:</label>
                        <select id="cost_center" class="form-control" wire:model="costCenter">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($costCenters as $center)
                                <option value=$center->id>{{ $center->cname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary mt-4"
                            wire:click="generateReport">{{ __('Generate Report') }}</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Account Code') }}</th>
                                <th>{{ __('Account Name') }}</th>
                                <th>{{ __('Expense Category') }}</th>
                                <th>{{ __('Cost Center') }}</th>
                                <th class="text-end">{{ __('Total Expenses') }}</th>
                                <th class="text-end">{{ __('Total Payments') }}</th>
                                <th class="text-end">{{ __('Balance') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenseBalances as $expense)
                                <tr>
                                    <td>{{ $expense->code }}</td>
                                    <td>{{ $expense->aname }}</td>
                                    <td>{{ $expense->category->name ?? '---' }}</td>
                                    <td>{{ $expense->costCenter->cname ?? '---' }}</td>
                                    <td class="text-end">{{ number_format($expense->total_expenses, 2) }}</td>
                                    <td class="text-end">{{ number_format($expense->total_payments, 2) }}</td>
                                    <td class="text-end">{{ number_format($expense->balance, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __('No Data Available') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <th colspan="4">{{ __('Total') }}</th>
                                <th class="text-end">{{ number_format($totalExpenses, 2) }}</th>
                                <th class="text-end">{{ number_format($totalPayments, 2) }}</th>
                                <th class="text-end">{{ number_format($totalBalance, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($expenseBalances->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $expenseBalances->links() }}
                    </div>
                @endif

                <!-- ملخص -->
                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="alert alert-info">
                            <strong>{{ __('Total Expense Accounts') }}:</strong> {{ $totalAccounts }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-success">
                            <strong>{{ __('Highest Expense') }}:</strong> {{ $highestExpense ?? '---' }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-warning">
                            <strong>{{ __('Average Expense Per Account') }}:</strong>
                            {{ number_format($averageExpensePerAccount, 2) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-primary">
                            <strong>{{ __('Net Expenses') }}:</strong> {{ number_format($netExpenses, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
