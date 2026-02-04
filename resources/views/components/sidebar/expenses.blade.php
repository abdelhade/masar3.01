@can('view Expenses')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('expenses.dashboard') ? 'active' : '' }}"
            href="{{ route('expenses.dashboard') }}">
            <i class="fas fa-tachometer-alt"></i>
            <span>{{ __('Expenses Dashboard') }}</span>
        </a>
    </li>
@endcan

@can('create Expenses')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('expenses.create') ? 'active' : '' }}" href="{{ route('expenses.create') }}">
            <i class="fas fa-plus-circle"></i>
            <span>{{ __('New Expense Record') }}</span>
        </a>
    </li>
@endcan

<li class="nav-divider"></li>

@can('view Expenses Reports')
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="fas fa-file-invoice-dollar"></i>
            {{ __('Expenses Reports') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.general-expenses-report') ? 'active' : '' }}"
                    href="{{ route('reports.general-expenses-report') }}">
                    <i class="ti-control-record"></i>{{ __('General Expenses Report') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.general-expenses-daily-report') ? 'active' : '' }}"
                    href="{{ route('reports.general-expenses-daily-report') }}">
                    <i class="ti-control-record"></i>{{ __('Expense Account Statement') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.expenses-balance-report') ? 'active' : '' }}"
                    href="{{ route('reports.expenses-balance-report') }}">
                    <i class="ti-control-record"></i>{{ __('Expenses Balance Sheet') }}
                </a>
            </li>
        </ul>
    </li>
@endcan

@can('view Cost Centers Report')
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="fas fa-sitemap"></i>
            {{ __('Cost Centers') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.general-cost-centers-report') ? 'active' : '' }}"
                    href="{{ route('reports.general-cost-centers-report') }}">
                    <i class="ti-control-record"></i>{{ __('Cost Centers Report') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.general-cost-center-account-statement') ? 'active' : '' }}"
                    href="{{ route('reports.general-cost-center-account-statement') }}">
                    <i class="ti-control-record"></i>{{ __('Cost Center Account Statement') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.general-cost-centers-list') ? 'active' : '' }}"
                    href="{{ route('reports.general-cost-centers-list') }}">
                    <i class="ti-control-record"></i>{{ __('Cost Centers List') }}
                </a>
            </li>
        </ul>
    </li>
@endcan
<li class="nav-divider"></li>

<li class="nav-item">
    <a class="nav-link" href="{{ route('reports.overall') }}">
        <i class="fas fa-arrow-right"></i>
        <span>{{ __('Back to Reports') }}</span>
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ route('admin.dashboard') }}">
        <i class="fas fa-home"></i>
        <span>{{ __('Home Page') }}</span>
    </a>
</li>
