{{-- @canany(['عرض العملاء', 'عرض الموردين', 'عرض الصناديق', 'عرض البنوك', 'عرض الموظفين', 'عرض المخازن', 'عرض المصروفات', 'عرض الايرادات', 'عرض دائنين متنوعين', 'عرض مدينين متنوعين', 'عرض الشركاء', 'عرض جارى الشركاء', 'عرض الأصول الثابتة', 'عرض الأصول القابلة للتأجير'])
<li class="li-main">
    <a href="javascript: void(0);">
        <i data-feather="database" style="color:#4e73df" class="align-self-center menu-icon"></i>
        <span>{{ __('navigation.accounts') }}</span>
        <span class="menu-arrow">
            <i class="mdi mdi-chevron-right"></i>
        </span>
    </a>
    <ul class="sub-menu mm-collapse" aria-expanded="false">

        {{-- Account Management --}}
{{-- <li class="menu-label my-2">{{ __('navigation.account_management') }}</li>  --}}


@can('عرض جميع الحسابات')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index') }}">
            <i class="ti-list"></i>{{ __('navigation.all_accounts') }}
        </a>
    </li>
@endcan


@can('عرض العملاء')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'clients']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.clients') }}
        </a>
    </li>
@endcan

@can('عرض الموردين')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'suppliers']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.suppliers') }}
        </a>
    </li>
@endcan

@can('عرض الصناديق')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'funds']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.funds') }}
        </a>
    </li>
@endcan

@can('عرض البنوك')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'banks']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.banks') }}
        </a>
    </li>
@endcan

@can('عرض الموظفين')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'employees']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.employees') }}
        </a>
    </li>
@endcan

@can('عرض المخازن')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'warhouses']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.warehouses') }}
        </a>
    </li>
@endcan

@can('عرض المصروفات')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'expenses']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.expenses') }}
        </a>
    </li>
@endcan

@can('عرض الايرادات')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'revenues']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.revenues') }}
        </a>
    </li>
@endcan

@can('عرض دائنين متنوعين')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'creditors']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.various_creditors') }}
        </a>
    </li>
@endcan

@can('عرض مدينين متنوعين')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'debtors']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.various_debtors') }}
        </a>
    </li>
@endcan

@can('عرض الشركاء')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'partners']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.partners') }}
        </a>
    </li>
@endcan

@can('عرض جارى الشركاء')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'current-partners']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.current_partners') }}
        </a>
    </li>
@endcan

@can('عرض الأصول الثابتة')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'assets']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.fixed_assets') }}
        </a>
    </li>
@endcan

@can('عرض الأصول القابلة للتأجير')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'rentables']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.rentable_assets') }}
        </a>
    </li>
@endcan

{{-- Account Reports --}}
{{-- <li class="menu-label my-2">{{ __('navigation.account_reports') }}</li> --}}

@can('عرض تقرير حركة الحساب')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.account-movement-report') }}">
            <i class="ti-bar-chart"></i>{{ __('navigation.account_movement_report') }}
        </a>
    </li>
@endcan

@can('عرض الميزانية العمومية')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.balance-sheet') }}">
            <i class="ti-pie-chart"></i>{{ __('navigation.balance_sheet') }}
        </a>
    </li>
@endcan

@can('إدارة الرصيد الافتتاحي')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.start-balance') }}">
            <i class="ti-settings"></i>{{ __('navigation.start_balance_management') }}
        </a>
    </li>
@endcan
{{--
</ul>
</li>
@endcanany --}}
