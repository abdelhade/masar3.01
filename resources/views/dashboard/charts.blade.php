@extends('dashboard.layout')
@section('content')
    @include('dashboard.components.summary-cards')
    @include('dashboard.components.summary-tables')
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-page-title mb-2">الرسوم البيانية</h1>
        <p class="text-body-sm text-text-secondary">متابعة الأداء والإحصائيات من خلال الرسوم البيانية التفاعلية</p>
    </div>

    <!-- Charts Grid -->
    <div class="grid-3-col gap-4 mb-6">
        @for($i = 1; $i <= 20; $i++)
            <div class="card hover-lift transition-base">
                <div class="card-header border-b border-border-light p-4" style="border-left: 4px solid #34d3a3;">
                    <h3 class="text-section-title mb-0">Chart {{ $i }}</h3>
                </div>
                <div class="card-body p-4">
                    @include('dashboard.components.chart' . $i)
                </div>
            </div>
        @endfor
    </div>
@endsection 