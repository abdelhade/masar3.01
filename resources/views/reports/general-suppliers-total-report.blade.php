@extends('admin.dashboard')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>تقرير الموردين إجماليات</h2>
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
                    <label for="group_by">تجميع حسب:</label>
                    <select id="group_by" class="form-control" wire:model="groupBy">
                        <option value="supplier">المورد</option>
                        <option value="day">اليوم</option>
                        <option value="week">الأسبوع</option>
                        <option value="month">الشهر</option>
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
                            <th>{{ $groupBy == 'supplier' ? 'المورد' : 'الفترة' }}</th>
                            <th class="text-end">عدد العمليات</th>
                            <th class="text-end">إجمالي المشتريات</th>
                            <th class="text-end">إجمالي المدفوع</th>
                            <th class="text-end">الرصيد</th>
                            <th class="text-end">متوسط العملية</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplierTotals as $total)
                        <tr>
                            <td>
                                @if($groupBy == 'supplier')
                                    {{ $total->supplier_name ?? '---' }}
                                @else
                                    {{ $total->period_name ?? '---' }}
                                @endif
                            </td>
                            <td class="text-end">{{ $total->transactions_count }}</td>
                            <td class="text-end">{{ number_format($total->total_purchases, 2) }}</td>
                            <td class="text-end">{{ number_format($total->total_payments, 2) }}</td>
                            <td class="text-end">{{ number_format($total->balance, 2) }}</td>
                            <td class="text-end">{{ number_format($total->average_transaction, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">لا توجد بيانات متاحة.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <th>الإجمالي</th>
                            <th class="text-end">{{ $grandTotalTransactions }}</th>
                            <th class="text-end">{{ number_format($grandTotalPurchases, 2) }}</th>
                            <th class="text-end">{{ number_format($grandTotalPayments, 2) }}</th>
                            <th class="text-end">{{ number_format($grandTotalBalance, 2) }}</th>
                            <th class="text-end">{{ number_format($grandAverageTransaction, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($supplierTotals->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $supplierTotals->links() }}
                </div>
            @endif

            <!-- ملخص -->
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="alert alert-info">
                        <strong>إجمالي الموردين:</strong> {{ $totalSuppliers }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-success">
                        <strong>أعلى مورد مشتريات:</strong> {{ $topSupplier ?? '---' }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning">
                        <strong>متوسط المشتريات للمورد:</strong> {{ number_format($averagePurchasesPerSupplier, 2) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-primary">
                        <strong>متوسط الرصيد للمورد:</strong> {{ number_format($averageBalancePerSupplier, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 