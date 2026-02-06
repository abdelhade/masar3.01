
<div class="col-lg-6">
    <div class="employees-info-card">
        <h4 class="gradient-text fw-bold mb-4">
            <i class="fas fa-users me-2"></i>{{ __('general.team_members') }}
        </h4>

        <div class="employee-performance-list">
            @forelse($project->employees as $employee)
                @php
                    // حساب أداء الموظف
                    $employeeProgress = $employee
                        ->dailyProgress()
                        ->whereHas('projectItem', function ($query) use ($project) {
                            $query->where('project_id', $project->id);
                        })
                        ->sum('quantity');

                    $performancePercentage =
                        $employeeProgress > 0 ? min(100, ($employeeProgress / 1000) * 100) : 0;
                @endphp
                <div class="employee-performance">
                    <div class="flex-shrink-0 me-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($employee->name) }}&background=4f46e5&color=fff"
                            alt="{{ $employee->name }}" class="employee-avatar">
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">{{ $employee->name }}</span>
                            <span class="badge bg-primary">{{ $employeeProgress }}
                                {{ __('general.units') }}</span>
                        </div>
                        <div class="performance-bar">
                            <div class="performance-fill bg-success"
                                style="width: {{ $performancePercentage }}%"></div>
                        </div>
                        <small
                            class="text-muted">{{ $employee->position ?? __('general.employee') }}</small>
                    </div>
                </div>
            @empty
                <div class="text-center py-3">
                    <i class="fas fa-users fa-2x text-muted mb-2"></i>
                    <p class="text-muted">{{ __('general.no_employees_assigned') }}</p>
                </div>
            @endforelse
        </div>

        <div class="mt-3 pt-3 border-top">
            @can('employees-list')
                <a href="{{ route('progress.employees.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i>{{ __('general.manage_employees') }}
                </a>
            @endcan
        </div>
    </div>
</div>

