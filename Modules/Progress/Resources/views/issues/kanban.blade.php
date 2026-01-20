@extends('progress::layouts.daily-progress')

@section('title', __('general.kanban_board'))

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">{{ __('general.kanban_board') }} <span class="badge bg-secondary ms-1">{{ $issues->count() }}</span></h4>
            <div class="page-title-right">
                <a href="{{ route('issues.index') }}" class="btn btn-soft-primary btn-sm me-2"><i class="las la-list me-1"></i> {{ __('general.table_view') }}</a>
                <ol class="breadcrumb m-0 d-inline-flex">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('issues.index') }}">{{ __('general.issues') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('general.kanban_board') }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!--  -->
{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('issues.kanban') }}" method="GET">
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
                    <a href="{{ route('issues.kanban') }}" class="btn btn-light w-100"><i class="las la-redo"></i> {{ __('general.reset') }}</a>
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

<div class="row">
    @php
        $statuses = [
            'New' => ['label' => __('general.status_new'), 'class' => 'border-primary'],
            'In Progress' => ['label' => __('general.in_progress'), 'class' => 'border-info'],
            'Testing' => ['label' => __('general.testing'), 'class' => 'border-warning'],
            'Closed' => ['label' => __('general.status_closed'), 'class' => 'border-success']
        ];
    @endphp

    @foreach($statuses as $key => $status)
    <div class="col-lg-3 col-md-6">
        <div class="card bg-light">
            <div class="card-header bg-white border-bottom-0">
                <h5 class="card-title mb-0">{{ $status['label'] }} <span class="badge bg-secondary rounded-pill ms-1">{{ $issues->where('status', $key)->count() }}</span></h5>
            </div>
            <div class="card-body" id="kanban-{{ strtolower(str_replace(' ', '-', $key)) }}" data-status="{{ $key }}" style="height: calc(100vh - 280px); overflow-y: auto;">
                <div class="vstack gap-3">
                    @forelse($issues->where('status', $key) as $issue)
                    <div class="card mb-0 shadow-sm border-start border-3 {{ $status['class'] }}" data-id="{{ $issue->id }}" style="cursor: grab;">
                        <div class="card-body p-3">
                             <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-soft-secondary text-secondary">#{{ $issue->id }}</span>
                                @php
                                    $priorityClass = match($issue->priority) {
                                        'Low' => 'bg-info',
                                        'Medium' => 'bg-warning',
                                        'High' => 'bg-danger',
                                        'Urgent' => 'bg-dark',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $priorityClass }}">{{ $issue->priority }}</span>
                            </div>
                            <h6 class="fs-15 mb-2"><a href="{{ route('issues.show', $issue->id) }}" class="text-reset">{{ $issue->title }}</a></h6>
                            <p class="text-muted fs-12 mb-2 text-truncate">{{ $issue->project->name ?? 'N/A' }}</p>
                            
                            <div class="d-flex align-items-center justify-content-between mt-3">
                                <div class="d-flex align-items-center" title="{{ __('general.assigned_to') }}: {{ $issue->assignee->name ?? __('general.unassigned') }}">
                                     @if($issue->assignee)
                                        <div class="avatar-xs" title="{{ $issue->assignee->name }}">
                                            <span class="avatar-title rounded-circle bg-soft-primary text-primary fs-10">
                                                {{ substr($issue->assignee->name, 0, 2) }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="avatar-xs" title="{{ __('general.unassigned') }}">
                                            <span class="avatar-title rounded-circle bg-soft-secondary text-secondary fs-10">
                                                <i class="las la-user-slash"></i>
                                            </span>
                                        </div>
                                    @endif
                                    <span class="ms-2 fs-12 text-muted">{{ $issue->assignee->name ?? '' }}</span>
                                </div>
                                
                                @if($issue->deadline)
                                <div class="text-muted fs-11" title="{{ __('general.deadline') }}">
                                    <i class="las la-calendar"></i> {{ date('M d', strtotime($issue->deadline)) }}
                                </div>
                                @endif
                            </div>
                        </div>
                         <div class="card-footer bg-transparent border-top-0 py-2 d-flex justify-content-end gap-1">
                             @can('view progress-issues')
                             <a href="{{ route('issues.show', $issue->id) }}" class="btn btn-sm btn-ghost-info"><i class="las la-eye"></i></a>
                             @endcan
                             @can('edit progress-issues')
                             <a href="{{ route('issues.edit', $issue->id) }}" class="btn btn-sm btn-ghost-primary"><i class="las la-pen"></i></a>
                             @endcan
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <p class="text-muted mb-0">{{ __('general.no_records_found') }}</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Create Modal (Same as Index) -->
<div class="modal fade" id="createIssueModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white fw-bold"><i class="las la-plus-circle me-2"></i>{{ __('general.new_issue') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('issues.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                     <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">{{ __('general.project') }}</label>
                            <div class="input-group">
                                <select name="project_id" class="form-select" required>
                                    <option value="">{{ __('general.select_project') }}</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                                <span class="input-group-text bg-light text-muted"><i class="las la-project-diagram"></i></span>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">{{ __('general.title') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="las la-heading"></i></span>
                                <input type="text" name="title" class="form-control" required placeholder="{{ __('general.issue_title') }}">
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">{{ __('general.module') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="las la-cube"></i></span>
                                <input type="text" name="module" class="form-control" placeholder="{{ __('general.module_name') }}">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('general.priority') }}</label>
                            <div class="input-group">
                                <select name="priority" class="form-select">
                                    <option value="Low">{{ __('general.low') }}</option>
                                    <option value="Medium" selected>{{ __('general.medium') }}</option>
                                    <option value="High">{{ __('general.high') }}</option>
                                    <option value="Urgent">{{ __('general.urgent') }}</option>
                                </select>
                                <span class="input-group-text bg-light text-muted"><i class="las la-flag"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('general.status') }}</label>
                            <div class="input-group">
                                <select name="status" class="form-select">
                                    <option value="New" selected>{{ __('general.status_new') }}</option>
                                    <option value="In Progress">{{ __('general.in_progress') }}</option>
                                    <option value="Testing">{{ __('general.testing') }}</option>
                                    <option value="Closed">{{ __('general.status_closed') }}</option>
                                </select>
                                <span class="input-group-text bg-light text-muted"><i class="las la-info-circle"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('general.assigned_to') }}</label>
                             <div class="input-group">
                                <select name="assigned_to" class="form-select">
                                    <option value="">{{ __('general.unassigned') }}</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <span class="input-group-text bg-light text-muted"><i class="las la-user"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Deadline</label>
                            <div class="input-group">
                                <input type="date" name="deadline" class="form-control">
                                <span class="input-group-text bg-light text-muted"><i class="las la-calendar"></i></span>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Detailed description..."></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Reproduce Steps</label>
                            <textarea name="reproduce_steps" class="form-control" rows="3" placeholder="1. Step one..."></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Attachments</label>
                            <div class="input-group">
                                <input type="file" name="attachments[]" class="form-control" multiple>
                                <span class="input-group-text bg-light text-muted"><i class="las la-paperclip"></i></span>
                            </div>
                            <small class="text-muted">Maximum file size: 10MB. Allowed types: JPG, PNG, PDF, DOC, XLS.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="las la-save me-1"></i> Create Issue</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const containers = [
            document.getElementById('kanban-new'),
            document.getElementById('kanban-inprogress'),
            document.getElementById('kanban-testing'),
            document.getElementById('kanban-closed')
        ];

        containers.forEach(container => {
            new Sortable(container, {
                group: 'shared', // set both lists to same group
                animation: 150,
                onEnd: function (evt) {
                    const itemEl = evt.item;
                    const newStatus = evt.to.getAttribute('data-status');
                    const issueId = itemEl.getAttribute('data-id');
                    
                    if(evt.from !== evt.to) {
                        updateStatus(issueId, newStatus);
                    }
                }
            });
        });

        function updateStatus(issueId, status) {
            fetch('{{ route("issues.updateStatus") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    issue_id: issueId,
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Start Bootstrap Toast for success
                    // Or just use sweetalert if simpler
                    // console.log('Status updated');
                } else {
                    alert('Failed to update status');
                    location.reload(); // Revert UI
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating status');
                location.reload();
            });
        }
    });
</script>
@endsection
