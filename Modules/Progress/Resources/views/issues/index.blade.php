@extends('progress::layouts.daily-progress')
{{-- Sidebar is now handled by the layout itself --}}
@section('title', 'Issues Management')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">{{ __('general.issues_management') }}</h4>
            <div class="page-title-right">
                <a href="{{ route('issues.kanban') }}" class="btn btn-soft-primary btn-sm me-2"><i class="las la-columns me-1"></i> {{ __('general.kanban_board') }}</a>
                <ol class="breadcrumb m-0 d-inline-flex">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('general.issues') }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

{{-- Stats Cards --}}
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate bg-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-white fw-bold fw-medium text-truncate mb-0 fs-16">{{ __('general.open_issues') }}</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-28 text-white fw-semibold fw-bold mb-4">{{ $stats['open'] }}</h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded fs-1">
                            <i class="las la-exclamation-circle text-white"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate bg-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-white fw-bold fw-medium text-truncate mb-0 fs-16">{{ __('general.closed_issues') }}</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-28 text-white fw-semibold fw-bold mb-4">{{ $stats['closed'] }}</h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded fs-1">
                            <i class="las la-check-circle text-white"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate bg-danger">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-white fw-bold fw-medium text-truncate mb-0 fs-16">{{ __('general.overdue_issues') }}</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-28 text-white fw-semibold fw-bold mb-4">{{ $stats['overdue'] }}</h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded fs-1">
                            <i class="las la-clock text-white"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate bg-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-white fw-bold fw-medium text-truncate mb-0 fs-16">{{ __('general.in_progress') }}</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-28 text-white fw-semibold fw-bold mb-4">{{ $stats['in_progress'] }}</h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded fs-1">
                            <i class="las la-list text-white"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('issues.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">{{ __('general.status') }}</label>
                    <select name="status" class="form-select">
                        <option value="All">{{ __('general.all') }}</option>
                        <option value="New" {{ request('status') == 'New' ? 'selected' : '' }}>{{ __('general.status_new') }}</option>
                        <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>{{ __('general.in_progress') }}</option>
                        <option value="Testing" {{ request('status') == 'Testing' ? 'selected' : '' }}>{{ __('general.testing') }}</option>
                        <option value="Closed" {{ request('status') == 'Closed' ? 'selected' : '' }}>{{ __('general.status_closed') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('general.priority') }}</label>
                    <select name="priority" class="form-select">
                        <option value="All">{{ __('general.all') }}</option>
                        <option value="Low" {{ request('priority') == 'Low' ? 'selected' : '' }}>{{ __('general.low') }}</option>
                        <option value="Medium" {{ request('priority') == 'Medium' ? 'selected' : '' }}>{{ __('general.medium') }}</option>
                        <option value="High" {{ request('priority') == 'High' ? 'selected' : '' }}>{{ __('general.high') }}</option>
                        <option value="Urgent" {{ request('priority') == 'Urgent' ? 'selected' : '' }}>{{ __('general.urgent') }}</option>
                    </select>
                </div>
                <!-- Project -->
                <div class="col-md-2">
                    <label class="form-label">{{ __('general.project') }}</label>
                    <select name="project_id" class="form-select">
                        <option value="All">{{ __('general.all') }}</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Assigned To -->
                <div class="col-md-2">
                    <label class="form-label">{{ __('general.assigned_to') }}</label>
                     <select name="assigned_to" class="form-select">
                        <option value="All">{{ __('general.all') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Module -->
                 <div class="col-md-2">
                    <label class="form-label">{{ __('general.module') }}</label>
                    <input type="text" name="module" class="form-control" placeholder="{{ __('general.module') }}" value="{{ request('module') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('general.search') }}</label>
                    <input type="text" name="search" class="form-control" placeholder="{{ __('general.search') }}..." value="{{ request('search') }}">
                </div>
                <!-- Deadlines -->
                <div class="col-md-2">
                    <label class="form-label">{{ __('general.deadline_from') }}</label>
                    <input type="date" name="deadline_from" class="form-control" value="{{ request('deadline_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('general.deadline_to') }}</label>
                    <input type="date" name="deadline_to" class="form-control" value="{{ request('deadline_to') }}">
                </div>

                <div class="col-md-2 align-self-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="las la-filter"></i> {{ __('general.filter') }}</button>
                </div>
                 <div class="col-md-2 align-self-end">
                    <a href="{{ route('issues.index') }}" class="btn btn-light w-100"><i class="las la-redo"></i> {{ __('general.reset') }}</a>
                </div>
                @can('create progress-issues')
                <div class="col-md-3 align-self-end">
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#createIssueModal">
                        <i class="las la-plus"></i> {{ __('general.new_issue') }}
                    </button>
                </div>
                @endcan                                                                                        
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-nowrap align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ __('general.title') }}</th>
                        {{-- <th scope="col">Project</th> --}}
                        <th scope="col">{{ __('general.priority') }}</th>
                        <th scope="col">{{ __('general.status') }}</th>
                        <th scope="col">{{ __('general.assigned_to') }}</th>
                        <th scope="col">{{ __('general.deadline') }}</th>
                        <th scope="col">{{ __('general.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($issues as $issue)
                    <tr>
                        <td>{{ $issue->id }}</td>
                        <td>{{ $issue->title }}</td>
                        {{-- <td>{{ $issue->project->name ?? 'N/A' }}</td> --}}
                        <td>
                            @php
                                $priorityClass = match($issue->priority) {
                                    'Low' => 'bg-info',
                                    'Medium' => 'bg-warning',
                                    'High' => 'bg-danger',
                                    'Urgent' => 'bg-dark',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $priorityClass }}">{{ __('general.' . strtolower($issue->priority)) }}</span>
                        </td>
                        <td>
                            @php
                                $statusClass = match($issue->status) {
                                    'New' => 'bg-primary',
                                    'In Progress' => 'bg-info',
                                    'Testing' => 'bg-warning',
                                    'Closed' => 'bg-success',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">
                                @if($issue->status == 'New') {{ __('general.status_new') }}
                                @elseif($issue->status == 'In Progress') {{ __('general.in_progress') }}
                                @elseif($issue->status == 'Testing') {{ __('general.testing') }}
                                @elseif($issue->status == 'Closed') {{ __('general.status_closed') }}
                                @else {{ $issue->status }}
                                @endif
                            </span>
                        </td>
                        <td>{{ $issue->assignee->name ?? __('general.unassigned') }}</td>
                        <td>{{ $issue->deadline }}</td>
                        <td>
                            @can('view progress-issues')
                            <a href="{{ route('issues.show', $issue->id) }}" class="btn btn-sm btn-soft-info"><i class="las la-eye"></i></a>
                            @endcan
                            @can('edit progress-issues')
                            <a href="{{ route('issues.edit', $issue->id) }}" class="btn btn-sm btn-soft-primary"><i class="las la-pen"></i></a>
                            @endcan
                            @can('delete progress-issues')
                            <form action="{{ route('issues.destroy', $issue->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-soft-danger" onclick="return confirm('{{ __('general.confirm_delete') }}')"><i class="las la-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>



                    @empty
                    <tr>
                        <td colspan="7" class="text-center">{{ __('general.no_records_found') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $issues->links() }}
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createIssueModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('general.new_issue') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('issues.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                     <div class="row">
                        <!-- Project -- Placeholder -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">{{ __('general.project') }}</label>
                            <select name="project_id" class="form-select" required>
                                <option value="">{{ __('general.select_project') }}</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">{{ __('general.title') }}</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">{{ __('general.module') }}</label>
                            <input type="text" name="module" class="form-control" >
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('general.priority') }}</label>
                            <select name="priority" class="form-select">
                                <option value="Low">{{ __('general.low') }}</option>
                                <option value="Medium" selected>{{ __('general.medium') }}</option>
                                <option value="High">{{ __('general.high') }}</option>
                                <option value="Urgent">{{ __('general.urgent') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('general.status') }}</label>
                            <select name="status" class="form-select">
                                <option value="New" selected>{{ __('general.status_new') }}</option>
                                <option value="In Progress">{{ __('general.in_progress') }}</option>
                                <option value="Testing">{{ __('general.testing') }}</option>
                                <option value="Closed">{{ __('general.status_closed') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('general.assigned_to') }}</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">{{ __('general.unassigned') }}</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('general.deadline') }}</label>
                            <input type="date" name="deadline" class="form-control">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">{{ __('general.description') }}</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">{{ __('general.reproduce_steps') }}</label>
                            <textarea name="reproduce_steps" class="form-control" rows="3" placeholder="{{ __('general.reproduce_steps') }}"></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">{{ __('general.attachments') }}</label>
                            <input type="file" name="attachments[]" class="form-control" multiple>
                            <small class="text-muted">Maximum file size: 10MB. Allowed types: JPG, PNG, PDF, DOC, XLS.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('general.close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('general.create') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
