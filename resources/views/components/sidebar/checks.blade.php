@can('view Check Portfolios Incoming')
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('checks.incoming') }}">
            <i class="fas fa-arrow-circle-down" style="color:#28a745"></i> {{ __('navigation.incoming_checks') }}
        </a>
    </li>
@endcan

@can('create Check Portfolios Incoming')
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('checks.incoming.create') }}">
            <i class="fas fa-plus-circle" style="color:#28a745"></i> {{ __('Add Incoming Check') }}
        </a>
    </li>
@endcan

<li class="nav-item">
    <hr class="my-2">
</li>

@can('view Check Portfolios Outgoing')
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('checks.outgoing') }}">
            <i class="fas fa-arrow-circle-up" style="color:#dc3545"></i> {{ __('navigation.outgoing_checks') }}
        </a>
    </li>
@endcan

@can('create Check Portfolios Outgoing')
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('checks.outgoing.create') }}">
            <i class="fas fa-plus-circle" style="color:#dc3545"></i> {{ __('Add Outgoing Check') }}
        </a>
    </li>
@endcan

<li class="nav-item">
    <hr class="my-2">
</li>

@can('view Checks')
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('checks.dashboard') }}">
            <i class="fas fa-chart-line" style="color:#667eea"></i> {{ __('navigation.checks_dashboard') }}
        </a>
    </li>
@endcan
