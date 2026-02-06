@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">{{ $standard->standard_name }}</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('quality.dashboard') }}">{{ __('Quality') }}</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('quality.standards.index') }}">{{ __('Quality Standards') }}</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">{{ $standard->standard_code }}</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="{{ route('quality.standards.edit', $standard) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>{{ __('Edit') }}
                        </a>
                        <a href="{{ route('quality.standards.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>{{ __('Back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Basic Information -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>{{ __('Standard Information') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">{{ __('Standard Code') }}</label>
                                <div class="fw-bold">{{ $standard->standard_code }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">{{ __('Standard Name') }}</label>
                                <div class="fw-bold">{{ $standard->standard_name }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">{{ __('Item') }}</label>
                                <div class="fw-bold">{{ $standard->item?->name ?? __('Not Specified') }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">{{ __('Branch') }}</label>
                                <div class="fw-bold">{{ $standard->branch?->name ?? __('Not Specified') }}</div>
                            </div>
                            @if ($standard->description)
                                <div class="col-12 mb-3">
                                    <label class="text-muted small">{{ __('Description') }}</label>
                                    <div>{{ $standard->description }}</div>
                                </div>
                            @endif
                            @if ($standard->test_method)
                                <div class="col-12 mb-3">
                                    <label class="text-muted small">{{ __('Test Method') }}</label>
                                    <div>{{ $standard->test_method }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($standard->notes)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-sticky-note me-2"></i>{{ __('Notes') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-0">{{ $standard->notes }}</div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Statistics & Status -->
            <div class="col-lg-4">
                <!-- Status -->
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            @if ($standard->is_active)
                                <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                            @else
                                <i class="fas fa-pause-circle text-secondary" style="font-size: 3rem;"></i>
                            @endif
                        </div>
                        <h4 class="mb-2">
                            {{ $standard->is_active ? __('Active') : __('Inactive') }}
                        </h4>
                        <div class="text-muted">{{ __('Standard Status') }}</div>
                    </div>
                </div>

                <!-- Test Criteria -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('Test Criteria') }}</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between">
                                <span>{{ __('Sample Size') }}</span>
                                <strong>{{ $standard->sample_size }}</strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between">
                                <span>{{ __('Test Frequency') }}</span>
                                <strong>
                                    {{ match ($standard->test_frequency) {
                                        'per_batch' => __('Per Batch'),
                                        'daily' => __('Daily'),
                                        'weekly' => __('Weekly'),
                                        'monthly' => __('Monthly'),
                                        default => $standard->test_frequency,
                                    } }}
                                </strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between text-success">
                                <span>{{ __('Acceptance Threshold') }}</span>
                                <strong>{{ number_format($standard->acceptance_threshold, 1) }}%</strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between text-danger">
                                <span>{{ __('Max Allowed Defects') }}</span>
                                <strong>{{ $standard->max_defects_allowed }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('Additional Information') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="small text-muted mb-2">{{ __('Created') }} {{ __('Date') }}</div>
                        <div class="mb-3">{{ $standard->created_at?->format('Y-m-d H:i') }}</div>

                        @if ($standard->updated_at != $standard->created_at)
                            <div class="small text-muted mb-2">{{ __('Last Updated') }}</div>
                            <div>{{ $standard->updated_at?->format('Y-m-d H:i') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
