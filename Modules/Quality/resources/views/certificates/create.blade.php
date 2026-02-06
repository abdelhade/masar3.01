@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-plus-circle me-2"></i>{{ __("Add New Certificate") }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('quality.dashboard') }}">{{ __("Quality") }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quality.certificates.index') }}">{{ __("Certificates") }}</a></li>
                    <li class="breadcrumb-item active">{{ __("New") }}</li>
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
                    <form action="{{ route('quality.certificates.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="certificate_number" class="form-label">{{ __("Certificate Number") }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('certificate_number') is-invalid @enderror" 
                                       id="certificate_number" name="certificate_number" value="{{ old('certificate_number') }}" required>
                                @error('certificate_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="certificate_name" class="form-label">{{ __("Certificate Name") }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('certificate_name') is-invalid @enderror" 
                                       id="certificate_name" name="certificate_name" value="{{ old('certificate_name') }}" required>
                                @error('certificate_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>



                            <div class="col-md-6 mb-3">
                                <label for="issuing_authority" class="form-label">{{ __("Issuing Authority") }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('issuing_authority') is-invalid @enderror" 
                                       id="issuing_authority" name="issuing_authority" value="{{ old('issuing_authority') }}" required>
                                @error('issuing_authority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="issue_date" class="form-label">{{ __("Issue Date") }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('issue_date') is-invalid @enderror" 
                                       id="issue_date" name="issue_date" value="{{ old('issue_date') }}" required>
                                @error('issue_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="expiry_date" class="form-label">{{ __("Valid Until") }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                       id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}" required>
                                @error('expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="notification_days" class="form-label">{{ __("Notification Before Expiry (Days)") }} <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('notification_days') is-invalid @enderror" 
                                       id="notification_days" name="notification_days" value="{{ old('notification_days', 30) }}" min="1" required>
                                @error('notification_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="certificate_cost" class="form-label">{{ __("Certificate Cost") }}</label>
                                <input type="number" step="0.01" class="form-control @error('certificate_cost') is-invalid @enderror" 
                                       id="certificate_cost" name="certificate_cost" value="{{ old('certificate_cost') }}" min="0">
                                @error('certificate_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="scope" class="form-label">{{ __("Scope") }}</label>
                                <textarea class="form-control @error('scope') is-invalid @enderror" 
                                          id="scope" name="scope" rows="3">{{ old('scope') }}</textarea>
                                @error('scope')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">{{ __("Notes") }}</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('quality.certificates.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>{{ __("Cancel") }}
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>{{ __("Save Certificate") }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection