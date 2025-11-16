@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('reports.expenses_daily_report') }}</h4>
                </div>
                <div class="card-body">
                    <!-- Filters Form -->
                    <form method="GET" action="{{ route('reports.general-expenses-daily-report') }}">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="expense_account" class="form-label">{{ __('reports.expense_account') }}</label>
                                <select name="expense_account" id="expense_account" class="form-select">
                                    <option value="">{{ __('reports.select_expense_account') }}</option>
                                    @foreach($expenseAccounts as $account)
                                        <option value="{{ $account->id }}" {{ request('expense_account') == $account->id ? 'selected' : '' }}>
                                            {{ $account->code }} - {{ $account->aname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="from_date" class="form-label">{{ __('reports.from_date') }}</label>
                                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control" id="from_date">
                            </div>
                            <div class="col-md-3">
                                <label for="to_date" class="form-label">{{ __('reports.to_date') }}</label>
                                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control" id="to_date">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block w-100">
                                    <i class="fas fa-search"></i> {{ __('reports.searching') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Account Info -->
                    @if($selectedAccount)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>{{ __('reports.selected_account') }}:</strong> {{ $selectedAccount->code }} - {{ $selectedAccount->aname }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>{{ __('reports.opening_balance') }}:</strong> 
                                        <span class="badge bg-primary">{{ number_format($openingBalance, 2) }}</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>{{ __('reports.closing_balance') }}:</strong> 
                                        <span class="badge bg-success">{{ number_format($closingBalance, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('reports.date') }}</th>
                                    <th>{{ __('reports.journal_number') }}</th>
                                    <th>{{ __('reports.description') }}</th>
                                    <th>{{ __('reports.cost_center') }}</th>
                                    <th class="text-end">{{ __('reports.debit') }}</th>
                                    <th class="text-end">{{ __('reports.credit') }}</th>
                                    <th class="text-end">{{ __('reports.balance') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $runningBalance = $openingBalance;
                                @endphp
                                @forelse($expenseTransactions as $transaction)
                                @php
                                    $runningBalance += ($transaction->debit - $transaction->credit);
                                @endphp
                                <tr>
                                    <td>{{ $transaction->crtime ? \Carbon\Carbon::parse($transaction->crtime)->format('Y-m-d') : '---' }}</td>
                                    <td>{{ $transaction->head->journal_id ?? '---' }}</td>
                                    <td>{{ $transaction->info ?? $transaction->description ?? '---' }}</td>
                                    <td>{{ $transaction->costCenter->name ?? '---' }}</td>
                                    <td class="text-end">
                                        @if($transaction->debit > 0)
                                            <span class="text-danger">{{ number_format($transaction->debit, 2) }}</span>
                                        @else
                                            <span class="text-muted">---</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($transaction->credit > 0)
                                            <span class="text-success">{{ number_format($transaction->credit, 2) }}</span>
                                        @else
                                            <span class="text-muted">---</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($runningBalance > 0)
                                            <span class="text-danger fw-bold">{{ number_format($runningBalance, 2) }}</span>
                                        @elseif($runningBalance < 0)
                                            <span class="text-success fw-bold">{{ number_format(abs($runningBalance), 2) }}</span>
                                        @else
                                            <span class="text-muted">0.00</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        @if($selectedAccount)
                                            {{ __('reports.no_movements_for_account_period') }}
                                        @else
                                            {{ __('reports.select_expense_account_to_view_movements') }}
                                        @endif
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($expenseTransactions->count() > 0)
                            <tfoot>
                                <tr class="table-secondary fw-bold">
                                    <td colspan="4" class="text-start">{{ __('reports.total') }}</td>
                                    <td class="text-end text-danger">{{ number_format($expenseTransactions->sum('debit'), 2) }}</td>
                                    <td class="text-end text-success">{{ number_format($expenseTransactions->sum('credit'), 2) }}</td>
                                    <td class="text-end">{{ number_format($closingBalance, 2) }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($expenseTransactions->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $expenseTransactions->appends(request()->query())->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
