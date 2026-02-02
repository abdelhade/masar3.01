@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">لوحة تحكم المشتريات والموردين</h4>
            <p class="text-muted small">تقييم الموردين، الطلبات المتأخرة، متوسط أسعار الشراء، أفضل 5 موردين في الالتزام بالوقت</p>
        </div>
    </div>

    {{-- الطلبات المتأخرة --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-exclamation-triangle me-1"></i> قائمة الطلبات المتأخرة</span>
                    <a href="{{ route('reports.purchasing.delayed-orders') }}" class="btn btn-sm btn-dark">عرض الكل</a>
                </div>
                <div class="card-body p-0">
                    @if($delayedOrders->isEmpty())
                        <p class="mb-0 p-3 text-muted">لا توجد طلبات شراء متأخرة.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>رقم الأمر</th>
                                        <th>المورد</th>
                                        <th>تاريخ الاستلام المتوقع</th>
                                        <th>أيام التأخر</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($delayedOrders->take(10) as $order)
                                        <tr>
                                            <td>{{ $order->pro_id ?? $order->id }}</td>
                                            <td>{{ $order->acc1Head->aname ?? '—' }}</td>
                                            <td>{{ $order->expected_delivery_date ? \Carbon\Carbon::parse($order->expected_delivery_date)->format('Y-m-d') : '—' }}</td>
                                            <td>{{ $order->expected_delivery_date ? now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($order->expected_delivery_date)->startOfDay(), false) : '—' }}</td>
                                            <td>
                                                <a href="{{ route('invoice.view', $order->id) }}" class="btn btn-xs btn-outline-primary">عرض</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- أفضل 5 موردين في الالتزام بالوقت (آخر 6 أشهر) --}}
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-trophy me-1"></i> أفضل 5 موردين في الالتزام بالوقت (آخر 6 أشهر)
                </div>
                <div class="card-body">
                    @if($topSuppliersOnTime->isEmpty())
                        <p class="text-muted mb-0">لا توجد بيانات كافية (فواتير مرتبطة بأوامر شراء لها تاريخ استلام متوقع).</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($topSuppliersOnTime as $idx => $s)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>{{ $idx + 1 }}. {{ $s->supplier_name }}</span>
                                    <span class="badge bg-success">{{ $s->on_time_rate }}% ({{ $s->on_time_deliveries }}/{{ $s->total_deliveries }})</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        {{-- متوسط أسعار الشراء لكل منتج (آخر 6 أشهر) --}}
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-chart-line me-1"></i> متوسط أسعار الشراء لكل منتج (آخر 6 أشهر)
                </div>
                <div class="card-body p-0">
                    @if($averagePricePerProduct->isEmpty())
                        <p class="text-muted p-3 mb-0">لا توجد مشتريات في الفترة.</p>
                    @else
                        <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                            <table class="table table-sm table-striped mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>الصنف</th>
                                        <th>متوسط السعر</th>
                                        <th>عدد الفواتير</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($averagePricePerProduct->take(15) as $row)
                                        <tr>
                                            <td>{{ $row->item_name }}</td>
                                            <td>{{ number_format($row->average_price, 2) }}</td>
                                            <td>{{ $row->invoices_count }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-2 small text-muted">عرض أول 15 صنف. للتفاصيل استخدم تقرير مشتريات أصناف مع فلتر آخر 6 أشهر.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">روابط سريعة</h6>
                    <a href="{{ route('reports.general-purchases-report') }}" class="btn btn-outline-primary me-2">تقرير المشتريات</a>
                    <a href="{{ route('reports.general-suppliers-total-report') }}" class="btn btn-outline-primary me-2">تقرير إجماليات الموردين</a>
                    <a href="{{ route('reports.general-purchases-items-report') }}" class="btn btn-outline-primary me-2">تقرير مشتريات أصناف</a>
                    @if(function_exists('route') && \Route::has('quality.suppliers.index'))
                        <a href="{{ route('quality.suppliers.index') }}" class="btn btn-outline-secondary">تقييم الموردين (الجودة)</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
