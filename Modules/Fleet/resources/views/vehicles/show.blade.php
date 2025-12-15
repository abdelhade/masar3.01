@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.fleet')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('fleet::Vehicle'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('fleet::Vehicles'), 'url' => route('fleet.vehicles.index')],
            ['label' => $vehicle->name],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="page-title">{{ __('fleet::Vehicle') }}: {{ $vehicle->name }}</h4>
                        <div class="d-flex gap-2">
                            @can('edit Vehicles')
                                <a href="{{ route('fleet.vehicles.edit', $vehicle) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> {{ __('fleet::Edit') }}
                                </a>
                            @endcan
                            <a href="{{ route('fleet.vehicles.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right"></i> {{ __('fleet::Back') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Code') }}:</label>
                            <div class="form-control-static">{{ $vehicle->code }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Plate Number') }}:</label>
                            <div class="form-control-static">{{ $vehicle->plate_number }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Vehicle Type') }}:</label>
                            <div class="form-control-static">{{ $vehicle->vehicleType->name ?? '-' }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Driver') }}:</label>
                            <div class="form-control-static">{{ $vehicle->driver->name ?? '-' }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Status') }}:</label>
                            <div class="form-control-static">
                                <span class="badge bg-{{ $vehicle->status->color() }}">
                                    {{ $vehicle->status->label() }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Current Mileage') }}:</label>
                            <div class="form-control-static">{{ number_format($vehicle->current_mileage, 2) }} km</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Model') }}:</label>
                            <div class="form-control-static">{{ $vehicle->model ?? '-' }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Year') }}:</label>
                            <div class="form-control-static">{{ $vehicle->year ?? '-' }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Color') }}:</label>
                            <div class="form-control-static">{{ $vehicle->color ?? '-' }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Purchase Date') }}:</label>
                            <div class="form-control-static">{{ $vehicle->purchase_date?->format('Y-m-d') ?? '-' }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Purchase Cost') }}:</label>
                            <div class="form-control-static">{{ $vehicle->purchase_cost ? number_format($vehicle->purchase_cost, 2) . ' ' . __('SAR') : '-' }}</div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Notes') }}:</label>
                            <div class="form-control-static">{{ $vehicle->notes ?? '-' }}</div>
                        </div>
                    </div>

                    @if($vehicle->trips->count() > 0)
                        <hr>
                        <h5 class="mb-3">{{ __('fleet::Trips') }}</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('fleet::Trip Number') }}</th>
                                        <th>{{ __('fleet::Start Location') }}</th>
                                        <th>{{ __('fleet::End Location') }}</th>
                                        <th>{{ __('fleet::Status') }}</th>
                                        <th>{{ __('fleet::Distance') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($vehicle->trips->take(10) as $trip)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $trip->trip_number }}</td>
                                            <td>{{ $trip->start_location }}</td>
                                            <td>{{ $trip->end_location }}</td>
                                            <td>
                                                <span class="badge bg-{{ $trip->status->color() }}">
                                                    {{ $trip->status->label() }}
                                                </span>
                                            </td>
                                            <td>{{ $trip->distance ? number_format($trip->distance, 2) . ' km' : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

