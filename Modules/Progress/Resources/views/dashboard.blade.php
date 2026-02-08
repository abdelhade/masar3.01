@extends('progress::layouts.app')

@section('title', __('general.dashboard'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/dashboard.js') }}" defer></script>
@endpush

@section('content')
    
    <div class="print-header d-none">
        <h2>{{ __('general.dashboard') }}</h2>
        <p class="print-date">{{ __('general.print_date') }}: {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    @can('dashboard-view')
        <div class="dashboard-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="dashboard-header-title">{{ __('general.dashboard_title') }}</h1>
                        <p class="dashboard-header-subtitle">{{ __('general.dashboard_subtitle') }}</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="date-badge">
                            <i class="fas fa-calendar-alt me-2"></i>
                            <span  id="current-date">{{ now()->format('d-m-Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mb-5">
            
            <div class="filter-section">
                <div class="filter-section">
                    <form action="{{ route('progress.dashboard') }}" method="GET" id="filterForm">
                        <div class="row g-3">
                            
                            <div class="col-md-2">
                                <label for="status" class="form-label">{{ __('general.status') }}</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>
                                        {{ __('general.all') }}</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>
                                        {{ __('general.active') }}</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                        {{ __('general.completed') }}</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                        {{ __('general.pending') }}</option>
                                </select>
                            </div>

                            
                            <div class="col-md-2">
                                <label for="employee_id" class="form-label">{{ __('general.employee') }}</label>
                                <select name="employee_id" id="employee_id" class="form-select">
                                    <option value="">{{ __('general.select_employee') }}</option>
                                    @foreach ($teamMembers as $member)
                                        <option value="{{ $member->id }}"
                                            {{ request('employee_id') == $member->id ? 'selected' : '' }}>
                                            {{ $member->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            
                            <div class="col-md-2">
                                <label for="type_id" class="form-label">{{ __('general.project__type') }}</label>
                                <select name="type_id" id="type_id" class="form-select">
                                    <option value="">{{ __('general.select_type') }}</option>
                                    @foreach ($projectTypes as $type)
                                        <option value="{{ $type->id }}"
                                            {{ request('type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            
                            <div class="col-md-2">
                                <label for="project_id" class="form-label">{{ __('general.project') }}</label>
                                <select name="project_id" id="project_id" class="form-select">
                                    <option value="">{{ __('general.select_project') }}</option>
                                    @foreach ($allProjects as $proj)
                                        <option value="{{ $proj->id }}"
                                            {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                                            {{ $proj->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            
                            <div class="col-md-2">
                                <label for="item_id" class="form-label">{{ __('general.item') }}</label>
                                <select name="item_id" id="item_id" class="form-select">
                                    <option value="">{{ __('general.select_item') }}</option>
                                    @foreach ($allItems as $item)
                                        <option value="{{ $item->id }}"
                                            {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            
                            <div class="col-md-2">
                                <label for="date_range" class="form-label">{{ __('general.date_range') }}</label>
                                <input type="number" name="date_range" id="date_range" class="form-control"
                                    placeholder="{{ __('general.last_x_days') }}" value="{{ request('date_range') }}">
                            </div>

                            
                            <div class="col-md-12 text-end mt-3">
                                <button type="submit" class="btn btn-filter" id="applyFilters">
                                    <i class="fas fa-filter me-2"></i>{{ __('general.apply_filters') }}
                                </button>
                                <a href="{{ route('progress.dashboard') }}" class="btn btn-secondary ms-2">
                                    <i class="fas fa-times me-2"></i>{{ __('general.reset') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

            </div>


            
            <div class="row">
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="fas fa-box icon"></i>
                        <div class="label">{{ __('general.total_employees') }}</div>
                        <div class="value" id="itemsCount">{{$teamMembersCount }}</div>
                        <div class="footer">
                            <a href="{{ route('progress.employees.index') }}">
                                {{ __('general.view_all') }} <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="fas fa-project-diagram icon"></i>
                        <div class="label">{{ __('general.total_projects') }}</div>
                        <div class="value" id="projectsCount">{{ $projectsCount }}</div>
                        <div class="footer">
                            <a href="{{ route('progress.projects.index') }}">
                                {{ __('general.view_all') }} <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="row">
                <div class="col-lg-6">
                    <div class="progress-container">
                        <h3 class="label">{{ __('general.overall_progress') }}</h3>
                        <div class="progress-circle-container">
                            <div class="progress-circle" style="--p: {{ $progress }}%;">
                                <span class="value">{{ $progress }}%</span>
                            </div>
                        </div>
                        <p class="mb-0">{{ __('general.completed_of_all_projects') }}</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="chart-container">
                        <div class="chart-title">
                            <i class="fas fa-chart-line"></i>
                            {{ __('general.planned_vs_actual_progress') }}
                        </div>
                        <canvas id="plannedVsActualChart" height="180"></canvas>
                    </div>
                </div>
            </div>

            
            <div class="row">
                <div class="col-lg-8">
                    <div class="chart-container">
                        <div class="chart-title">
                            <i class="fas fa-chart-bar"></i>
                            {{ __('general.project_progress') }}
                        </div>
                        <canvas id="projectProgressChart" height="150"></canvas>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="chart-container">
                        <div class="chart-title">
                            <i class="fas fa-chart-pie"></i>
                            {{ __('general.project_status_distribution') }}
                        </div>
                        <canvas id="projectStatusChart" height="150"></canvas>
                    </div>
                </div>
            </div>

            
            <div class="row">
                <div class="col-12">
                    <div class="chart-container p-0">
                        <div class="chart-title p-3 border-bottom">
                            <i class="fas fa-table"></i>
                            {{ __('general.projects_overview') }}
                        </div>
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __('general.project_name') }}</th>
                                            
                                            <th>{{ __('general.start_date') }}</th>
                                            <th>{{ __('general.deadline') }}</th>
                                            <th>{{ __('general.progress') }}</th>
                                            <th>{{ __('general.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="projectsTableBody">
                                        @foreach ($projects as $project)
                                            <tr>
                                                <td class="fw-semibold">{{ $project->name }}</td>
                                                
                                                <td>{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : '—' }}
                                                </td>
                                                <td>{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('Y-m-d') : '—' }}
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 me-2">
                                                            <div class="progress-bar"
                                                                style="width: {{ $project->progress }}%">
                                                            </div>
                                                        </div>
                                                        <small class="fw-semibold">{{ $project->progress }}%</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge badge-status
                                                    @if ($project->status == 'in_progress') bg-active
                                                    @elseif($project->status == 'completed') bg-completed
                                                    @else bg-pending @endif">
                                                        {{ __("general.{$project->status}") }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize charts with initial data
                const progressCtx = document.getElementById('projectProgressChart').getContext('2d');
                const statusCtx = document.getElementById('projectStatusChart').getContext('2d');
                const plannedVsActualCtx = document.getElementById('plannedVsActualChart').getContext('2d');

                let progressChart = new Chart(progressCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($projectProgressData['labels']),
                        datasets: [{
                            label: '{{ __('general.percentage_completed') }}',
                            data: @json($projectProgressData['data']),
                            backgroundColor: 'rgba(74, 108, 247, 0.7)',
                            borderColor: 'rgba(74, 108, 247, 1)',
                            borderWidth: 1,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: '{{ __('general.project_completion_status') }}',
                                font: {
                                    size: 16
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        }
                    }
                });

                let statusChart = new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: [
                            '{{ __('general.active') }}',
                            '{{ __('general.completed') }}',
                            '{{ __('general.pending') }}'
                        ],
                        datasets: [{
                            data: @json($projectStatusData),
                            backgroundColor: [
                                'rgba(58, 179, 113, 0.7)',
                                'rgba(111, 66, 193, 0.7)',
                                'rgba(255, 193, 7, 0.7)'
                            ],
                            borderColor: [
                                'rgba(58, 179, 113, 1)',
                                'rgba(111, 66, 193, 1)',
                                'rgba(255, 193, 7, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            }
                        },
                        cutout: '60%'
                    }
                });

                // Planned vs Actual Chart
                let plannedVsActualChart = new Chart(plannedVsActualCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($plannedVsActualData['labels']),
                        datasets: [
                            {
                                label: '{{ __('general.planned_progress') }}',
                                data: @json($plannedVsActualData['planned']),
                                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1,
                                borderRadius: 4
                            },
                            {
                                label: '{{ __('general.actual_progress') }}',
                                data: @json($plannedVsActualData['actual']),
                                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1,
                                borderRadius: 4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: '{{ __('general.planned_vs_actual_comparison') }}',
                                font: {
                                    size: 16
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        }
                    }
                });

                // Filter form submission
                document.getElementById('filterForm').addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Show loading state
                    const button = document.getElementById('applyFilters');
                    const originalText = button.innerHTML;
                    button.innerHTML =
                        '<span class="loading-spinner me-2"></span> {{ __('general.filtering') }}';
                    button.disabled = true;

                    // Collect form data
                    const formData = new FormData(this);
                    const params = new URLSearchParams(formData);

                    // Send AJAX request
                    fetch('{{ route('progress.dashboard') }}?' + params.toString(), {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.error) {
                                throw new Error(data.message);
                            }
                            
                            // Update stats cards
                            document.getElementById('itemsCount').textContent = data.itemsCount;
                            if (document.getElementById('dailyFormsCount')) {
                                document.getElementById('dailyFormsCount').textContent = data.dailyFormsCount;
                            }
                            document.getElementById('projectsCount').textContent = data.projectsCount;

                            // Update progress circle
                            const progressCircle = document.querySelector('.progress-circle');
                            progressCircle.style.setProperty('--p', data.progress + '%');
                            document.querySelector('.progress-circle .value').textContent = data.progress +
                                '%';

                            // Update charts
                            progressChart.data.labels = data.projectProgressData.labels;
                            progressChart.data.datasets[0].data = data.projectProgressData.data;
                            progressChart.update();

                            statusChart.data.datasets[0].data = data.projectStatusData;
                            statusChart.update();

                            // Update planned vs actual chart
                            plannedVsActualChart.data.labels = data.plannedVsActualData.labels;
                            plannedVsActualChart.data.datasets[0].data = data.plannedVsActualData.planned;
                            plannedVsActualChart.data.datasets[1].data = data.plannedVsActualData.actual;
                            plannedVsActualChart.update();

                            // Update projects table
                            let tableBody = '';
                            data.projects.forEach(project => {
                                tableBody += `
                        <tr>
                            <td class="fw-semibold">${project.name}</td>
                            <td>${project.start_date ? new Date(project.start_date).toLocaleDateString() : '—'}</td>
                            <td>${project.end_date ? new Date(project.end_date).toLocaleDateString() : '—'}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2">
                                        <div class="progress-bar" style="width: ${project.progress}%"></div>
                                    </div>
                                    <small class="fw-semibold">${project.progress}%</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-status ${getStatusClass(project.status)}">
                                    ${getStatusText(project.status)}
                                </span>
                            </td>
                        </tr>
                    `;
                            });
                            document.getElementById('projectsTableBody').innerHTML = tableBody;

                            // Update URL without reloading page
                            window.history.replaceState({}, '', '{{ route('progress.dashboard') }}?' + params
                                .toString());

                            // Restore button state
                            button.innerHTML = originalText;
                            button.disabled = false;
                        })
                        .catch(error => {
                            console.error('Filter Error:', error);
                            button.innerHTML = originalText;
                            button.disabled = false;
                            alert('An error occurred while filtering: ' + error.message);
                        });
                });

                function getStatusClass(status) {
                    switch (status) {
                        case 'active':
                            return 'bg-active';
                        case 'completed':
                            return 'bg-completed';
                        case 'pending':
                            return 'bg-pending';
                        default:
                            return '';
                    }
                }

                function getStatusText(status) {
                    switch (status) {
                        case 'active':
                            return '{{ __('general.active') }}';
                        case 'completed':
                            return '{{ __('general.completed') }}';
                        case 'pending':
                            return '{{ __('general.pending') }}';
                        default:
                            return status;
                    }
                }

                // Update current date with locale format
                function updateCurrentDate() {
                    const now = new Date();
                    const options = {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    };
                    document.getElementById('current-date').textContent = now.toLocaleDateString(
                        '{{ app()->getLocale() }}', options);
                }

                updateCurrentDate();
            });
        </script>
    @endcan
@endsection
