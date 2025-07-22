@extends('admin.dashboard')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>تقرير المشتريات إجماليات</h2>
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
                        <option value="day">اليوم</option>
                        <option value="week">الأسبوع</option>
                        <option value="month">الشهر</option>
                        <option value="supplier">المورد</option>
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
                            <th class="text-end">عدد الفواتير</th>
                            <th class="text-end">إجمالي الكمية</th>
                            <th class="text-end">إجمالي المشتريات</th>
                            <th class="text-end">إجمالي الخصم</th>
                            <th class="text-end">صافي المشتريات</th>
                            <th class="text-end">متوسط الفاتورة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchasesTotals as $total)
                        <tr>
                            <td>
                                @if($groupBy == 'supplier')
                                    {{ $total->supplier_name ?? '---' }}
                                @else
                                    {{ $total->period_name ?? '---' }}
                                @endif
                            </td>
                            <td class="text-end">{{ $total->invoices_count }}</td>
                            <td class="text-end">{{ number_format($total->total_quantity, 2) }}</td>
                            <td class="text-end">{{ number_format($total->total_purchases, 2) }}</td>
                            <td class="text-end">{{ number_format($total->total_discount, 2) }}</td>
                            <td class="text-end">{{ number_format($total->net_purchases, 2) }}</td>
                            <td class="text-end">{{ number_format($total->average_invoice, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">لا توجد بيانات متاحة.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <th>الإجمالي</th>
                            <th class="text-end">{{ $grandTotalInvoices }}</th>
                            <th class="text-end">{{ number_format($grandTotalQuantity, 2) }}</th>
                            <th class="text-end">{{ number_format($grandTotalPurchases, 2) }}</th>
                            <th class="text-end">{{ number_format($grandTotalDiscount, 2) }}</th>
                            <th class="text-end">{{ number_format($grandTotalNetPurchases, 2) }}</th>
                            <th class="text-end">{{ number_format($grandAverageInvoice, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($purchasesTotals->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $purchasesTotals->links() }}
                </div>
            @endif

            <!-- ملخص -->
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="alert alert-info">
                        <strong>إجمالي الفترات:</strong> {{ $totalPeriods }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-success">
                        <strong>أعلى مشتريات:</strong> {{ number_format($highestPurchases, 2) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning">
                        <strong>أدنى مشتريات:</strong> {{ number_format($lowestPurchases, 2) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-primary">
                        <strong>متوسط المشتريات:</strong> {{ number_format($averagePurchases, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 