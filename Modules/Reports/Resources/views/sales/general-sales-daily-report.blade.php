@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-head">
                <h2>تقرير المبيعات اليومية</h2>
            </div>
            <div class="card-body">

                <!-- الفورم بطريقة GET -->
                <form method="GET" action="{{ route('reports.general-sales-daily-report') }}">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="from_date">من تاريخ:</label>
                            <input type="date" name="from_date" id="from_date" class="form-control"
                                value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="to_date">إلى تاريخ:</label>
                            <input type="date" name="to_date" id="to_date" class="form-control"
                                value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="customer_id">العميل:</label>
                            <select name="customer_id" id="customer_id" class="form-control">
                                <option value="">الكل</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                        {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->aname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary mt-4">توليد التقرير</button>
                        </div>
                    </div>
                </form>

                <!-- الجدول -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th>رقم الفاتورة</th>
                                <th>العميل</th>
                                <th class="text-end">عدد الأصناف</th>
                                <th class="text-end">إجمالي الكمية</th>
                                <th class="text-end">إجمالي المبيعات</th>
                                <th class="text-end">الخصم</th>
                                <th class="text-end">صافي المبيعات</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                                <tr>
                                    <td>{{ $sale->pro_date ? \Carbon\Carbon::parse($sale->pro_date)->format('Y-m-d') : '---' }}
                                    </td>
                                    <td>{{ $sale->pro_num ?? '---' }}</td>
                                    <td>{{ $sale->acc1Head->aname ?? '---' }}</td>
                                    <td class="text-end">{{ $sale->items_count ?? 0 }}</td>
                                    <td class="text-end">{{ number_format($sale->total_quantity, 2) }}</td>
                                    <td class="text-end">{{ number_format($sale->total_sales, 2) }}</td>
                                    <td class="text-end">{{ number_format($sale->fat_disc ?? 0, 2) }}</td>
                                    <td class="text-end">{{ number_format($sale->net_sales, 2) }}</td>
                                    <td>
                                        @if ($sale->status == 'completed')
                                            <span class="badge bg-success">مكتمل</span>
                                        @elseif($sale->status == 'pending')
                                            <span class="badge bg-warning">معلق</span>
                                        @else
                                            <span class="badge bg-secondary">غير محدد</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">لا توجد بيانات متاحة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <th colspan="4">الإجمالي</th>
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
                {{ $sales->appends(request()->query())->links() }}

                <!-- الملخص -->
                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="alert alert-info">
                            <strong>إجمالي الفواتير:</strong> {{ $totalInvoices }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-success">
                            <strong>متوسط قيمة الفاتورة:</strong> {{ number_format($averageInvoiceValue, 2) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-warning">
                            <strong>إجمالي الخصومات:</strong> {{ number_format($totalDiscount, 2) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-primary">
                            <strong>صافي المبيعات:</strong> {{ number_format($totalNetSales, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
