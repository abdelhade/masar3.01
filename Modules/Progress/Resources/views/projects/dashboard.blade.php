@extends('progress::layouts.app')

@section('title', __('general.project_dashboard'))

@section('content')

@if(!$project)
<div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle"></i> {{ __('general.project_not_found') }}
</div>
@php
return;
@endphp
@endif
<style>
    .project-dashboard {
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .dashboard-container {
        backdrop-filter: blur(10px);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        margin: 20px;
        overflow: hidden;
    }

    .dashboard-header {
        padding: 2rem;
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
        background: rgba(255, 255, 255, 0.05);
        transform: rotate(-5deg);
    }

    .stat-card {
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .progress-ring {
        width: 100px;
        height: 100px;
        position: relative;
    }

    .progress-ring-circle {
        transform: rotate(-90deg);
        transform-origin: 50% 50%;
    }

    .chart-container {
        position: relative;
        height: 300px;
        margin: 1rem 0;
    }

    .timeline-item {
        position: relative;
        padding-left: 2rem;
        margin-bottom: 2rem;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .timeline-item::after {
        content: '';
        position: absolute;
        left: 5px;
        top: 12px;
        width: 2px;
        height: calc(100% + 1rem);
        background: #e5e7eb;
    }

    .timeline-item:last-child::after {
        display: none;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .employee-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .gradient-text {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .actions-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .action-btn {
        margin: 0.25rem;
        transition: all 0.3s ease;
    }

    .action-btn:hover {
        transform: translateY(-2px);
    }

    .client-info-card,
    .employees-info-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .employee-performance {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #eee;
    }

    .employee-performance:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .performance-bar {
        height: 8px;
        border-radius: 4px;
        background: #e9ecef;
        overflow: hidden;
        margin-top: 0.5rem;
    }

    .performance-fill {
        height: 100%;
        border-radius: 4px;
    }

    @media (max-width: 768px) {
        .dashboard-header {
            padding: 1.5rem;
        }

        .stat-card {
            padding: 1rem;
        }
    }


    
    .dashboard-component.hidden {
        display: none !important;
    }

    
    .highlight {
        background-color: yellow;
        font-weight: bold;
    }

    
    .item-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
</style>
<div class="no-print">
    <button type="button" class="btn btn-sm btn-light me-2" data-bs-toggle="modal" data-bs-target="#componentsModal">
        <i class="fas fa-cog me-1"></i>{{ __('general.customize_view') }}
    </button>
    <button type="button" class="btn btn-sm btn-light me-2 no-print" id="printDashboardBtn">
        <i class="fas fa-print me-1"></i>{{ __('general.print') }}
    </button>
    <button type="button" class="btn btn-sm btn-light me-2" data-bs-toggle="modal" data-bs-target="#calculationFormulasModal">
        <i class="fas fa-calculator me-1"></i>{{ __('general.calculation_formulas') }}
    </button>
</div>
<div class="project-dashboard">
  
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="h2 mb-2"><i class="fas fa-chart-line me-2"></i>{{ __('general.project_dashboard') }}</h1>
                    <p class="mb-0 opacity-75">{{ $project->name }}@if($project->client) - {{ $project->client->cname }}@endif</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="status-badge bg-{{ $projectStatus['color'] }}">
                        <i class="fas fa-{{ $projectStatus['icon'] }} me-1"></i>
                        {{ $projectStatus['message'] }}
                    </span>
                    <div class="mt-2 text-white-50">
                        <small><i class="fas fa-sync-alt me-1"></i>{{ __('general.last_updated') }}:
                            {{ now()->format('d/m/Y H:i') }}</small>
                    </div>

                </div>
            </div>
        </div>

        <div class="container-fluid pt-4 dashboard-component" data-component="actions">
            <div class="actions-section">
                <h4 class="gradient-text fw-bold mb-3">
                    <i class="fas fa-cogs me-2"></i>{{ __('general.actions') }}
                </h4>
                <div class="d-flex flex-wrap">

                    <a href="{{ route('progress.projects.edit', $project->id) }}" class="btn btn-warning action-btn">
                        <i class="fas fa-edit me-1"></i>{{ __('general.edit_project') }}
                    </a>

                    @can('delete progress-projects')
                    <form action="{{ route('progress.projects.destroy', $project->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger action-btn" onclick="return confirm('{{ __('general.confirm_delete_project') }}')">
                            <i class="fas fa-trash me-1"></i>{{ __('general.delete_project') }}
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>

        <div class="container-fluid py-4">
            <div class="row g-4 mb-5 dashboard-component" data-component="statistics-cards">
                <div class="col-xl-4 col-md-6">
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

                <div class="col-xl-4 col-md-6">
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

                <div class="col-xl-4 col-md-6">
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

        <!-- Total Weighted Progress Section -->
        @if($project->subprojects->count() > 0)
        <div class="row g-4 mb-5 dashboard-component" data-component="total-weighted-progress">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body text-white p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1"><i class="fas fa-chart-line me-2"></i>{{ __('general.total_weighted_progress') }}</h5>
                                <p class="mb-0 text-white-50">{{ __('general.total_weighted_progress_description') }}</p>
                            </div>
                            <div class="text-end d-flex align-items-center gap-3">
                                <div id="totalWeightedProgressDisplay">
                                    <div class="text-center">
                                        <div class="spinner-border spinner-border-sm text-white" role="status">
                                            <span class="visually-hidden">{{ __('general.loading') }}...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="row g-4 mb-5 dashboard-component" data-component="charts">
            <div class="col-lg-12">
                <div class="stat-card dashboard-component" data-component="bar-chart">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="gradient-text fw-bold mb-0">
                            <i class="fas fa-chart-bar me-2"></i>{{ __('general.project_progress_overview') }}
                        </h4>
                        <div class="d-flex gap-2">
                            <button type="button" id="toggleGroupedView" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-layer-group me-1"></i><span id="toggleGroupedText">{{ __('general.grouped_view') }}</span>
                            </button>
                            <button type="button" id="selectAllItems" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-check-double me-1"></i>{{ __('general.select_all') }}
                            </button>
                            <button type="button" id="deselectAllItems" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>{{ __('general.deselect_all') }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Items Filter -->
                    <div class="mb-4 p-3 rounded" style="background-color: #f8f9fa; border: 1px solid #dee2e6;">
                        <label class="form-label fw-semibold mb-2">
                            <i class="fas fa-filter me-2"></i>{{ __('general.filter_items') }}
                        </label>
                        <div id="itemsFilterContainer" class="d-flex flex-wrap gap-3" style="max-height: 150px; overflow-y: auto;">
                            <!-- Checkboxes will be generated by JavaScript -->
                        </div>
                    </div>
                    
                    <div class="chart-container">
                        <canvas id="progressChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5 dashboard-component" data-component="additional-charts">
            <div class="col-lg-12">
                <div class="stat-card dashboard-component" data-component="subprojects-chart">
                    <h4 class="gradient-text fw-bold mb-2">
                        <i class="fas fa-sitemap me-2"></i>{{ __('general.subprojects_progress') }}
                    </h4>
                    <p class="text-muted small mb-4" style="font-size: 0.75rem;">
                        Progress = (Completed Quantity / Total Quantity) × 100 | Planned Progress = (Planned Total Quantity / Total Quantity) × 100
                    </p>
                    <div class="chart-container">
                        <canvas id="subprojectsChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="stat-card dashboard-component" data-component="categories-chart">
                    <h4 class="gradient-text fw-bold mb-2">
                        <i class="fas fa-tags me-2"></i>{{ __('general.categories_progress') }}
                    </h4>
                    <p class="text-muted small mb-4" style="font-size: 0.75rem;">
                        Progress = (Completed Quantity / Total Quantity) × 100 | Planned Progress = (Planned Total Quantity / Total Quantity) × 100
                    </p>
                    <div class="chart-container">
                        <canvas id="categoriesChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="stat-card dashboard-component" data-component="subproject-items-chart">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="gradient-text fw-bold mb-0">
                            <i class="fas fa-list me-2"></i>{{ __('general.items_by_subproject') }}
                        </h4>
                        <div class="d-flex align-items-center gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="showAllSubprojectItems" checked>
                                <label class="form-check-label small" for="showAllSubprojectItems">
                                    {{ __('general.show_all') }}
                                </label>
                            </div>
                        <select id="subprojectSelect" class="form-select form-select-sm" style="width: auto;">
                            <option value="">{{ __('general.select_subproject') }}</option>
                            @if(isset($subprojectsWithItems))
                                @foreach($subprojectsWithItems as $subproject)
                                    <option value="{{ $subproject->name }}">{{ $subproject->name }}</option>
                                @endforeach
                            @else
                                @foreach($project->subprojects as $subproject)
                                    <option value="{{ $subproject->name }}">{{ $subproject->name }}</option>
                                @endforeach
                                @if($project->items->whereNull('subproject_name')->isNotEmpty())
                                    <option value="{{ __('general.without_subproject') }}">{{ __('general.without_subproject') }}</option>
                                @endif
                            @endif
                        </select>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="subprojectItemsChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="stat-card dashboard-component" data-component="category-items-chart">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="gradient-text fw-bold mb-0">
                            <i class="fas fa-folder me-2"></i>{{ __('general.items_by_category') }}
                        </h4>
                        <select id="categorySelect" class="form-select form-select-sm" style="width: auto;">
                            <option value="">{{ __('general.select_category') }}</option>
                            @php
                                $categories = $project->items->map(function($item) {
                                    return $item->workItem && $item->workItem->category 
                                        ? $item->workItem->category->name 
                                        : null;
                                })->filter()->unique()->sort();
                            @endphp
                            @foreach($categories as $categoryName)
                                <option value="{{ $categoryName }}">{{ $categoryName }}</option>
                            @endforeach
                            @if($project->items->filter(function($item) { return !$item->workItem || !$item->workItem->category; })->isNotEmpty())
                                <option value="{{ __('general.uncategorized') }}">{{ __('general.uncategorized') }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="chart-container">
                        <canvas id="categoryItemsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5 dashboard-component" data-component="hierarchical-view">
            <div class="col-12">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="gradient-text fw-bold mb-0">
                            <i class="fas fa-sitemap me-2"></i>{{ __('general.hierarchical_view') }}
                        </h4>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="text" id="searchItems" class="form-control form-control-sm d-inline-block" style="width: 200px;" placeholder="{{ __('general.search') }}...">
                            <span class="badge bg-primary ms-2">{{ $totalItems }} {{ __('general.items') }}</span>
                        </div>
                    </div>

                    <div class="accordion accordion-flush" id="hierarchicalAccordion">
                        @if(isset($hierarchicalData) && count($hierarchicalData) > 0)
                        @foreach($hierarchicalData as $subprojectName => $subprojectData)
                        @php
                        $subprojectId = 'subproject-' . str_replace(' ', '-', $subprojectName);
                        $subproject = $subprojectData['subproject'];
                        @endphp
                        <div class="accordion-item mb-2 border rounded">
                            <h2 class="accordion-header" id="heading{{ $loop->index }}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $loop->index }}" aria-expanded="false">
                                    <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                        <div>
                                            <i class="fas fa-folder-open me-2"></i>
                                            <strong>{{ $subprojectName }}</strong>
                                            @if($subproject && $subproject->weight)
                                            <span class="badge bg-info ms-2">{{ $subproject->weight }}%</span>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <div class="small text-muted mb-1">
                                                {{ number_format($subprojectData['progress'], 1) }}% {{ __('general.completed') }}
                                            </div>
                                            <div class="progress" style="height: 6px; width: 150px;">
                                                <div class="progress-bar 
                                                                    @if($subprojectData['progress'] >= 80) bg-success
                                                                    @elseif($subprojectData['progress'] >= 50) bg-primary
                                                                    @elseif($subprojectData['progress'] >= 30) bg-warning
                                                                    @else bg-danger @endif"
                                                    style="width: {{ $subprojectData['progress'] }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse{{ $loop->index }}" class="accordion-collapse collapse" data-bs-parent="#hierarchicalAccordion">
                                <div class="accordion-body">
                                    @foreach($subprojectData['categories'] as $categoryName => $categoryData)
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="fas fa-tag me-2"></i>
                                                    <strong>{{ $categoryName }}</strong>
                                                    <span class="badge bg-secondary ms-2">{{ $categoryData['count'] }} {{ __('general.items') }}</span>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-muted">{{ number_format($categoryData['progress'], 1) }}%</small>
                                                    <div class="progress" style="height: 4px; width: 100px; margin-top: 2px;">
                                                        <div class="progress-bar bg-info" style="width: {{ $categoryData['progress'] }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach($categoryData['items'] as $item)
                                                @php
                                                $completionPercentage = $item->total_quantity > 0
                                                ? ($item->completed_quantity / $item->total_quantity) * 100
                                                : 0;
                                                @endphp
                                                <div class="col-md-6 mb-3 item-card" data-item-name="{{ strtolower($item->workItem->name ?? '') }}">
                                                    <div class="border rounded p-3">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <div class="flex-grow-1">
                                                                <div class="fw-bold text-dark">{{ $item->workItem->name ?? '-' }}</div>
                                                                @if($item->subproject_name ?? null)
                                                                <small class="text-primary d-block"><i class="fas fa-folder-open me-1"></i>{{ $item->subproject_name }}</small>
                                                                @endif
                                                                @if($item->workItem->unit ?? null)
                                                                <small class="text-muted d-block"><i class="fas fa-ruler me-1"></i>{{ $item->workItem->unit }}</small>
                                                                @endif
                                                                @if($item->notes ?? null)
                                                                <small class="text-muted d-block"><i class="fas fa-sticky-note me-1"></i>{{ Str::limit($item->notes, 30) }}</small>
                                                                @endif
                                                            </div>
                                                            <span class="fw-bold text-primary ms-2">{{ number_format($completionPercentage, 1) }}%</span>
                                                        </div>
                                                        <div class="progress mb-2" style="height: 10px; border-radius: 5px;">
                                                            <div class="progress-bar
                                                                                        @if ($completionPercentage >= 80) bg-success
                                                                                        @elseif($completionPercentage >= 50) bg-primary
                                                                                        @elseif($completionPercentage >= 30) bg-warning
                                                                                        @else bg-danger @endif"
                                                                role="progressbar" style="width: {{ $completionPercentage }}%">
                                                            </div>
                                                        </div>
                                                        <div class="d-flex justify-content-between text-muted small">
                                                            <span><i class="fas fa-check-circle text-success me-1"></i>{{ number_format($item->completed_quantity, 2) }}</span>
                                                            <span><i class="fas fa-clock text-warning me-1"></i>{{ number_format($item->remaining_quantity, 2) }}</span>
                                                            <span><i class="fas fa-list text-info me-1"></i>{{ number_format($item->total_quantity, 2) }}</span>
                                                        </div>
                                                        @if($item->start_date && $item->end_date)
                                                        <div class="mt-2 pt-2 border-top">
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar me-1"></i>
                                                                {{ \Carbon\Carbon::parse($item->start_date)->format('d/m/Y') }} -
                                                                {{ \Carbon\Carbon::parse($item->end_date)->format('d/m/Y') }}
                                                            </small>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">{{ __('general.no_items_found') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5 dashboard-component" data-component="work-items-progress">
            <div class="col-12">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="gradient-text fw-bold mb-0">
                            <i class="fas fa-list-check me-2"></i>{{ __('general.work_items_progress') }}
                        </h4>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" id="toggleGroupedView" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-layer-group me-1"></i><span id="toggleGroupedText">{{ __('general.group_by_item') }}</span>
                            </button>
                            <span class="badge bg-primary" id="workItemsCount">{{ $totalItems }} {{ __('general.items') }}</span>
                        </div>
                    </div>

                    <div class="card mb-4 bg-light">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">{{ __('general.search') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" id="workItemsSearch" class="form-control form-control-sm"
                                            placeholder="{{ __('general.search_by_name_unit_category') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">{{ __('general.subproject') }}</label>
                                    <select id="workItemsSubprojectFilter" class="form-select form-select-sm">
                                        <option value="">{{ __('general.all_subprojects') }}</option>
                                        @if(isset($subprojectsWithItems))
                                            @foreach($subprojectsWithItems as $subproject)
                                                <option value="{{ $subproject->name }}">{{ $subproject->name }}</option>
                                            @endforeach
                                        @else
                                            @foreach($project->subprojects as $subproject)
                                                <option value="{{ $subproject->name }}">{{ $subproject->name }}</option>
                                            @endforeach
                                            @if($project->items->whereNull('subproject_name')->isNotEmpty())
                                                <option value="{{ __('general.without_subproject') }}">{{ __('general.without_subproject') }}</option>
                                            @endif
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">{{ __('general.category') }}</label>
                                    <select id="workItemsCategoryFilter" class="form-select form-select-sm">
                                        <option value="">{{ __('general.all_categories') }}</option>
                                        @php
                                            $categories = $project->items->map(function($item) {
                                                return $item->workItem && $item->workItem->category 
                                                    ? $item->workItem->category->name 
                                                    : null;
                                            })->filter()->unique()->sort();
                                        @endphp
                                        @foreach($categories as $categoryName)
                                            <option value="{{ $categoryName }}">{{ $categoryName }}</option>
                                        @endforeach
                                        <option value="uncategorized">{{ __('general.uncategorized') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small text-muted">{{ __('general.status') }}</label>
                                    <select id="workItemsStatusFilter" class="form-select form-select-sm">
                                        <option value="">{{ __('general.all') }}</option>
                                        <option value="completed">{{ __('general.completed') }}</option>
                                        <option value="in_progress">{{ __('general.in_progress') }}</option>
                                        <option value="pending">{{ __('general.pending') }}</option>
                                        <option value="delayed">{{ __('general.delayed') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12 d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="showPlannedProgress" checked>
                                        <label class="form-check-label" for="showPlannedProgress">
                                            <i class="fas fa-chart-line me-1"></i>{{ __('general.show_planned_progress') }}
                                        </label>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1"><i class="fas fa-tags me-1"></i>{{ __('general.filter_by_status') }}</label>
                                        <select id="itemStatusFilter" class="form-select form-select-sm">
                                            <option value="">{{ __('general.all_statuses') }}</option>
                                            @foreach($itemStatuses ?? [] as $status)
                                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="button" id="clearWorkItemsFilters" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>{{ __('general.clear_filters') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion" id="accordionWorkItems">
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingWorkItems">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseWorkItems" aria-expanded="false" aria-controls="collapseWorkItems">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chevron-down me-2"></i>{{ __('general.view_work_items') }}
                                    </h5>
                                </button>
                            </h2>
                            <div id="collapseWorkItems" class="accordion-collapse collapse" aria-labelledby="headingWorkItems" data-bs-parent="#accordionWorkItems">
                                <div class="accordion-body">
                                    <!-- Normal View -->
                                    <div class="row" id="workItemsContainer">
                        @foreach ($project->items as $item)
                        @php
                        $completionPercentage =
                        $item->total_quantity > 0
                        ? ($item->completed_quantity / $item->total_quantity) * 100
                        : 0;
                        
                        $today = \Carbon\Carbon::today();
                        $endDate = $item->end_date ? \Carbon\Carbon::parse($item->end_date) : null;
                        $startDate = $item->start_date ? \Carbon\Carbon::parse($item->start_date) : null;
                        
                        $itemStatus = 'pending';
                        if ($completionPercentage >= 100) {
                            $itemStatus = 'completed';
                        } elseif ($startDate && $today->lt($startDate)) {
                            $itemStatus = 'pending';
                        } elseif ($endDate && $today->gt($endDate) && $completionPercentage < 100) {
                            $itemStatus = 'delayed';
                        } elseif ($completionPercentage > 0 && $completionPercentage < 100) {
                            $itemStatus = 'in_progress';
                        }
                        
                        $plannedProgressUntilToday = 0;
                        if ($startDate && $endDate) {
                            $totalDays = $startDate->diffInDays($endDate);
                            if ($totalDays > 0) {
                                if ($today->greaterThanOrEqualTo($endDate)) {
                                    $plannedProgressUntilToday = 100;
                                } elseif ($today->greaterThanOrEqualTo($startDate)) {
                                    $daysPassed = $startDate->diffInDays($today);
                                    $plannedProgressUntilToday = min(100, round(($daysPassed / $totalDays) * 100, 2));
                                }
                            }
                        }
                        
                        $progressDifference = $completionPercentage - $plannedProgressUntilToday;
                        @endphp
                        <div class="col-md-6 mb-4 work-item-card" 
                             data-item-name="{{ strtolower($item->workItem->name ?? '') }}"
                             data-subproject="{{ $item->subproject_name ?? __('general.without_subproject') }}"
                             data-category="{{ $item->workItem && $item->workItem->category ? strtolower($item->workItem->category->name) : 'uncategorized' }}"
                             data-status="{{ $itemStatus }}"
                             data-item-id="{{ $item->id }}"
                             data-item-status-id="{{ $item->item_status_id ?? '' }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <div class="fw-bold text-dark">{{ $item->workItem->name }}</div>
                                    @if($item->workItem->category ?? null)
                                    <small class="text-muted d-block"><i class="fas fa-folder me-1"></i>{{ $item->workItem->category->name }}</small>
                                    @endif
                                    @if($item->subproject_name ?? null)
                                    <small class="text-muted d-block"><i class="fas fa-sitemap me-1"></i>{{ $item->subproject_name }}</small>
                                    @else
                                    <small class="text-muted d-block"><i class="fas fa-sitemap me-1"></i>{{ __('general.without_subproject') }}</small>
                                    @endif
                                    @if($item->workItem->unit ?? null)
                                    <small class="text-muted d-block"><i class="fas fa-ruler me-1"></i>{{ $item->workItem->unit }}</small>
                                    @endif
                                    @if($item->notes ?? null)
                                    <small class="text-muted d-block"><i class="fas fa-sticky-note me-1"></i>{{ Str::limit($item->notes, 30) }}</small>
                                    @endif
                                </div>
                                <br>
                                <div class="text-end">
                                    <span class="fw-bold text-primary d-block">{{ number_format($completionPercentage, 1) }}%</span>
                                    <span class="badge bg-{{ $itemStatus === 'completed' ? 'success' : ($itemStatus === 'in_progress' ? 'primary' : ($itemStatus === 'delayed' ? 'danger' : 'warning')) }} badge-sm">
                                        {{ __('general.' . $itemStatus) }}
                                    </span>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small mb-1"><i class="fas fa-tags me-1"></i>{{ __('general.item_status') }}</label>
                                <select class="form-select form-select-sm item-status-select" 
                                        data-item-id="{{ $item->id }}"
                                        data-project-id="{{ $project->id }}">
                                    <option value="">{{ __('general.no_status') }}</option>
                                    @foreach($itemStatuses ?? [] as $status)
                                        <option value="{{ $status->id }}" 
                                                {{ ($item->item_status_id == $status->id) ? 'selected' : '' }}
                                                data-color="{{ $status->color }}"
                                                data-icon="{{ $status->icon }}">
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="item-status-loading d-none mt-1">
                                    <small class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i>{{ __('general.saving') }}...</small>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted">
                                        <i class="fas fa-check-circle me-1 text-success"></i>{{ __('general.actual_progress') }}
                                    </small>
                                    <small class="fw-bold text-success">{{ number_format($completionPercentage, 1) }}%</small>
                                </div>
                                <div class="progress" style="height: 10px; border-radius: 8px;">
                                    <div class="progress-bar
                                            @if ($completionPercentage >= 80) bg-success
                                            @elseif($completionPercentage >= 50) bg-primary
                                            @elseif($completionPercentage >= 30) bg-warning
                                            @else bg-danger @endif"
                                        role="progressbar" style="width: {{ min($completionPercentage, 100) }}%">
                                    </div>
                                </div>
                            </div>
                            <div class="planned-progress-section">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-check me-1 text-info"></i>{{ __('general.planned_progress') }}
                                    </small>
                                    <small class="fw-bold text-info">{{ number_format($plannedProgressUntilToday, 1) }}%</small>
                                </div>
                                <div class="progress" style="height: 10px; border-radius: 8px;">
                                    <div class="progress-bar bg-info" 
                                         role="progressbar" 
                                         style="width: {{ min($plannedProgressUntilToday, 100) }}%">
                                    </div>
                                </div>
                                @if($progressDifference != 0)
                                <div class="mt-1 text-center">
                                    <small class="badge 
                                        @if($progressDifference > 0) bg-success
                                        @else bg-danger
                                        @endif badge-sm">
                                        @if($progressDifference > 0)
                                            <i class="fas fa-arrow-up me-1"></i>{{ __('general.ahead_by') }} {{ number_format(abs($progressDifference), 1) }}%
                                        @else
                                            <i class="fas fa-arrow-down me-1"></i>{{ __('general.behind_by') }} {{ number_format(abs($progressDifference), 1) }}%
                                        @endif
                                    </small>
                                </div>
                                @else
                                <div class="mt-1 text-center">
                                    <small class="badge bg-secondary badge-sm">
                                        <i class="fas fa-equals me-1"></i>{{ __('general.on_track') }}
                                    </small>
                                </div>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between mt-2 text-muted small">
                                <span>{{ number_format($item->completed_quantity) }}
                                    {{ $item->workItem->unit }}</span>
                                <span>{{ number_format($item->remaining_quantity) }}
                                    {{ $item->workItem->unit }}</span>
                                <span>{{ number_format($item->total_quantity) }}
                                    {{ $item->workItem->unit }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                                    <!-- Grouped View -->
                                    <div class="row" id="workItemsGroupedContainer" style="display: none;">
                                        <!-- Will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="stat-card dashboard-component" data-component="recent-activity">
                    <div class="accordion" id="accordionRecentActivity">
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingRecentActivity">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRecentActivity" aria-expanded="false" aria-controls="collapseRecentActivity">
                                    <h4 class="gradient-text fw-bold mb-0">
                                        <i class="fas fa-history me-2"></i>{{ __('general.daily_progress_record') }}
                                    </h4>
                                </button>
                            </h2>
                            <div id="collapseRecentActivity" class="accordion-collapse collapse" aria-labelledby="headingRecentActivity" data-bs-parent="#accordionRecentActivity">
                                <div class="accordion-body">
                                    <!-- Filters -->
                                    <div class="mb-4 p-3 bg-light rounded">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label small mb-1">
                                                    <i class="fas fa-search me-1"></i>{{ __('general.search') }}
                                                </label>
                                                <input type="text" id="dailyProgressSearch" class="form-control form-control-sm" placeholder="{{ __('general.search_by_employee_or_item') }}...">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small mb-1">
                                                    <i class="fas fa-calendar-alt me-1"></i>{{ __('general.from_date') }}
                                                </label>
                                                <input type="date" id="dailyProgressFromDate" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small mb-1">
                                                    <i class="fas fa-calendar-alt me-1"></i>{{ __('general.to_date') }}
                                                </label>
                                                <input type="date" id="dailyProgressToDate" class="form-control form-control-sm">
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <button type="button" id="clearDailyProgressFilters" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-times me-1"></i>{{ __('general.clear_filters') }}
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline" id="dailyProgressTimeline">
                        @foreach ($recentProgress as $date => $progresses)
                        <div class="timeline-item" data-date="{{ $date }}" data-progresses="{{ json_encode($progresses->map(function($p) { return ['employee_name' => $p->employee->name ?? '', 'item_name' => $p->projectItem->workItem->name ?? '', 'quantity' => $p->quantity, 'unit' => $p->projectItem->workItem->unit ?? '']; })->toArray()) }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span
                                    class="fw-bold text-dark">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
                                <span class="badge bg-light text-dark">
                                    {{ $progresses->sum('quantity') }} {{ __('general.units') }}
                                </span>
                            </div>
                            @foreach ($progresses as $progress)
                            <div class="progress-item d-flex align-items-center mb-2 p-2 bg-light rounded" 
                                 data-employee="{{ strtolower($progress->employee->name ?? '') }}" 
                                 data-item="{{ strtolower($progress->projectItem->workItem->name ?? '') }}">
                                <div class="flex-shrink-0">
                                    @if($progress->employee ?? null)
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($progress->employee->name) }}&background=4f46e5&color=fff"
                                        alt="{{ $progress->employee->name }}" class="employee-avatar">
                                    @else
                                    <img src="https://ui-avatars.com/api/?name=Unknown&background=6c757d&color=fff"
                                        alt="Unknown" class="employee-avatar">
                                    @endif
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-medium">{{ $progress->employee->name ?? 'موظف غير معروف' }}</div>
                                    <div class="fw-semibold text-dark">{{ $progress->projectItem->workItem->name ?? '-' }}</div>
                                    @if($progress->projectItem->workItem->category ?? null)
                                    <small class="text-muted d-block"><i class="fas fa-folder me-1"></i>{{ $progress->projectItem->workItem->category->name }}</small>
                                    @endif
                                    @if($progress->projectItem->notes ?? null)
                                    <small class="text-muted d-block"><i class="fas fa-sticky-note me-1"></i>{{ Str::limit($progress->projectItem->notes, 25) }}</small>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-primary">{{ $progress->quantity }}</div>
                                    <small
                                        class="text-muted">{{ $progress->projectItem->workItem->unit ?? '' }}</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="stat-card dashboard-component" data-component="timeline">
                    <div class="accordion" id="accordionTimeline">
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingTimeline">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTimeline" aria-expanded="false" aria-controls="collapseTimeline">
                                    <h4 class="gradient-text fw-bold mb-0">
                                        <i class="fas fa-project-diagram me-2"></i>{{ __('general.project_timeline') }}
                                    </h4>
                                </button>
                            </h2>
                            <div id="collapseTimeline" class="accordion-collapse collapse" aria-labelledby="headingTimeline" data-bs-parent="#accordionTimeline">
                                <div class="accordion-body">
                                    <div class="timeline">
                        @if($project->start_date)
                        <div class="timeline-item">
                            <div class="fw-bold text-primary">{{ __('general.start_date') }}</div>
                            <div class="text-muted">
                                {{ \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') }}
                            </div>
                            <small class="text-success">{{ __('general.project_kickoff') }}</small>
                        </div>
                        @endif

                        <div class="timeline-item">
                            <div class="fw-bold text-primary">{{ __('general.current_date') }}</div>
                            <div class="text-muted">{{ now()->format('d/m/Y') }}</div>
                            <small class="text-info">{{ __('general.progress') }}:
                                {{ number_format($overallProgress, 1) }}%</small>
                        </div>

                        @if ($project->end_date)
                        <div class="timeline-item">
                            <div class="fw-bold text-primary">{{ __('general.end_date') }}</div>
                            <div class="text-muted">
                                {{ \Carbon\Carbon::parse($project->end_date)->format('d/m/Y') }}
                            </div>
                            <small class="text-warning">{{ __('general.expected_completion') }}</small>
                        </div>
                        @endif
                    </div>

                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="fw-bold mb-3">{{ __('general.project_information') }}</h6>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <small class="text-muted">{{ __('general.client') }}:</small>
                                <div class="fw-medium">{{ $project->client->cname ?? __('general.not_specified') }}</div>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">{{ __('general.working_zone') }}:</small>
                                <div class="fw-medium">{{ $project->working_zone }}</div>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">{{ __('general.status') }}:</small>
                                <div class="fw-medium text-capitalize">{{ $project->status }}</div>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted">{{ __('general.project_duration') }}:</small>
                                <div class="fw-medium">{{ $daysPassed + $daysRemaining }}
                                    {{ __('general.days') }}
                                </div>
                            </div>
                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5 dashboard-component" data-component="client-employees-info">
            <div class="col-lg-6">
                <div class="client-info-card dashboard-component" data-component="client-info">
                    <div class="accordion" id="accordionClientInfo">
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingClientInfo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseClientInfo" aria-expanded="false" aria-controls="collapseClientInfo">
                                    <h4 class="gradient-text fw-bold mb-0">
                                        <i class="fas fa-building me-2"></i>{{ __('general.client_information') }}
                                    </h4>
                                </button>
                            </h2>
                            <div id="collapseClientInfo" class="accordion-collapse collapse" aria-labelledby="headingClientInfo" data-bs-parent="#accordionClientInfo">
                                <div class="accordion-body">
                                    @if($project->client)
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <div class="client-avatar mx-auto mb-2">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($project->client->cname) }}&background=4f46e5&color=fff&size=80"
                                    alt="{{ $project->client->cname }}" class="rounded-circle" width="80"
                                    height="80">
                            </div>
                            <h5 class="fw-bold">{{ $project->client->cname }}</h5>
                        </div>
                        <div class="col-md-8">
                            <div class="client-details">
                                <div class="mb-2">
                                    <small class="text-muted">{{ __('general.contact_person') }}:</small>
                                    <div class="fw-medium">
                                        {{ $project->client->contact_person ?? __('general.not_specified') }}
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">{{ __('general.email') }}:</small>
                                    <div class="fw-medium">
                                        {{ $project->client->email ?? __('general.not_specified') }}
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">{{ __('general.phone') }}:</small>
                                    <div class="fw-medium">
                                        {{ $project->client->phone ?? __('general.not_specified') }}
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">{{ __('general.address') }}:</small>
                                    <div class="fw-medium">
                                        {{ $project->client->address ?? __('general.not_specified') }}
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">{{ __('general.projects_count') }}:</small>
                                    <div class="fw-medium">
                                        {{ $project->client->projects_count ?? $project->client->projects()->count() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-building fa-2x text-muted mb-2"></i>
                        <p class="text-muted">{{ __('general.no_client_assigned') }}</p>
                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="employees-info-card dashboard-component" data-component="employees-info">
                    <div class="accordion" id="accordionTeamMembers">
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingTeamMembers">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTeamMembers" aria-expanded="false" aria-controls="collapseTeamMembers">
                                    <h4 class="gradient-text fw-bold mb-0">
                                        <i class="fas fa-users me-2"></i>{{ __('general.team_members') }}
                                    </h4>
                                </button>
                            </h2>
                            <div id="collapseTeamMembers" class="accordion-collapse collapse" aria-labelledby="headingTeamMembers" data-bs-parent="#accordionTeamMembers">
                                <div class="accordion-body">
                                    <div class="employee-performance-list">
                        @forelse($project->employees as $employee)
                        @php
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
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</div>

<div class="modal fade" id="componentsModal" tabindex="-1" aria-labelledby="componentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="componentsModalLabel">
                    <i class="fas fa-cog me-2"></i>{{ __('general.customize_view') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input component-checkbox" type="checkbox" value="statistics-cards" id="check-statistics" checked>
                            <label class="form-check-label" for="check-statistics">
                                <i class="fas fa-chart-line me-2"></i>{{ __('general.statistics_cards') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input component-checkbox" type="checkbox" value="client-info" id="check-client" checked>
                            <label class="form-check-label" for="check-client">
                                <i class="fas fa-building me-2"></i>{{ __('general.client_information') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input component-checkbox" type="checkbox" value="employees-info" id="check-employees" checked>
                            <label class="form-check-label" for="check-employees">
                                <i class="fas fa-users me-2"></i>{{ __('general.team_members') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input component-checkbox" type="checkbox" value="bar-chart" id="check-bar-chart" checked>
                            <label class="form-check-label" for="check-bar-chart">
                                <i class="fas fa-chart-bar me-2"></i>{{ __('general.progress_chart') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input component-checkbox" type="checkbox" value="total-weighted-progress" id="check-total-weighted-progress" checked>
                            <label class="form-check-label" for="check-total-weighted-progress">
                                <i class="fas fa-chart-line me-2"></i>{{ __('general.total_weighted_progress') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input component-checkbox" type="checkbox" value="subprojects-chart" id="check-subprojects-chart" checked>
                            <label class="form-check-label" for="check-subprojects-chart">
                                <i class="fas fa-sitemap me-2"></i>{{ __('general.subprojects_progress') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input component-checkbox" type="checkbox" value="categories-chart" id="check-categories-chart" checked>
                            <label class="form-check-label" for="check-categories-chart">
                                <i class="fas fa-tags me-2"></i>{{ __('general.categories_progress') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input component-checkbox" type="checkbox" value="subproject-items-chart" id="check-subproject-items-chart" checked>
                            <label class="form-check-label" for="check-subproject-items-chart">
                                <i class="fas fa-list me-2"></i>{{ __('general.items_by_subproject') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input component-checkbox" type="checkbox" value="category-items-chart" id="check-category-items-chart" checked>
                            <label class="form-check-label" for="check-category-items-chart">
                                <i class="fas fa-folder me-2"></i>{{ __('general.items_by_category') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input component-checkbox" type="checkbox" value="hierarchical-view" id="check-hierarchical" checked>
                            <label class="form-check-label" for="check-hierarchical">
                                <i class="fas fa-sitemap me-2"></i>{{ __('general.hierarchical_view') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input component-checkbox" type="checkbox" value="work-items-progress" id="check-work-items" checked>
                            <label class="form-check-label" for="check-work-items">
                                <i class="fas fa-list-check me-2"></i>{{ __('general.work_items_progress') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input component-checkbox" type="checkbox" value="recent-activity" id="check-activity" checked>
                            <label class="form-check-label" for="check-activity">
                                <i class="fas fa-history me-2"></i>{{ __('general.daily_progress_record') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input component-checkbox" type="checkbox" value="timeline" id="check-timeline" checked>
                            <label class="form-check-label" for="check-timeline">
                                <i class="fas fa-project-diagram me-2"></i>{{ __('general.project_timeline') }}
                            </label>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAllComponents">
                        <i class="fas fa-check-double me-1"></i>{{ __('general.select_all') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllComponents">
                        <i class="fas fa-times me-1"></i>{{ __('general.deselect_all') }}
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.close') }}</button>
                <button type="button" class="btn btn-primary" id="saveComponentsSettings">
                    <i class="fas fa-save me-1"></i>{{ __('general.save') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Calculation Formulas -->
<div class="modal fade" id="calculationFormulasModal" tabindex="-1" aria-labelledby="calculationFormulasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="calculationFormulasModalLabel">
                    <i class="fas fa-calculator me-2"></i>{{ __('general.calculation_formulas') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="p-4 bg-light rounded d-none">
                    <h6 class="fw-bold mb-3">{{ __('general.weighted_ratio_calculation') }}</h6>
                    <p class="mb-2"><strong>{{ __('general.formula') }}:</strong></p>
                    <p class="mb-3 text-muted">
                        <code>Weighted Ratio = Progress Percentage × (Subproject Weight / 100)</code>
                    </p>
                    <p class="mb-2"><strong>{{ __('general.where') }}:</strong></p>
                    <ul class="mb-4">
                        <li><code>Progress Percentage = (Completed Quantity / Total Quantity) × 100</code></li>
                        <li><code>Subproject Weight</code> = الوزن المخصص للمشروع الفرعي (يجب أن يكون مجموع الأوزان = 100%)</li>
                    </ul>

                    <h6 class="fw-bold mb-3 mt-4">{{ __('general.planned_weighted_ratio_calculation') }}</h6>
                    <p class="mb-2"><strong>{{ __('general.formula') }}:</strong></p>
                    <p class="mb-3 text-muted">
                        <code>Planned Weighted Ratio = Planned Progress Percentage × (Subproject Weight / 100)</code>
                    </p>
                    <p class="mb-2"><strong>{{ __('general.where') }}:</strong></p>
                    <ul class="mb-4">
                        <li><code>Planned Progress Percentage = (Planned Total Quantity / Total Quantity) × 100</code></li>
                        <li><code>Planned Total Quantity = min(Working Days Until Today × Estimated Daily Quantity, Total Quantity)</code></li>
                        <li><code>Working Days Until Today</code> = عدد أيام العمل من تاريخ بداية البند حتى اليوم (باستثناء أيام الإجازة الأسبوعية)</li>
                        <li><code>Estimated Daily Quantity</code> = الكمية المتوقعة يومياً (estimated_daily_qty)</li>
                    </ul>

                    <h6 class="fw-bold mb-3 mt-4">{{ __('general.total_weighted_progress') }}</h6>
                    <p class="mb-2"><strong>{{ __('general.formula') }}:</strong></p>
                    <p class="mb-3 text-muted">
                        <code>Total Weighted Progress = Σ(Weighted Progress of all Subprojects)</code>
                    </p>
                    <p class="mb-2 text-muted">
                        <small>{{ __('general.note') }}: يتم جمع جميع النسب المرجحة للمشاريع الفرعية للحصول على التقدم الكلي المرجح للمشروع.</small>
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.close') }}</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const workItems = @json($chartData['work_items']);
        const completionPercentages = @json($chartData['completion_percentages']);
        const weightedRatios = @json($chartData['weighted_ratios'] ?? []);
        const plannedWeightedRatios = @json($chartData['planned_weighted_ratios'] ?? []);
        const weeklyProgress = @json($chartData['weekly_progress']);
        const groupedData = @json($chartData['grouped_data'] ?? []);
        
        let isGroupedView = false;
        let currentLabels = [...workItems];
        let currentWeightedRatios = [...weightedRatios];
        let currentPlannedWeightedRatios = [...plannedWeightedRatios];

        const itemsFilterContainer = document.getElementById('itemsFilterContainer');
        if (itemsFilterContainer && workItems && workItems.length > 0) {
            workItems.forEach((item, index) => {
                const checkboxDiv = document.createElement('div');
                checkboxDiv.className = 'form-check';
                checkboxDiv.innerHTML = `
                    <input class="form-check-input item-filter-checkbox" type="checkbox" 
                           value="${index}" id="itemFilter${index}" checked>
                    <label class="form-check-label" for="itemFilter${index}">
                        ${item}
                    </label>
                `;
                itemsFilterContainer.appendChild(checkboxDiv);
            });
        } else if (itemsFilterContainer) {
            itemsFilterContainer.innerHTML = '<p class="text-muted mb-0">{{ __('general.no_items_found') }}</p>';
        }

        function drawDataLabels(chart) {
            const ctx = chart.ctx;
            ctx.save();
            ctx.font = 'bold 12px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            
            chart.data.datasets.forEach((dataset, datasetIndex) => {
                const meta = chart.getDatasetMeta(datasetIndex);
                meta.data.forEach((bar, index) => {
                    const data = dataset.data[index];
                    if (data !== null && data !== undefined && bar && bar.height > 0) {
                        const x = bar.x;
                        const y = bar.y - 15;
                        const text = data.toFixed(1) + '%';
                        const textMetrics = ctx.measureText(text);
                        const textWidth = textMetrics.width;
                        
                        ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
                        ctx.fillRect(x - textWidth/2 - 4, y - 9, textWidth + 8, 18);
                        
                        ctx.fillStyle = '#fff';
                        ctx.fillText(text, x, y);
                    }
                });
            });
            ctx.restore();
        }

        const progressCtx = document.getElementById('progressChart').getContext('2d');
        let progressChart = new Chart(progressCtx, {
            type: 'bar',
            data: {
                labels: workItems,
                datasets: [
                    {
                        label: '{{ __('general.progress') }}',
                        data: weightedRatios,
                        backgroundColor: 'rgba(255, 99, 71, 0.7)',
                        borderColor: 'rgba(255, 99, 71, 1)',
                        borderWidth: 1,
                        borderRadius: 0
                    },
                    {
                        label: '{{ __('general.planned_progress') }}',
                        data: plannedWeightedRatios,
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        borderRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        enabled: true
                    }
                },
                animation: {
                    onComplete: function() {
                        drawDataLabels(this);
                    }
                },
                scales: {
                    x: {
                        grouped: true
                    },
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


        function updateProgressChart() {
            if (!progressChart || currentLabels.length === 0) {
                return;
            }
            
            const checkedBoxes = document.querySelectorAll('.item-filter-checkbox:checked');
            const selectedIndices = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
            
            if (selectedIndices.length === 0) {
                progressChart.data.labels = [];
                progressChart.data.datasets[0].data = [];
                progressChart.data.datasets[1].data = [];
                progressChart.update('none');
                setTimeout(() => {
                    drawDataLabels(progressChart);
                }, 50);
                return;
            }
            
            const filteredWorkItems = selectedIndices.map(idx => currentLabels[idx]).filter(item => item !== undefined);
            const filteredWeightedRatios = selectedIndices.map(idx => currentWeightedRatios[idx]).filter(ratio => ratio !== undefined);
            const filteredPlannedWeightedRatios = selectedIndices.map(idx => currentPlannedWeightedRatios[idx]).filter(ratio => ratio !== undefined);
            
            progressChart.data.labels = filteredWorkItems;
            progressChart.data.datasets[0].data = filteredWeightedRatios;
            progressChart.data.datasets[1].data = filteredPlannedWeightedRatios;
            
            progressChart.update('none'); // 'none' لتجنب animation
            setTimeout(() => {
                drawDataLabels(progressChart);
            }, 50);
        }
        
        function toggleGroupedView() {
            isGroupedView = !isGroupedView;
            const toggleBtn = document.getElementById('toggleGroupedView');
            const toggleText = document.getElementById('toggleGroupedText');
            
            if (isGroupedView) {
                const groupedLabels = [];
                const groupedRatios = [];
                const groupedPlannedRatios = [];
                
                Object.keys(groupedData).forEach(subprojectName => {
                    const subprojectItems = groupedData[subprojectName].items;
                    subprojectItems.forEach(item => {
                        groupedLabels.push(item.label);
                        groupedRatios.push(item.percentage);
                        groupedPlannedRatios.push(item.planned_progress);
                    });
                });
                
                currentLabels = groupedLabels;
                currentWeightedRatios = groupedRatios;
                currentPlannedWeightedRatios = groupedPlannedRatios;
                
                if (toggleText) toggleText.textContent = '{{ __('general.flat_view') }}';
            } else {
                currentLabels = [...workItems];
                currentWeightedRatios = [...weightedRatios];
                currentPlannedWeightedRatios = [...plannedWeightedRatios];
                
                if (toggleText) toggleText.textContent = '{{ __('general.grouped_view') }}';
            }
            
            rebuildCheckboxes();
            
            updateProgressChart();
        }
        
        function rebuildCheckboxes() {
            const itemsFilterContainer = document.getElementById('itemsFilterContainer');
            if (!itemsFilterContainer) return;
            
            itemsFilterContainer.innerHTML = '';
            
            if (currentLabels && currentLabels.length > 0) {
                currentLabels.forEach((item, index) => {
                    const checkboxDiv = document.createElement('div');
                    checkboxDiv.className = 'form-check';
                    checkboxDiv.innerHTML = `
                        <input class="form-check-input item-filter-checkbox" type="checkbox" 
                               value="${index}" id="itemFilter${index}" checked>
                        <label class="form-check-label" for="itemFilter${index}">
                            ${item}
                        </label>
                    `;
                    itemsFilterContainer.appendChild(checkboxDiv);
                });
                
                document.querySelectorAll('.item-filter-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', updateProgressChart);
                });
            } else {
                itemsFilterContainer.innerHTML = '<p class="text-muted mb-0">{{ __('general.no_items_found') }}</p>';
            }
        }
        
        document.getElementById('toggleGroupedView')?.addEventListener('click', toggleGroupedView);

        document.querySelectorAll('.item-filter-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateProgressChart);
        });

        document.getElementById('selectAllItems')?.addEventListener('click', function() {
            document.querySelectorAll('.item-filter-checkbox').forEach(checkbox => {
                checkbox.checked = true;
            });
            updateProgressChart();
        });

        document.getElementById('deselectAllItems')?.addEventListener('click', function() {
            document.querySelectorAll('.item-filter-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            updateProgressChart();
        });

        const subprojectsData = @json($subprojectsChartData);
        const subprojectsCtx = document.getElementById('subprojectsChart').getContext('2d');
        const subprojectsChart = new Chart(subprojectsCtx, {
            type: 'bar',
            data: {
                labels: subprojectsData.labels || [],
                datasets: [
                    {
                        label: '{{ __('general.progress') }}',
                        data: subprojectsData.weighted_ratios || [],
                        backgroundColor: 'rgba(255, 99, 71, 0.7)',
                        borderColor: 'rgba(255, 99, 71, 1)',
                        borderWidth: 1,
                        borderRadius: 0
                    },
                    {
                        label: '{{ __('general.planned_progress') }}',
                        data: subprojectsData.planned_weighted_ratios || [],
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        borderRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        enabled: true
                    }
                },
                animation: {
                    onComplete: function() {
                        drawDataLabels(this);
                    }
                },
                scales: {
                    x: {
                        grouped: true
                    },
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

        setTimeout(() => {
            drawDataLabels(subprojectsChart);
        }, 100);

        const categoriesData = @json($categoriesChartData);
        const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
        const categoriesChart = new Chart(categoriesCtx, {
            type: 'bar',
            data: {
                labels: categoriesData.labels || [],
                datasets: [
                    {
                        label: '{{ __('general.progress') }}',
                        data: categoriesData.weighted_ratios || [],
                        backgroundColor: 'rgba(255, 99, 71, 0.7)',
                        borderColor: 'rgba(255, 99, 71, 1)',
                        borderWidth: 1,
                        borderRadius: 0
                    },
                    {
                        label: '{{ __('general.planned_progress') }}',
                        data: categoriesData.planned_weighted_ratios || [],
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        borderRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        grouped: true
                    },
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

        @if($project->subprojects->count() > 0)
        function loadTotalWeightedProgress() {
            const displayElement = document.getElementById('totalWeightedProgressDisplay');
            if (!displayElement) return;

            fetch(`/api/projects/{{ $project->id }}/subprojects`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                let subprojects = null;
                
                if (Array.isArray(data)) {
                    subprojects = data;
                } else if (data.success && Array.isArray(data.subprojects)) {
                    subprojects = data.subprojects;
                } else if (Array.isArray(data.subprojects)) {
                    subprojects = data.subprojects;
                }
                
                if (subprojects && subprojects.length > 0) {
                    
                    const actualProgressLabel = '{{ __('general.actual_progress') }}';
                    const plannedProgressLabel = '{{ __('general.planned_progress') }}';
                    const aheadByLabel = '{{ __('general.ahead_by') }}';
                    const behindByLabel = '{{ __('general.behind_by') }}';
                    const onTrackLabel = '{{ __('general.on_track') }}';
                    
                    // Use overallProgress from backend (same as /projects page) - ensures consistency
                    const totalProgressPercentage = {{ $overallProgress ?? 0 }};
                    
                    // Check if there are weighted subprojects (same logic as Project model)
                    const hasWeightedSubprojects = subprojects.some(sp => (sp.weight || 0) > 0);
                    
                    let totalPlannedProgressPercentage = 0;
                    let totalQuantity = 0;
                    let totalCompletedQuantity = 0;
                    let totalPlannedQuantity = 0;
                    
                    if (hasWeightedSubprojects) {
                        // Weighted calculation for planned progress (same as Project->getOverallProgressAttribute)
                        let overallPlannedProgress = 0;
                        
                        subprojects.forEach(subproject => {
                            const weight = subproject.weight || 0;
                            if (weight <= 0) return;
                            
                            const subTotalQty = subproject.total_quantity || 0;
                            const subPlannedQty = subproject.planned_total_quantity || 0;
                            
                            const subPlannedProgress = subTotalQty > 0 ? (subPlannedQty / subTotalQty) * 100 : 0;
                            
                            overallPlannedProgress += subPlannedProgress * (weight / 100);
                        });
                        
                        totalPlannedProgressPercentage = Math.round(overallPlannedProgress * 10) / 10;
                        
                        // For display purposes, calculate totals
                        totalQuantity = subprojects.reduce((sum, sp) => sum + (sp.total_quantity || 0), 0);
                        totalCompletedQuantity = subprojects.reduce((sum, sp) => sum + (sp.completed_quantity_under_100 || sp.completed_quantity || 0), 0);
                        totalPlannedQuantity = subprojects.reduce((sum, sp) => sum + (sp.planned_total_quantity || 0), 0);
                    } else {
                        // Simple calculation (Classic Mode - same as Project model fallback)
                        totalQuantity = subprojects.reduce((sum, sp) => sum + (sp.total_quantity || 0), 0);
                        totalCompletedQuantity = subprojects.reduce((sum, sp) => sum + (sp.completed_quantity_under_100 || sp.completed_quantity || 0), 0);
                        totalPlannedQuantity = subprojects.reduce((sum, sp) => sum + (sp.planned_total_quantity || 0), 0);
                        
                        totalPlannedProgressPercentage = totalQuantity > 0 ? Math.round((totalPlannedQuantity / totalQuantity) * 100 * 10) / 10 : 0;
                    }
                    
                    const progressDifference = totalCompletedQuantity - totalPlannedQuantity;
                    const differenceIcon = progressDifference > 0 ? 'fa-arrow-up' : progressDifference < 0 ? 'fa-arrow-down' : 'fa-equals';
                    const differenceColor = progressDifference > 0 ? 'text-success' : progressDifference < 0 ? 'text-warning' : 'text-white';
                    
                    const formatNumber = (num) => {
                        return num.toLocaleString('en-US', { maximumFractionDigits: 2, minimumFractionDigits: 0 });
                    };
                    
                    displayElement.innerHTML = `
                        <div class="text-center">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fas fa-check-circle fa-lg text-success"></i>
                                                <span class="text-white-50 fw-semibold">${actualProgressLabel}</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="fw-bold text-white" style="font-size: 2.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">${Math.min(totalProgressPercentage, 100).toFixed(1)}%</span>
                                            </div>
                                        </div>
                                        <div class="progress mb-2" style="height: 24px; background-color: rgba(255,255,255,0.15); border-radius: 12px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);">
                                            <div class="progress-bar bg-success d-flex align-items-center justify-content-end" 
                                                 role="progressbar" 
                                                 style="width: ${Math.min(totalProgressPercentage, 100)}%; border-radius: 12px; transition: width 0.6s ease;" 
                                                 aria-valuenow="${totalProgressPercentage}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                <span class="text-white fw-bold px-2" style="font-size: 0.75rem;">${Math.min(totalProgressPercentage, 100).toFixed(1)}%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fas fa-calendar-check fa-lg text-info"></i>
                                                <span class="text-white-50 fw-semibold">${plannedProgressLabel}</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="fw-bold text-white" style="font-size: 2.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">${Math.min(totalPlannedProgressPercentage, 100).toFixed(1)}%</span>
                                            </div>
                                        </div>
                                        <div class="progress mb-2" style="height: 24px; background-color: rgba(255,255,255,0.15); border-radius: 12px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);">
                                            <div class="progress-bar bg-info d-flex align-items-center justify-content-end" 
                                                 role="progressbar" 
                                                 style="width: ${Math.min(totalPlannedProgressPercentage, 100)}%; border-radius: 12px; transition: width 0.6s ease;" 
                                                 aria-valuenow="${totalPlannedProgressPercentage}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                <span class="text-white fw-bold px-2" style="font-size: 0.75rem;">${Math.min(totalPlannedProgressPercentage, 100).toFixed(1)}%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="p-2 rounded-3 text-center" style="background: rgba(255,255,255,0.08);">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <i class="fas ${differenceIcon} ${differenceColor}"></i>
                                            <span class="text-white-50 small">
                                                ${formatNumber(Math.abs(progressDifference))} 
                                                ${progressDifference > 0 ? aheadByLabel : progressDifference < 0 ? behindByLabel : onTrackLabel}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    updateProjectProgressBar(totalProgressPercentage);
                } else {
                    displayElement.innerHTML = '<div class="text-white-50">{{ __('general.no_subprojects_found') }}</div>';
                }
            })
            .catch(error => {
                console.error('Error loading total weighted progress:', error);
                displayElement.innerHTML = '<div class="text-white-50">{{ __('general.error_loading_data') }}</div>';
            });
        }
        
        function updateProjectProgressBar(progressValue) {
            const workItemsCard = document.querySelector('.stat-card .fa-tasks');
            if (workItemsCard) {
                const statCard = workItemsCard.closest('.stat-card');
                const progressBar = statCard.querySelector('.progress-bar.bg-primary');
                if (progressBar) {
                    progressBar.style.width = Math.min(progressValue, 100) + '%';
                }
            }
        }
        
        loadTotalWeightedProgress();
        
        function updateHierarchicalViewProgress() {
            fetch(`/api/projects/{{ $project->id }}/subprojects`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                let subprojects = null;
                
                if (Array.isArray(data)) {
                    subprojects = data;
                } else if (data.success && Array.isArray(data.subprojects)) {
                    subprojects = data.subprojects;
                } else if (Array.isArray(data.subprojects)) {
                    subprojects = data.subprojects;
                }
                
                if (subprojects && subprojects.length > 0) {
                    subprojects.forEach(subproject => {
                        const subprojectName = subproject.name;
                        const accordionItems = document.querySelectorAll('#hierarchicalAccordion .accordion-item');
                        
                        accordionItems.forEach(item => {
                            const subprojectNameElement = item.querySelector('strong');
                            if (subprojectNameElement && subprojectNameElement.textContent.trim() === subprojectName) {
                                const progressText = item.querySelector('.small.text-muted.mb-1');
                                if (progressText) {
                                    progressText.textContent = subproject.progress_under_100.toFixed(1) + '% {{ __('general.completed') }}';
                                }
                                
                                const progressBar = item.querySelector('.progress-bar');
                                if (progressBar) {
                                    const progressValue = Math.min(subproject.progress_under_100, 100);
                                    progressBar.style.width = progressValue + '%';
                                    
                                    progressBar.className = 'progress-bar ' + 
                                        (progressValue >= 80 ? 'bg-success' :
                                         progressValue >= 50 ? 'bg-primary' :
                                         progressValue >= 30 ? 'bg-warning' : 'bg-danger');
                                }
                            }
                        });
                    });
                }
            })
            .catch(error => {
                console.error('Error loading hierarchical view progress:', error);
            });
        }
        
        updateHierarchicalViewProgress();
        @endif

        const subprojectItemsData = @json($subprojectItemsChartData);
        let subprojectItemsChart = null;
        const subprojectItemsCtx = document.getElementById('subprojectItemsChart').getContext('2d');
        
        function updateSubprojectItemsChart(subprojectName, showAll = true) {
            if (subprojectItemsChart) {
                subprojectItemsChart.destroy();
            }
            
            if (!subprojectName || !subprojectItemsData[subprojectName]) {
                subprojectItemsChart = new Chart(subprojectItemsCtx, {
                    type: 'bar',
                    data: {
                        labels: [],
                        datasets: []
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
                return;
            }
            
            const data = subprojectItemsData[subprojectName];
            
            let filteredLabels = [];
            let filteredWeightedRatios = [];
            let filteredPlannedWeightedRatios = [];
            
            if (showAll) {
                filteredLabels = data.labels || [];
                filteredWeightedRatios = data.weighted_ratios || [];
                filteredPlannedWeightedRatios = data.planned_weighted_ratios || [];
            } else {
                const isMeasurable = data.is_measurable || [];
                filteredLabels = (data.labels || []).filter((label, index) => isMeasurable[index]);
                filteredWeightedRatios = (data.weighted_ratios || []).filter((ratio, index) => isMeasurable[index]);
                filteredPlannedWeightedRatios = (data.planned_weighted_ratios || []).filter((ratio, index) => isMeasurable[index]);
            }
            
            subprojectItemsChart = new Chart(subprojectItemsCtx, {
                type: 'bar',
                data: {
                    labels: filteredLabels,
                    datasets: [
                        {
                            label: '{{ __('general.progress') }}',
                            data: filteredWeightedRatios,
                            backgroundColor: 'rgba(255, 99, 71, 0.7)',
                            borderColor: 'rgba(255, 99, 71, 1)',
                            borderWidth: 1,
                            borderRadius: 0
                        },
                        {
                            label: '{{ __('general.planned_progress') }}',
                            data: filteredPlannedWeightedRatios,
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            borderRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        x: {
                            grouped: true
                        },
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
        }
        
        const subprojectSelect = document.getElementById('subprojectSelect');
        const showAllSubprojectItems = document.getElementById('showAllSubprojectItems');
        
        if (subprojectSelect) {
            subprojectSelect.addEventListener('change', function(e) {
                const showAll = showAllSubprojectItems ? showAllSubprojectItems.checked : true;
                updateSubprojectItemsChart(e.target.value, showAll);
            });
        }

        if (showAllSubprojectItems) {
            showAllSubprojectItems.addEventListener('change', function(e) {
                const subprojectName = subprojectSelect ? subprojectSelect.value : '';
                if (subprojectName) {
                    updateSubprojectItemsChart(subprojectName, e.target.checked);
                }
            });
        }

        const categoryItemsData = @json($categoryItemsChartData);
        let categoryItemsChart = null;
        const categoryItemsCtx = document.getElementById('categoryItemsChart').getContext('2d');
        
        function updateCategoryItemsChart(categoryName) {
            if (categoryItemsChart) {
                categoryItemsChart.destroy();
            }
            
            if (!categoryName || !categoryItemsData[categoryName]) {
                categoryItemsChart = new Chart(categoryItemsCtx, {
                    type: 'bar',
                    data: {
                        labels: [],
                        datasets: []
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
                return;
            }
            
            const data = categoryItemsData[categoryName];
            categoryItemsChart = new Chart(categoryItemsCtx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: '{{ __('general.progress') }}',
                            data: data.weighted_ratios || [],
                            backgroundColor: 'rgba(255, 99, 71, 0.7)',
                            borderColor: 'rgba(255, 99, 71, 1)',
                            borderWidth: 1,
                            borderRadius: 0
                        },
                        {
                            label: '{{ __('general.planned_progress') }}',
                            data: data.planned_weighted_ratios || [],
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            borderRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        x: {
                            grouped: true
                        },
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
        }
        
        const categorySelect = document.getElementById('categorySelect');
        if (categorySelect) {
            categorySelect.addEventListener('change', function(e) {
                updateCategoryItemsChart(e.target.value);
            });
        }

        setInterval(() => {
            console.log('Auto-refresh dashboard data');
        }, 30000);
    });

    (function() {
        const STORAGE_KEY = 'dashboard_components_visibility';

        const componentToCheckboxId = {
            'statistics-cards': 'check-statistics',
            'client-info': 'check-client',
            'employees-info': 'check-employees',
            'bar-chart': 'check-bar-chart',
            'total-weighted-progress': 'check-total-weighted-progress',
            'subprojects-chart': 'check-subprojects-chart',
            'categories-chart': 'check-categories-chart',
            'subproject-items-chart': 'check-subproject-items-chart',
            'category-items-chart': 'check-category-items-chart',
            'hierarchical-view': 'check-hierarchical',
            'work-items-progress': 'check-work-items',
            'recent-activity': 'check-activity',
            'timeline': 'check-timeline'
        };

        function loadVisibilitySettings() {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                try {
                    const settings = JSON.parse(saved);
                    Object.keys(settings).forEach(component => {
                        const checkboxId = componentToCheckboxId[component];
                        if (checkboxId) {
                            const checkbox = document.querySelector(`#${checkboxId}`);
                            if (checkbox) {
                                checkbox.checked = settings[component];
                            }
                        }

                        const element = document.querySelector(`[data-component="${component}"]`);
                        if (element) {
                            if (!settings[component]) {
                                element.classList.add('hidden');
                            } else {
                                element.classList.remove('hidden');
                            }
                        }
                    });
                } catch (e) {
                    console.error('Error loading visibility settings:', e);
                }
            }
        }

        function saveVisibilitySettings() {
            const checkboxes = document.querySelectorAll('.component-checkbox');
            const settings = {};

            checkboxes.forEach(checkbox => {
                const component = checkbox.value;
                settings[component] = checkbox.checked;

                const element = document.querySelector(`[data-component="${component}"]`);
                if (element) {
                    if (checkbox.checked) {
                        element.classList.remove('hidden');
                    } else {
                        element.classList.add('hidden');
                    }
                }
            });

            localStorage.setItem(STORAGE_KEY, JSON.stringify(settings));
        }

        function initializeCheckboxes() {
            const checkboxes = document.querySelectorAll('.component-checkbox');
            checkboxes.forEach(checkbox => {
                if (!checkbox.hasAttribute('data-listener-attached')) {
                    checkbox.setAttribute('data-listener-attached', 'true');
                    checkbox.addEventListener('change', function() {
                        const component = this.value;
                        const element = document.querySelector(`[data-component="${component}"]`);

                        if (element) {
                            if (this.checked) {
                                element.classList.remove('hidden');
                            } else {
                                element.classList.add('hidden');
                            }
                        }
                    });
                }
            });
        }


        const searchInput = document.getElementById('searchItems');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const itemCards = document.querySelectorAll('.item-card');

                itemCards.forEach(card => {
                    const itemName = card.getAttribute('data-item-name') || '';
                    if (itemName.includes(searchTerm) || searchTerm === '') {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }

        function initializeDashboard() {
            loadVisibilitySettings();
            initializeCheckboxes();

            const saveButton = document.getElementById('saveComponentsSettings');
            if (saveButton && !saveButton.hasAttribute('data-listener-attached')) {
                saveButton.setAttribute('data-listener-attached', 'true');
                saveButton.addEventListener('click', function() {
                    saveVisibilitySettings();
                    const modalElement = document.getElementById('componentsModal');
                    if (modalElement) {
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                        } else {
                            const bsModal = new bootstrap.Modal(modalElement);
                            bsModal.hide();
                        }
                    }
                });
            }

            const selectAllBtn = document.getElementById('selectAllComponents');
            if (selectAllBtn && !selectAllBtn.hasAttribute('data-listener-attached')) {
                selectAllBtn.setAttribute('data-listener-attached', 'true');
                selectAllBtn.addEventListener('click', function() {
                    document.querySelectorAll('.component-checkbox').forEach(cb => {
                        cb.checked = true;
                        const component = cb.value;
                        const element = document.querySelector(`[data-component="${component}"]`);
                        if (element) {
                            element.classList.remove('hidden');
                        }
                    });
                });
            }

            const deselectAllBtn = document.getElementById('deselectAllComponents');
            if (deselectAllBtn && !deselectAllBtn.hasAttribute('data-listener-attached')) {
                deselectAllBtn.setAttribute('data-listener-attached', 'true');
                deselectAllBtn.addEventListener('click', function() {
                    document.querySelectorAll('.component-checkbox').forEach(cb => {
                        cb.checked = false;
                        const component = cb.value;
                        const element = document.querySelector(`[data-component="${component}"]`);
                        if (element) {
                            element.classList.add('hidden');
                        }
                    });
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeDashboard);
        } else {
            setTimeout(initializeDashboard, 100);
        }
    })();

    (function() {
        const workItemsSearch = document.getElementById('workItemsSearch');
        const workItemsSubprojectFilter = document.getElementById('workItemsSubprojectFilter');
        const workItemsCategoryFilter = document.getElementById('workItemsCategoryFilter');
        const workItemsStatusFilter = document.getElementById('workItemsStatusFilter');
        const itemStatusFilter = document.getElementById('itemStatusFilter');
        const clearFiltersBtn = document.getElementById('clearWorkItemsFilters');
        const workItemsContainer = document.getElementById('workItemsContainer');
        const workItemsCount = document.getElementById('workItemsCount');

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        let cachedItems = null;
        function getItems() {
            if (!cachedItems) {
                cachedItems = Array.from(document.querySelectorAll('.work-item-card'));
            }
            return cachedItems;
        }

        function filterWorkItems() {
            const searchTerm = (workItemsSearch?.value || '').toLowerCase().trim();
            const subprojectFilter = workItemsSubprojectFilter?.value || '';
            const categoryFilter = workItemsCategoryFilter?.value || '';
            const statusFilter = workItemsStatusFilter?.value || '';
            const itemStatusIdFilter = itemStatusFilter?.value || '';

            const items = getItems();
            let visibleCount = 0;

            items.forEach(item => {
                const itemName = item.getAttribute('data-item-name') || '';
                const itemSubproject = item.getAttribute('data-subproject') || '';
                const itemCategory = item.getAttribute('data-category') || '';
                const itemStatus = item.getAttribute('data-status') || '';
                const itemStatusId = item.getAttribute('data-item-status-id') || '';
                
                const itemText = item.textContent.toLowerCase();

                const matchesSearch = !searchTerm || 
                    itemName.includes(searchTerm) ||
                    itemText.includes(searchTerm) ||
                    itemSubproject.toLowerCase().includes(searchTerm) ||
                    itemCategory.includes(searchTerm);

                let matchesSubproject = true;
                if (subprojectFilter) {
                    const withoutSubprojectValue = '{{ __('general.without_subproject') }}';
                    if (subprojectFilter === withoutSubprojectValue || subprojectFilter === 'بدون فرعي' || subprojectFilter === 'Without Subproject') {
                        matchesSubproject = (!itemSubproject || itemSubproject === '' || 
                                           itemSubproject === 'uncategorized' || 
                                           itemSubproject === withoutSubprojectValue ||
                                           itemSubproject === 'بدون فرعي' ||
                                           itemSubproject === 'Without Subproject');
                    } else {
                        matchesSubproject = (itemSubproject === subprojectFilter.toLowerCase() || 
                                           itemSubproject === subprojectFilter);
                    }
                }

                let matchesCategory = true;
                if (categoryFilter) {
                    if (categoryFilter === 'uncategorized') {
                        matchesCategory = (itemCategory === 'uncategorized' || itemCategory === '');
                    } else {
                        matchesCategory = (itemCategory === categoryFilter.toLowerCase() || 
                                         itemCategory === categoryFilter);
                    }
                }

                const matchesStatus = !statusFilter || itemStatus === statusFilter;

                let matchesItemStatus = true;
                if (itemStatusIdFilter) {
                    if (itemStatusIdFilter === 'none' || itemStatusIdFilter === '') {
                        matchesItemStatus = (!itemStatusId || itemStatusId === '');
                    } else {
                        matchesItemStatus = (itemStatusId === itemStatusIdFilter);
                    }
                }

                if (matchesSearch && matchesSubproject && matchesCategory && matchesStatus && matchesItemStatus) {
                    item.style.display = '';
                    item.style.opacity = '1';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                    item.style.opacity = '0';
                }
            });

            if (workItemsCount) {
                const totalItems = items.length;
                if (visibleCount === totalItems) {
                    workItemsCount.textContent = totalItems + ' {{ __('general.items') }}';
                    workItemsCount.className = 'badge bg-primary';
                } else {
                    workItemsCount.textContent = visibleCount + ' / ' + totalItems + ' {{ __('general.items') }}';
                    workItemsCount.className = 'badge bg-info';
                }
            }
        }

        if (workItemsSearch) {
            workItemsSearch.addEventListener('input', debounce(filterWorkItems, 150));
            workItemsSearch.addEventListener('paste', debounce(filterWorkItems, 150));
        }
        
        if (workItemsSubprojectFilter) {
            workItemsSubprojectFilter.addEventListener('change', filterWorkItems);
        }
        
        if (workItemsCategoryFilter) {
            workItemsCategoryFilter.addEventListener('change', filterWorkItems);
        }
        
        if (workItemsStatusFilter) {
            workItemsStatusFilter.addEventListener('change', filterWorkItems);
        }
        
        if (itemStatusFilter) {
            itemStatusFilter.addEventListener('change', filterWorkItems);
        }
        
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', function() {
                if (workItemsSearch) workItemsSearch.value = '';
                if (workItemsSubprojectFilter) workItemsSubprojectFilter.value = '';
                if (workItemsCategoryFilter) workItemsCategoryFilter.value = '';
                if (workItemsStatusFilter) workItemsStatusFilter.value = '';
                if (itemStatusFilter) itemStatusFilter.value = '';
                filterWorkItems();
            });
        }

        const observer = new MutationObserver(() => {
            cachedItems = null;
        });
        if (workItemsContainer) {
            observer.observe(workItemsContainer, { childList: true, subtree: true });
        }
        
        const showPlannedProgressCheckbox = document.getElementById('showPlannedProgress');
        if (showPlannedProgressCheckbox) {
            const plannedProgressSections = document.querySelectorAll('.planned-progress-section');
            
            function togglePlannedProgress() {
                plannedProgressSections.forEach(section => {
                    if (showPlannedProgressCheckbox.checked) {
                        section.style.display = '';
                    } else {
                        section.style.display = 'none';
                    }
                });
            }
            
            showPlannedProgressCheckbox.addEventListener('change', togglePlannedProgress);
            
            togglePlannedProgress();
        }
    })();

    (function() {
        const toggleGroupedViewBtn = document.getElementById('toggleGroupedView');
        const toggleGroupedText = document.getElementById('toggleGroupedText');
        const workItemsContainer = document.getElementById('workItemsContainer');
        const workItemsGroupedContainer = document.getElementById('workItemsGroupedContainer');
        let isGroupedView = false;

        function getItemsData() {
            const items = [];
            @foreach ($project->items as $item)
            @php
            $completionPercentage = $item->total_quantity > 0
                ? ($item->completed_quantity / $item->total_quantity) * 100
                : 0;
            @endphp
            items.push({
                name: @json($item->workItem->name ?? ''),
                category: @json($item->workItem && $item->workItem->category ? $item->workItem->category->name : 'uncategorized'),
                unit: @json($item->workItem->unit ?? ''),
                subproject: @json($item->subproject_name ?? ''),
                completed_quantity: {{ $item->completed_quantity }},
                remaining_quantity: {{ $item->remaining_quantity }},
                total_quantity: {{ $item->total_quantity }},
                completion_percentage: {{ $completionPercentage }},
                status: '{{ $item->total_quantity > 0 && $item->completed_quantity >= $item->total_quantity ? "completed" : ($item->completed_quantity > 0 ? "in_progress" : "pending") }}'
            });
            @endforeach
            return items;
        }

        const statusTranslations = {
            'completed': '{{ __('general.completed') }}',
            'in_progress': '{{ __('general.in_progress') }}',
            'pending': '{{ __('general.pending') }}',
            'delayed': '{{ __('general.delayed') }}'
        };
        
        function getStatusText(status) {
            return statusTranslations[status] || status;
        }

        function groupItems(items) {
            const grouped = {};
            
            items.forEach(item => {
                const key = `${item.name}_${item.unit}`.toLowerCase();
                
                if (!grouped[key]) {
                    grouped[key] = {
                        name: item.name,
                        unit: item.unit,
                        category: item.category,
                        subprojects: [],
                        completed_quantity: 0,
                        remaining_quantity: 0,
                        total_quantity: 0,
                        items_count: 0,
                        status: item.status
                    };
                }
                
                if (item.subproject && !grouped[key].subprojects.includes(item.subproject)) {
                    grouped[key].subprojects.push(item.subproject);
                }
                
                grouped[key].completed_quantity += item.completed_quantity;
                grouped[key].remaining_quantity += item.remaining_quantity;
                grouped[key].total_quantity += item.total_quantity;
                grouped[key].items_count += 1;
            });

            Object.keys(grouped).forEach(key => {
                const group = grouped[key];
                group.completion_percentage = group.total_quantity > 0 
                    ? (group.completed_quantity / group.total_quantity) * 100 
                    : 0;
                
                if (group.completion_percentage >= 100) {
                    group.status = 'completed';
                } else if (group.completion_percentage > 0) {
                    group.status = 'in_progress';
                } else {
                    group.status = 'pending';
                }
            });

            return Object.values(grouped);
        }

        function renderGroupedView() {
            const items = getItemsData();
            const grouped = groupItems(items);
            
            workItemsGroupedContainer.innerHTML = '';
            
            if (grouped.length === 0) {
                workItemsGroupedContainer.innerHTML = '<div class="col-12 text-center py-4"><p class="text-muted">لا توجد عناصر</p></div>';
                return;
            }

            grouped.forEach(group => {
                const card = document.createElement('div');
                card.className = 'col-md-6 mb-4 grouped-item-card';
                card.setAttribute('data-item-name', group.name.toLowerCase());
                card.setAttribute('data-category', group.category.toLowerCase());
                card.setAttribute('data-status', group.status);
                
                const statusColors = {
                    'completed': 'success',
                    'in_progress': 'primary',
                    'delayed': 'danger',
                    'pending': 'warning'
                };
                const statusColor = statusColors[group.status] || 'secondary';
                
                const progressColor = group.completion_percentage >= 80 ? 'success' :
                                    group.completion_percentage >= 50 ? 'primary' :
                                    group.completion_percentage >= 30 ? 'warning' : 'danger';

                card.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <div class="fw-bold text-dark">${group.name}</div>
                            ${group.category && group.category !== 'uncategorized' ? 
                                `<small class="text-muted d-block"><i class="fas fa-folder me-1"></i>${group.category}</small>` : ''}
                            ${group.subprojects && group.subprojects.length > 0 ? 
                                `<small class="text-primary d-block"><i class="fas fa-sitemap me-1"></i>${group.subprojects.join(', ')}</small>` : 
                                `<small class="text-muted d-block"><i class="fas fa-sitemap me-1"></i>{{ __('general.without_subproject') }}</small>`}
                            ${group.unit ? 
                                `<small class="text-muted d-block"><i class="fas fa-ruler me-1"></i>${group.unit}</small>` : ''}
                            <small class="text-muted d-block"><i class="fas fa-list me-1"></i>عدد الأصناف: ${group.items_count}</small>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold text-primary d-block">${group.completion_percentage.toFixed(1)}%</span>
                            <span class="badge bg-${statusColor} badge-sm">
                                ${getStatusText(group.status)}
                            </span>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">
                                <i class="fas fa-check-circle me-1 text-success"></i>التقدم الفعلي
                            </small>
                            <small class="fw-bold text-success">${group.completion_percentage.toFixed(1)}%</small>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 8px;">
                            <div class="progress-bar bg-${progressColor}" 
                                 role="progressbar" 
                                 style="width: ${Math.min(group.completion_percentage, 100)}%">
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-2 text-muted small">
                        <span><i class="fas fa-check-circle text-success me-1"></i>${group.completed_quantity.toFixed(2)} ${group.unit}</span>
                        <span><i class="fas fa-clock text-warning me-1"></i>${group.remaining_quantity.toFixed(2)} ${group.unit}</span>
                        <span><i class="fas fa-list text-info me-1"></i>${group.total_quantity.toFixed(2)} ${group.unit}</span>
                    </div>
                `;
                
                workItemsGroupedContainer.appendChild(card);
            });

            const countBadge = document.getElementById('workItemsCount');
            if (countBadge) {
                countBadge.textContent = grouped.length + ' {{ __('general.items') }} (مجمعة)';
            }
        }

        if (toggleGroupedViewBtn) {
            toggleGroupedViewBtn.addEventListener('click', function() {
                isGroupedView = !isGroupedView;
                
                if (isGroupedView) {
                    workItemsContainer.style.display = 'none';
                    workItemsGroupedContainer.style.display = 'flex';
                    toggleGroupedText.textContent = 'عرض الأصناف الفردية';
                    renderGroupedView();
                } else {
                    workItemsContainer.style.display = 'flex';
                    workItemsGroupedContainer.style.display = 'none';
                    toggleGroupedText.textContent = '{{ __('general.group_by_item') }}';
                    const countBadge = document.getElementById('workItemsCount');
                    if (countBadge) {
                        const totalItems = document.querySelectorAll('.work-item-card').length;
                        countBadge.textContent = totalItems + ' {{ __('general.items') }}';
                    }
                }
            });
        }
    })();
</script>

<!-- Modal for Update All Subproject Weights -->
<div class="modal fade" id="updateAllWeightsModal" tabindex="-1" aria-labelledby="updateAllWeightsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateAllWeightsModalLabel">
                    <i class="fas fa-balance-scale me-2"></i>{{ __('general.update_all_weights') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">{{ __('general.update_all_weights_description') }}</p>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('general.subproject') }}</th>
                                <th class="text-end" style="width: 200px;">{{ __('general.weight') }} (%)</th>
                            </tr>
                        </thead>
                        <tbody id="weightsTableBody">
                            @foreach($hierarchicalData ?? [] as $subprojectName => $subprojectData)
                            @php
                                $subproject = $subprojectData['subproject'] ?? null;
                            @endphp
                            @if($subproject)
                            <tr>
                                <td>
                                    <strong>{{ $subprojectName }}</strong>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control form-control-sm weight-input-dashboard" 
                                               data-subproject-id="{{ $subproject->id }}"
                                               value="{{ $subproject->weight ?? 0 }}"
                                               min="0"
                                               max="100"
                                               step="0.1"
                                               onchange="updateWeightInput(this)">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <th>{{ __('general.total') }}</th>
                                <th class="text-end">
                                    <span id="totalWeightDisplay" class="fw-bold">0%</span>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div id="weightValidationMessage" class="alert alert-warning mt-3" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span id="weightValidationText"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="confirmUpdateAllWeightsBtn">
                    <i class="fas fa-save me-1"></i>{{ __('general.update_all_weights') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateAllWeightsModal = new bootstrap.Modal(document.getElementById('updateAllWeightsModal'));
    const confirmUpdateAllWeightsBtn = document.getElementById('confirmUpdateAllWeightsBtn');
    const totalWeightDisplay = document.getElementById('totalWeightDisplay');
    const weightValidationMessage = document.getElementById('weightValidationMessage');
    const weightValidationText = document.getElementById('weightValidationText');

    function updateTotalWeight() {
        const allWeightInputs = document.querySelectorAll('.weight-input-dashboard');
        let totalWeight = 0;
        
        allWeightInputs.forEach(input => {
            const weight = parseFloat(input.value) || 0;
            totalWeight += weight;
        });
        
        totalWeightDisplay.textContent = totalWeight.toFixed(2) + '%';
        
        if (Math.abs(totalWeight - 100) > 0.01) {
            weightValidationMessage.style.display = 'block';
            weightValidationText.textContent = '{{ __('general.total_weights_must_equal_100') }}: ' + totalWeight.toFixed(2) + '%';
            weightValidationMessage.className = 'alert alert-warning mt-3';
            confirmUpdateAllWeightsBtn.disabled = true;
        } else {
            weightValidationMessage.style.display = 'none';
            confirmUpdateAllWeightsBtn.disabled = false;
        }
    }

    window.updateWeightInput = function(input) {
        const weight = parseFloat(input.value) || 0;
        if (weight < 0) input.value = 0;
        if (weight > 100) input.value = 100;
        
        input.style.borderColor = weight > 0 ? '#28a745' : '#e9ecef';
        
        updateTotalWeight();
    };

    const modalElement = document.getElementById('updateAllWeightsModal');
    if (modalElement) {
        modalElement.addEventListener('show.bs.modal', function() {
            setTimeout(() => {
                updateTotalWeight();
                const inputs = modalElement.querySelectorAll('.weight-input-dashboard');
                inputs.forEach(input => {
                    input.addEventListener('input', updateTotalWeight);
                    input.addEventListener('change', updateTotalWeight);
                });
            }, 100);
        });
    }

    document.querySelectorAll('.weight-input-dashboard').forEach(input => {
        input.addEventListener('input', updateTotalWeight);
        input.addEventListener('change', updateTotalWeight);
    });

    if (confirmUpdateAllWeightsBtn) {
        confirmUpdateAllWeightsBtn.addEventListener('click', function() {
            const allWeightInputs = document.querySelectorAll('.weight-input-dashboard');
            const weights = {};
            
            allWeightInputs.forEach(input => {
                const subprojectId = input.getAttribute('data-subproject-id');
                const weight = parseFloat(input.value) || 0;
                if (subprojectId) {
                    weights[subprojectId] = weight;
                }
            });

            const totalWeight = Object.values(weights).reduce((sum, w) => sum + w, 0);
            if (Math.abs(totalWeight - 100) > 0.01) {
                alert('{{ __('general.total_weights_must_equal_100') }}: ' + totalWeight.toFixed(2) + '%');
                return;
            }

            confirmUpdateAllWeightsBtn.disabled = true;
            confirmUpdateAllWeightsBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __('general.updating') }}...';

            fetch('{{ route("progress.projects.update-all-subprojects-weight", $project) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    weights: weights
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || '{{ __('general.all_weights_updated_successfully') }}');
                    updateAllWeightsModal.hide();
                    location.reload();
                } else {
                    alert(data.message || '{{ __('general.error_occurred') }}');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __('general.error_occurred') }}: ' + error.message);
            })
            .finally(() => {
                confirmUpdateAllWeightsBtn.disabled = false;
                confirmUpdateAllWeightsBtn.innerHTML = '<i class="fas fa-save me-1"></i>{{ __('general.update_all_weights') }}';
            });
        });
    }

    (function() {
        function initItemStatusAutoSave() {
            const statusSelects = document.querySelectorAll('.item-status-select');
            
            if (statusSelects.length === 0) {
                console.log('No item status selects found');
                return;
            }
            
            console.log('Found ' + statusSelects.length + ' item status selects');
            
            statusSelects.forEach(select => {
                if (select.hasAttribute('data-listener-attached')) {
                    return;
                }
                
                select.setAttribute('data-listener-attached', 'true');
                
                select.dataset.originalValue = select.value || '';
                
                select.addEventListener('change', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const itemId = this.dataset.itemId;
                    const projectId = this.dataset.projectId;
                    const statusId = this.value || null;
                    const card = this.closest('.work-item-card');
                    const loadingDiv = card ? card.querySelector('.item-status-loading') : null;
                    const originalValue = this.dataset.originalValue || '';
                    
                    console.log('Status changed:', {
                        itemId: itemId,
                        projectId: projectId,
                        statusId: statusId,
                        originalValue: originalValue
                    });
                    
                    if (!itemId || !projectId) {
                        console.error('Missing itemId or projectId');
                        this.value = originalValue;
                        return;
                    }
                    
                    if (loadingDiv) {
                        loadingDiv.classList.remove('d-none');
                    }
                    this.disabled = true;
                    
                    const url = `/progress/projects/${projectId}/items/${itemId}/status`;
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    
                    console.log('Sending request to:', url);
                    
                    fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            item_status_id: statusId
                        })
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        if (!response.ok) {
                            return response.json().then(err => {
                                throw new Error(err.message || 'Request failed');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            if (card) {
                                card.setAttribute('data-item-status-id', data.item.item_status_id || '');
                            }
                            
                            this.dataset.originalValue = statusId || '';
                            
                            if (window.showToast) {
                                showToast(data.message || '{{ __('general.item_status_updated_successfully') }}', 'success');
                            } else {
                                alert(data.message || '{{ __('general.item_status_updated_successfully') }}');
                            }
                        } else {
                            this.value = originalValue || '';
                            const errorMsg = data.message || '{{ __('general.error_updating_status') }}';
                            if (window.showToast) {
                                showToast(errorMsg, 'error');
                            } else {
                                alert(errorMsg);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error updating status:', error);
                        this.value = originalValue || '';
                        const errorMsg = error.message || '{{ __('general.error_updating_status') }}';
                        if (window.showToast) {
                            showToast(errorMsg, 'error');
                        } else {
                            alert(errorMsg);
                        }
                    })
                    .finally(() => {
                        if (loadingDiv) {
                            loadingDiv.classList.add('d-none');
                        }
                        this.disabled = false;
                    });
                });
            });
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initItemStatusAutoSave);
        } else {
            setTimeout(initItemStatusAutoSave, 100);
        }
    })();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const printBtn = document.getElementById('printDashboardBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            const STORAGE_KEY = 'dashboard_components_visibility';
            const saved = localStorage.getItem(STORAGE_KEY);
            let settings = {};
            
            if (saved) {
                try {
                    settings = JSON.parse(saved);
                } catch (e) {
                    console.error('Error parsing saved settings:', e);
                }
            } else {
                document.querySelectorAll('.component-checkbox').forEach(checkbox => {
                    settings[checkbox.value] = checkbox.checked;
                });
            }
            
            const url = new URL('{{ route("progress.projects.dashboard.print", $project->id) }}', window.location.origin);
            url.searchParams.set('components', JSON.stringify(settings));
            
            window.open(url.toString(), '_blank');
        });
    }

    const dailyProgressSearch = document.getElementById('dailyProgressSearch');
    const dailyProgressFromDate = document.getElementById('dailyProgressFromDate');
    const dailyProgressToDate = document.getElementById('dailyProgressToDate');
    const clearDailyProgressFilters = document.getElementById('clearDailyProgressFilters');
    const dailyProgressTimeline = document.getElementById('dailyProgressTimeline');

    function filterDailyProgress() {
        const searchTerm = (dailyProgressSearch?.value || '').toLowerCase();
        const fromDate = dailyProgressFromDate?.value || '';
        const toDate = dailyProgressToDate?.value || '';

        const timelineItems = dailyProgressTimeline?.querySelectorAll('.timeline-item') || [];

        timelineItems.forEach(timelineItem => {
            const date = timelineItem.getAttribute('data-date');
            const progressItems = timelineItem.querySelectorAll('.progress-item');
            let shouldShowTimelineItem = true;
            let hasVisibleItems = false;

            if (fromDate && date < fromDate) {
                shouldShowTimelineItem = false;
            }
            if (toDate && date > toDate) {
                shouldShowTimelineItem = false;
            }

            if (!shouldShowTimelineItem) {
                timelineItem.style.display = 'none';
                return;
            }

            progressItems.forEach(progressItem => {
                const employeeName = (progressItem.getAttribute('data-employee') || '').toLowerCase();
                const itemName = (progressItem.getAttribute('data-item') || '').toLowerCase();
                const matchesSearch = !searchTerm || 
                    employeeName.includes(searchTerm) || 
                    itemName.includes(searchTerm);

                if (matchesSearch) {
                    progressItem.style.display = '';
                    hasVisibleItems = true;
                } else {
                    progressItem.style.display = 'none';
                }
            });

            if (hasVisibleItems) {
                timelineItem.style.display = '';
            } else {
                timelineItem.style.display = 'none';
            }
        });
    }

    if (dailyProgressSearch) {
        dailyProgressSearch.addEventListener('input', filterDailyProgress);
    }
    if (dailyProgressFromDate) {
        dailyProgressFromDate.addEventListener('change', filterDailyProgress);
    }
    if (dailyProgressToDate) {
        dailyProgressToDate.addEventListener('change', filterDailyProgress);
    }
    if (clearDailyProgressFilters) {
        clearDailyProgressFilters.addEventListener('click', function() {
            if (dailyProgressSearch) dailyProgressSearch.value = '';
            if (dailyProgressFromDate) dailyProgressFromDate.value = '';
            if (dailyProgressToDate) dailyProgressToDate.value = '';
            filterDailyProgress();
        });
    }

});
</script>
@endsection
