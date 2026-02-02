@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">قائمة الطلبات المتأخرة</h4>
            <p class="text-muted small">أوامر شراء تجاوز تاريخ الاستلام المتوقع ولم تُستلم بعد.</p>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    @if($delayedOrders->isEmpty())
                        <p class="mb-0 p-4 text-muted">لا توجد طلبات شراء متأخرة. تأكد من إدخال «تاريخ الاستلام المتوقع» في أوامر الشراء وعروض الأسعار.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>رقم الأمر</th>
                                        <th>المورد</th>
                                        <th>تاريخ الاستلام المتوقع</th>
                                        <th>أيام التأخر</th>
                                        <th>إجراء</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($delayedOrders as $order)
                                        <tr>
                                            <td>{{ $order->pro_id ?? $order->id }}</td>
                                            <td>{{ $order->acc1Head->aname ?? '—' }}</td>
                                            <td>{{ $order->expected_delivery_date ? \Carbon\Carbon::parse($order->expected_delivery_date)->format('Y-m-d') : '—' }}</td>
                                            <td>{{ $order->expected_delivery_date ? now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($order->expected_delivery_date)->startOfDay(), false) : '—' }}</td>
                                            <td>
                                                <a href="{{ route('invoice.view', $order->id) }}" class="btn btn-sm btn-outline-primary">عرض الأمر</a>
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
    <div class="row mt-2">
        <div class="col-12">
            <a href="{{ route('reports.purchasing.dashboard') }}" class="btn btn-secondary">العودة للوحة تحكم المشتريات</a>
        </div>
    </div>
</div>
@endsection
