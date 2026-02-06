
<div class="container-fluid py-4">
    <div class="row g-4 mb-5">
        
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ $totalItems }}</h3>
                        <p class="text-muted mb-0">{{ __('general.work_items') }}</p>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: {{ min($overallProgress, 100) }}%">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ number_format($overallProgress, 1) }}%</h3>
                        <p class="text-muted mb-0">{{ __('general.overall_progress') }}</p>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: {{ $overallProgress }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ abs($daysPassed) }}</h3>
                        <p class="text-muted mb-0">{{ __('general.days_passed') }}</p>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="progress" style="height: 6px;">
                        @php
                            $totalDays = $daysPassed + $daysRemaining;
                            $passedPercentage = $totalDays > 0 ? ($daysPassed / $totalDays) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-warning" style="width: {{ $passedPercentage }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ $totalEmployees }}</h3>
                        <p class="text-muted mb-0">{{ __('general.total_employees') }}</p>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

