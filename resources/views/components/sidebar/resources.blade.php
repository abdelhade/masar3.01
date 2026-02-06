@can('view Resources Dashboard')
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('myresources.dashboard') }}">
            <i class="ti-control-record"></i>{{ __('Resources Dashboard') }}
        </a>
    </li>
@endcan

@can('view Resources')
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('myresources.index') }}">
            <i class="ti-control-record"></i>{{ __('Resources Management') }}
        </a>
    </li>
@endcan

@can('view Resource Assignments')
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('myresources.assignments.index') }}">
            <i class="ti-control-record"></i>{{ __('Resource Assignments') }}
        </a>
    </li>
@endcan

@can('view Resource Categories')
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('myresources.categories.index') }}">
            <i class="ti-control-record"></i>{{ __('Resource Categories') }}
        </a>
    </li>
@endcan

@can('view Resource Types')
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('myresources.types.index') }}">
            <i class="ti-control-record"></i>{{ __('Resource Types') }}
        </a>
    </li>
@endcan

@can('view Resource Statuses')
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('myresources.statuses.index') }}">
            <i class="ti-control-record"></i>{{ __('Resource Statuses') }}
        </a>
    </li>
@endcan
