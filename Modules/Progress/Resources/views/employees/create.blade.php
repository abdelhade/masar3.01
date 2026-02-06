@extends('progress::layouts.app')
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-muted text-decoration-none">
            {{ __('general.dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('progress.employees.index') }}" class="text-muted text-decoration-none">
            {{ __('general.employees') }}
        </a>
    </li>
@endsection
@section('title', __('employees.new'))

@section('content')
<style>
    :root {
        --primary-color: #2c7be5;
        --secondary-color: #6c757d;
        --card-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
    }

    .main-card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: var(--card-shadow);
        margin-top: 2rem;
        transition: all 0.3s ease;
    }

    .main-card:hover {
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.1);
    }

    .card-header {
        border-radius: 0.75rem 0.75rem 0 0 !important;
        padding: 1.2rem 1.5rem;
        background: linear-gradient(120deg, #2c7be5 0%, #1a56ce 100%) !important;
        border: none;
    }

    .card-body {
        padding: 2rem;
    }

    .form-label {
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #344050;
    }

    .form-control {
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        border: 1px solid #e3ebf6;
        transition: all 0.3s;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(44, 123, 229, 0.15);
    }

    .input-group-text {
        background-color: #f5f7f9;
        border-radius: 0.5rem 0 0 0.5rem;
        border: 1px solid #e3ebf6;
    }

    .btn-primary {
        background: linear-gradient(120deg, #2c7be5 0%, #1a56ce 100%);
        border: none;
        border-radius: 0.5rem;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(44, 123, 229, 0.3);
    }

    .btn-secondary {
        border-radius: 0.5rem;
        padding: 0.75rem 2rem;
        font-weight: 600;
    }
</style>

<div class="container">
    <div class="main-card card">
        <div class="card-header text-white">
            <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i> {{ __('employees.new') }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('progress.employees.store') }}" method="POST">
                @csrf

                <div class="row mb-4">
                    
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">{{ __('employees.name') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required>
                        </div>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    
                    <div class="col-md-6 mb-3">
                        <label for="position" class="form-label">{{ __('employees.position') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                            <input type="text"
                                   class="form-control @error('position') is-invalid @enderror"
                                   id="position"
                                   name="position"
                                   value="{{ old('position') }}"
                                   required>
                        </div>
                        @error('position')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">{{ __('employees.phone') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="text"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   id="phone"
                                   name="phone"
                                   value="{{ old('phone') }}"
                                   required>
                        </div>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">{{ __('employees.email') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}">
                        </div>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">{{ __('employees.password') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password"
                                   name="password"
                                   required>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">{{ __('employees.password_confirmation') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password"
                                   class="form-control"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   required>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> {{ __('employees.save') }}
                    </button>
                    <a href="{{ route('progress.employees.index') }}" class="btn btn-secondary ms-2">
                        <i class="fas fa-times me-2"></i> {{ __('employees.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
