@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <h2>
                    <i class="fas fa-plus-circle me-2"></i>{{ __('New Supplier Evaluation') }}
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('quality.dashboard') }}">{{ __('Quality') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('quality.suppliers.index') }}">{{ __('Supplier Evaluations') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('New') }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <form action="{{ route('quality.suppliers.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('Evaluation Information') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">{{ __('Supplier') }}</label>
                                    <select name="supplier_id"
                                        class="form-select @error('supplier_id') is-invalid @enderror" required>
                                        <option value="">{{ __('Select Supplier') }}</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}"
                                                {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->aname }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">{{ __('Period Type') }}</label>
                                    <select name="period_type"
                                        class="form-select @error('period_type') is-invalid @enderror" required>
                                        <option value="">{{ __('Select Period Type') }}</option>
                                        <option value="monthly" {{ old('period_type') == 'monthly' ? 'selected' : '' }}>
                                            {{ __('Monthly') }}
                                        </option>
                                        <option value="quarterly"
                                            {{ old('period_type') == 'quarterly' ? 'selected' : '' }}>
                                            {{ __('Quarterly') }}
                                        </option>
                                        <option value="annual" {{ old('period_type') == 'annual' ? 'selected' : '' }}>
                                            {{ __('Annual') }}
                                        </option>
                                    </select>
                                    @error('period_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">{{ __('Period Start') }}</label>
                                    <input type="date" name="period_start"
                                        class="form-control @error('period_start') is-invalid @enderror"
                                        value="{{ old('period_start') }}" required>
                                    @error('period_start')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">{{ __('Period End') }}</label>
                                    <input type="date" name="period_end"
                                        class="form-control @error('period_end') is-invalid @enderror"
                                        value="{{ old('period_end') }}" required>
                                    @error('period_end')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('Evaluation Scores') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">{{ __('Quality Score') }} (0-100)</label>
                                    <input type="number" name="quality_score"
                                        class="form-control @error('quality_score') is-invalid @enderror"
                                        value="{{ old('quality_score') }}" min="0" max="100" step="0.1"
                                        required>
                                    @error('quality_score')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">{{ __('Product and service quality') }}</small>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">{{ __('Delivery Score') }} (0-100)</label>
                                    <input type="number" name="delivery_score"
                                        class="form-control @error('delivery_score') is-invalid @enderror"
                                        value="{{ old('delivery_score') }}" min="0" max="100" step="0.1"
                                        required>
                                    @error('delivery_score')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">{{ __('Delivery schedule compliance') }}</small>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">{{ __('Documentation Score') }} (0-100)</label>
                                    <input type="number" name="documentation_score"
                                        class="form-control @error('documentation_score') is-invalid @enderror"
                                        value="{{ old('documentation_score') }}" min="0" max="100"
                                        step="0.1" required>
                                    @error('documentation_score')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">{{ __('Documentation completeness and accuracy') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('Additional Information') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('The following metrics will be calculated automatically:') }}
                                <ul class="mb-0 mt-2">
                                    <li>{{ __('Number of inspections') }}</li>
                                    <li>{{ __('Success rate') }}</li>
                                    <li>{{ __('Non-conformance reports') }}</li>
                                    <li>{{ __('Final evaluation') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save me-2"></i>{{ __('Save Evaluation') }}
                        </button>
                        <a href="{{ route('quality.suppliers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>{{ __('Cancel') }}
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
