@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.service')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box no-print">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="page-title">{{ __('Service Type Details') }}: {{ $type->name }}</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('service.types.edit', $type) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> {{ __('Edit') }}
                        </a>
                        <button onclick="window.print()" class="btn btn-info">
                            <i class="fas fa-print"></i> {{ __('Print') }}
                        </button>
                        <a href="{{ route('service.types.index') }}" class="btn btn-secondary">
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
                    <h5 class="mb-0"><i class="fas fa-cog"></i> {{ __('Service Type Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Name') }}:</label>
                            <div class="form-control-static">{{ $type->name }}</div>
                        </div>

                        @if($type->branch)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Branch') }}:</label>
                            <div class="form-control-static">{{ $type->branch->name }}</div>
                        </div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">{{ __('Description') }}:</label>
                            <div class="form-control-static">{{ $type->description ?? __('N/A') }}</div>
                        </div>
                    </div>
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
    }
</style>
@endpush
@endsection

