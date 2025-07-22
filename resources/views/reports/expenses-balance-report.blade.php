@extends('admin.dashboard')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>ميزان المصروفات</h2>
            <div class="text-muted">حتى تاريخ: {{ $asOfDate ? \Carbon\Carbon::parse($asOfDate)->format('Y-m-d') : now()->format('Y-m-d') }}</div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="as_of_date">حتى تاريخ:</label>
                    <input type="date" id="as_of_date" class="form-control" wire:model="asOfDate">
                </div>
                <div class="col-md-3">
                    <label for="expense_category">فئة المصروف:</label>
                    <select id="expense_category" class="form-control" wire:model="expenseCategory">
                        <option value="">الكل</option>
                        @foreach($expenseCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="cost_center">مركز التكلفة:</label>
                    <select id="cost_center" class="form-control" wire:model="costCenter">
                        <option value="">الكل</option>
                        @foreach($costCenters as $center)
                            <option value="{{ $center->id }}">{{ $center->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary mt-4" wire:click="generateReport">توليد التقرير</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>رقم الحساب</th>
                            <th>اسم الحساب</th>
                            <th>فئة المصروف</th>
                            <th>مركز التكلفة</th>
                            <th class="text-end">المصروفات</th>
                            <th class="text-end">المدفوع</th>
                            <th class="text-end">الرصيد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenseBalances as $expense)
                        <tr>
                            <td>{{ $expense->code }}</td>
                            <td>{{ $expense->name }}</td>
                            <td>{{ $expense->category->name ?? '---' }}</td>
                            <td>{{ $expense->costCenter->name ?? '---' }}</td>
                            <td class="text-end">{{ number_format($expense->total_expenses, 2) }}</td>
                            <td class="text-end">{{ number_format($expense->total_payments, 2) }}</td>
                            <td class="text-end">{{ number_format($expense->balance, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">لا توجد بيانات متاحة.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <th colspan="4">الإجمالي</th>
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
                        <strong>إجمالي حسابات المصروفات:</strong> {{ $totalAccounts }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-success">
                        <strong>أعلى مصروف:</strong> {{ $highestExpense ?? '---' }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning">
                        <strong>متوسط المصروف للحساب:</strong> {{ number_format($averageExpensePerAccount, 2) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-primary">
                        <strong>صافي المصروفات:</strong> {{ number_format($netExpenses, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 