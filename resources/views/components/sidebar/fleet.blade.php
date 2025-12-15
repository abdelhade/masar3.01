<li class="sidebar-main-title">
    <div>
        <h6 class="lan-1">{{ __('fleet::Fleet Management') }}</h6>
    </div>
</li>

@can('view Fleet Dashboard')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('fleet.dashboard.index') }}">
            <i class="ti-control-record"></i>{{ __('fleet::Fleet Dashboard') }}
        </a>
    </li>
@endcan

@can('view Vehicle Types')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('fleet.vehicle-types.index') }}">
            <i class="ti-control-record"></i>{{ __('fleet::Vehicle Types') }}
        </a>
    </li>
@endcan

@can('view Vehicles')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('fleet.vehicles.index') }}">
            <i class="ti-control-record"></i>{{ __('fleet::Vehicles') }}
        </a>
    </li>
@endcan

@can('view Trips')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('fleet.trips.index') }}">
            <i class="ti-control-record"></i>{{ __('fleet::Trips') }}
        </a>
    </li>
@endcan

@can('view Fuel Records')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('fleet.fuel-records.index') }}">
            <i class="ti-control-record"></i>{{ __('fleet::Fuel Records') }}
        </a>
    </li>
@endcan

