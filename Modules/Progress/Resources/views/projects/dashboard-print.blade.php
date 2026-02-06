@extends('progress::layouts.print')

@section('title', __('general.project_dashboard') . ' - ' . $project->name)

@php
// Helper to check if component is visible
$isComponentVisible = function($component, $visibleComponents) {
    // Default to true if not specified (show all components)
    return !isset($visibleComponents[$component]) || $visibleComponents[$component] === true;
};
@endphp

@section('content')
<style>
    .project-dashboard {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .dashboard-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 10px;
        margin-bottom: 2rem;
    }
    .stat-card {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        background: #fff;
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }
    .table-responsive {
        margin-bottom: 2rem;
    }
    .print-table {
        font-size: 0.9rem;
    }
    @media print {
        @page {
            margin: 1cm;
        }
        .no-print {
            display: none !important;
        }
        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .stat-card {
            page-break-inside: avoid;
        }
    }
</style>

<div class="project-dashboard">
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="h3 mb-2">{{ __('general.project_dashboard') }}</h1>
                <p class="mb-0">{{ $project->name }}@if($project->client) - {{ $project->client->cname }}@endif</p>
            </div>
            <div class="col-md-4 text-md-end">
                <span class="badge bg-{{ $projectStatus['color'] }}">
                    <i class="fas fa-{{ $projectStatus['icon'] }} me-1"></i>
                    {{ $projectStatus['message'] }}
                </span>
                <div class="mt-2">
                    <small>{{ __('general.last_updated') }}: {{ now()->format('d/m/Y H:i') }}</small>
                </div>
            </div>
        </div>
    </div>

    @if($isComponentVisible('statistics-cards', $visibleComponents ?? []))
    <div class="row g-4 mb-4">
        <div class="col-md-4">
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
                        <div class="progress-bar bg-primary" style="width: {{ min($overallProgress, 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
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

        <div class="col-md-4">
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
    @endif

    @if($isComponentVisible('total-weighted-progress', $visibleComponents ?? []) && $project->subprojects->count() > 0 && isset($totalWeightedProgress))
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="mb-3"><i class="fas fa-chart-line me-2"></i>{{ __('general.total_weighted_progress') }}</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-semibold">{{ __('general.actual_progress') }}:</span>
                            <span class="fw-bold" style="font-size: 2rem;">{{ number_format($totalWeightedProgress, 1) }}%</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-semibold">{{ __('general.planned_progress') }}:</span>
                            <span class="fw-bold" style="font-size: 2rem;">{{ number_format($totalPlannedWeightedProgress ?? 0, 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($isComponentVisible('bar-chart', $visibleComponents ?? []))
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="fw-bold mb-3"><i class="fas fa-chart-bar me-2"></i>{{ __('general.project_progress_overview') }}</h5>
                @if(isset($chartData['work_items']) && count($chartData['work_items']) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered print-table">
                            <thead>
                                <tr>
                                    <th>{{ __('general.work_items') }}</th>
                                    <th>{{ __('general.progress') }} (%)</th>
                                    <th>{{ __('general.planned_progress') }} (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($chartData['work_items'] as $index => $workItem)
                                    <tr>
                                        <td>{{ $workItem }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ number_format($chartData['weighted_ratios'][$index] ?? 0, 1) }}%</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ number_format($chartData['planned_weighted_ratios'][$index] ?? 0, 1) }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">{{ __('general.no_items_found') }}</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($isComponentVisible('subprojects-chart', $visibleComponents ?? []))
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="fw-bold mb-3"><i class="fas fa-sitemap me-2"></i>{{ __('general.subprojects_progress') }}</h5>
                @if(isset($subprojectsChartData['labels']) && count($subprojectsChartData['labels']) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered print-table">
                            <thead>
                                <tr>
                                    <th>{{ __('general.subproject') }}</th>
                                    <th>{{ __('general.progress') }} (%)</th>
                                    <th>{{ __('general.planned_progress') }} (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subprojectsChartData['labels'] as $index => $label)
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ number_format($subprojectsChartData['completion_percentages'][$index] ?? 0, 1) }}%</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ number_format($subprojectsChartData['planned_weighted_ratios'][$index] ?? 0, 1) }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">{{ __('general.no_subprojects_found') }}</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($isComponentVisible('categories-chart', $visibleComponents ?? []))
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="fw-bold mb-3"><i class="fas fa-tags me-2"></i>{{ __('general.categories_progress') }}</h5>
                @if(isset($categoriesChartData['labels']) && count($categoriesChartData['labels']) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered print-table">
                            <thead>
                                <tr>
                                    <th>{{ __('general.category') }}</th>
                                    <th>{{ __('general.progress') }} (%)</th>
                                    <th>{{ __('general.planned_progress') }} (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categoriesChartData['labels'] as $index => $label)
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ number_format($categoriesChartData['completion_percentages'][$index] ?? 0, 1) }}%</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ number_format($categoriesChartData['planned_weighted_ratios'][$index] ?? 0, 1) }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">{{ __('general.no_items_found') }}</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($isComponentVisible('subproject-items-chart', $visibleComponents ?? []))
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="fw-bold mb-3"><i class="fas fa-list me-2"></i>{{ __('general.items_by_subproject') }}</h5>
                @if(isset($subprojectItemsChartData) && count($subprojectItemsChartData) > 0)
                    @foreach($subprojectItemsChartData as $subprojectName => $data)
                        <h6 class="fw-bold mt-3 mb-2">{{ $subprojectName }}</h6>
                        @if(isset($data['labels']) && count($data['labels']) > 0)
                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-bordered print-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('general.work_items') }}</th>
                                            <th>{{ __('general.progress') }} (%)</th>
                                            <th>{{ __('general.planned_progress') }} (%)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['labels'] as $index => $label)
                                            <tr>
                                                <td>{{ $label }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary">{{ number_format($data['completion_percentages'][$index] ?? 0, 1) }}%</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info">{{ number_format($data['planned_weighted_ratios'][$index] ?? 0, 1) }}%</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endforeach
                @else
                    <p class="text-muted">{{ __('general.no_items_found') }}</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($isComponentVisible('category-items-chart', $visibleComponents ?? []))
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="fw-bold mb-3"><i class="fas fa-folder me-2"></i>{{ __('general.items_by_category') }}</h5>
                @if(isset($categoryItemsChartData) && count($categoryItemsChartData) > 0)
                    @foreach($categoryItemsChartData as $categoryName => $data)
                        <h6 class="fw-bold mt-3 mb-2">{{ $categoryName }}</h6>
                        @if(isset($data['labels']) && count($data['labels']) > 0)
                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-bordered print-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('general.work_items') }}</th>
                                            <th>{{ __('general.progress') }} (%)</th>
                                            <th>{{ __('general.planned_progress') }} (%)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['labels'] as $index => $label)
                                            <tr>
                                                <td>{{ $label }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary">{{ number_format($data['completion_percentages'][$index] ?? 0, 1) }}%</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info">{{ number_format($data['planned_weighted_ratios'][$index] ?? 0, 1) }}%</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endforeach
                @else
                    <p class="text-muted">{{ __('general.no_items_found') }}</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($isComponentVisible('client-info', $visibleComponents ?? []))
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="fw-bold mb-3"><i class="fas fa-building me-2"></i>{{ __('general.client_information') }}</h5>
                @if($project->client)
                    <p class="mb-2"><strong>{{ __('general.client_name') }}:</strong> {{ $project->client->cname }}</p>
                    @if($project->client->contact_person)
                        <p class="mb-2"><strong>{{ __('general.contact_person') }}:</strong> {{ $project->client->contact_person }}</p>
                    @endif
                    @if($project->client->phone)
                        <p class="mb-2"><strong>{{ __('general.phone') }}:</strong> {{ $project->client->phone }}</p>
                    @endif
                    @if($project->client->email)
                        <p class="mb-2"><strong>{{ __('general.email') }}:</strong> {{ $project->client->email }}</p>
                    @endif
                    @if($project->client->address)
                        <p class="mb-2"><strong>{{ __('general.address') }}:</strong> {{ $project->client->address }}</p>
                    @endif
                @else
                    <p class="text-muted">{{ __('general.no_client_assigned') }}</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($isComponentVisible('employees-info', $visibleComponents ?? []))
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="fw-bold mb-3"><i class="fas fa-users me-2"></i>{{ __('general.team_members') }}</h5>
                @forelse($project->employees as $employee)
                    @php
                    $employeeProgress = $employee
                        ->dailyProgress()
                        ->whereHas('projectItem', function ($query) use ($project) {
                            $query->where('project_id', $project->id);
                        })
                        ->sum('quantity');
                    @endphp
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <div class="ms-3">
                            <div class="fw-bold">{{ $employee->name }}</div>
                            <div class="small text-muted">{{ $employee->position ?? __('general.employee') }}</div>
                            <div class="mt-2">
                                <span class="badge bg-primary">{{ $employeeProgress }} {{ __('general.units') }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">{{ __('general.no_employees_assigned') }}</p>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    @if($isComponentVisible('recent-activity', $visibleComponents ?? []))
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="fw-bold mb-3"><i class="fas fa-history me-2"></i>{{ __('general.daily_progress_record') }}</h5>
                @if(isset($recentProgress) && $recentProgress->count() > 0)
                    @foreach ($recentProgress as $date => $progresses)
                        <div class="mb-4 border-bottom pb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
                                <span class="badge bg-light text-dark">
                                    {{ $progresses->sum('quantity') }} {{ __('general.units') }}
                                </span>
                            </div>
                            @foreach ($progresses as $progress)
                                <div class="d-flex align-items-center mb-2 p-2 bg-light rounded">
                                    <div class="flex-grow-1 ms-3">
                                        <div class="fw-medium">{{ $progress->employee->name ?? __('general.unknown') }}</div>
                                        <div class="fw-semibold text-dark">{{ $progress->projectItem->workItem->name ?? '-' }}</div>
                                        @if($progress->projectItem->workItem->category ?? null)
                                            <small class="text-muted d-block"><i class="fas fa-folder me-1"></i>{{ $progress->projectItem->workItem->category->name }}</small>
                                        @endif
                                        @if($progress->projectItem->notes ?? null)
                                            <small class="text-muted d-block"><i class="fas fa-sticky-note me-1"></i>{{ Str::limit($progress->projectItem->notes, 30) }}</small>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-primary">{{ $progress->quantity }}</div>
                                        <small class="text-muted">{{ $progress->projectItem->workItem->unit ?? '' }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">{{ __('general.no_items_found') }}</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($isComponentVisible('timeline', $visibleComponents ?? []))
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="fw-bold mb-3"><i class="fas fa-project-diagram me-2"></i>{{ __('general.project_timeline') }}</h5>
                <div class="mb-4">
                    @if($project->start_date)
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="fw-bold text-primary">{{ __('general.start_date') }}</div>
                            <div class="text-muted">{{ \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') }}</div>
                            <small class="text-success">{{ __('general.project_kickoff') }}</small>
                        </div>
                    @endif

                    <div class="mb-3 pb-3 border-bottom">
                        <div class="fw-bold text-primary">{{ __('general.current_date') }}</div>
                        <div class="text-muted">{{ now()->format('d/m/Y') }}</div>
                        <small class="text-info">{{ __('general.progress') }}: {{ number_format($overallProgress, 1) }}%</small>
                    </div>

                    @if ($project->end_date)
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="fw-bold text-primary">{{ __('general.end_date') }}</div>
                            <div class="text-muted">{{ \Carbon\Carbon::parse($project->end_date)->format('d/m/Y') }}</div>
                            <small class="text-warning">{{ __('general.expected_completion') }}</small>
                        </div>
                    @endif
                </div>

                <div class="mt-4 p-3 bg-light rounded">
                    <h6 class="fw-bold mb-3">{{ __('general.project_information') }}</h6>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <small class="text-muted">{{ __('general.client') }}:</small>
                            <div class="fw-medium">{{ $project->client->cname ?? __('general.not_specified') }}</div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted">{{ __('general.working_zone') }}:</small>
                            <div class="fw-medium">{{ $project->working_zone ?? __('general.not_specified') }}</div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted">{{ __('general.status') }}:</small>
                            <div class="fw-medium text-capitalize">{{ $project->status }}</div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted">{{ __('general.project_duration') }}:</small>
                            <div class="fw-medium">{{ $daysPassed + $daysRemaining }} {{ __('general.days') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($isComponentVisible('hierarchical-view', $visibleComponents ?? []))
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="fw-bold mb-3"><i class="fas fa-sitemap me-2"></i>{{ __('general.hierarchical_view') }}</h5>
                @if(isset($hierarchicalData) && count($hierarchicalData) > 0)
                    @foreach($hierarchicalData as $subprojectName => $subprojectData)
                        <div class="mb-4 border-bottom pb-3">
                            <h6 class="fw-bold">
                                <i class="fas fa-folder-open me-2"></i>{{ $subprojectName }}
                                @if(isset($subprojectData['subproject']) && $subprojectData['subproject']->weight)
                                    <span class="badge bg-info ms-2">{{ $subprojectData['subproject']->weight }}%</span>
                                @endif
                            </h6>
                            <div class="ms-4 mt-2">
                                <p class="small text-muted mb-2">{{ number_format($subprojectData['progress'], 1) }}% {{ __('general.completed') }}</p>
                                <div class="progress mb-3" style="height: 8px;">
                                    <div class="progress-bar bg-primary" style="width: {{ $subprojectData['progress'] }}%"></div>
                                </div>
                                @foreach($subprojectData['categories'] as $categoryName => $categoryData)
                                    <div class="mb-3">
                                        <h6 class="small fw-bold">{{ $categoryName }} <span class="badge bg-secondary">{{ $categoryData['count'] }} {{ __('general.items') }}</span></h6>
                                        <div class="ms-3">
                                            @foreach($categoryData['items'] as $item)
                                                @php
                                                $completionPercentage = $item->total_quantity > 0
                                                    ? ($item->completed_quantity / $item->total_quantity) * 100
                                                    : 0;
                                                @endphp
                                                <div class="mb-2 small">
                                                    <strong>{{ $item->workItem->name ?? '-' }}</strong>
                                                    @if($item->notes)
                                                        <span class="text-muted">({{ $item->notes }})</span>
                                                    @endif
                                                    - {{ number_format($completionPercentage, 1) }}%
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">{{ __('general.no_items_found') }}</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($isComponentVisible('work-items-progress', $visibleComponents ?? []))
    <div class="row mb-4">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="fw-bold mb-3"><i class="fas fa-list-check me-2"></i>{{ __('general.work_items_progress') }}</h5>
                <div class="row">
                    @foreach ($project->items as $item)
                        @php
                        $completionPercentage = $item->total_quantity > 0
                            ? ($item->completed_quantity / $item->total_quantity) * 100
                            : 0;
                        @endphp
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong>{{ $item->workItem->name ?? '-' }}</strong>
                                        @if($item->notes)
                                            <small class="text-muted d-block">({{ $item->notes }})</small>
                                        @endif
                                    </div>
                                    <span class="fw-bold text-primary">{{ number_format($completionPercentage, 1) }}%</span>
                                </div>
                                <div class="progress mb-2" style="height: 10px;">
                                    <div class="progress-bar bg-primary" style="width: {{ $completionPercentage }}%"></div>
                                </div>
                                <div class="small text-muted">
                                    {{ __('general.completed') }}: {{ number_format($item->completed_quantity, 2) }} / 
                                    {{ __('general.total') }}: {{ number_format($item->total_quantity, 2) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
