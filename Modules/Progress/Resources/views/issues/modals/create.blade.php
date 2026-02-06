<!-- Create Issue Modal -->
<div class="modal fade" id="createIssueModal" tabindex="-1" aria-labelledby="createIssueModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('progress.issues.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createIssueModalLabel">
                        <i class="fas fa-plus-circle me-2"></i>{{ __('general.new_issue') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">{{ __('general.project') }} <span class="text-danger">*</span></label>
                            <select name="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                                <option value="">{{ __('general.select_project') }}</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
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
                                   value="{{ old('title') }}" required maxlength="255">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('general.priority') }} <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                <option value="Low" {{ old('priority') == 'Low' ? 'selected' : '' }}>{{ __('general.low') }}</option>
                                <option value="Medium" {{ old('priority', 'Medium') == 'Medium' ? 'selected' : '' }}>{{ __('general.medium') }}</option>
                                <option value="High" {{ old('priority') == 'High' ? 'selected' : '' }}>{{ __('general.high') }}</option>
                                <option value="Urgent" {{ old('priority') == 'Urgent' ? 'selected' : '' }}>{{ __('general.urgent') }}</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('general.status') }}</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="New" {{ old('status', 'New') == 'New' ? 'selected' : '' }}>{{ __('general.new') }}</option>
                                <option value="In Progress" {{ old('status') == 'In Progress' ? 'selected' : '' }}>{{ __('general.in_progress') }}</option>
                                <option value="Testing" {{ old('status') == 'Testing' ? 'selected' : '' }}>{{ __('general.testing') }}</option>
                                <option value="Closed" {{ old('status') == 'Closed' ? 'selected' : '' }}>{{ __('general.closed') }}</option>
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
                                    <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
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
                                   value="{{ old('module') }}" maxlength="255">
                            @error('module')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('general.deadline') }}</label>
                            <input type="date" name="deadline" class="form-control @error('deadline') is-invalid @enderror" 
                                   value="{{ old('deadline') }}" min="{{ date('Y-m-d') }}">
                            @error('deadline')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">{{ __('general.description') }}</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                      rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">{{ __('general.reproduce_steps') }}</label>
                            <textarea name="reproduce_steps" class="form-control @error('reproduce_steps') is-invalid @enderror" 
                                      rows="3" placeholder="{{ __('general.steps_to_reproduce') }}">{{ old('reproduce_steps') }}</textarea>
                            @error('reproduce_steps')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">{{ __('general.attachments') }}</label>
                            <input type="file" name="attachments[]" class="form-control @error('attachments.*') is-invalid @enderror" 
                                   multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                            <small class="form-text text-muted">{{ __('general.max_file_size_10mb') }}</small>
                            @error('attachments.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>{{ __('general.create') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

