@extends('admin.dashboard')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>كشف حساب مصروف</h2>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="expense_account">حساب المصروف:</label>
                    <select id="expense_account" class="form-control" wire:model="expenseAccount">
                        <option value="">اختر حساب المصروف</option>
                        @foreach($expenseAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->aname }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="from_date">من تاريخ:</label>
                    <input type="date" id="from_date" class="form-control" wire:model="fromDate">
                </div>
                <div class="col-md-3">
                    <label for="to_date">إلى تاريخ:</label>
                    <input type="date" id="to_date" class="form-control" wire:model="toDate">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary mt-4" wire:click="generateReport">توليد التقرير</button>
                </div>
            </div>

            @if($selectedAccount)
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <strong>الحساب المحدد:</strong> {{ $selectedAccount->code }} - {{ $selectedAccount->aname }}
                        <br>
                        <strong>الرصيد الافتتاحي:</strong> {{ number_format($openingBalance, 2) }}
                    </div>
                </div>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>رقم العملية</th>
                            <th>البيان</th>
                            <th>مركز التكلفة</th>
                            <th class="text-end">مدين</th>
                            <th class="text-end">دائن</th>
                            <th class="text-end">الرصيد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenseTransactions as $transaction)
                        <tr>
                            <td>{{ $transaction->crtime ? \Carbon\Carbon::parse($transaction->crtime)->format('Y-m-d') : '---' }}</td>
                            <td>{{ $transaction->head->journal_id ?? '---' }}</td>
                            <td>{{ $transaction->info ?? '---' }}</td>
                            <td>{{ $transaction->costCenter->name ?? '---' }}</td>
                            <td class="text-end">{{ $transaction->debit > 0 ? number_format($transaction->debit, 2) : '---' }}</td>
                            <td class="text-end">{{ $transaction->credit > 0 ? number_format($transaction->credit, 2) : '---' }}</td>
                            <td class="text-end">{{ number_format($transaction->running_balance, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">لا توجد بيانات متاحة.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($expenseTransactions->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $expenseTransactions->links() }}
                </div>
            @endif

            @if($selectedAccount)
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert alert-success">
                        <strong>الرصيد الختامي:</strong> {{ number_format($closingBalance, 2) }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection 