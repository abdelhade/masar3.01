{{-- KPIs Information Tab --}}
<div x-show="activeViewTab === 'kpi'" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-cloak
>
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm animate-on-scroll">
                <div class="card-header bg-gradient-primary text-white py-2">
                    <h6 class="card-title mb-0 fw-bold font-hold">
                        <i class="fas fa-chart-line me-2"></i>{{ __('معدلات الأداء') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if ($viewEmployee && $viewEmployee->kpis->count() > 0)
                        <div class="row g-3">
                            @foreach ($viewEmployee->kpis as $kpi)
                                <div class="col-md-6 col-lg-4">
                                    <div class="card border-success h-100 shadow-sm hover-lift">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title fw-bold text-success mb-0">
                                                    <i class="fas fa-star me-1"></i>{{ e($kpi->name) }}
                                                </h6>
                                                <span class="badge bg-primary fs-6">
                                                    {{ $kpi->pivot->weight_percentage }}%
                                                </span>
                                            </div>
                                            @if ($kpi->description)
                                                <p class="card-text text-muted small mb-0">
                                                    {{ \Illuminate\Support\Str::limit(e($kpi->description), 100) }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Total Weight Summary --}}
                        <div class="mt-4">
                            @php
                                $totalWeight = $viewEmployee->kpis->sum('pivot.weight_percentage');
                            @endphp
                            <div class="alert {{ $totalWeight == 100 ? 'alert-success' : 'alert-warning' }} mb-0 d-flex justify-content-between align-items-center">
                                <span class="fw-bold">
                                    <i class="fas fa-calculator me-2"></i>{{ __('المجموع الكلي:') }}
                                </span>
                                <span class="badge {{ $totalWeight == 100 ? 'bg-success' : 'bg-warning' }} fs-5">
                                    {{ $totalWeight }}%
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info text-center mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('لا توجد معدلات أداء محددة لهذا الموظف.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

