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
                    <h2 class="mb-0"><i class="fas fa-barcode me-2"></i>{{ __("Batch Details") }}</h2>
                </div>
                <div>
                    <a href="{{ route('quality.batches.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-right me-2"></i>{{ __("Back to List") }}
                    </a>
                    <a href="{{ route('quality.batches.edit', $batch) }}" class="btn btn-warning">
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
                            <label class="form-label fw-bold">{{ __("Batch Number") }}:</label>
                            <p class="mb-0">{{ $batch->batch_number }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __("Item") }}:</label>
                            <p class="mb-0">{{ $batch->item?->name ?? '---' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __("Production Date") }}:</label>
                            <p class="mb-0">{{ $batch->production_date ? $batch->production_date->format('Y-m-d') : '---' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __("Expiry Date") }}:</label>
                            <p class="mb-0">{{ $batch->expiry_date ? $batch->expiry_date->format('Y-m-d') : '---' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __("Quantity") }}:</label>
                            <p class="mb-0">{{ number_format($batch->quantity, 3) }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __("Remaining Quantity") }}:</label>
                            <p class="mb-0">{{ number_format($batch->remaining_quantity, 3) }}</p>
                        </div>
                        @if($batch->supplier)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __("Supplier") }}:</label>
                            <p class="mb-0">{{ $batch->supplier->aname ?? '---' }}</p>
                        </div>
                        @endif
                        @if($batch->warehouse)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __("Warehouse") }}:</label>
                            <p class="mb-0">{{ $batch->warehouse->aname ?? '---' }}</p>
                        </div>
                        @endif
                        @if($batch->location)
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">{{ __("Storage Location") }}:</label>
                            <p class="mb-0">{{ $batch->location }}</p>
                        </div>
                        @endif
                        @if($batch->notes)
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">{{ __("Notes") }}:</label>
                            <p class="mb-0">{{ $batch->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>{{ __("Status") }}</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <span class="badge bg-{{ $batch->status == 'active' ? 'success' : ($batch->status == 'consumed' ? 'secondary' : 'danger') }} fs-6 px-3 py-2">
                            @switch($batch->status)
                                @case('active') {{ __("Active") }} @break
                                @case('consumed') {{ __("Consumed") }} @break
                                @case('expired') {{ __("Expired") }} @break
                                @case('rejected') {{ __("Rejected") }} @break
                                @default {{ $batch->status }}
                            @endswitch
                        </span>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>{{ __("Quality Status") }}</h5>
                </div>
                <div class="card-body text-center">
                    <span class="badge bg-{{ $batch->quality_status == 'passed' ? 'success' : ($batch->quality_status == 'failed' ? 'danger' : 'warning') }} fs-6 px-3 py-2">
                        @switch($batch->quality_status)
                            @case('passed') {{ __("Passed") }} @break
                            @case('failed') {{ __("Failed") }} @break
                            @case('conditional') {{ __("Conditional") }} @break
                            @case('quarantine') {{ __("Quarantine") }} @break
                            @default {{ $batch->quality_status }}
                        @endswitch
                    </span>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>{{ __("System Information") }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __("Created At") }}:</label>
                        <p class="mb-0">{{ $batch->created_at ? $batch->created_at->format('Y-m-d H:i') : '---' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __("Last Updated") }}:</label>
                        <p class="mb-0">{{ $batch->updated_at ? $batch->updated_at->format('Y-m-d H:i') : '---' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

