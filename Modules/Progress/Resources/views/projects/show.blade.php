@extends('progress::layouts.app')
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-muted text-decoration-none">
            {{ __('general.dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('progress.projects.index') }}" class="text-muted text-decoration-none">
            {{ __('general.projects') }}
        </a>
    </li>
@endsection

@section('title', __('general.project_dashboard'))

@section('content')
<style>
:root {
    --primary: #3498db;
    --primary-dark: #2980b9;
    --secondary: #2c3e50;
    --secondary-light: #34495e;
    --success: #27ae60;
    --success-light: #2ecc71;
    --warning: #f39c12;
    --warning-light: #f1c40f;
    --danger: #e74c3c;
    --danger-light: #e67e22;
    --light: #f8f9fa;
    --light-gray: #ecf0f1;
    --dark: #2d3748;
    --dark-light: #4a5568;
    --purple: #9b59b6;
    --pink: #e84393;
    --teal: #1abc9c;
    --cyan: #00c9ff;
    --gray-100: #f8f9fa;
    --gray-200: #e9ecef;
    --gray-300: #dee2e6;
    --gray-400: #ced4da;
    --gray-500: #adb5bd;
    --gray-600: #6c757d;
    --gray-700: #495057;
    --gray-800: #343a40;
    --gray-900: #212529;
    --box-shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
    --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    --box-shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
    --border-radius: 8px;
    --border-radius-lg: 12px;
    --border-radius-xl: 16px;
    --font-family-sans-serif: 'Tajawal', 'Segoe UI', system-ui, -apple-system, sans-serif;
    --transition: all 0.3s ease;
}

.project-dashboard {
    background-color: #f8f9fa;
    font-family: var(--font-family-sans-serif);
    color: var(--gray-800);
    line-height: 1.6;
}


.dashboard-header {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    padding: 2rem 0;
    border-bottom: none;
    box-shadow: var(--box-shadow);
    position: relative;
    overflow: hidden;
}

.dashboard-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 200%;
    background: rgba(255, 255, 255, 0.1);
    transform: rotate(-5deg);
}

.dashboard-header h1 {
    font-weight: 800;
    letter-spacing: -0.5px;
    margin-bottom: 0.25rem;
}

.dashboard-header p {
    opacity: 0.9;
    font-weight: 300;
}


.stat-card {
    border-radius: var(--border-radius-lg);
    box-shadow: var(--box-shadow-sm);
    transition: var(--transition);
    height: 100%;
    border: none;
    overflow: hidden;
    margin-bottom: 1.5rem;
    background: white;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--box-shadow);
}

.stat-card .card-body {
    display: flex;
    align-items: center;
    padding: 1.5rem;
}

.stat-icon {
    font-size: 1.75rem;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    margin-left: 15px;
    flex-shrink: 0;
    box-shadow: var(--box-shadow-sm);
}

.stat-card .card-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--gray-600);
    margin-bottom: 0.5rem;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.stat-card h3 {
    font-weight: 700;
    font-size: 1.75rem;
    margin-bottom: 0;
    color: var(--dark);
}


.chart-container {
    position: relative;
    height: 300px;
    margin: 1rem 0;
}


.progress-container {
    background: white;
    border-radius: var(--border-radius);
    padding: 1.25rem;
    box-shadow: var(--box-shadow-sm);
    border: 1px solid var(--gray-200);
    margin-bottom: 1.25rem;
    transition: var(--transition);
}

.progress-container:hover {
    box-shadow: var(--box-shadow);
}

.progress-bar-animated {
    position: relative;
    overflow: hidden;
    border-radius: 100px;
    height: 20px;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
}

.progress-bar-animated::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    animation: progressGlow 2s infinite;
    border-radius: inherit;
}

@keyframes progressGlow {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.progress-label {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-weight: 700;
    font-size: 0.8rem;
    color: white;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}


.section-title {
    position: relative;
    padding-bottom: 0.75rem;
    margin-bottom: 1.5rem;
    font-weight: 700;
    color: var(--dark);
    font-size: 1.35rem;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 4px;
    background: linear-gradient(to right, var(--primary), var(--teal));
    border-radius: 10px;
}


.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.85rem;
    box-shadow: var(--box-shadow-sm);
    letter-spacing: 0.5px;
}


.table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    color: var(--gray-700);
    border-radius: var(--border-radius);
    overflow: hidden;
}

.table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
    border-top: 1px solid var(--gray-300);
    padding: 0.9rem 0.75rem;
    background-color: var(--gray-100);
}

.table td {
    padding: 1rem 0.75rem;
    border-top: 1px solid var(--gray-200);
    vertical-align: middle;
}

.table tbody tr {
    transition: var(--transition);
}

.table tbody tr:hover {
    background-color: rgba(52, 152, 219, 0.03);
}

.table-hover tbody tr:hover {
    background-color: rgba(52, 152, 219, 0.05);
}

.pivot-highlight {
    background-color: rgba(52, 152, 219, 0.08);
    font-weight: 600;
}

.pivot-highlight td {
    border-top: 2px solid var(--primary);
    border-bottom: 2px solid var(--primary);
}


.timeline-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.25rem;
}

.timeline-item {
    background: white;
    border-radius: var(--border-radius-lg);
    padding: 1.25rem;
    box-shadow: var(--box-shadow-sm);
    border-left: 4px solid var(--success);
    position: relative;
    transition: var(--transition);
}

.timeline-item:hover {
    transform: translateY(-3px);
    box-shadow: var(--box-shadow);
}

.timeline-item::before {
    content: '';
    position: absolute;
    top: 1.25rem;
    left: -10px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--success);
    border: 4px solid white;
    box-shadow: 0 0 0 2px var(--success);
}

.timeline-date {
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}


.list-group-item {
    border-color: var(--gray-200);
    padding: 0.9rem 0;
}


.client-card {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: linear-gradient(to right, #f8f9fa, white);
    border-radius: var(--border-radius);
    margin-bottom: 1.5rem;
}

.client-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--light);
    border-radius: 50%;
    font-size: 1.5rem;
    color: var(--primary);
    margin-left: 15px;
    box-shadow: var(--box-shadow-sm);
}


@media (max-width: 768px) {
    .dashboard-header {
        padding: 1.5rem 0;
    }

    .dashboard-header h1 {
        font-size: 1.5rem;
    }

    .stat-card .card-body {
        padding: 1.25rem;
        flex-direction: column;
        text-align: center;
    }

    .stat-icon {
        margin-left: 0;
        margin-bottom: 1rem;
    }

    .section-title {
        font-size: 1.2rem;
    }

    .table-responsive {
        border-radius: var(--border-radius);
        border: 1px solid var(--gray-200);
    }

    .timeline-container {
        grid-template-columns: 1fr;
    }

    .client-card {
        flex-direction: column;
        text-align: center;
    }

    .client-icon {
        margin-left: 0;
        margin-bottom: 1rem;
    }
}


@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.stat-card {
    animation: fadeIn 0.5s ease-out forwards;
}

.stat-card:nth-child(2) { animation-delay: 0.1s; }
.stat-card:nth-child(3) { animation-delay: 0.2s; }
.stat-card:nth-child(4) { animation-delay: 0.3s; }


body[dir="rtl"] .stat-icon {
    margin-right: 15px;
    margin-left: 0;
}

body[dir="rtl"] .progress-bar-animated::after {
    animation: progressGlowRTL 2s infinite;
}

@keyframes progressGlowRTL {
    0% { transform: translateX(100%); }
    100% { transform: translateX(-100%); }
}

body[dir="rtl"] .section-title::after {
    left: auto;
    right: 0;
}

body[dir="rtl"] .timeline-item {
    border-left: none;
    border-right: 4px solid var(--success);
}

body[dir="rtl"] .timeline-item::before {
    left: auto;
    right: -10px;
}

body[dir="rtl"] .client-icon {
    margin-right: 15px;
    margin-left: 0;
}


::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: var(--gray-100);
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: var(--gray-400);
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--gray-500);
}


a:focus,
button:focus,
.btn:focus,
.form-control:focus,
.form-check-input:focus {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}


@media print {
    .project-dashboard {
        background: white !important;
        color: black !important;
    }

    .stat-card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }

    .dashboard-header {
        background: #f1f1f1 !important;
        color: black !important;
        box-shadow: none !important;
    }
}
</style>

<div class="project-dashboard">
    
    
    <div class="dashboard-header">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                <div class="text-center text-md-start mb-3 mb-md-0">
                    <h1 class="h3 mb-1">{{ $project->name }}</h1>
                </div>
                <div class="text-center text-md-end">
                    <div class="mb-2">
                        <span class="badge bg-success status-badge">
                            @if($project->status == 'active')
                                {{ __('general.active') }}
                            @elseif($project->status == 'completed')
                                {{ __('general.completed') }}
                            @else
                                {{ __('general.suspended') }}
                            @endif
                        </span>
                    </div>
                    
                    <div class="mb-2">
                        <a href="{{ route('progress.projects.edit', $project->id) }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-edit me-1"></i>
                            {{ __('general.edit') }}
                        </a>
                    </div>
                    
                    <div class="mb-2">
                        <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#saveAsTemplateModal">
                            <i class="fas fa-save me-1"></i>
                            {{ __('general.save_as_template') }}
                        </button>
                    </div>
                    <div class="mt-2">
                        <small>{{ __('general.last_updated') }}: {{ \Carbon\Carbon::now()->format('d-m-Y H:i') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="stat-card card bg-light h-100">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-primary text-white">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="card-title text-muted mb-1">{{ __('general.work_items') }}</h5>
                                <h3 class="mb-0">{{ count($project->items) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="stat-card card bg-light h-100">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-success text-white">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="card-title text-muted mb-1">{{ __('general.overall_progress') }}</h5>
                                <h3 class="mb-0">{{ number_format($overallProgress, 1) }}%</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="stat-card card bg-light h-100">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-warning text-white">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="card-title text-muted mb-1">{{ __('general.days_remaining') }}</h5>
                                <h3 class="mb-0">
                                    @if($project->end_date)
                                        {{ floor(\Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($project->end_date))) }}
                                    @else
                                        {{ __('general.not_specified') }}
                                    @endif
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="stat-card card bg-light h-100">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-info text-white">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="card-title text-muted mb-1">{{ __('general.days_passed') }}</h5>
                                <h3 class="mb-0">
                                    @if($project->start_date)
                                        {{  abs(floor(\Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($project->start_date)))) }}
                                    @else
                                        {{ __('general.not_specified') }}
                                    @endif
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <h4 class="section-title">{{ __('general.project_progress_overview') }}</h4>
                        <div class="chart-container">
                            <canvas id="progressChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <h4 class="section-title">{{ __('general.progress_distribution') }}</h4>
                        <div class="chart-container">
                            <canvas id="donutChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="section-title mb-0">{{ __('general.work_items_summary') }}</h4>
                            <span class="badge bg-primary">{{ count($project->items) }} {{ __('general.items') }}</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('general.work_item') }}</th>
                                        <th>{{ __('general.predecessor') }}</th>
                                        <th class="text-end">{{ __('general.progress') }}</th>
                                        <th class="text-end">{{ __('general.completed') }}</th>
                                        <th class="text-end">{{ __('general.remaining') }}</th>
                                        <th class="text-end">{{ __('general.expected_daily_progress') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($project->items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">
                                                {{ $item->workItem->name }}
                                                @if($item->item_label)
                                                    <span class="badge bg-info ms-2">{{ $item->item_label }}</span>
                                                @endif
                                            </div>
                                            @if($item->workItem->category)
                                                <small class="text-muted d-block"><i class="fas fa-folder me-1"></i>{{ $item->workItem->category->name }}</small>
                                            @endif
                                            <div class="progress mt-2" style="height: 8px;">
                                                <div class="progress-bar
                                                    @if($item->completion_percentage >= 80) bg-success
                                                    @elseif($item->completion_percentage >= 50) bg-primary
                                                    @elseif($item->completion_percentage >= 30) bg-warning
                                                    @else bg-danger
                                                    @endif"
                                                    role="progressbar"
                                                    style="width: {{ $item->completion_percentage }}%">
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            @if($item->predecessor)
                                                @php
                                                    $predecessorItem = $project->items->find($item->predecessor);
                                                @endphp
                                                @if($predecessorItem)
                                                    <span class="badge bg-secondary">{{ $predecessorItem->workItem->name }}</span>
                                                    @if($item->dependency_type == 'start_to_start')
                                                        <small class="text-muted d-block">{{ __('general.start_to_start') }}</small>
                                                    @else
                                                        <small class="text-muted d-block">{{ __('general.end_to_start') }}</small>
                                                    @endif
                                                    @if($item->lag != 0)
                                                        <small class="text-info d-block">{{ __('general.lag') }}: {{ $item->lag }} {{ __('general.days') }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">{{ __('general.predecessor_not_found') }}</span>
                                                @endif
                                            @else
                                                <span class="text-muted">{{ __('general.none') }}</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold text-end align-middle">{{ number_format($item->completion_percentage, 1) }}%</td>
                                        <td class="text-end align-middle">{{ number_format($item->completed_quantity) }} {{ $item->workItem->unit }}</td>
                                        <td class="text-end align-middle">{{ number_format($item->remaining_quantity) }} {{ $item->workItem->unit }}</td>
                                        <td class="text-end align-middle">
                                            @if($item->estimated_daily_qty)
                                                <span class="badge bg-info">{{ number_format($item->estimated_daily_qty, 2) }} {{ $item->workItem->unit }}/{{ __('general.day') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-group-divider">
                                    <tr class="pivot-highlight">
                                        <td><strong>{{ __('general.total') }}</strong></td>
                                        <td></td>
                                        <td class="fw-bold text-end">{{ number_format($overallProgress, 1) }}%</td>
                                        <td class="text-end">{{ number_format($project->items->sum('completed_quantity')) }}</td>
                                        <td class="text-end">{{ number_format($project->items->sum('remaining_quantity')) }}</td>
                                        <td class="text-end">
                                            <span class="badge bg-secondary">{{ number_format($project->items->sum('estimated_daily_qty'), 2) }}</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="section-title mb-0">{{ __('general.daily_progress_record') }}</h4>
                            <span class="badge bg-info">{{ $project->dailyProgress->count() }} {{ __('general.records') }}</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('general.date') }}</th>
                                        <th>{{ __('general.contact_person') }}</th>
                                        <th>{{ __('general.work_item') }}</th>
                                        <th class="text-end">{{ __('general.quantity') }}</th>
                                        <th class="text-center">{{ __('general.percentage') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($project->dailyProgress->sortByDesc('progress_date')->take(5) as $progress)
                                    <tr>
                                        <td class="fw-bold">{{ \Carbon\Carbon::parse($progress->progress_date)->format('Y-m-d') }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $progress->employee->name }}</span>
                                        </td>
                                        <td class="small">{{ $progress->projectItem->workItem->name }}</td>
                                        <td class="text-end">{{ number_format($progress->quantity) }} {{ $progress->projectItem->workItem->unit }}</td>
                                        <td class="text-center">
                                            <span class="badge
                                                @php
                                                    $percent = $progress->projectItem->total_quantity > 0 
                                                        ? ($progress->quantity / $progress->projectItem->total_quantity) * 100 
                                                        : 0;
                                                    if($percent >= 10) echo 'bg-success';
                                                    elseif($percent >= 5) echo 'bg-primary';
                                                    else echo 'bg-warning';
                                                @endphp
                                            ">
                                                {{ number_format($percent, 1) }}%
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

        
        <div class="card stat-card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="section-title mb-1">{{ __('general.work_items_progress') }}</h4>
                        <p class="text-muted mb-0">{{ __('general.detailed_progress_each_item') }}</p>
                    </div>
                    <div class="badge bg-purple">{{ __('general.visual_progress') }}</div>
                </div>

                <div class="row">
                    @foreach($project->items as $item)
                    <div class="col-md-6">
                        <div class="progress-container">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <span class="fw-bold">{{ $item->workItem->name }}</span>
                                    <span class="badge bg-light text-dark ms-2">{{ $item->workItem->unit }}</span>
                                </div>
                                <span class="fw-bold">{{ number_format($item->completion_percentage, 1) }}%</span>
                            </div>
                            <div class="progress" style="position: relative;">
                                <div class="progress-bar
                                    @if($item->completion_percentage >= 80) bg-success
                                    @elseif($item->completion_percentage >= 50) bg-primary
                                    @elseif($item->completion_percentage >= 30) bg-warning
                                    @else bg-danger
                                    @endif
                                    progress-bar-animated"
                                    role="progressbar"
                                    style="width: {{ $item->completion_percentage }}%">
                                </div>
                                <div class="progress-label">{{ number_format($item->completion_percentage, 1) }}%</div>
                            </div>
                            <div class="d-flex justify-content-between mt-2 text-muted small">
                                <span>{{ __('general.completed') }}: {{ number_format($item->completed_quantity) }}</span>
                                <span>{{ __('general.remaining') }}: {{ number_format($item->remaining_quantity) }}</span>
                                <span>{{ __('general.total') }}: {{ number_format($item->total_quantity) }}</span>
                            </div>
                            @if($item->estimated_daily_qty)
                                <div class="mt-2">
                                    <span class="badge bg-info">
                                        <i class="fas fa-chart-line me-1"></i>
                                        {{ __('general.expected_daily') }}: {{ number_format($item->estimated_daily_qty, 2) }} {{ $item->workItem->unit }}/{{ __('general.day') }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        
        <div class="card stat-card mb-4">
            <div class="card-body">
                <h4 class="section-title">{{ __('general.project_timeline') }}</h4>
                <p class="text-muted mb-4">{{ __('general.key_dates_milestones') }}</p>

                <div class="timeline-container">
                    <div class="timeline-item">
                        <div class="timeline-date">{{ __('general.start_date') }}</div>
                        <div class="fw-bold">{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : __('general.not_specified') }}</div>
                        <div class="text-muted small">{{ __('general.project_kickoff') }}</div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-date">{{ __('general.mid_point') }}</div>
                        @if($project->start_date && $project->end_date)
                            @php
                                $midDate = \Carbon\Carbon::parse($project->start_date)
                                    ->addDays(\Carbon\Carbon::parse($project->start_date)
                                    ->diffInDays(\Carbon\Carbon::parse($project->end_date)) / 2);
                            @endphp
                            <div class="fw-bold">{{ $midDate->format('Y-m-d') }}</div>
                        @else
                            <div class="fw-bold">{{ __('general.not_specified') }}</div>
                        @endif
                        <div class="text-muted small">{{ __('general.expected_50_completion') }}</div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-date">{{ __('general.current_date') }}</div>
                        <div class="fw-bold">{{ \Carbon\Carbon::now()->format('Y-m-d') }}</div>
                        <div class="text-muted small">{{ __('general.progress') }}: {{ number_format($overallProgress, 1) }}%</div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-date">{{ __('general.end_date') }}</div>
                        <div class="fw-bold">{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('Y-m-d') : __('general.not_specified') }}</div>
                        <div class="text-muted small">{{ __('general.expected_completion') }}</div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <h4 class="section-title">{{ __('general.project_information') }}</h4>
                        <p class="mb-4">{{ $project->description }}</p>

                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ __('general.start_date') }}:</strong>
                                    <span>{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : __('general.not_specified') }}</span>
                                </div>
                            </div>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ __('general.end_date') }}:</strong>
                                    <span>{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('Y-m-d') : __('general.not_specified') }}</span>
                                </div>
                            </div>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ __('general.project_duration') }}:</strong>
                                    <span>
                                        @if($project->start_date && $project->end_date)
                                            {{ \Carbon\Carbon::parse($project->start_date)->diffInDays(\Carbon\Carbon::parse($project->end_date)) }} {{ __('general.days') }}
                                        @else
                                            {{ __('general.not_specified') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ __('general.working_zone') }}:</strong>
                                    <span>{{ $project->working_zone }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <h4 class="section-title">{{ __('general.client_information') }}</h4>

                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-light p-3 rounded-circle me-3">
                                <i class="fas fa-building fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $project->client->cname }}</h5>
                                <p class="text-muted mb-0">{{ $project->client->contact_person }}</p>
                            </div>
                        </div>

                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-phone me-3 text-success"></i>
                                    <span>{{ $project->client->phone }}</span>
                                </div>
                            </div>
                            <div class="list-group-item px-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-envelope me-3 text-info"></i>
                                    <span>{{ $project->client->email }}</span>
                                </div>
                            </div>
                            <div class="list-group-item px-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-map-marker-alt me-3 text-danger"></i>
                                    <span>{{ $project->client->address ?? __('general.not_specified') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="saveAsTemplateModal" tabindex="-1" aria-labelledby="saveAsTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="saveAsTemplateForm" action="{{ route('progress.projects.save-as-template', $project) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="saveAsTemplateModalLabel">
                        <i class="fas fa-save me-2"></i>
                        {{ __('general.save_as_template') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="template_name" class="form-label">{{ __('general.template_name') }}</label>
                        <input type="text" class="form-control" id="template_name" name="template_name" 
                               value="{{ $project->name }} - Template" required>
                    </div>
                    <div class="mb-3">
                        <label for="template_description" class="form-label">{{ __('general.description') }}</label>
                        <textarea class="form-control" id="template_description" name="template_description" rows="3"
                                  placeholder="{{ __('general.template_description_placeholder') }}">{{ $project->description }}</textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('general.template_save_info') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('general.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveTemplateBtn">
                        <i class="fas fa-save me-1"></i>
                        {{ __('general.save_template') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing save-as-template form');
    
    const form = document.getElementById('saveAsTemplateForm');
    const submitBtn = document.getElementById('saveTemplateBtn');
    const modal = document.getElementById('saveAsTemplateModal');
    
    console.log('Form element:', form);
    console.log('Submit button:', submitBtn);
    console.log('Modal element:', modal);
    
    if (form) {
        console.log('Form found, adding event listener');
        
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            console.log('Form submission started (AJAX)');
            console.log('Form action:', form.action);
            console.log('Form method:', form.method);
            
            // Validate form data
            const templateName = document.getElementById('template_name').value;
            if (!templateName || templateName.trim() === '') {
                alert('{{ __('general.please_enter_template_name') }}');
                return false;
            }
            
            // Disable submit button to prevent double submission
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __('general.saving') }}';
            }
            
            // Prepare form data
            const formData = new FormData(form);
            console.log('Form data:');
            for (let [key, value] of formData.entries()) {
                console.log(key + ': ' + value);
            }
            
            // Send AJAX request
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Response received:', response);
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    // Success - close modal and redirect or show success message
                    const modalElement = bootstrap.Modal.getInstance(modal);
                    if (modalElement) {
                        modalElement.hide();
                    }
                    
                    // Show success message
                    alert('{{ __('general.template_saved_successfully') }}');
                    
                    // Redirect if URL provided
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else {
                        // Reload page
                        window.location.reload();
                    }
                } else {
                    // Show error message
                    alert('{{ __('general.error_occurred_with_message') }}: ' + (data.message || '{{ __('general.unknown_error_occurred') }}'));
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('{{ __('general.connection_error') }}: ' + error.message);
            })
            .finally(() => {
                // Re-enable submit button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>{{ __('general.save_template') }}';
                }
            });
        });
        
        // Add error handling for form submission
        form.addEventListener('error', function(e) {
            console.error('Form error:', e);
            alert('{{ __('general.form_submission_error') }}');
        });
    } else {
        console.error('Save as template form not found!');
    }
    
    // Add modal event listeners for debugging
    if (modal) {
        modal.addEventListener('show.bs.modal', function() {
            console.log('Modal is being shown');
        });
        
        modal.addEventListener('shown.bs.modal', function() {
            console.log('Modal is fully shown');
        });
        
        modal.addEventListener('hide.bs.modal', function() {
            console.log('Modal is being hidden');
        });
    }
    
    // Global error handler
    window.addEventListener('error', function(e) {
        console.error('Global error:', e.error);
        console.error('Error message:', e.message);
        console.error('Error filename:', e.filename);
        console.error('Error line:', e.lineno);
    });
    
    // Check if user has permission
    console.log('Checking user permissions...');
    @can('projects-save-as-template')
        console.log('User has projects-save-as-template permission');
    @else
        console.log('User does NOT have projects-save-as-template permission');
    @endcan
});
</script>
@endsection
