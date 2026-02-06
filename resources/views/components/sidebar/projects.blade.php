@can('view projects-statistics')
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('projects.statistics') }}">
            <i class="ti-control-record"></i>{{ __('Projects Statistics') }}
        </a>
    </li>
@endcan

@can('view projects')
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('progress.project.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.projects') }}
        </a>
    </li>
@endcan

@can('view rentals')
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('rentals.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.rentals') }}
        </a>
    </li>
@endcan
