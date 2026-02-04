@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-edit me-2"></i>{{ __("Edit Certificate") }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('quality.dashboard') }}">{{ __("Quality") }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quality.certificates.index') }}">{{ __("Certificates") }}</a></li>
                    <li class="breadcrumb-item active">{{ __("Edit") }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __("Certificate Details") }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('quality.certificates.update', $certificate) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="certificate_number" class="form-label">{{ __("Certificate Number") }}</label>
                                <input type="text" class="form-control" 
                                       id="certificate_number" name="certificate_number" 
                                       value="{{ $certificate->certificate_number }}" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="certificate_name" class="form-label">{{ __("Certificate Name") }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('certificate_name') is-invalid @enderror" 
                                       id="certificate_name" name="certificate_name" 
                                       value="{{ old('certificate_name', $certificate->certificate_name) }}" required>
                                @error('certificate_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="issuing_authority" class="form-label">{{ __("Issuing Authority") }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('issuing_authority') is-invalid @enderror" 
                                       id="issuing_authority" name="issuing_authority" 
                                       value="{{ old('issuing_authority', $certificate->issuing_authority) }}" required>
                                @error('issuing_authority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="issue_date" class="form-label">{{ __("Issue Date") }}</label>
                                <input type="text" class="form-control" 
                                       value="{{ $certificate->issue_date ? $certificate->issue_date->format('Y-m-d') : '---' }}" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="expiry_date" class="form-label">{{ __("Valid Until") }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                       id="expiry_date" name="expiry_date" 
                                       value="{{ old('expiry_date', $certificate->expiry_date ? $certificate->expiry_date->format('Y-m-d') : '') }}" required>
                                @error('expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">{{ __("Status") }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="active" {{ old('status', $certificate->status) == 'active' ? 'selected' : '' }}>{{ __("Active") }}</option>
                                    <option value="expired" {{ old('status', $certificate->status) == 'expired' ? 'selected' : '' }}>{{ __("Expired") }}</option>
                                    <option value="renewal_pending" {{ old('status', $certificate->status) == 'renewal_pending' ? 'selected' : '' }}>{{ __("Renewal Pending") }}</option>
                                    <option value="suspended" {{ old('status', $certificate->status) == 'suspended' ? 'selected' : '' }}>{{ __("Suspended") }}</option>
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="notification_days" class="form-label">{{ __("Notification Before Expiry (Days)") }} <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('notification_days') is-invalid @enderror" 
                                       id="notification_days" name="notification_days" 
                                       value="{{ old('notification_days', $certificate->notification_days) }}" min="1" required>
                                @error('notification_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="scope" class="form-label">{{ __("Scope") }}</label>
                                <textarea class="form-control" 
                                          id="scope" name="scope" rows="3" readonly>{{ $certificate->scope }}</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">{{ __("Notes") }}</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3">{{ old('notes', $certificate->notes) }}</textarea>
                                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('quality.certificates.show', $certificate) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>{{ __("Cancel") }}
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>{{ __("Save") }} ال{{ __("Edit") }}ات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection