@can('view basicData-statistics')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.basic-data-statistics') }}">
            <i class="ti-list"></i>{{ __('Basic Data Statistics') }}
        </a>
    </li>
@endcan

@can('view Clients')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'clients']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.clients') }}
        </a>
    </li>
@endcan

@can('view Suppliers')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'suppliers']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.suppliers') }}
        </a>
    </li>
@endcan

@can('view Funds')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'funds']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.funds') }}
        </a>
    </li>
@endcan

@can('view Banks')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'banks']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.banks') }}
        </a>
    </li>
@endcan

@can('view Employees')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'employees']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.employees') }}
        </a>
    </li>
@endcan

@can('view warhouses')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'warhouses']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.warehouses') }}
        </a>
    </li>
@endcan

@can('view Expenses')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'expenses']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.expenses') }}
        </a>
    </li>
@endcan

@can('view Revenues')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'revenues']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.revenues') }}
        </a>
    </li>
@endcan

@can('view various_creditors')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'creditors']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.various_creditors') }}
        </a>
    </li>
@endcan

@can('view various_debtors')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'debtors']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.various_debtors') }}
        </a>
    </li>
@endcan

@can('view partners')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'partners']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.partners') }}
        </a>
    </li>
@endcan

@can('view current_partners')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'current-partners']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.current_partners') }}
        </a>
    </li>
@endcan

@can('view assets')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'assets']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.fixed_assets') }}
        </a>
    </li>
@endcan

@can('view rentables')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'rentables']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.rentable_assets') }}
        </a>
    </li>
@endcan

@can('view check-portfolios-incoming')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'check-portfolios-incoming']) }}">
            <i class="ti-control-record"></i>{{ __('Incoming Check Portfolios') }}
        </a>
    </li>
@endcan

@can('view check-portfolios-outgoing')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'check-portfolios-outgoing']) }}">
            <i class="ti-control-record"></i>{{ __('Outgoing Check Portfolios') }}
        </a>
    </li>
@endcan
