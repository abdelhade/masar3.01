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
                    <h4 class="page-title">{{ __('Maintenance Details') }}: {{ $maintenance->item_name ?? '#' . $maintenance->id }}</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('maintenances.edit', $maintenance) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> {{ __('Edit') }}
                        </a>
                        <button onclick="window.print()" class="btn btn-info">
                            <i class="fas fa-print"></i> {{ __('Print') }}
                        </button>
                        <a href="{{ route('maintenances.index') }}" class="btn btn-secondary">
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
                    <h5 class="mb-0"><i class="fas fa-tools"></i> {{ __('Maintenance Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Client Name') }}:</label>
                            <div class="form-control-static">{{ $maintenance->client_name ?? __('N/A') }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Client Phone') }}:</label>
                            <div class="form-control-static">{{ $maintenance->client_phone ?? __('N/A') }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Item Name') }}:</label>
                            <div class="form-control-static">{{ $maintenance->item_name ?? __('N/A') }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Item Number') }}:</label>
                            <div class="form-control-static">{{ $maintenance->item_number ?? __('N/A') }}</div>
                        </div>
                    </div>

                    <div class="row">
                        @if($maintenance->type)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Service Type') }}:</label>
                            <div class="form-control-static">{{ $maintenance->type->name }}</div>
                        </div>
                        @endif

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Status') }}:</label>
                            <div class="form-control-static">
                                @php
                                    $statusLabels = [
                                        '0' => __('Pending'),
                                        '1' => __('In Progress'),
                                        '2' => __('Completed'),
                                        '3' => __('Cancelled')
                                    ];
                                    $status = (string)($maintenance->status ?? '0');
                                    $label = $statusLabels[$status] ?? $status;
                                @endphp
                                {{ $label }}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Date') }}:</label>
                            <div class="form-control-static">
                                {{ $maintenance->date ? $maintenance->date->format('Y-m-d') : __('N/A') }}
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Accural Date') }}:</label>
                            <div class="form-control-static">
                                {{ $maintenance->accural_date ? $maintenance->accural_date->format('Y-m-d') : __('N/A') }}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">{{ __('Notes') }}:</label>
                            <div class="form-control-static">{{ $maintenance->notes ?? __('N/A') }}</div>
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

