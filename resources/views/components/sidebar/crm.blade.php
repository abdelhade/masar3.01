@can('view Inquiries Statistics')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('statistics.index') }}">
            <i class="ti-control-record"></i>{{ __('Statistics') }}
        </a>
    </li>
@endcan

@can('view Clients')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('clients.index') }}">
            <i class="ti-control-record"></i>{{ __('Clients') }}
        </a>
    </li>
@endcan

@can('view Chance Sources')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('chance-sources.index') }}">
            <i class="ti-control-record"></i>{{ __('Chance Source') }}
        </a>
    </li>
@endcan

@can('view Client Contacts')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('client-contacts.index') }}">
            <i class="ti-control-record"></i>{{ __('Client Contacts') }}
        </a>
    </li>
@endcan

@can('view Client Types')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('client-types.index') }}">
            <i class="ti-control-record"></i>{{ __('Clients Types') }}
        </a>
    </li>
@endcan

@can('view Lead Statuses')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('lead-status.index') }}">
            <i class="ti-control-record"></i>{{ __('Lead Status') }}
        </a>
    </li>
@endcan

@can('view Leads')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('leads.board') }}">
            <i class="ti-control-record"></i>{{ __('Lead') }}
        </a>
    </li>
@endcan

@can('view Tasks')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('tasks.index') }}">
            <i class="ti-control-record"></i>{{ __('Tasks') }}
        </a>
    </li>
@endcan

@can('view Tickets')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('tickets.index') }}">
            <i class="ti-control-record"></i>{{ __('Tickets') }}
        </a>
    </li>
@endcan

@can('view Returns')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('returns.index') }}">
            <i class="ti-control-record"></i>{{ __('Returns') }}
        </a>
    </li>
@endcan

@can('view Task Types')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('tasks.types.index') }}">
            <i class="ti-control-record"></i>{{ __('Tasks types') }}
        </a>
    </li>
@endcan

@can('view Activities')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('activities.index') }}">
            <i class="ti-control-record"></i>{{ __('Activities') }}
        </a>
    </li>
@endcan
