@extends('progress::layouts.app')
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-muted text-decoration-none">
            {{ __('general.dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('progress.clients.index') }}" class="text-muted text-decoration-none">
            {{ __('general.clients') }}
        </a>
    </li>
@endsection
@section('title', __('general.edit_client'))

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
            <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i> {{ __('general.edit_client') }}: {{ $client->cname }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('progress.clients.update', $client) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('general.client_name') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                            <input type="text" name="name" class="form-control" value="{{ $client->cname }}" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('general.contact_person') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="contact_person" class="form-control" value="{{ $client->contact_person }}">
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('general.phone') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="text" name="phone" class="form-control" value="{{ $client->phone }}">
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('general.email') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control" value="{{ $client->email }}">
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('general.address') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                            <input type="text" name="address" class="form-control" value="{{ $client->address }}">
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> {{ __('general.save_changes') }}
                    </button>
                    <a href="{{ route('progress.clients.index') }}" class="btn btn-secondary ms-2">
                        <i class="fas fa-times me-2"></i> {{ __('general.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
