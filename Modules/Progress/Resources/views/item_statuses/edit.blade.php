@extends('progress::layouts.app')
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-muted text-decoration-none">
            {{ __('general.dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('progress.item-statuses.index') }}" class="text-muted text-decoration-none">
            {{ __('general.item_statuses') }}
        </a>
    </li>
@endsection
@section('title', __('general.edit_item_status'))

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
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i> {{ __('general.edit_item_status') }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('progress.item-statuses.update', $itemStatus->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label">{{ __('general.name') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-tag"></i></span>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $itemStatus->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('general.color') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-palette"></i></span>
                            <input type="text" name="color" class="form-control @error('color') is-invalid @enderror"
                                   value="{{ old('color', $itemStatus->color) }}" placeholder="#28a745">
                            @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted">{{ __('general.color_hint') }}</small>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('general.icon') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-icons"></i></span>
                            <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror"
                                   value="{{ old('icon', $itemStatus->icon) }}" placeholder="fa-check-circle">
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted">{{ __('general.icon_hint') }}</small>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">{{ __('general.description') }}</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $itemStatus->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('general.order') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-sort-numeric-up"></i></span>
                            <input type="number" name="order" class="form-control @error('order') is-invalid @enderror"
                                   value="{{ old('order', $itemStatus->order) }}" min="0">
                            @error('order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('general.status') }}</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                   id="is_active" {{ old('is_active', $itemStatus->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                {{ __('general.active') }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> {{ __('general.update') }}
                    </button>
                    <a href="{{ route('progress.item-statuses.index') }}" class="btn btn-secondary ms-2">
                        <i class="fas fa-times me-2"></i> {{ __('general.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

