@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0"><i class="fas fa-tools me-2"></i>{{ __("CAPA Details") }}</h2>
                </div>
                <div>
                    <a href="{{ route('quality.capa.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-right me-2"></i>{{ __("Back to List") }}
                    </a>
                    <a href="{{ route('quality.capa.edit', $capa) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>{{ __("Edit") }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>{{ __("Basic Information") }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __("CAPA Number") }}:</label>
                            <p class="mb-0">{{ $capa->capa_number }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __("CAPA Type") }}:</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $capa->action_type == 'corrective' ? 'warning' : 'info' }}">
                                    {{ $capa->action_type == 'corrective' ? __("Corrective") : __("Preventive") }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __("Related NCR") }}:</label>
                            <p class="mb-0">{{ $capa->nonConformanceReport->ncr_number ?? '---' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __("Priority") }}:</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ match($capa->priority) {
                                    'high' => 'danger',
                                    'medium' => 'warning',
                                    'low' => 'success',
                                    default => 'secondary'
                                } }}">
                                    {{ match($capa->priority) {
                                        'high' => __("High"),
                                        'medium' => __("Medium"),
                                        'low' => __("Low"),
                                        default => $capa->priority
                                    } }}
                                </span>
                            </p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">{{ __("Problem Description") }}:</label>
                            <p class="mb-0">{{ $capa->problem_description }}</p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">{{ __("Root Cause") }}:</label>
                            <p class="mb-0">{{ $capa->root_cause_analysis }}</p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">{{ __("Action Plan") }}:</label>
                            <p class="mb-0">{{ $capa->proposed_action }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>{{ __("Dates and Implementation") }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __("Planned Start Date") }}:</label>
                            <p class="mb-0">{{ $capa->planned_start_date->format('Y-m-d') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __("Planned Completion Date") }}:</label>
                            <p class="mb-0">{{ $capa->planned_completion_date->format('Y-m-d') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __("Actual Start Date") }}:</label>
                            <p class="mb-0">{{ $capa->actual_start_date ? $capa->actual_start_date->format('Y-m-d') : '---' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __("Actual Completion Date") }}:</label>
                            <p class="mb-0">{{ $capa->actual_completion_date ? $capa->actual_completion_date->format('Y-m-d') : '---' }}</p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">{{ __("Completion Percentage") }}:</label>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-{{ $capa->completion_percentage >= 100 ? 'success' : 'primary' }}" 
                                     style="width: {{ $capa->completion_percentage }}%">
                                    {{ $capa->completion_percentage }}%
                                </div>
                            </div>
                        </div>
                        @if($capa->implementation_details)
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">{{ __("Implementation Details") }}:</label>
                            <p class="mb-0">{{ $capa->implementation_details }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($capa->verification_details || $capa->effectiveness_review)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>{{ __("Verification Method") }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($capa->verification_details)
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">{{ __("Verification Details") }}:</label>
                            <p class="mb-0">{{ $capa->verification_details }}</p>
                        </div>
                        @endif
                        @if($capa->effectiveness_review)
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">{{ __("Effectiveness Review") }}:</label>
                            <p class="mb-0">{{ $capa->effectiveness_review }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>الحالة</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <span class="badge bg-{{ match($capa->status) {
                            'completed' => 'success',
                            'in_progress' => 'warning',
                            'verified' => 'info',
                            default => 'secondary'
                        } }} fs-6 px-3 py-2">
                            {{ match($capa->status) {
                                'completed' => __("Completed"),
                                'in_progress' => __("In Progress"),
                                'verified' => __("Verified"),
                                default => $capa->status
                            } }}
                        </span>
                    </div>
                    @if($capa->isOverdue())
                    <div class="alert alert-danger text-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ __("Overdue") }}
                    </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>{{ __("Responsibilities") }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __("Assigned To") }}:</label>
                        <p class="mb-0">{{ $capa->responsiblePerson->name ?? '---' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __("Verified By") }}:</label>
                        <p class="mb-0">{{ $capa->verifiedBy->name ?? '---' }}</p>
                    </div>
                    @if($capa->verification_date)
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __("Verification Date") }}:</label>
                        <p class="mb-0">{{ $capa->verification_date->format('Y-m-d') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>{{ __("System Information") }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __("Created At") }}:</label>
                        <p class="mb-0">{{ $capa->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __("Last Updated") }}:</label>
                        <p class="mb-0">{{ $capa->updated_at->format('Y-m-d H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection