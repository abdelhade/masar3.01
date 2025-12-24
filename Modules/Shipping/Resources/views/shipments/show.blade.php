@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.shipping')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Shipment Details'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Shipments'), 'url' => route('shipments.index')],
            ['label' => __('Details')],
        ],
    ])

    <div class="row">
        <!-- معلومات الشحنة -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-box"></i> {{ __('Shipment Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>{{ __('Status') }}:</strong>
                            <p>
                                @if ($shipment->status == 'pending')
                                    <span class="badge bg-warning fs-6">{{ __('Pending') }}</span>
                                @elseif($shipment->status == 'processing')
                                    <span class="badge bg-info fs-6">{{ __('Processing') }}</span>
                                @elseif($shipment->status == 'in_transit')
                                    <span class="badge bg-primary fs-6">{{ __('In Transit') }}</span>
                                @elseif($shipment->status == 'out_for_delivery')
                                    <span class="badge bg-primary fs-6">{{ __('Out for Delivery') }}</span>
                                @elseif($shipment->status == 'delivered')
                                    <span class="badge bg-success fs-6">{{ __('Delivered') }}</span>
                                @elseif($shipment->status == 'returned')
                                    <span class="badge bg-danger fs-6">{{ __('Returned') }}</span>
                                @else
                                    <span class="badge bg-dark fs-6">{{ __('Cancelled') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>{{ __('Customer') }}:</strong>
                            <p>{{ $shipment->customer_name }}</p>
                            @if ($shipment->customer_phone)
                                <small><i class="fas fa-phone"></i> {{ $shipment->customer_phone }}</small><br>
                            @endif
                            @if ($shipment->customer_email)
                                <small><i class="fas fa-envelope"></i> {{ $shipment->customer_email }}</small>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>{{ __('Shipping Company') }}:</strong>
                            <p>{{ $shipment->shippingCompany->name }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <strong>{{ __('Address') }}:</strong>
                            <p>{{ $shipment->customer_address }}</p>
                            @if ($shipment->zone)
                                <span class="badge bg-secondary">{{ $shipment->zone }}</span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <strong>{{ __('Weight') }}:</strong>
                            <p>{{ $shipment->weight }} {{ __('kg') }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <strong>{{ __('Priority') }}:</strong>
                            <p>
                                @if ($shipment->priority == 'express')
                                    <span class="badge bg-danger">{{ __('Express') }}</span>
                                @elseif($shipment->priority == 'urgent')
                                    <span class="badge bg-warning">{{ __('Urgent') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('Normal') }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <strong>{{ __('Estimated Delivery') }}:</strong>
                            <p>{{ $shipment->estimated_delivery_date ? $shipment->estimated_delivery_date->format('Y-m-d') : '-' }}
                            </p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <strong>{{ __('Actual Delivery') }}:</strong>
                            <p>{{ $shipment->actual_delivery_date ? $shipment->actual_delivery_date->format('Y-m-d') : '-' }}
                            </p>
                        </div>
                    </div>

                    @if ($shipment->notes)
                        <div class="alert alert-info">
                            <strong>{{ __('Notes') }}:</strong>
                            <p class="mb-0">{{ $shipment->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- الكارد الجانبي -->
        <div class="col-lg-4">
            <!-- كارد التكلفة -->
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <h5 class="text-white"><i class="fas fa-dollar-sign"></i> {{ __('Cost Breakdown') }}</h5>
                    <hr class="bg-white">
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('Shipping Cost') }}:</span>
                        <strong>{{ number_format($shipment->shipping_cost, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('Insurance') }}:</span>
                        <strong>{{ number_format($shipment->insurance_cost, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('Additional Fees') }}:</span>
                        <strong>{{ number_format($shipment->additional_fees, 2) }}</strong>
                    </div>
                    <hr class="bg-white">
                    <div class="d-flex justify-content-between">
                        <h5 class="text-white">{{ __('Total') }}:</h5>
                        <h5 class="text-white">{{ number_format($shipment->total_cost, 2) }}</h5>
                    </div>
                </div>
            </div>

            <!-- كارد الأبعاد -->
            @if ($shipment->length || $shipment->width || $shipment->height)
                <div class="card mt-3">
                    <div class="card-body">
                        <h6><i class="fas fa-ruler-combined"></i> {{ __('Package Dimensions') }}</h6>
                        <hr>
                        <p class="mb-1"><strong>{{ __('Length') }}:</strong> {{ $shipment->length ?? '-' }} cm</p>
                        <p class="mb-1"><strong>{{ __('Width') }}:</strong> {{ $shipment->width ?? '-' }} cm</p>
                        <p class="mb-1"><strong>{{ __('Height') }}:</strong> {{ $shipment->height ?? '-' }} cm</p>
                        @if ($shipment->package_value)
                            <hr>
                            <p class="mb-0"><strong>{{ __('Package Value') }}:</strong>
                                {{ number_format($shipment->package_value, 2) }}</p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- أزرار الإجراءات -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6><i class="fas fa-cog"></i> {{ __('Actions') }}</h6>
                    <hr>
                    <a href="{{ route('shipments.edit', $shipment->id) }}" class="btn btn-warning btn-block mb-2">
                        <i class="fas fa-edit"></i> {{ __('Edit') }}
                    </a>
                    <button class="btn btn-secondary btn-block" onclick="window.print()">
                        <i class="fas fa-print"></i> {{ __('Print') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }

        .timeline-badge {
            position: absolute;
            left: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: white;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
@endsection
