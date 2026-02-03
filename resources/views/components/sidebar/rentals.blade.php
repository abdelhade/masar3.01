@can('view Rentals Statistics')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('rentals.statistics') }}">
            <i class="ti-control-record"></i>{{ __('Rentals Statistics') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('rentals.reports') }}">
            <i class="ti-control-record"></i>{{ __('Reports') }}
        </a>
    </li>
@endcan

@can('view Buildings')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('rentals.buildings.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.rent_building') }}
        </a>
    </li>
@endcan

@can('view Leases')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('rentals.leases.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.rent_contracts') }}
        </a>
    </li>
@endcan
