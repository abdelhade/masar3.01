{{-- @canany([
    'عرض قيد يومية',
    'عرض قيد يوميه متعدد',
    'عرض قيود يومية عمليات',
    'عرض قيود يوميه عمليات متعدده',
    'عرض قيود
    يوميه حسابات',
    'عرض تسجيل الارصده الافتتاحيه للمخازن',
])
    <li class="li-main">
        <a href="javascript: void(0);">
            <i data-feather="bar-chart-2" style="color:#007bff" class="align-self-center menu-icon"></i>
            <span>{{ __('navigation.account_management') }}</span>
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse" aria-expanded="false"> --}}
@can('عرض قيد يومية')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('journals.create', ['type' => 'basic_journal']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.daily_journal') }}
        </a>
    </li>
@endcan

@can('عرض قيد يوميه متعدد')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-journals.create') }}">
            <i class="ti-control-record"></i>{{ __('navigation.multi_journal') }}
        </a>
    </li>
@endcan
@can('عرض قيود يومية عمليات')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('journals.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.daily_ledgers_operations') }}
        </a>
    </li>
@endcan
@can('عرض قيود يوميه عمليات متعدده')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-journals.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.multi_daily_ledgers_operations') }}
        </a>
    </li>
@endcan
@can('عرض قيود يوميه حسابات')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('journal-summery') }}">
            <i class="ti-control-record"></i>{{ __('navigation.daily_ledgers_accounts') }}
        </a>
    </li>
@endcan

@can('عرض تسجيل الارصده الافتتاحيه للمخازن')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('inventory-balance.create') }}">
            <i class="ti-control-record"></i>{{ __('navigation.opening_inventory_balance') }}
        </a>
    </li>
@endcan
{{-- الرصيد الافتتاحى للحسابات --}}
<li class="nav-item">
    <a class="nav-link font-family-cairo fw-bold" href="{{ route('accounts.startBalance') }}">
        <i class="ti-control-record"></i>{{ __('navigation.opening_balance_accounts') }}
    </a>
</li>
{{-- الرصيد الافتتاحى للحسابات --}}
{{-- account movement --}}
<li class="nav-item">
    <a class="nav-link font-family-cairo fw-bold" href="{{ route('account-movement') }}">
        <i class="ti-control-record"></i>{{ __('navigation.account_movement_report') }}
    </a>
</li>

{{-- account movement --}}
{{-- balance sheet --}}
<li class="nav-item">
    <a class="nav-link font-family-cairo fw-bold" href="{{ route('accounts.balanceSheet') }}">
        <i class="ti-control-record"></i>{{ __('navigation.balance_sheet') }}
    </a>
</li>
{{-- balance sheet --}}
{{-- </ul>
    </li>
@endcanany --}}
