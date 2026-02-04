@can('view journals-statistics')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('journal.statistics') }}">
            <i class="ti-control-record"></i>{{ __('Journal Statistics') }}
        </a>
    </li>
@endcan

@can('create journals')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('journals.create', ['type' => 'basic_journal']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.daily_journal') }}
        </a>
    </li>
@endcan

@can('create multi-journals')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-journals.create') }}">
            <i class="ti-control-record"></i>{{ __('navigation.multi_journal') }}
        </a>
    </li>
@endcan

@can('view journals')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('journals.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.daily_ledgers_operations') }}
        </a>
    </li>
@endcan

@can('view multi-journals')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-journals.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.multi_daily_ledgers_operations') }}
        </a>
    </li>
@endcan

@can('create inventory-balance')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('inventory-balance.create') }}">
            <i class="ti-control-record"></i>{{ __('navigation.opening_inventory_balance') }}
        </a>
    </li>
@endcan

@can('view opening-balance-accounts')
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('accounts.startBalance') }}">
            <i class="ti-control-record"></i>{{ __('navigation.opening_balance_accounts') }}
        </a>
    </li>
@endcan

@can('view balance-sheet')
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('accounts.balanceSheet') }}">
            <i class="ti-control-record"></i>{{ __('navigation.balance_sheet') }}
        </a>
    </li>
@endcan
