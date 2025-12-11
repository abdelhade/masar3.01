<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Track Shipment') }} - {{ $shipment->tracking_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="text-center mb-4">{{ __('Shipment Tracking') }}</h3>
                        
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
                            <div class="timeline-item mb-3 p-3 border-start border-3 border-primary">
                                <h6>{{ __(ucfirst($history->status)) }}</h6>
                                <small class="text-muted">{{ $history->created_at->format('Y-m-d H:i') }}</small>
                                @if($history->notes)
                                <p class="mb-0 mt-2">{{ $history->notes }}</p>
                                @endif
                                @if($history->location)
                                <small><i class="fas fa-map-marker-alt"></i> {{ $history->location }}</small>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
