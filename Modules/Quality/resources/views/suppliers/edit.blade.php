@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <h2>
                    <i class="fas fa-edit me-2"></i>{{ __('Edit') }} {{ __('Supplier Evaluation') }}
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('quality.dashboard') }}">{{ __('Quality') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('quality.suppliers.index') }}">{{ __('Supplier Evaluations') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Edit') }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <form action="{{ route('quality.suppliers.update', $rating) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('Evaluation Information') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('Supplier') }}</label>
                                    <input type="text" class="form-control"
                                        value="{{ $rating->supplier->aname ?? '---' }}" readonly>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('Period Type') }}</label>
                                    <input type="text" class="form-control"
                                        value="{{ match ($rating->period_type) {
                                            'monthly' => __('Monthly'),
                                            'quarterly' => __('Quarterly'),
                                            'annual' => __('Annual'),
                                            default => $rating->period_type,
                                        } }}"
                                        readonly>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('Period Start') }}</label>
                                    <input type="text" class="form-control"
                                        value="{{ $rating->period_start ? $rating->period_start->format('Y-m-d') : '---' }}"
                                        readonly>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('Period End') }}</label>
                                    <input type="text" class="form-control"
                                        value="{{ $rating->period_end ? $rating->period_end->format('Y-m-d') : '---' }}"
                                        readonly>
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
                                        value="{{ old('quality_score', $rating->quality_score) }}" min="0"
                                        max="100" step="0.1" required>
                                    @error('quality_score')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">{{ __('Product and service quality') }}</small>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">{{ __('Delivery Score') }} (0-100)</label>
                                    <input type="number" name="delivery_score"
                                        class="form-control @error('delivery_score') is-invalid @enderror"
                                        value="{{ old('delivery_score', $rating->delivery_score) }}" min="0"
                                        max="100" step="0.1" required>
                                    @error('delivery_score')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">{{ __('Delivery schedule compliance') }}</small>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">{{ __('Documentation Score') }} (0-100)</label>
                                    <input type="number" name="documentation_score"
                                        class="form-control @error('documentation_score') is-invalid @enderror"
                                        value="{{ old('documentation_score', $rating->documentation_score) }}"
                                        min="0" max="100" step="0.1" required>
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
                            <h5 class="mb-0">{{ __('Current Metrics') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <h5 class="text-primary">{{ $rating->total_inspections }}</h5>
                                    <small class="text-muted">{{ __('Total Inspections') }}</small>
                                </div>
                                <div class="col-6 mb-3">
                                    <h5 class="text-success">{{ $rating->passed_inspections }}</h5>
                                    <small class="text-muted">{{ __('Passed Inspections') }}</small>
                                </div>
                                <div class="col-6 mb-3">
                                    <h5 class="text-danger">{{ $rating->failed_inspections }}</h5>
                                    <small class="text-muted">{{ __('Failed Inspections') }}</small>
                                </div>
                                <div class="col-6 mb-3">
                                    <h5 class="text-warning">{{ $rating->ncrs_raised }}</h5>
                                    <small class="text-muted">{{ __('Non-conformance Reports') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save me-2"></i>{{ __('Save Changes') }}
                        </button>
                        <a href="{{ route('quality.suppliers.show', $rating) }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>{{ __('Cancel') }}
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
