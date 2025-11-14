@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.rentals')
@endsection

@section('content')
    <div class="container-fluid px-4">
        <br>
        <div class="row">
            <h2 class="mb-4 col-4">
                <i class="fas fa-building text-primary me-2"></i>
                {{ $building->name }}
            </h2>

            <p class="text-muted col-4">
                <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                {{ $building->address }}
            </p>
        </div>

        @can('create Unit')
            <a href="{{ route('rentals-units.create', $building->id) }}" class="btn btn-info " type="button">
                <i class="fas fa-plus"></i> {{ __('Add Units') }}
            </a>
        @endcan
        <br>
        <br>
        <div class="row">
            @foreach ($building->units as $unit)
                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                    <div class="card unit-card shadow border-0 h-100">
                        <div class="card-header unit-header status-{{ strtolower($unit->status->value ?? 'available') }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="fas fa-home me-2"></i>
                                    {{ __('Unit') }} {{ $unit->name }}
                                </h6>

                                <div>
                                    @can('edit Unit')
                                        <a href="{{ route('rentals.units.edit', $unit->id) }}"
                                            class="btn btn-sm btn-success me-2">
                                            <i class="fas fa-edit"></i> {{ __('Edit') }}
                                        </a>
                                    @endcan

                                    @can('delete Unit')
                                        <form action="{{ route('rentals.units.destroy', $unit->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('{{ __('Are you sure you want to delete this unit?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> {{ __('Delete') }}
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                                <span class="badge badge-status">
                                    {{ $unit->status->label ?? __('Available') }}
                                </span>
                            </div>
                        </div>

                        <div class="card-body">
                            @if ($unit->floor)
                                <p><i class="fas fa-layer-group me-2 text-info"></i> {{ __('Floor') }}
                                    {{ $unit->floor }}</p>
                            @endif
                            @if ($unit->area)
                                <p><i class="fas fa-ruler-combined me-2 text-warning"></i> {{ $unit->area }} م²</p>
                            @endif

                            @php
                                $activeLease = $unit->leases
                                    ->where('status', \Modules\Rentals\Enums\LeaseStatus::ACTIVE)
                                    ->first();
                            @endphp
                            @if ($activeLease)
                                <div class="alert alert-light p-2">
                                    <small class="text-success">
                                        <i class="fas fa-user me-1"></i>
                                        {{ __('Rented to Mr./Ms.') }} {{ $activeLease->client->name }}
                                    </small><br>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ __('Until') }} {{ $activeLease->end_date->format('Y/m/d') }}
                                    </small>
                                </div>
                            @endif
                        </div>

                        <div class="card-footer bg-light">
                            <div class="btn-group w-100">
                                @if ($unit->status === \Modules\Rentals\Enums\UnitStatus::AVAILABLE)
                                    @can('create Leases')
                                        <a href="{{ route('rentals.leases.create', $unit->id) }}"
                                            class="btn btn-success btn-sm">
                                            <i class="fas fa-file-contract me-1"></i> {{ __('New Lease') }}
                                        </a>
                                    @endcan
                                @elseif($activeLease)
                                    @can('view Leases')
                                        <a href="{{ route('rentals.leases.show', $activeLease->id) }}"
                                            class="btn btn-info btn-sm">
                                            <i class="fas fa-file-alt me-1"></i> {{ __('Current Lease') }}
                                        </a>
                                    @endcan
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
