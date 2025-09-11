@extends('admin.dashboard')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>تقرير العملاء اليومية</h2>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="from_date">من تاريخ:</label>
                    <input type="date" id="from_date" class="form-control" wire:model="fromDate">
                </div>
                <div class="col-md-3">
                    <label for="to_date">إلى تاريخ:</label>
                    <input type="date" id="to_date" class="form-control" wire:model="toDate">
                </div>
                <div class="col-md-3">
                    <label for="customer_id">العميل:</label>
                    <select id="customer_id" class="form-control" wire:model="customerId">
                        <option value="">الكل</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->aname }}</option>
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
                            <th>التاريخ</th>
                            <th>العميل</th>
                            <th>نوع العملية</th>
                            <th>رقم العملية</th>
                            <th class="text-end">المبلغ</th>
                            <th class="text-end">الرصيد</th>
                            <th>البيان</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customerTransactions as $transaction)
                        <tr>
                            <td>{{ $transaction->pro_date ? \Carbon\Carbon::parse($transaction->pro_date)->format('Y-m-d') : '---' }}</td>
                            <td>{{ $transaction->accountHead->aname ?? '---' }}</td>
                            <td>
                                <span class="badge bg-{{ $transaction->type == 'sale' ? 'success' : ($transaction->type == 'payment' ? 'info' : 'warning') }}">
                                    {{-- {{ $transaction->getTransactionTypeText() }} --}}
                                </span>
                            </td>
                            <td>{{ $transaction->pro_num ?? '---' }}</td>
                            <td class="text-end">{{ number_format($transaction->amount, 2) }}</td>
                            <td class="text-end">{{ number_format($transaction->balance, 2) }}</td>
                            <td>{{ $transaction->details ?? '---' }}</td>
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
                            <th class="text-end">{{ number_format($totalAmount, 2) }}</th>
                            <th class="text-end">{{ number_format($finalBalance, 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($customerTransactions->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $customerTransactions->links() }}
                </div>
            @endif

            <!-- ملخص -->
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="alert alert-info">
                        <strong>إجمالي العمليات:</strong> {{ $totalTransactions }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-success">
                        <strong>إجمالي المبيعات:</strong> {{ number_format($totalSales, 2) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning">
                        <strong>إجمالي المدفوع:</strong> {{ number_format($totalPayments, 2) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-primary">
                        <strong>الرصيد النهائي:</strong> {{ number_format($finalBalance, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
