@extends('progress::layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-muted text-decoration-none">
            {{ __('general.dashboard') }}
        </a>
    </li>
@endsection

@section('title', __('general.issues_management'))

@push('styles')
<style>
    .stat-card {
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-3px);
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .priority-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    .issue-card {
        border-left: 4px solid;
        transition: all 0.3s ease;
    }
    .issue-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="fas fa-bug me-2"></i>{{ __('general.issues_management') }}</h4>
            <p class="text-muted mb-0">{{ __('general.manage_project_issues') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('progress.issues.kanban') }}" class="btn btn-outline-primary">
                <i class="fas fa-columns me-1"></i>{{ __('general.kanban_board') }}
            </a>
            @can('create progress-issues')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createIssueModal">
                <i class="fas fa-plus me-1"></i>{{ __('general.new_issue') }}
            </button>
            @endcan
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-icon bg-white bg-opacity-25 mb-2">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <h5 class="mb-0">{{ $statistics['total_open'] }}</h5>
                        <small class="opacity-75">{{ __('general.open_issues') }}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-icon bg-white bg-opacity-25 mb-2">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h5 class="mb-0">{{ $statistics['total_closed'] }}</h5>
                        <small class="opacity-75">{{ __('general.closed_issues') }}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-danger text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-icon bg-white bg-opacity-25 mb-2">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h5 class="mb-0">{{ $statistics['overdue'] }}</h5>
                        <small class="opacity-75">{{ __('general.overdue_issues') }}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-info text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-icon bg-white bg-opacity-25 mb-2">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h5 class="mb-0">{{ $statistics['by_status']['In Progress'] }}</h5>
                        <small class="opacity-75">{{ __('general.in_progress') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('progress.issues.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">{{ __('general.status') }}</label>
                        <select name="status" class="form-select">
                            <option value="">{{ __('general.all') }}</option>
                            <option value="New" {{ request('status') == 'New' ? 'selected' : '' }}>{{ __('general.new') }}</option>
                            <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>{{ __('general.in_progress') }}</option>
                            <option value="Testing" {{ request('status') == 'Testing' ? 'selected' : '' }}>{{ __('general.testing') }}</option>
                            <option value="Closed" {{ request('status') == 'Closed' ? 'selected' : '' }}>{{ __('general.closed') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('general.priority') }}</label>
                        <select name="priority" class="form-select">
                            <option value="">{{ __('general.all') }}</option>
                            <option value="Low" {{ request('priority') == 'Low' ? 'selected' : '' }}>{{ __('general.low') }}</option>
                            <option value="Medium" {{ request('priority') == 'Medium' ? 'selected' : '' }}>{{ __('general.medium') }}</option>
                            <option value="High" {{ request('priority') == 'High' ? 'selected' : '' }}>{{ __('general.high') }}</option>
                            <option value="Urgent" {{ request('priority') == 'Urgent' ? 'selected' : '' }}>{{ __('general.urgent') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('general.project') }}</label>
                        <select name="project_id" class="form-select">
                            <option value="">{{ __('general.all') }}</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('general.assigned_to') }}</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">{{ __('general.all') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('general.module') }}</label>
                        <input type="text" name="module" class="form-control" value="{{ request('module') }}" placeholder="{{ __('general.module') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('general.search') }}</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('general.search') }}">
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-3">
                        <label class="form-label">{{ __('general.deadline_from') }}</label>
                        <input type="date" name="deadline_from" class="form-control" value="{{ request('deadline_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('general.deadline_to') }}</label>
                        <input type="date" name="deadline_to" class="form-control" value="{{ request('deadline_to') }}">
                    </div>
                    <div class="col-md-6 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i>{{ __('general.filter') }}
                        </button>
                        <a href="{{ route('progress.issues.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo me-1"></i>{{ __('general.reset') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Issues Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('general.title') }}</th>
                            <th>{{ __('general.project') }}</th>
                            <th>{{ __('general.priority') }}</th>
                            <th>{{ __('general.status') }}</th>
                            <th>{{ __('general.assigned_to') }}</th>
                            <th>{{ __('general.deadline') }}</th>
                            <th>{{ __('general.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($issues as $issue)
                        <tr class="issue-card" style="border-left-color: {{ $issue->priority == 'Urgent' ? '#dc3545' : ($issue->priority == 'High' ? '#fd7e14' : ($issue->priority == 'Medium' ? '#ffc107' : '#0dcaf0')) }};">
                            <td>{{ $issue->id }}</td>
                            <td>
                                <a href="{{ route('progress.issues.show', $issue) }}" class="text-decoration-none">
                                    {{ Str::limit($issue->title, 50) }}
                                </a>
                                @if($issue->isOverdue())
                                    <span class="badge bg-danger ms-1">{{ __('general.overdue') }}</span>
                                @endif
                            </td>
                            <td>{{ $issue->project->name ?? 'N/A' }}</td>
                            <td>
                                <span class="priority-badge bg-{{ $issue->priority_color }}">
                                    {{ $issue->priority }}
                                </span>
                            </td>
                            <td>
                                <span class="status-badge bg-{{ $issue->status_color }}">
                                    {{ $issue->status }}
                                </span>
                            </td>
                            <td>{{ $issue->assignedUser->name ?? __('general.unassigned') }}</td>
                            <td>
                                @if($issue->deadline)
                                    {{ $issue->deadline->format('Y-m-d') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('progress.issues.show', $issue) }}" class="btn btn-info" title="{{ __('general.view') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('edit progress-issues')
                                    <a href="{{ route('progress.issues.edit', $issue) }}" class="btn btn-warning" title="{{ __('general.edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete progress-issues')
                                    <form action="{{ route('progress.issues.destroy', $issue) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="{{ __('general.delete') }}" onclick="return confirm('{{ __('general.are_you_sure') }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">{{ __('general.no_issues_found') }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $issues->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Create Issue Modal -->
@can('create progress-issues')
@include('progress::issues.modals.create', ['projects' => $projects, 'users' => $users])
@endcan

@endsection

