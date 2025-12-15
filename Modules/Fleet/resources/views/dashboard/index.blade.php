@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.fleet')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('fleet::Fleet Dashboard'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('fleet::Fleet Dashboard')]],
    ])

    <div class="row">
        <!-- Vehicle Statistics -->
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('fleet::Total Vehicles') }}</h6>
                            <h3 class="mb-0">{{ $totalVehicles }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-primary rounded-circle">
                                <i class="las la-car"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('fleet::Available Vehicles') }}</h6>
                            <h3 class="mb-0 text-success">{{ $availableVehicles }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-success rounded-circle">
                                <i class="las la-check-circle"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('fleet::In Use Vehicles') }}</h6>
                            <h3 class="mb-0 text-primary">{{ $inUseVehicles }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-primary rounded-circle">
                                <i class="las la-road"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('fleet::Maintenance Vehicles') }}</h6>
                            <h3 class="mb-0 text-warning">{{ $maintenanceVehicles }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-warning rounded-circle">
                                <i class="las la-tools"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Trip Statistics -->
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('fleet::Today Trips') }}</h6>
                            <h3 class="mb-0">{{ $todayTrips }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-info rounded-circle">
                                <i class="las la-calendar-day"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('fleet::Month Trips') }}</h6>
                            <h3 class="mb-0">{{ $monthTrips }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-info rounded-circle">
                                <i class="las la-calendar"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('fleet::Month Fuel Cost') }}</h6>
                            <h3 class="mb-0 text-danger">{{ number_format($monthFuelCost, 2) }} {{ __('SAR') }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-danger rounded-circle">
                                <i class="las la-gas-pump"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('fleet::Total Fuel Quantity') }}</h6>
                            <h3 class="mb-0">{{ number_format($totalFuelQuantity, 2) }} L</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-secondary rounded-circle">
                                <i class="las la-tint"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('fleet::Recent Trips') }}</h5>
                </div>
                <div class="card-body">
                    @forelse($recentTrips as $trip)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <h6 class="mb-1">{{ $trip->trip_number }}</h6>
                                <p class="text-muted mb-0">{{ $trip->start_location }} → {{ $trip->end_location }}</p>
                            </div>
                            <span class="badge bg-{{ $trip->status->color() }}">{{ $trip->status->label() }}</span>
                        </div>
                    @empty
                        <p class="text-muted">{{ __('fleet::No data available') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('fleet::Recent Fuel Records') }}</h5>
                </div>
                <div class="card-body">
                    @forelse($recentFuelRecords as $record)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <h6 class="mb-1">{{ $record->vehicle->name ?? '-' }}</h6>
                                <p class="text-muted mb-0">{{ number_format($record->quantity, 2) }} L - {{ number_format($record->cost, 2) }} {{ __('SAR') }}</p>
                            </div>
                            <small class="text-muted">{{ $record->fuel_date->format('Y-m-d') }}</small>
                        </div>
                    @empty
                        <p class="text-muted">{{ __('fleet::No data available') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

