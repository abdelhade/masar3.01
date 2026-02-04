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
                    <h2 class="mb-1">{{ $ncr->ncr_number }}</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('quality.dashboard') }}">{{ __("Quality") }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('quality.ncr.index') }}">{{ __("NCR") }}</a></li>
                            <li class="breadcrumb-item active">{{ $ncr->ncr_number }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('quality.ncr.edit', $ncr) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>{{ __("Edit") }}
                    </a>
                    <a href="{{ route('quality.ncr.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-2"></i>{{ __("Back") }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- معلومات أساسية -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>{{ __("Report Information") }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">{{ __("Item") }}</label>
                            <div class="fw-bold">{{ $ncr->item?->name ?? __("Not Specified") }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">{{ __("Source") }}</label>
                            <div class="fw-bold">
                                {{ match($ncr->source) {
                                    'receiving_inspection' => __("Receiving Inspection"),
                                    'in_process' => __("In-Process Inspection"),
                                    'final_inspection' => __("Final Inspection"),
                                    'customer_complaint' => __("Customer Complaint Inspection"),
                                    'internal_audit' => __("Internal"),
                                    'supplier_notification' => __("Supplier Notification"),
                                    default => $ncr->source
                                } }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">{{ __("Detection Date") }}</label>
                            <div class="fw-bold">{{ $ncr->detected_date?->format('Y-m-d') }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">{{ __("Affected Quantity") }}</label>
                            <div class="fw-bold">{{ number_format($ncr->affected_quantity, 3) }}</div>
                        </div>
                        @if($ncr->batch_number)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">{{ __("Batch Number") }}</label>
                            <div class="fw-bold">{{ $ncr->batch_number }}</div>
                        </div>
                        @endif
                        @if($ncr->inspection)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">{{ __("Related Inspection") }}</label>
                            <div class="fw-bold">
                                <a href="{{ route('quality.inspections.show', $ncr->inspection) }}" class="text-decoration-none">
                                    {{ $ncr->inspection->inspection_number }}
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- الوصف والتفاصيل -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>{{ __("Description and Details") }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">{{ __("Problem Description") }}</label>
                        <div class="alert alert-danger mb-0">{{ $ncr->problem_description }}</div>
                    </div>
                    @if($ncr->immediate_action)
                    <div class="mb-0">
                        <label class="text-muted small">{{ __("Immediate Action") }}</label>
                        <div class="alert alert-info mb-0">{{ $ncr->immediate_action }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- الحالة والإحصائيات -->
        <div class="col-lg-4">
            <!-- الحالة -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($ncr->status == 'closed')
                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                        @elseif($ncr->status == 'open')
                            <i class="fas fa-exclamation-circle text-danger" style="font-size: 3rem;"></i>
                        @else
                            <i class="fas fa-clock text-warning" style="font-size: 3rem;"></i>
                        @endif
                    </div>
                    <h4 class="mb-2">
                        {{ match($ncr->status) {
                            'open' => __("Open"),
                            'in_progress' => __("In Progress"),
                            'closed' => __("Closed"),
                            'cancelled' => __("Cancelled"),
                            default => $ncr->status
                        } }}
                    </h4>
                    <div class="text-muted">{{ __("Report Status") }}</div>
                </div>
            </div>

            <!-- التصنيف -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">{{ __("Classification") }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between">
                            <span>{{ __("Severity") }}</span>
                            <span class="badge bg-{{ $ncr->severity == 'critical' ? 'danger' : ($ncr->severity == 'major' ? 'warning' : 'info') }}">
                                {{ match($ncr->severity) {
                                    'critical' => __("Critical"),
                                    'major' => __("Major"),
                                    'minor' => __("Minor"),
                                    default => $ncr->severity
                                } }}
                            </span>
                        </div>
                        @if($ncr->disposition)
                        <div class="list-group-item d-flex justify-content-between">
                            <span>{{ __("Disposition") }}</span>
                            <strong>
                                {{ match($ncr->disposition) {
                                    'rework' => __("Rework"),
                                    'scrap' => __("Scrap"),
                                    'return_to_supplier' => __("Return to Supplier"),
                                    'use_as_is' => __("Use As Is"),
                                    'repair' => __("Repair"),
                                    'downgrade' => __("Downgrade"),
                                    default => $ncr->disposition
                                } }}
                            </strong>
                        </div>
                        @endif
                        @if($ncr->estimated_cost)
                        <div class="list-group-item d-flex justify-content-between">
                            <span>{{ __("Estimated Cost") }}</span>
                            <strong class="text-warning">{{ number_format($ncr->estimated_cost, 2) }}</strong>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- المسؤولون -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">{{ __("Responsibilities") }}</h6>
                </div>
                <div class="card-body">
                    <div class="small text-muted mb-2">{{ __("Detected By") }}</div>
                    <div class="mb-3">{{ $ncr->detectedBy?->name ?? __("Not Specified") }}</div>
                    
                    @if($ncr->assignedTo)
                    <div class="small text-muted mb-2">{{ __("Assigned To") }}</div>
                    <div class="mb-3">{{ $ncr->assignedTo->name }}</div>
                    @endif

                    @if($ncr->target_closure_date)
                    <div class="small text-muted mb-2">{{ __("Target Closure Date") }}</div>
                    <div>{{ $ncr->target_closure_date->format('Y-m-d') }}</div>
                    @endif
                </div>
            </div>

            <!-- معلومات إضافية -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ __("Additional Information") }}</h6>
                </div>
                <div class="card-body">
                    <div class="small text-muted mb-2">تاريخ ال{{ __("Create") }}</div>
                    <div class="mb-3">{{ $ncr->created_at?->format('Y-m-d H:i') }}</div>
                    
                    @if($ncr->updated_at != $ncr->created_at)
                    <div class="small text-muted mb-2">{{ __("Last Updated") }}</div>
                    <div>{{ $ncr->updated_at?->format('Y-m-d H:i') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection