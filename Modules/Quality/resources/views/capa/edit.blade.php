@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-edit me-2"></i>{{ __("Edit CAPA") }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('quality.dashboard') }}">{{ __("Quality") }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quality.capa.index') }}">{{ __("CAPA") }}</a></li>
                    <li class="breadcrumb-item active">{{ __("Edit") }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <form action="{{ route('quality.capa.update', $capa) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __("CAPA Details") }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __("Related NCR") }}</label>
                                <input type="text" class="form-control" value="{{ $capa->nonConformanceReport->ncr_number ?? '---' }}" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __("CAPA Type") }}</label>
                                <input type="text" class="form-control" value="{{ $capa->action_type == 'corrective' ? __("Corrective") : __("Preventive") }}" readonly>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label required">{{ __("Action Description") }}</label>
                                <textarea name="action_description" rows="4" 
                                          class="form-control @error('action_description') is-invalid @enderror" 
                                          placeholder="{{ __("Explain the required action...") }}" required>{{ old('action_description', $capa->action_description) }}</textarea>
                                @error('action_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">{{ __("Root Cause") }}</label>
                                <textarea name="root_cause_analysis" rows="3" class="form-control" 
                                          placeholder="{{ __("What is the root cause of the problem?") }}">{{ old('root_cause_analysis', $capa->root_cause_analysis) }}</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">{{ __("Preventive Measures") }}</label>
                                <textarea name="preventive_measures" rows="3" class="form-control" 
                                          placeholder="{{ __("What are the preventive measures to prevent recurrence?") }}">{{ old('preventive_measures', $capa->preventive_measures) }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __("Planned Start Date") }}</label>
                                <input type="text" class="form-control" value="{{ $capa->planned_start_date->format('Y-m-d') }}" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">{{ __("Planned Completion Date") }}</label>
                                <input type="date" name="planned_completion_date" 
                                       class="form-control @error('planned_completion_date') is-invalid @enderror" 
                                       value="{{ old('planned_completion_date', $capa->planned_completion_date->format('Y-m-d')) }}" required>
                                @error('planned_completion_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __("Assignment and Follow-up") }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">{{ __("Assigned To") }}</label>
                            <select name="responsible_person" class="form-select @error('responsible_person') is-invalid @enderror" required>
                                <option value="">{{ __("Select Responsible") }}</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('responsible_person', $capa->responsible_person) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('responsible_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __("Priority") }}</label>
                            <select name="priority" class="form-select">
                                <option value="low" {{ old('priority', $capa->priority) == 'low' ? 'selected' : '' }}>{{ __("Low") }}</option>
                                <option value="medium" {{ old('priority', $capa->priority) == 'medium' ? 'selected' : '' }}>{{ __("Medium") }}</option>
                                <option value="high" {{ old('priority', $capa->priority) == 'high' ? 'selected' : '' }}>{{ __("High") }}</option>
                                <option value="critical" {{ old('priority', $capa->priority) == 'critical' ? 'selected' : '' }}>{{ __("Critical") }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __("Completion Percentage") }}</label>
                            <input type="number" name="completion_percentage" 
                                   class="form-control" value="{{ old('completion_percentage', $capa->completion_percentage) }}" 
                                   min="0" max="100">
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">{{ __("Status") }}</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="planned" {{ old('status', $capa->status) == 'planned' ? 'selected' : '' }}>{{ __("Planned") }}</option>
                                <option value="in_progress" {{ old('status', $capa->status) == 'in_progress' ? 'selected' : '' }}>{{ __("In Progress") }}</option>
                                <option value="completed" {{ old('status', $capa->status) == 'completed' ? 'selected' : '' }}>{{ __("Completed") }}</option>
                                <option value="verified" {{ old('status', $capa->status) == 'verified' ? 'selected' : '' }}>{{ __("Verified") }}</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __("Implementation Details") }}</label>
                            <textarea name="implementation_notes" rows="3" class="form-control" 
                                      placeholder="{{ __("Notes about implementation...") }}">{{ old('implementation_notes', $capa->implementation_notes) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save me-2"></i>{{ __("Save") }} ال{{ __("Edit") }}ات
                    </button>
                    <a href="{{ route('quality.capa.show', $capa) }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>{{ __("Cancel") }}
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection