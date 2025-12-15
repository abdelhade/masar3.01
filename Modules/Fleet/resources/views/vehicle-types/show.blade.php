@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.fleet')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('fleet::Vehicle Type'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('fleet::Vehicle Types'), 'url' => route('fleet.vehicle-types.index')],
            ['label' => $type->name],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="page-title">{{ __('fleet::Vehicle Type') }}: {{ $type->name }}</h4>
                        <div class="d-flex gap-2">
                            @can('edit Vehicle Types')
                                <a href="{{ route('fleet.vehicle-types.edit', $type) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> {{ __('fleet::Edit') }}
                                </a>
                            @endcan
                            <a href="{{ route('fleet.vehicle-types.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right"></i> {{ __('fleet::Back') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Name') }}:</label>
                            <div class="form-control-static">{{ $type->name }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Status') }}:</label>
                            <div class="form-control-static">
                                @if($type->is_active)
                                    <span class="badge bg-success">{{ __('fleet::Active') }}</span>
                                @else
                                    <span class="badge bg-danger">{{ __('fleet::Inactive') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Description') }}:</label>
                            <div class="form-control-static">{{ $type->description ?? __('N/A') }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('fleet::Total Vehicles') }}:</label>
                            <div class="form-control-static">{{ $type->vehicles->count() }}</div>
                        </div>
                    </div>

                    @if($type->vehicles->count() > 0)
                        <hr>
                        <h5 class="mb-3">{{ __('fleet::Vehicles') }}</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('fleet::Code') }}</th>
                                        <th>{{ __('fleet::Plate Number') }}</th>
                                        <th>{{ __('fleet::Name') }}</th>
                                        <th>{{ __('fleet::Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($type->vehicles as $vehicle)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $vehicle->code }}</td>
                                            <td>{{ $vehicle->plate_number }}</td>
                                            <td>{{ $vehicle->name }}</td>
                                            <td>
                                                <span class="badge bg-{{ $vehicle->status->color() }}">
                                                    {{ $vehicle->status->label() }}
                                                </span>
                                            </td>
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

