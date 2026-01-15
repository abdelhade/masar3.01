@extends('progress::layouts.daily-progress')

@section('title', 'Issues Kanban')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Kanban Board ({{ $issues->count() }} Issues)</h4>
            <div class="page-title-right">
                <a href="{{ route('issues.index') }}" class="btn btn-soft-secondary"><i class="las la-list me-1"></i> Table View</a>
                <button type="button" class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#createIssueModal">
                     <i class="las la-plus me-1"></i> New Issue
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('issues.kanban') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="All">All</option>
                        <option value="New" {{ request('status') == 'New' ? 'selected' : '' }}>New</option>
                        <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Testing" {{ request('status') == 'Testing' ? 'selected' : '' }}>Testing</option>
                        <option value="Closed" {{ request('status') == 'Closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="All">All</option>
                        <option value="Low" {{ request('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Medium" {{ request('priority') == 'Medium' ? 'selected' : '' }}>Medium</option>
                        <option value="High" {{ request('priority') == 'High' ? 'selected' : '' }}>High</option>
                        <option value="Urgent" {{ request('priority') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>
                <!-- Project -->
                <div class="col-md-2">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select">
                        <option value="All">All</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Assigned To -->
                <div class="col-md-2">
                    <label class="form-label">Assigned To</label>
                     <select name="assigned_to" class="form-select">
                        <option value="All">All</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Module -->
                 <div class="col-md-2">
                    <label class="form-label">Module</label>
                    <input type="text" name="module" class="form-control" placeholder="Module" value="{{ request('module') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                </div>
                <!-- Deadlines -->
                <div class="col-md-2">
                    <label class="form-label">Deadline From</label>
                    <input type="date" name="deadline_from" class="form-control" value="{{ request('deadline_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Deadline To</label>
                    <input type="date" name="deadline_to" class="form-control" value="{{ request('deadline_to') }}">
                </div>

                <div class="col-md-2 align-self-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="las la-filter"></i> Filter</button>
                </div>
                 <div class="col-md-2 align-self-end">
                    <a href="{{ route('issues.kanban') }}" class="btn btn-light w-100"><i class="las la-redo"></i> Reset</a>
                </div>
                <div class="col-md-3 align-self-end">
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#createIssueModal">
                        <i class="las la-plus"></i> New Issue
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <!-- NEW Column -->
    <div class="col-md-3">
        <div class="card" style="background-color: #e3f2fd; border: 1px solid #90caf9;">
            <div class="card-header bg-transparent">
                <h5 class="card-title text-dark mb-0">New ({{ $issues->filter(fn($i) => trim($i->status) == 'New')->count() }})</h5>
            </div>
            <div class="card-body p-2" id="kanban-new" data-status="New" style="min-height: 400px;">
                @foreach($issues as $issue)
                    @if(trim($issue->status) == 'New')
                    <div class="card mb-2 shadow-sm" data-id="{{ $issue->id }}" style="cursor: grab;">
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-dark">{{ $issue->title }}</h6>
                            <p class="text-muted small mb-2">{{ Str::limit($issue->description, 50) }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-secondary">{{ $issue->priority }}</span>
                                <small class="text-muted">{{ $issue->assignee->name ?? 'Unassigned' }}</small>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- IN PROGRESS Column -->
    <div class="col-md-3">
        <div class="card" style="background-color: #fff3e0; border: 1px solid #ffcc80;">
             <div class="card-header bg-transparent">
                <h5 class="card-title text-dark mb-0">In Progress ({{ $issues->filter(fn($i) => trim($i->status) == 'In Progress')->count() }})</h5>
            </div>
            <div class="card-body p-2" id="kanban-inprogress" data-status="In Progress" style="min-height: 400px;">
                 @foreach($issues as $issue)
                    @if(trim($issue->status) == 'In Progress')
                    <div class="card mb-2 shadow-sm" data-id="{{ $issue->id }}" style="cursor: grab;">
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-dark">{{ $issue->title }}</h6>
                             <p class="text-muted small mb-2">{{ Str::limit($issue->description, 50) }}</p>
                             <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-secondary">{{ $issue->priority }}</span>
                                <small class="text-muted">{{ $issue->assignee->name ?? 'Unassigned' }}</small>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- TESTING Column -->
    <div class="col-md-3">
        <div class="card" style="background-color: #fffde7; border: 1px solid #fff59d;">
             <div class="card-header bg-transparent">
                <h5 class="card-title text-dark mb-0">Testing ({{ $issues->filter(fn($i) => trim($i->status) == 'Testing')->count() }})</h5>
            </div>
            <div class="card-body p-2" id="kanban-testing" data-status="Testing" style="min-height: 400px;">
                 @foreach($issues as $issue)
                    @if(trim($issue->status) == 'Testing')
                    <div class="card mb-2 shadow-sm" data-id="{{ $issue->id }}" style="cursor: grab;">
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-dark">{{ $issue->title }}</h6>
                             <p class="text-muted small mb-2">{{ Str::limit($issue->description, 50) }}</p>
                             <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-warning">{{ $issue->priority }}</span>
                                <small class="text-muted">{{ $issue->assignee->name ?? 'Unassigned' }}</small>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- CLOSED Column -->
    <div class="col-md-3">
        <div class="card" style="background-color: #e8f5e9; border: 1px solid #a5d6a7;">
             <div class="card-header bg-transparent">
                <h5 class="card-title text-dark mb-0">Closed ({{ $issues->filter(fn($i) => trim($i->status) == 'Closed')->count() }})</h5>
            </div>
            <div class="card-body p-2" id="kanban-closed" data-status="Closed" style="min-height: 400px;">
                 @foreach($issues as $issue)
                    @if(trim($issue->status) == 'Closed')
                    <div class="card mb-2 shadow-sm" data-id="{{ $issue->id }}" style="cursor: grab;">
                        <div class="card-body p-3">
                             <h6 class="fw-bold text-dark">{{ $issue->title }}</h6>
                              <p class="text-muted small mb-2">{{ Str::limit($issue->description, 50) }}</p>
                              <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-secondary">{{ $issue->priority }}</span>
                                <small class="text-muted">{{ $issue->assignee->name ?? 'Unassigned' }}</small>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Create Modal (Include same as index or verify if reused) -->
<!-- Using the same modal ID as index to simplify, assuming included in dashboard or copied here -->
<div class="modal fade" id="createIssueModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white fw-bold"><i class="las la-plus-circle me-2"></i>New Issue</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('issues.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                     <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Project</label>
                            <div class="input-group">
                                <select name="project_id" class="form-select" required>
                                    <option value="">Select Project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                                <span class="input-group-text bg-light text-muted"><i class="las la-project-diagram"></i></span>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Title</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="las la-heading"></i></span>
                                <input type="text" name="title" class="form-control" required placeholder="Issue Title">
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Module</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="las la-cube"></i></span>
                                <input type="text" name="module" class="form-control" placeholder="Module Name">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Priority</label>
                            <div class="input-group">
                                <select name="priority" class="form-select">
                                    <option value="Low">Low</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="High">High</option>
                                    <option value="Urgent">Urgent</option>
                                </select>
                                <span class="input-group-text bg-light text-muted"><i class="las la-flag"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <div class="input-group">
                                <select name="status" class="form-select">
                                    <option value="New" selected>New</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Testing">Testing</option>
                                    <option value="Closed">Closed</option>
                                </select>
                                <span class="input-group-text bg-light text-muted"><i class="las la-info-circle"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Assigned To</label>
                             <div class="input-group">
                                <select name="assigned_to" class="form-select">
                                    <option value="">Unassigned</option>
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
