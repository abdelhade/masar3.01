@extends('progress::layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-muted text-decoration-none">{{ __('general.dashboard') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('progress.issues.index') }}" class="text-muted text-decoration-none">{{ __('general.issues') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('progress.issues.show', $issue) }}" class="text-muted text-decoration-none">{{ __('general.issue') }} #{{ $issue->id }}</a>
    </li>
@endsection

@section('title', __('general.edit_issue'))

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>{{ __('general.edit_issue') }} #{{ $issue->id }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('progress.issues.update', $issue) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">{{ __('general.project') }} <span class="text-danger">*</span></label>
                        <select name="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ old('project_id', $issue->project_id) == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">{{ __('general.title') }} <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title', $issue->title) }}" required maxlength="255">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ __('general.priority') }} <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                            <option value="Low" {{ old('priority', $issue->priority) == 'Low' ? 'selected' : '' }}>{{ __('general.low') }}</option>
                            <option value="Medium" {{ old('priority', $issue->priority) == 'Medium' ? 'selected' : '' }}>{{ __('general.medium') }}</option>
                            <option value="High" {{ old('priority', $issue->priority) == 'High' ? 'selected' : '' }}>{{ __('general.high') }}</option>
                            <option value="Urgent" {{ old('priority', $issue->priority) == 'Urgent' ? 'selected' : '' }}>{{ __('general.urgent') }}</option>
                        </select>
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ __('general.status') }} <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="New" {{ old('status', $issue->status) == 'New' ? 'selected' : '' }}>{{ __('general.new') }}</option>
                            <option value="In Progress" {{ old('status', $issue->status) == 'In Progress' ? 'selected' : '' }}>{{ __('general.in_progress') }}</option>
                            <option value="Testing" {{ old('status', $issue->status) == 'Testing' ? 'selected' : '' }}>{{ __('general.testing') }}</option>
                            <option value="Closed" {{ old('status', $issue->status) == 'Closed' ? 'selected' : '' }}>{{ __('general.closed') }}</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ __('general.assigned_to') }}</label>
                        <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                            <option value="">{{ __('general.unassigned') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to', $issue->assigned_to) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ __('general.module') }}</label>
                        <input type="text" name="module" class="form-control @error('module') is-invalid @enderror" 
                               value="{{ old('module', $issue->module) }}" maxlength="255">
                        @error('module')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ __('general.deadline') }}</label>
                        <input type="date" name="deadline" class="form-control @error('deadline') is-invalid @enderror" 
                               value="{{ old('deadline', $issue->deadline?->format('Y-m-d')) }}">
                        @error('deadline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">{{ __('general.description') }}</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                  rows="4">{{ old('description', $issue->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">{{ __('general.reproduce_steps') }}</label>
                        <textarea name="reproduce_steps" class="form-control @error('reproduce_steps') is-invalid @enderror" 
                                  rows="4">{{ old('reproduce_steps', $issue->reproduce_steps) }}</textarea>
                        @error('reproduce_steps')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">{{ __('general.add_attachments') }}</label>
                        <input type="file" name="attachments[]" class="form-control @error('attachments.*') is-invalid @enderror" 
                               multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                        <small class="form-text text-muted">{{ __('general.max_file_size_10mb') }}</small>
                        @error('attachments.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>{{ __('general.update') }}
                    </button>
                    <a href="{{ route('progress.issues.show', $issue) }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>{{ __('general.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

