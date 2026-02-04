@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-plus-circle me-2"></i>{{ __("New CAPA") }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('quality.dashboard') }}">{{ __("Quality") }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quality.capa.index') }}">{{ __("CAPA") }}</a></li>
                    <li class="breadcrumb-item active">{{ __("New") }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <form action="{{ route('quality.capa.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __("CAPA Details") }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">{{ __("Related NCR") }}</label>
                                <select name="ncr_id" class="form-select @error('ncr_id') is-invalid @enderror" required>
                                    <option value="">{{ __("Select Report") }}</option>
                                    @foreach($ncrs as $ncr)
                                        <option value="{{ $ncr->id }}" {{ old('ncr_id') == $ncr->id ? 'selected' : '' }}>
                                            {{ $ncr->ncr_number }} - {{ $ncr->item->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('ncr_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">{{ __("CAPA Type") }}</label>
                                <select name="action_type" class="form-select @error('action_type') is-invalid @enderror" required>
                                    <option value="">{{ __("Select Type") }}</option>
                                    <option value="corrective" {{ old('action_type') == 'corrective' ? 'selected' : '' }}>{{ __("Corrective") }}</option>
                                    <option value="preventive" {{ old('action_type') == 'preventive' ? 'selected' : '' }}>{{ __("Preventive") }}</option>
                                </select>
                                @error('action_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label required">{{ __("Action Description") }}</label>
                                <textarea name="action_description" rows="4" 
                                          class="form-control @error('action_description') is-invalid @enderror" 
                                          placeholder="{{ __("Explain the required action...") }}" required>{{ old('action_description') }}</textarea>
                                @error('action_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">{{ __("Root Cause") }}</label>
                                <textarea name="root_cause_analysis" rows="3" class="form-control" 
                                          placeholder="{{ __("What is the root cause of the problem?") }}">{{ old('root_cause_analysis') }}</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">{{ __("Preventive Measures") }}</label>
                                <textarea name="preventive_measures" rows="3" class="form-control" 
                                          placeholder="{{ __("What are the preventive measures to prevent recurrence?") }}">{{ old('preventive_measures') }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">{{ __("Planned Start Date") }}</label>
                                <input type="date" name="planned_start_date" 
                                       class="form-control @error('planned_start_date') is-invalid @enderror" 
                                       value="{{ old('planned_start_date', date('Y-m-d')) }}" required>
                                @error('planned_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">{{ __("Planned Completion Date") }}</label>
                                <input type="date" name="planned_completion_date" 
                                       class="form-control @error('planned_completion_date') is-invalid @enderror" 
                                       value="{{ old('planned_completion_date') }}" required>
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
                                    <option value="{{ $user->id }}" {{ old('responsible_person') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('responsible_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __("Priority") }}</label>
                            <select name="priority" class="form-select">
                                <option value="low" {{ old('priority', 'medium') == 'low' ? 'selected' : '' }}>{{ __("Low") }}</option>
                                <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>{{ __("Medium") }}</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>{{ __("High") }}</option>
                                <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>{{ __("Critical") }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __("Estimated Cost") }}</label>
                            <input type="number" step="0.01" name="estimated_cost" 
                                   class="form-control" value="{{ old('estimated_cost') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __("Completion Percentage") }}</label>
                            <input type="number" name="completion_percentage" 
                                   class="form-control" value="{{ old('completion_percentage', 0) }}" 
                                   min="0" max="100">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __("Attachments") }}</label>
                            <input type="file" name="attachments[]" class="form-control" multiple>
                            <small class="text-muted">{{ __("Photos, reports, certificates") }}</small>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save me-2"></i>{{ __("Save Action") }}
                    </button>
                    <a href="{{ route('quality.capa.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>{{ __("Cancel") }}
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection