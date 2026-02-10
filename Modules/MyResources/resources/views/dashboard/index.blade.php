@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.myresources')
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary">
                <div class="card-body">
                    <h2 class="mb-0">
                        <i class="fas fa-cubes me-2"></i>
                        {{ __('Resources Dashboard') }}
                    </h2>
                    <p class="mb-0 mt-2">{{ __('Comprehensive dashboard to track all resources and costs') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Resources -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">{{ __('Total Resources') }}</h6>
                            <h3 class="mb-0">{{ $totalResources }}</h3>
                            <small class="text-success">
                                <i class="fas fa-check-circle"></i> {{ __('Active') }}: {{ $activeResources }}
                            </small>
                        </div>
                        <div class="text-primary" style="font-size: 3rem;">
                            <i class="fas fa-cubes"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Assignments -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">{{ __('Active Assignments') }}</h6>
                            <h3 class="mb-0">{{ $activeAssignments }}</h3>
                            <small class="text-info">
                                <i class="fas fa-clock"></i> {{ __('Scheduled') }}: {{ $scheduledAssignments }}
                            </small>
                        </div>
                        <div class="text-info" style="font-size: 3rem;">
                            <i class="fas fa-tasks"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Maintenance -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">{{ __('Upcoming Maintenance') }}</h6>
                            <h3 class="mb-0">{{ $upcomingMaintenance->count() }}</h3>
                            <small class="text-warning">
                                <i class="fas fa-calendar-alt"></i> {{ __('Within 7 days') }}
                            </small>
                        </div>
                        <div class="text-warning" style="font-size: 3rem;">
                            <i class="fas fa-wrench"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resources by Status -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">{{ __('By Status') }}</h6>
                            <h3 class="mb-0">{{ $resourcesByStatus->count() }}</h3>
                            <small class="text-muted">
                                <i class="fas fa-chart-pie"></i> {{ __('Different statuses') }}
                            </small>
                        </div>
                        <div class="text-secondary" style="font-size: 3rem;">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Recent Activity -->
    <div class="row">
        <!-- Resources by Category -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        {{ __('Resources by Category') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Category') }}</th>
                                    <th class="text-center">{{ __('Count') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($resourcesByCategory as $category)
                                <tr>
                                    <td>{{ $category->category->name ?? $category->category->name_ar ?? __('Unspecified') }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $category->count }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center">{{ __('No resources found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resources by Status -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        {{ __('Resources by Status') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Status') }}</th>
                                    <th class="text-center">{{ __('Count') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($resourcesByStatus as $status)
                                <tr>
                                    <td>
                                        <span >
                                            {{ $status->status->name ?? $status->status->name_ar ?? __('Unspecified') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $status->count }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center">{{ __('No statuses found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Maintenance & Recent Assignments -->
    <div class="row">
        <!-- Upcoming Maintenance -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-wrench me-2"></i>
                        {{ __('Upcoming Maintenance (within 7 days)') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Resource') }}</th>
                                    <th>{{ __('Maintenance Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingMaintenance as $resource)
                                <tr>
                                    <td>
                                        <strong>{{ $resource->code }}</strong><br>
                                        <small class="text-muted">{{ $resource->name }}</small>
                                    </td>
                                    <td>{{ $resource->next_maintenance_date?->format('Y-m-d') ?? '---' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $resource->status->color ?? 'secondary' }}">
                                            {{ $resource->status->name ?? $resource->status->name_ar ?? __('Unspecified') }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">{{ __('No upcoming maintenance') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Assignments -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        {{ __('Recent Assignments') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Resource') }}</th>
                                    <th>{{ __('Project') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAssignments as $assignment)
                                <tr>
                                    <td>
                                        @if($assignment->resource)
                                            <strong>{{ $assignment->resource->code }}</strong><br>
                                            <small class="text-muted">{{ $assignment->resource->name }}</small>
                                        @else
                                            <span class="text-muted">---</span>
                                        @endif
                                    </td>
                                    <td>{{ $assignment->project->name ?? '---' }}</td>
                                    <td>{{ $assignment->start_date?->format('Y-m-d') ?? '---' }}</td>
                                    <td>
                                        @if($assignment->status instanceof \Modules\MyResources\Enums\ResourceAssignmentStatus)
                                            <span class="badge bg-{{ $assignment->status->color() }}">
                                                {{ $assignment->status->label() }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">{{ $assignment->status ?? '---' }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">{{ __('No assignments found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        {{ __('Quick Actions') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="{{ route('myresources.index') }}" class="btn btn-primary w-100">
                                <i class="fas fa-list me-2"></i>
                                {{ __('Resources List') }}
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('myresources.create') }}" class="btn btn-success w-100">
                                <i class="fas fa-plus-circle me-2"></i>
                                {{ __('New Resource') }}
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('myresources.assignments.create') }}" class="btn btn-info w-100">
                                <i class="fas fa-tasks me-2"></i>
                                {{ __('New Assignment') }}
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('myresources.categories.index') }}" class="btn btn-warning w-100">
                                <i class="fas fa-folder me-2"></i>
                                {{ __('Categories') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

