@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Add Pricing Status'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Pricing Statuses'), 'url' => route('pricing-statuses.index')],
            ['label' => __('Add New')],
        ],
    ])

    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('pricing-statuses.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <!-- Name -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                {{ __('Name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('Description') }}</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Color -->
                        <div class="col-md-1">
                            <label class="form-label fw-bold">
                                {{ __('Color') }} <span class="text-danger">*</span>
                            </label>
                            <input type="color" name="color" class="form-control @error('color') is-invalid @enderror"
                                value="{{ old('color', '#6c757d') }}" required>
                            @error('color')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-1">

                        </div>
                        <!-- Is Active -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('Status') }}</label>
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" name="is_active" value="1"
                                    class="form-check-input @error('is_active') is-invalid @enderror" id="is_active"
                                    {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    {{ __('Active') }}
                                </label>
                            </div>
                            @error('is_active')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="mt-4">
                        <button type="submit" class="btn btn-main">
                            <i class="fas fa-save me-1"></i>{{ __('Save') }}
                        </button>
                        <a href="{{ route('pricing-statuses.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>{{ __('Back') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
