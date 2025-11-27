@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.transfers')
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mb-4">إحصائيات التحويلات النقدية 📊</h2>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100 border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted font-hold fw-bold mb-2">
                                    إجمالي التحويلات الكلي
                                </h6>
                                <h2 class="font-hold fw-bold mb-0 text-primary">
                                    {{ number_format($overallTotal->overall_value, 2) }}
                                </h2>
                                <small class="text-muted font-hold">
                                    {{ number_format($overallTotal->overall_count) }} تحويل
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
                @if ($stats)
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card shadow-sm h-100 border-start border-{{ $stats['color'] }} border-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted font-hold fw-bold mb-2">
                                            {{ $stats['title'] }}
                                        </h6>
                                        <h2 class="font-hold fw-bold mb-0 text-{{ $stats['color'] }}">
                                            {{ number_format($stats['value'], 2) }}
                                        </h2>
                                        <small class="text-muted font-hold">
                                            {{ number_format($stats['count']) }} تحويل
                                        </small>
                                    </div>
                                    <div class="text-{{ $stats['color'] }}" style="font-size: 3rem; opacity: 0.3;">
                                        <i class="las {{ $stats['icon'] }}"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <h3 class="mt-5">تفاصيل الإحصائيات</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>نوع التحويل</th>
                        <th>عدد التحويلات</th>
                        <th>إجمالي القيمة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sortedStatistics as $typeId => $stats)
                        @if ($stats)
                            <tr>
                                <td>{{ $typeId }}</td>
                                <td>{{ $stats['title'] }}</td>
                                <td>{{ number_format($stats['count']) }}</td>
                                <td>{{ number_format($stats['value'], 2) }}</td>
                            </tr>
                        @endif
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
