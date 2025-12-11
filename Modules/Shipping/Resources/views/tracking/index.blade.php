@extends('admin.dashboard')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-4">{{ __('Track Your Shipment') }}</h4>
                <form action="{{ route('shipments.tracking') }}" method="GET">
                    <div class="input-group mb-3">
                        <input type="text" name="tracking_number" class="form-control form-control-lg" 
                               placeholder="{{ __('Enter Tracking Number') }}" 
                               value="{{ $trackingNumber }}" required>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i> {{ __('Track') }}
                        </button>
                    </div>
                </form>

                @if($shipment)
                <div class="mt-4">
                    <div class="alert alert-info">
                        <h5>{{ __('Shipment Details') }}</h5>
                        <p><strong>{{ __('Tracking Number') }}:</strong> {{ $shipment->tracking_number }}</p>
                        <p><strong>{{ __('Customer') }}:</strong> {{ $shipment->customer_name }}</p>
                        <p><strong>{{ __('Status') }}:</strong> 
                            <span class="badge bg-primary">{{ __(ucfirst($shipment->status)) }}</span>
                        </p>
                        <p><strong>{{ __('Estimated Delivery') }}:</strong> 
                            {{ $shipment->estimated_delivery_date ? $shipment->estimated_delivery_date->format('Y-m-d') : 'N/A' }}
                        </p>
                    </div>

                    <h5 class="mt-4">{{ __('Tracking History') }}</h5>
                    <div class="timeline">
                        @foreach($shipment->statusHistory->sortByDesc('created_at') as $history)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="fas fa-circle text-primary"></i>
                                </div>
                                <div>
                                    <h6>{{ __(ucfirst($history->status)) }}</h6>
                                    <small class="text-muted">{{ $history->created_at->format('Y-m-d H:i') }}</small>
                                    @if($history->notes)
                                    <p class="mb-0">{{ $history->notes }}</p>
                                    @endif
                                    @if($history->location)
                                    <small><i class="fas fa-map-marker-alt"></i> {{ $history->location }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @elseif($trackingNumber)
                <div class="alert alert-warning mt-4">
                    {{ __('No shipment found with this tracking number.') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
