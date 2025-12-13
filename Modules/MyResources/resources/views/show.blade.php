@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.myresources')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box no-print">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="page-title">{{ __('Resource Details') }}: {{ $resource->name }}</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('myresources.edit', $resource) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> {{ __('Edit') }}
                        </a>
                        <button onclick="window.print()" class="btn btn-info">
                            <i class="fas fa-print"></i> {{ __('Print') }}
                        </button>
                        <a href="{{ route('myresources.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right"></i> {{ __('Back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card printable-content">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-box"></i> {{ __('Resource Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Name') }}:</label>
                            <div class="form-control-static">{{ $resource->name }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Status') }}:</label>
                            <div class="form-control-static">
                                @if($resource->status)
                                    <span class="badge" style="background-color: {{ $resource->status->color ?? '#007bff' }}">
                                        {{ $resource->status->name_ar ?? $resource->status->name }}
                                    </span>
                                @else
                                    {{ __('N/A') }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Category') }}:</label>
                            <div class="form-control-static">{{ $resource->category->name ?? __('N/A') }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Type') }}:</label>
                            <div class="form-control-static">{{ $resource->type->name ?? __('N/A') }}</div>
                        </div>
                    </div>

                    @if($resource->description)
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">{{ __('Description') }}:</label>
                            <div class="form-control-static">{{ $resource->description }}</div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Branch') }}:</label>
                            <div class="form-control-static">{{ $resource->branch->name ?? __('N/A') }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Employee') }}:</label>
                            <div class="form-control-static">{{ $resource->employee->aname ?? __('N/A') }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('Serial Number') }}:</label>
                            <div class="form-control-static">{{ $resource->serial_number ?? __('N/A') }}</div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('Model Number') }}:</label>
                            <div class="form-control-static">{{ $resource->model_number ?? __('N/A') }}</div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('Manufacturer') }}:</label>
                            <div class="form-control-static">{{ $resource->manufacturer ?? __('N/A') }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('Purchase Date') }}:</label>
                            <div class="form-control-static">{{ $resource->purchase_date ? \Carbon\Carbon::parse($resource->purchase_date)->format('Y-m-d') : __('N/A') }}</div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('Purchase Cost') }}:</label>
                            <div class="form-control-static">{{ $resource->purchase_cost ? number_format($resource->purchase_cost, 2) : __('N/A') }}</div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('Current Location') }}:</label>
                            <div class="form-control-static">{{ $resource->current_location ?? __('N/A') }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('Daily Rate') }}:</label>
                            <div class="form-control-static">{{ $resource->daily_rate ? number_format($resource->daily_rate, 2) : __('N/A') }}</div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('Hourly Rate') }}:</label>
                            <div class="form-control-static">{{ $resource->hourly_rate ? number_format($resource->hourly_rate, 2) : __('N/A') }}</div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('Warranty Expiry') }}:</label>
                            <div class="form-control-static">{{ $resource->warranty_expiry ? \Carbon\Carbon::parse($resource->warranty_expiry)->format('Y-m-d') : __('N/A') }}</div>
                        </div>
                    </div>

                    @if($resource->notes)
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">{{ __('Notes') }}:</label>
                            <div class="form-control-static">{{ $resource->notes }}</div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Active') }}:</label>
                            <div class="form-control-static">
                                @if($resource->is_active)
                                    <span class="badge bg-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-danger">{{ __('Inactive') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($resource->assignments->count() > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3">{{ __('Assignments') }}:</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Project') }}</th>
                                            <th>{{ __('Start Date') }}</th>
                                            <th>{{ __('End Date') }}</th>
                                            <th>{{ __('Assigned By') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($resource->assignments as $assignment)
                                        <tr>
                                            <td>{{ $assignment->project->name ?? __('N/A') }}</td>
                                            <td>{{ $assignment->start_date ? \Carbon\Carbon::parse($assignment->start_date)->format('Y-m-d') : __('N/A') }}</td>
                                            <td>{{ $assignment->end_date ? \Carbon\Carbon::parse($assignment->end_date)->format('Y-m-d') : __('N/A') }}</td>
                                            <td>{{ $assignment->assignedBy->name ?? __('N/A') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .form-control-static {
        padding: 0.5rem;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        min-height: 2.5rem;
        display: flex;
        align-items: center;
    }

    @media print {
        .no-print { display: none !important; }
        .card { border: 1px solid #000 !important; box-shadow: none !important; }
        .card-header { background: #f1f1f1 !important; color: #000 !important; }
        body { font-size: 12px; }
        .form-control-static { background: #fff !important; border: 1px solid #000 !important; }
        .table { font-size: 10px; }
    }
</style>
@endpush
@endsection

