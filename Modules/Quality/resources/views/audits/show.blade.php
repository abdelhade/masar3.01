@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-search me-2"></i>{{ __('Audit Details') }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('quality.dashboard') }}">{{ __('Quality') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quality.audits.index') }}">{{ __('Audit') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('View') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Audit Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Audit Number') }}</label>
                            <p class="form-control-plaintext">{{ $audit->audit_number }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Audit Title') }}</label>
                            <p class="form-control-plaintext">{{ $audit->audit_title }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Audit Type') }}</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-info">
                                    {{ match($audit->audit_type) {
                                        'internal' => __('Internal'),
                                        'external' => __('External'),
                                        'supplier' => __('Supplier Audit'),
                                        'certification' => __('Certification'),
                                        'customer' => __('Customer'),
                                        default => $audit->audit_type
                                    } }}
                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Status') }}</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-{{ match($audit->status) {
                                    'completed' => 'success',
                                    'in_progress' => 'warning',
                                    'planned' => 'info',
                                    'cancelled' => 'danger',
                                    default => 'secondary'
                                } }}">
                                    {{ match($audit->status) {
                                        'planned' => __('Planned'),
                                        'in_progress' => __('In Progress'),
                                        'completed' => __('Completed'),
                                        'cancelled' => __('Cancelled'),
                                        default => $audit->status
                                    } }}
                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Planned Date') }}</label>
                            <p class="form-control-plaintext">{{ $audit->planned_date?->format('Y-m-d') ?? '---' }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Lead Auditor') }}</label>
                            <p class="form-control-plaintext">{{ $audit->leadAuditor?->name ?? '---' }}</p>
                        </div>

                        @if($audit->audit_objectives)
                        <div class="col-12 mb-3">
                            <label class="form-label">{{ __('Audit Objectives') }}</label>
                            <p class="form-control-plaintext">{{ $audit->audit_objectives }}</p>
                        </div>
                        @endif

                        @if($audit->summary)
                        <div class="col-12 mb-3">
                            <label class="form-label">{{ __('Summary') }}</label>
                            <p class="form-control-plaintext">{{ $audit->summary }}</p>
                        </div>
                        @endif

                        @if($audit->status == 'completed')
                        <div class="col-12">
                            <h6 class="mb-3">{{ __('Results Statistics') }}</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $audit->total_findings ?? 0 }}</h4>
                                            <small>{{ __('Total Findings') }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $audit->critical_findings ?? 0 }}</h4>
                                            <small>{{ __('Critical Findings') }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $audit->major_findings ?? 0 }}</h4>
                                            <small>{{ __('Major Findings') }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $audit->minor_findings ?? 0 }}</h4>
                                            <small>{{ __('Minor Findings') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Actions') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('quality.audits.edit', $audit) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>{{ __('Edit Audit') }}
                        </a>
                        <button type="button" class="btn btn-danger" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteModal">
                            <i class="fas fa-trash me-2"></i>{{ __('Delete Audit') }}
                        </button>
                        <a href="{{ route('quality.audits.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>{{ __('Back to List') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Additional Information') }}</h5>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <strong>{{ __('Created At') }}:</strong><br>
                        {{ $audit->created_at?->format('Y-m-d H:i') }}<br><br>
                        <strong>{{ __('Last Updated') }}:</strong><br>
                        {{ $audit->updated_at?->format('Y-m-d H:i') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Confirm Delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{ __('Are you sure you want to delete audit') }} "{{ $audit->audit_title }}"?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <form action="{{ route('quality.audits.destroy', $audit) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection