@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.shipping')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box no-print">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="page-title">{{ __('Order Details') }}: {{ $order->order_number ?? '#' . $order->id }}</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('orders.edit', $order) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> {{ __('Edit') }}
                        </a>
                        <button onclick="window.print()" class="btn btn-info">
                            <i class="fas fa-print"></i> {{ __('Print') }}
                        </button>
                        <a href="{{ route('orders.index') }}" class="btn btn-secondary">
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
                    <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> {{ __('Order Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Order Number') }}:</label>
                            <div class="form-control-static">{{ $order->order_number ?? '#' . $order->id }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Delivery Status') }}:</label>
                            <div class="form-control-static">
                                @php
                                    $statusLabels = [
                                        'pending' => __('Pending'),
                                        'assigned' => __('Assigned'),
                                        'in_transit' => __('In Transit'),
                                        'delivered' => __('Delivered')
                                    ];
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'assigned' => 'info',
                                        'in_transit' => 'primary',
                                        'delivered' => 'success'
                                    ];
                                    $status = $order->delivery_status ?? 'pending';
                                    $label = $statusLabels[$status] ?? $status;
                                    $color = $statusColors[$status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ $label }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Driver') }}:</label>
                            <div class="form-control-static">
                                {{ $order->driver->name ?? __('N/A') }}
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Shipment') }}:</label>
                            <div class="form-control-static">
                                {{ $order->shipment->tracking_number ?? __('N/A') }}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Customer Name') }}:</label>
                            <div class="form-control-static">{{ $order->customer_name ?? __('N/A') }}</div>
                        </div>

                        @if($order->branch)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Branch') }}:</label>
                            <div class="form-control-static">{{ $order->branch->name }}</div>
                        </div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">{{ __('Customer Address') }}:</label>
                            <div class="form-control-static">{{ $order->customer_address ?? __('N/A') }}</div>
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

    .printable-content {
        page-break-inside: avoid;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .card {
            border: 1px solid #000 !important;
            box-shadow: none !important;
            page-break-inside: avoid;
        }

        .card-header {
            background: #f1f1f1 !important;
            color: #000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-size: 12px;
        }

        .form-control-static {
            background: #fff !important;
            border: 1px solid #000 !important;
        }
    }
</style>
@endpush
@endsection

