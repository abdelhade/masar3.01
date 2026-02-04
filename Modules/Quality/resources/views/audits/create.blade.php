@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-plus-circle me-2"></i>{{ __('Create New Audit') }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('quality.dashboard') }}">{{ __('Quality') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quality.audits.index') }}">{{ __('Audit') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Create') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Audit Details') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('quality.audits.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="audit_title" class="form-label">{{ __('Audit Title') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('audit_title') is-invalid @enderror" 
                                       id="audit_title" name="audit_title" value="{{ old('audit_title') }}" required>
                                @error('audit_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="audit_type" class="form-label">{{ __('Audit Type') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('audit_type') is-invalid @enderror" 
                                        id="audit_type" name="audit_type" required>
                                    <option value="">-- {{ __('Select Audit Type') }} --</option>
                                    <option value="internal" {{ old('audit_type') == 'internal' ? 'selected' : '' }}>{{ __('Internal') }}</option>
                                    <option value="external" {{ old('audit_type') == 'external' ? 'selected' : '' }}>{{ __('External') }}</option>
                                    <option value="supplier" {{ old('audit_type') == 'supplier' ? 'selected' : '' }}>{{ __('Supplier Audit') }}</option>
                                    <option value="certification" {{ old('audit_type') == 'certification' ? 'selected' : '' }}>{{ __('Certification') }}</option>
                                    <option value="customer" {{ old('audit_type') == 'customer' ? 'selected' : '' }}>{{ __('Customer') }}</option>
                                </select>
                                @error('audit_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="planned_date" class="form-label">{{ __('Planned Date') }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('planned_date') is-invalid @enderror" 
                                       id="planned_date" name="planned_date" value="{{ old('planned_date') }}" required>
                                @error('planned_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="lead_auditor_id" class="form-label">{{ __('Lead Auditor') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('lead_auditor_id') is-invalid @enderror" 
                                        id="lead_auditor_id" name="lead_auditor_id" required>
                                    <option value="">-- {{ __('Select Lead Auditor') }} --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('lead_auditor_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('lead_auditor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="audit_team" class="form-label">{{ __('Audit Team') }}</label>
                                <select class="form-select" id="audit_team" name="audit_team[]" multiple>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">{{ __('You can select multiple members (Ctrl + Click)') }}</small>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="audit_objectives" class="form-label">{{ __('Audit Objectives') }}</label>
                                <textarea class="form-control @error('audit_objectives') is-invalid @enderror" 
                                          id="audit_objectives" name="audit_objectives" rows="3">{{ old('audit_objectives') }}</textarea>
                                @error('audit_objectives')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3" id="external_fields" style="display: none;">
                                <label for="external_auditor" class="form-label">{{ __('External Auditor') }}</label>
                                <input type="text" class="form-control @error('external_auditor') is-invalid @enderror" 
                                       id="external_auditor" name="external_auditor" value="{{ old('external_auditor') }}">
                                @error('external_auditor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3" id="external_org_field" style="display: none;">
                                <label for="external_organization" class="form-label">{{ __('External Organization') }}</label>
                                <input type="text" class="form-control @error('external_organization') is-invalid @enderror" 
                                       id="external_organization" name="external_organization" value="{{ old('external_organization') }}">
                                @error('external_organization')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('quality.audits.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>{{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>{{ __('Save Audit') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const auditTypeSelect = document.getElementById('audit_type');
    const externalFields = document.getElementById('external_fields');
    const externalOrgField = document.getElementById('external_org_field');
    
    auditTypeSelect.addEventListener('change', function() {
        if (this.value === 'external' || this.value === 'certification') {
            externalFields.style.display = 'block';
            externalOrgField.style.display = 'block';
        } else {
            externalFields.style.display = 'none';
            externalOrgField.style.display = 'none';
        }
    });
    
    if (auditTypeSelect.value === 'external' || auditTypeSelect.value === 'certification') {
        externalFields.style.display = 'block';
        externalOrgField.style.display = 'block';
    }
});
</script>
@endsection