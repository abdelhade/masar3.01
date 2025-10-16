@extends('admin.dashboard')
@section('sidebar')
    @include('components.sidebar.items')
@endsection
@section('content')
    <div class="container-fluid">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="font-family-cairo fw-bold">
                <i class="las la-chart-bar"></i> إحصائيات الأصناف
            </h3>
            <div>
                <a href="{{ route('items.index') }}" class="btn btn-secondary font-family-cairo fw-bold">
                    <i class="las la-arrow-right"></i> رجوع
                </a>
                <a href="{{ route('items.statistics.refresh') }}" class="btn btn-primary font-family-cairo fw-bold">
                    <i class="las la-sync"></i> تحديث
                </a>
            </div>
        </div>

        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show font-family-cairo fw-bold" role="alert">
                <i class="las la-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Cards Row 1: Overview --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100 border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted font-family-cairo fw-bold mb-2">إجمالي الأصناف</h6>
                                <h2 class="font-family-cairo fw-bold mb-0 text-primary">{{ number_format($totalItems) }}
                                </h2>
                            </div>
                            <div class="text-primary" style="font-size: 3rem; opacity: 0.3;">
                                <i class="las la-box"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100 border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted font-family-cairo fw-bold mb-2">إجمالي الوحدات</h6>
                                <h2 class="font-family-cairo fw-bold mb-0 text-success">{{ number_format($totalUnits) }}
                                </h2>
                            </div>
                            <div class="text-success" style="font-size: 3rem; opacity: 0.3;">
                                <i class="las la-balance-scale"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100 border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted font-family-cairo fw-bold mb-2">إجمالي الباركود</h6>
                                <h2 class="font-family-cairo fw-bold mb-0 text-info">{{ number_format($totalBarcodes) }}
                                </h2>
                            </div>
                            <div class="text-info" style="font-size: 3rem; opacity: 0.3;">
                                <i class="las la-barcode"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100 border-start border-danger border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted font-family-cairo fw-bold mb-2">إحصائيات المتغيرات</h6>
                                <h2 class="font-family-cairo fw-bold mb-0 text-danger"> {{ number_format($totalVaribals) }}
                                </h2>
                            </div>
                            <div class="text-danger" style="font-size: 3rem; opacity: 0.3;">
                                <i class="las la-layer-group"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Cards Row 2: Averages --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="las la-calculator text-primary mb-2" style="font-size: 2.5rem;"></i>
                        <h6 class="text-muted font-family-cairo fw-bold mb-2">متوسط الوحدات/صنف</h6>
                        <h3 class="font-family-cairo fw-bold text-primary mb-0">{{ $avgUnitsPerItem }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="las la-qrcode text-success mb-2" style="font-size: 2.5rem;"></i>
                        <h6 class="text-muted font-family-cairo fw-bold mb-2">متوسط الباركود/صنف</h6>
                        <h3 class="font-family-cairo fw-bold text-success mb-0">{{ $avgBarcodesPerItem }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="las la-sticky-note text-info mb-2" style="font-size: 2.5rem;"></i>
                        <h6 class="text-muted font-family-cairo fw-bold mb-2">إجمالي الملاحظات</h6>
                        <h3 class="font-family-cairo fw-bold text-info mb-0">{{ number_format($totalNotes) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="las la-code-branch text-warning mb-2" style="font-size: 2.5rem;"></i>
                        <h6 class="text-muted font-family-cairo fw-bold mb-2">إجمالي المتغيرات</h6>
                        <h3 class="font-family-cairo fw-bold text-warning mb-0">{{ number_format($totalVaribals) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            {{-- Items by Type --}}
            <div class="col-xl-4 col-lg-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="font-family-cairo fw-bold mb-0">
                            <i class="las la-chart-pie text-primary"></i> الأصناف حسب النوع
                        </h5>
                    </div>
                    <div class="card-body">
                        @forelse($itemsByType as $type => $count)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="font-family-cairo fw-bold">{{ $type }}</span>
                                    <span
                                        class="badge bg-primary font-family-cairo fw-bold">{{ number_format($count) }}</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-gradient" role="progressbar"
                                        style="width: {{ $totalItems > 0 ? ($count / $totalItems) * 100 : 0 }}%"
                                        aria-valuenow="{{ $count }}" aria-valuemin="0"
                                        aria-valuemax="{{ $totalItems }}">
                                    </div>
                                </div>
                                <small class="text-muted font-family-cairo">
                                    {{ $totalItems > 0 ? number_format(($count / $totalItems) * 100, 1) : 0 }}%
                                </small>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="las la-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted font-family-cairo fw-bold">لا توجد بيانات</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Most Used Units --}}
            <div class="col-xl-4 col-lg-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="font-family-cairo fw-bold mb-0">
                            <i class="las la-trophy text-success"></i> أكثر الوحدات استخداماً
                        </h5>
                    </div>
                    <div class="card-body">
                        @forelse($mostUsedUnits as $index => $unit)
                            <div
                                class="d-flex justify-content-between align-items-center mb-3 p-2 rounded {{ $index == 0 ? 'bg-light' : '' }}">
                                <div class="d-flex align-items-center">
                                    <span class="badge {{ $index == 0 ? 'bg-warning' : 'bg-secondary' }} me-2">
                                        {{ $index + 1 }}
                                    </span>
                                    <span class="font-family-cairo fw-bold">{{ $unit->name }}</span>
                                </div>
                                <span class="badge bg-success font-family-cairo fw-bold">
                                    {{ number_format($unit->usage_count) }}
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="las la-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted font-family-cairo fw-bold">لا توجد بيانات</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Price Ranges --}}
            <div class="col-xl-4 col-lg-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="font-family-cairo fw-bold mb-0">
                            <i class="las la-tag text-info"></i> نطاقات الأسعار
                        </h5>
                    </div>
                    <div class="card-body">
                        @forelse($priceRanges as $range => $count)
                            <div class="d-flex justify-content-between align-items-center mb-3 p-2 border-bottom">
                                <span class="font-family-cairo fw-bold">
                                    <i class="las la-money-bill text-success me-1"></i>
                                    {{ $range }}
                                </span>
                                <span class="badge bg-info font-family-cairo fw-bold">
                                    {{ number_format($count) }}
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="las la-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted font-family-cairo fw-bold">لا توجد بيانات</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            {{-- Items with Most Units --}}
            <div class="col-xl-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="font-family-cairo fw-bold mb-0">
                            <i class="las la-layer-group text-primary"></i> الأصناف ذات أكثر عدد وحدات
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="font-family-cairo fw-bold">#</th>
                                        <th class="font-family-cairo fw-bold">اسم الصنف</th>
                                        <th class="font-family-cairo fw-bold text-center">عدد الوحدات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($itemsWithMostUnits as $index => $item)
                                        <tr>
                                            <td class="font-family-cairo fw-bold">{{ $index + 1 }}</td>
                                            <td class="font-family-cairo fw-bold">{{ $item['name'] }}</td>
                                            <td class="font-family-cairo fw-bold text-center">
                                                <span class="badge bg-primary">{{ $item['units_count'] }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4">
                                                <i class="las la-inbox text-muted" style="font-size: 3rem;"></i>
                                                <p class="text-muted font-family-cairo fw-bold mb-0">لا توجد بيانات</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Items --}}
            <div class="col-xl-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="font-family-cairo fw-bold mb-0">
                            <i class="las la-clock text-success"></i> أحدث الأصناف المضافة
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="font-family-cairo fw-bold">الكود</th>
                                        <th class="font-family-cairo fw-bold">الاسم</th>
                                        <th class="font-family-cairo fw-bold">النوع</th>
                                        <th class="font-family-cairo fw-bold">التاريخ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentItems as $item)
                                        <tr>
                                            <td class="font-family-cairo fw-bold">{{ $item['code'] }}</td>
                                            <td class="font-family-cairo fw-bold">{{ $item['name'] }}</td>
                                            <td class="font-family-cairo fw-bold">
                                                <span class="badge bg-secondary">{{ $item['type'] }}</span>
                                            </td>
                                            <td class="font-family-cairo fw-bold">
                                                <small>{{ $item['created_at'] }}</small>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <i class="las la-inbox text-muted" style="font-size: 3rem;"></i>
                                                <p class="text-muted font-family-cairo fw-bold mb-0">لا توجد بيانات</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cost Statistics --}}
        @if ($costStats && $costStats->min_cost !== null)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="font-family-cairo fw-bold mb-0">
                                <i class="las la-dollar-sign text-danger"></i> إحصائيات التكلفة
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4 mb-3">
                                    <div class="p-3">
                                        <h6 class="text-muted font-family-cairo fw-bold mb-2">أقل تكلفة</h6>
                                        <h3 class="font-family-cairo fw-bold text-success">
                                            {{ number_format($costStats->min_cost, 2) }}</h3>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="p-3">
                                        <h6 class="text-muted font-family-cairo fw-bold mb-2">متوسط التكلفة</h6>
                                        <h3 class="font-family-cairo fw-bold text-primary">
                                            {{ number_format($costStats->avg_cost, 2) }}</h3>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="p-3">
                                        <h6 class="text-muted font-family-cairo fw-bold mb-2">أعلى تكلفة</h6>
                                        <h3 class="font-family-cairo fw-bold text-danger">
                                            {{ number_format($costStats->max_cost, 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
