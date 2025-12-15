@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.fleet')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('fleet::Fuel Record'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('fleet::Fuel Records'), 'url' => route('fleet.fuel-records.index')],
            ['label' => __('fleet::Fuel Record') . ' #' . $fuelRecord->id],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="page-title">{{ __('fleet::Fuel Record') }} #{{ $fuelRecord->id }}</h4>
                        <div class="d-flex gap-2">
                            @can('edit Fuel Records')
                                <a href="{{ route('fleet.fuel-records.edit', $fuelRecord) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> {{ __('fleet::Edit') }}
                                </a>
                            @endcan
                            <a href="{{ route('fleet.fuel-records.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right"></i> {{ __('fleet::Back') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Vehicle') }}:</label>
                            <div class="form-control-static">{{ $fuelRecord->vehicle->name ?? '-' }} ({{ $fuelRecord->vehicle->plate_number ?? '-' }})</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Trip') }}:</label>
                            <div class="form-control-static">
                                @if($fuelRecord->trip)
                                    <a href="{{ route('fleet.trips.show', $fuelRecord->trip_id) }}">
                                        {{ $fuelRecord->trip->trip_number }}
                                    </a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Fuel Date') }}:</label>
                            <div class="form-control-static">{{ $fuelRecord->fuel_date->format('Y-m-d') }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Fuel Type') }}:</label>
                            <div class="form-control-static">{{ $fuelRecord->fuel_type->label() }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Quantity') }}:</label>
                            <div class="form-control-static">{{ number_format($fuelRecord->quantity, 2) }} L</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Cost') }}:</label>
                            <div class="form-control-static">{{ number_format($fuelRecord->cost, 2) }} {{ __('SAR') }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Mileage at Fueling') }}:</label>
                            <div class="form-control-static">{{ number_format($fuelRecord->mileage_at_fueling, 2) }} km</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Station Name') }}:</label>
                            <div class="form-control-static">{{ $fuelRecord->station_name ?? '-' }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Receipt Number') }}:</label>
                            <div class="form-control-static">{{ $fuelRecord->receipt_number ?? '-' }}</div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Notes') }}:</label>
                            <div class="form-control-static">{{ $fuelRecord->notes ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

