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
                    <h4 class="card-title">كشف حساب مصروف</h4>
                </div>
                <div class="card-body">
                    <!-- Filters Form -->
                    <form method="GET" action="{{ route('reports.general-expenses-daily-report') }}">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="expense_account" class="form-label">حساب المصروف</label>
                                <select name="expense_account" id="expense_account" class="form-select">
                                    <option value="">اختر حساب المصروف</option>
                                    @foreach($expenseAccounts as $account)
                                        <option value="{{ $account->id }}" {{ request('expense_account') == $account->id ? 'selected' : '' }}>
                                            {{ $account->code }} - {{ $account->aname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="from_date" class="form-label">من تاريخ</label>
                                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control" id="from_date">
                            </div>
                            <div class="col-md-3">
                                <label for="to_date" class="form-label">إلى تاريخ</label>
                                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control" id="to_date">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block w-100">
                                    <i class="fas fa-search"></i> بحث
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
                                        <strong>الحساب المحدد:</strong> {{ $selectedAccount->code }} - {{ $selectedAccount->aname }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>الرصيد الافتتاحي:</strong> 
                                        <span class="badge bg-primary">{{ number_format($openingBalance, 2) }}</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>الرصيد الختامي:</strong> 
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
                                    <th>التاريخ</th>
                                    <th>رقم القيد</th>
                                    <th>البيان</th>
                                    <th>مركز التكلفة</th>
                                    <th class="text-end">مدين</th>
                                    <th class="text-end">دائن</th>
                                    <th class="text-end">الرصيد</th>
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
                                            لا توجد حركات لهذا الحساب في الفترة المحددة.
                                        @else
                                            الرجاء اختيار حساب مصروف لعرض الحركات.
                                        @endif
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($expenseTransactions->count() > 0)
                            <tfoot>
                                <tr class="table-secondary fw-bold">
                                    <td colspan="4" class="text-start">الإجماليات</td>
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
