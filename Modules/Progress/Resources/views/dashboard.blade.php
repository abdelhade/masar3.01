@extends('progress::layouts.daily-progress')

@section('content')
<div class="containers-fluid p-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 text-primary">{{ __('general.dashboard_title') }}</h2>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body">
            <form action="{{ route('progress.dashboard') }}" method="GET">
                <div class="row g-3">
                    <!-- Status Filter -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted">{{ __('general.status') }}</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="All" {{ request('status') == 'All' ? 'selected' : '' }}>{{ __('general.all') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('general.active') }}</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('general.completed') }}</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('general.pending') }}</option>
                        </select>
                    </div>

                    <!-- Employee Filter -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted">{{ __('general.employee') }}</label>
                        <select name="employee_id" class="form-select form-select-sm">
                            <option value="">{{ __('general.select_employee') }}</option>
                            @foreach($employeesList as $emp)
                                <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Project Type Filter -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted">{{ __('general.project_type') }}</label>
                        <select name="project_type_id" class="form-select form-select-sm">
                            <option value="">{{ __('general.select_type') }}</option>
                            @foreach($projectTypesList as $type)
                                <option value="{{ $type->id }}" {{ request('project_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Project Filter -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted">{{ __('general.project') }}</label>
                        <select name="project_id" class="form-select form-select-sm">
                            <option value="">{{ __('general.select_project') }}</option>
                            @foreach($projectsList as $proj)
                                <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                                    {{ $proj->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Item Filter -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted">{{ __('general.item') }}</label>
                        <select name="item_id" class="form-select form-select-sm">
                            <option value="">{{ __('general.select_item') }}</option>
                            @foreach($itemsList as $item)
                                <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Range Filter -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted">{{ __('general.last_x_days') }}</label>
                        <input type="number" name="date_range" class="form-control form-control-sm" 
                               value="{{ request('date_range') }}" placeholder="e.g. 30">
                    </div>

                    <!-- Submit Button -->
                    <div class="col-12 text-end mt-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="las la-filter"></i> {{ __('general.filter') }}
                        </button>
                        <a href="{{ route('progress.dashboard') }}" class="btn btn-secondary btn-sm">
                            <i class="las la-sync"></i> {{ __('general.reset') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4 g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 py-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase mb-1 small">{{ __('general.total_employees') }}</h6>
                        <h3 class="mb-0 fw-bold">{{ $totalEmployees }}</h3>
                    </div>
                    <div class="icon-circle bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                        <i class="las la-users la-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 py-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase mb-1 small">{{ __('general.total_projects') }}</h6>
                        <h3 class="mb-0 fw-bold">{{ $totalProjects }}</h3>
                    </div>
                    <div class="icon-circle bg-success bg-opacity-10 text-success p-3 rounded-circle">
                        <i class="las la-project-diagram la-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 py-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase mb-1 small">{{ __('general.overall_progress') }}</h6>
                        <h3 class="mb-0 fw-bold">{{ $overallCompletion }}%</h3>
                    </div>
                    <div class="icon-circle bg-info bg-opacity-10 text-info p-3 rounded-circle" style="width: 60px; height: 60px; position:relative;">
                        <!-- Simple CSS Circle or Just Icon -->
                        <i class="las la-chart-pie la-2x w-100 h-100 d-flex justify-content-center align-items-center"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4 g-4">
        <!-- Planned vs Actual -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-3">
                    <h5 class="card-title fw-bold small text-uppercase">{{ __('general.planned_vs_actual_chart') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="plannedVsActualChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Project Status Distribution -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-3">
                    <h5 class="card-title fw-bold small text-uppercase">{{ __('general.project_status_distribution') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4 g-4">
        <!-- Project Progress -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                 <div class="card-header bg-white border-bottom-0 pt-3">
                    <h5 class="card-title fw-bold small text-uppercase">{{ __('general.project_progress') }}</h5>
                </div>
                <div class="card-body">
                     <canvas id="projectsProgressChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 fw-bold">{{ __('general.projects_list') }}</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0">{{ __('general.project_name') }}</th>
                        <th class="border-0">{{ __('general.start_date') }}</th>
                        <th class="border-0">{{ __('general.end_date') }}</th>
                        <th class="border-0">{{ __('general.progress') }}</th>
                        <th class="border-0">{{ __('general.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                        @php
                            // Recalculate or use attribute
                            $total = $project->items->sum('total_quantity');
                            $done = $project->items->sum('completed_quantity');
                            $perc = $total > 0 ? round(($done/$total)*100) : 0;
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $project->name }}</td>
                            <td>{{ $project->start_date }}</td>
                            <td>{{ $project->end_date ?? '-' }}</td>
                            <td style="width: 200px;">
                                <div class="d-flex align-items-center">
                                    <span class="me-2 small fw-bold">{{ $perc }}%</span>
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-info" role="progressbar" 
                                             style="width: {{ $perc }}%;" 
                                             aria-valuenow="{{ $perc }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $project->status == 'completed' ? 'success' : ($project->status == 'active' ? 'primary' : 'warning') }}">
                                    {{ ucfirst($project->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">{{ __('general.no_records_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($projects->hasPages())
        <div class="card-footer bg-white border-top-0">
            {{ $projects->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        
        const labels = {
            planned: "{{ __('general.planned') }}",
            actual: "{{ __('general.actual') }}",
            active: "{{ __('general.active') }}",
            completed: "{{ __('general.completed') }}",
            pending: "{{ __('general.pending') }}",
            other: "{{ __('general.other') }}",
            completion: "{{ __('general.completion_percentage') }}",
            progress: "{{ __('general.progress') }}"
        };

        // 1. Planned vs Actual Chart
        const ctxPlanned = document.getElementById('plannedVsActualChart').getContext('2d');
        new Chart(ctxPlanned, {
            type: 'bar',
            data: {
                labels: [labels.progress],
                datasets: [
                    {
                        label: labels.planned,
                        data: [{{ $plannedData }}],
                        backgroundColor: '#0d6efd', // Blue
                        borderRadius: 5
                    },
                    {
                        label: labels.actual,
                        data: [{{ $actualData }}],
                        backgroundColor: '#0dcaf0', // Cyan/Info
                        borderRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // 2. Status Distribution (Doughnut)
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: [labels.active, labels.completed, labels.pending, labels.other],
                datasets: [{
                    data: [
                        {{ $statusCounts['active'] }}, 
                        {{ $statusCounts['completed'] }}, 
                        {{ $statusCounts['pending'] }},
                        {{ $statusCounts['other'] }}
                    ],
                    backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#6c757d'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // 3. Project Progress Bar Chart
        const ctxProj = document.getElementById('projectsProgressChart').getContext('2d');
        const projectNames = {!! json_encode($projectNames) !!};
        const projectValues = {!! json_encode($projectProgressValues) !!};

        new Chart(ctxProj, {
            type: 'bar',
            data: {
                labels: projectNames,
                datasets: [{
                    label: labels.completion,
                    data: projectValues,
                    backgroundColor: '#20c997', // Teal
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y', // Horizontal bars for better name readability
                scales: {
                    x: { beginAtZero: true, max: 100 }
                }
            }
        });
    });
</script>
@endsection
