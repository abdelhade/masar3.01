@extends('admin.dashboard')
@section('sidebar')
    @include('components.sidebar.vouchers')
@endsection
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mb-4">إحصائيات السندات 📊</h2>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100 border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted font-hold fw-bold mb-2">
                                    إجمالي السندات الكلي
                                </h6>
                                <h2 class="font-hold fw-bold mb-0 text-primary">
                                    {{ number_format($overallTotal->overall_value, 2) }}
                                </h2>
                                <small class="text-muted font-hold">
                                    {{ number_format($overallTotal->overall_count) }} سند
                                </small>
                            </div>
                            <div class="text-primary" style="font-size: 3rem; opacity: 0.3;">
                                <i class="las la-chart-pie"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @foreach ($sortedStatistics as $typeId => $stats)
                @php
                    // تحديد اللون والأيقونة بناءً على النوع
                    switch ($typeId) {
                        case 1: // سند قبض
                        case 5: // قبض آخر
                            $color = 'success';
                            $icon = 'la-hand-holding-usd';
                            break;

                        case 2: // سند صرف
                        case 4: // دفع آخر
                            $color = 'danger';
                            $icon = 'la-money-bill-wave-alt';
                            break;

                        case 3: // مصروف
                            $color = 'warning';
                            $icon = 'la-file-invoice-dollar';
                            break;

                        default:
                            $color = 'secondary';
                            $icon = 'la-chart-pie';
                    }
                @endphp

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card shadow-sm h-100 border-start border-{{ $color }} border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted font-hold fw-bold mb-2">
                                        {{ $stats['title'] }}
                                    </h6>
                                    <h2 class="font-hold fw-bold mb-0 text-{{ $color }}">
                                        {{ number_format($stats['value'], 2) }}
                                    </h2>
                                    <small class="text-muted font-hold">
                                        {{ number_format($stats['count']) }} سند
                                    </small>
                                </div>
                                <div class="text-{{ $color }}" style="font-size: 3rem; opacity: 0.3;">
                                    <i class="las {{ $icon }}"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <h3 class="mt-5">تفاصيل الإحصائيات</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>نوع السند</th>
                        <th>عدد السندات</th>
                        <th>إجمالي القيمة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sortedStatistics as $typeId => $stats)
                        <tr>
                            <td>{{ $typeId }}</td>
                            <td>{{ $stats['title'] }}</td>
                            <td>{{ number_format($stats['count']) }}</td>
                            <td>{{ number_format($stats['value'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-light font-weight-bold">
                    <tr>
                        <td colspan="2" class="text-right">الإجمالي الكلي:</td>
                        <td>{{ number_format($overallTotal->overall_count) }}</td>
                        <td>{{ number_format($overallTotal->overall_value, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
@endsection
