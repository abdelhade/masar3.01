@extends('admin.dashboard')

@section('title', 'تقرير المبيعات اليومية')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-chart-line"></i> تقرير المبيعات اليومية
                </h4>
            </div>

            <div class="card-body">
                <!-- فلاتر -->
                <form method="GET" action="{{ route('reports.sales.daily') }}" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">من تاريخ</label>
                            <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">إلى تاريخ</label>
                            <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">العميل</label>
                            <select name="customer_id" class="form-select">
                                <option value="">كل العملاء</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                        {{ $customerId == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->aname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-search"></i> توليد التقرير
                            </button>
                        </div>
                    </div>
                </form>

                <!-- الملخص -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="alert alert-info mb-0 p-3">
                            <strong>الفواتير:</strong> <span class="fs-5">{{ $totalInvoices }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-success mb-0 p-3">
                            <strong>المتوسط:</strong> <span
                                class="fs-5">{{ number_format($averageInvoiceValue, 2) }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-warning mb-0 p-3">
                            <strong>الخصومات:</strong> <span class="fs-5">{{ number_format($totalDiscount, 2) }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-primary mb-0 p-3">
                            <strong>الصافي:</strong> <span class="fs-5">{{ number_format($totalNetSales, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- الجدول -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th>رقم الفاتورة</th>
                                <th>العميل</th>
                                <th class="text-end">الأصناف</th>
                                <th class="text-end">الكمية</th>
                                <th class="text-end">المبيعات</th>
                                <th class="text-end">الخصم</th>
                                <th class="text-end">الصافي</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                                <tr>
                                    <td>{{ $sale->pro_date ? \Carbon\Carbon::parse($sale->pro_date)->format('d/m/Y') : '---' }}
                                    </td>
                                    <td><strong>{{ $sale->pro_num ?? '---' }}</strong></td>
                                    <td>{{ $sale->acc1Head->aname ?? 'غير محدد' }}</td>

                                    <!-- عدد الأصناف من items -->
                                    <td class="text-end">{{ $sale->operationItems->count() }}</td>

                                    <!-- الكمية من fat_quantity -->
                                    <td class="text-end">{{ number_format($sale->operationItems->sum('fat_quantity'), 2) }}
                                    </td>

                                    <!-- المبيعات من OperHead -->
                                    <td class="text-end">{{ number_format($sale->fat_total, 2) }}</td>

                                    <!-- الخصم من OperHead -->
                                    <td class="text-end">{{ number_format($sale->fat_disc, 2) }}</td>

                                    <!-- الصافي من OperHead -->
                                    <td class="text-end fw-bold text-success">{{ number_format($sale->fat_net, 2) }}</td>

                                    <!-- الحالة -->
                                    <td>
                                        @switch($sale->status)
                                            @case('completed')
                                                <span class="badge bg-success">مكتمل</span>
                                            @break

                                            @case('pending')
                                                <span class="badge bg-warning">معلق</span>
                                            @break

                                            @default
                                                <span class="badge bg-secondary">غير محدد</span>
                                        @endswitch
                                    </td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                            لا توجد فواتير مبيعات في هذا النطاق
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-primary fw-bold">
                                <tr>
                                    <th colspan="3" class="text-start">الإجمالي</th>
                                    <th class="text-end">{{ $totalItemsCount }}</th>
                                    <th class="text-end">{{ number_format($totalQuantity, 2) }}</th>
                                    <th class="text-end">{{ number_format($totalSales, 2) }}</th>
                                    <th class="text-end">{{ number_format($totalDiscount, 2) }}</th>
                                    <th class="text-end">{{ number_format($totalNetSales, 2) }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $sales->links() }}
                    </div>
                </div>
            </div>
        </div>
    @endsection
