@extends('progress::layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-muted text-decoration-none">{{ __('general.dashboard') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('progress.issues.index') }}" class="text-muted text-decoration-none">{{ __('general.issues') }}</a>
    </li>
@endsection

@section('title', __('general.kanban_board'))

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.css">
<style>
    .kanban-board {
        display: flex;
        gap: 1rem;
        overflow-x: auto;
        padding: 1rem 0;
    }
    .kanban-column {
        min-width: 300px;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
    }
    .kanban-column-header {
        font-weight: 600;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #dee2e6;
    }
    .kanban-card {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        cursor: move;
        transition: all 0.3s ease;
    }
    .kanban-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    .kanban-card.overdue {
        border-left: 4px solid #dc3545;
    }
    .kanban-card.urgent {
        border-left: 4px solid #dc3545;
    }
    .kanban-card.high {
        border-left: 4px solid #fd7e14;
    }
    .kanban-card.medium {
        border-left: 4px solid #ffc107;
    }
    .kanban-card.low {
        border-left: 4px solid #0dcaf0;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const columns = ['New', 'In Progress', 'Testing', 'Closed'];
    
    columns.forEach(status => {
        const column = document.getElementById(`kanban-${status.replace(' ', '-')}`);
        if (column) {
            new Sortable(column, {
                group: 'kanban',
                animation: 150,
                onEnd: function(evt) {
                    const issueId = evt.item.dataset.issueId;
                    const newStatus = evt.to.dataset.status;
                    
                    fetch(`/issues/${issueId}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ status: newStatus })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Optionally show a success message
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Revert the move on error
                        evt.from.appendChild(evt.item);
                    });
                }
            });
        }
    });
});
</script>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="fas fa-columns me-2"></i>{{ __('general.kanban_board') }}</h4>
            <p class="text-muted mb-0">{{ __('general.drag_and_drop_issues') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('progress.issues.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list me-1"></i>{{ __('general.table_view') }}
            </a>
            @can('create progress-issues')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createIssueModal">
                <i class="fas fa-plus me-1"></i>{{ __('general.new_issue') }}
            </button>
            @endcan
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('progress.issues.kanban') }}" class="row g-3">
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <label class="form-label">{{ __('general.priority') }}</label>
                    <select name="priority" class="form-select">
                        <option value="">{{ __('general.all') }}</option>
                        <option value="Low" {{ request('priority') == 'Low' ? 'selected' : '' }}>{{ __('general.low') }}</option>
                        <option value="Medium" {{ request('priority') == 'Medium' ? 'selected' : '' }}>{{ __('general.medium') }}</option>
                        <option value="High" {{ request('priority') == 'High' ? 'selected' : '' }}>{{ __('general.high') }}</option>
                        <option value="Urgent" {{ request('priority') == 'Urgent' ? 'selected' : '' }}>{{ __('general.urgent') }}</option>
                    </select>
                </div>
                <div class="col-md-4">
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
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>{{ __('general.filter') }}
                    </button>
                    <a href="{{ route('progress.issues.kanban') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i>{{ __('general.reset') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="kanban-board">
        @foreach(['New', 'In Progress', 'Testing', 'Closed'] as $status)
        <div class="kanban-column">
            <div class="kanban-column-header">
                {{ $status }} ({{ $issuesByStatus[$status]->count() }})
            </div>
            <div id="kanban-{{ str_replace(' ', '-', $status) }}" data-status="{{ $status }}">
                @foreach($issuesByStatus[$status] as $issue)
                <div class="kanban-card {{ strtolower($issue->priority) }} {{ $issue->isOverdue() ? 'overdue' : '' }}" 
                     data-issue-id="{{ $issue->id }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">
                            <a href="{{ route('progress.issues.show', $issue) }}" class="text-decoration-none">
                                #{{ $issue->id }}: {{ Str::limit($issue->title, 30) }}
                            </a>
                        </h6>
                        <span class="badge bg-{{ $issue->priority_color }}">{{ $issue->priority }}</span>
                    </div>
                    <p class="text-muted small mb-2">{{ Str::limit($issue->description, 60) }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-project-diagram me-1"></i>{{ Str::limit($issue->project->name ?? 'N/A', 15) }}
                        </small>
                        @if($issue->assignedUser)
                        <small class="text-muted">
                            <i class="fas fa-user me-1"></i>{{ Str::limit($issue->assignedUser->name, 10) }}
                        </small>
                        @endif
                    </div>
                    @if($issue->deadline)
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>{{ $issue->deadline->format('Y-m-d') }}
                        </small>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Create Issue Modal -->
@can('create progress-issues')
@include('progress::issues.modals.create', ['projects' => $projects, 'users' => $users])
@endcan

@endsection

