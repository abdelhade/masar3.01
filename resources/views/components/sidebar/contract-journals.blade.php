{{-- @canany([
    'عرض سند قبض متعدد',
    'عرض اتفاقية خدمة',
    'عرض مصروفات مستحقة',
    'عرض ايرادات مستحقة',
    'عرض احتساب
    عمولة بنكية',
    'عرض عقد بيع',
    'عرض توزيع الارباح علي الشركا',
])
    <li class="li-main">
        <a href="javascript: void(0);">
            <i data-feather="clock" style="color:#6f42c1" class="align-self-center menu-icon"></i>
            <span>{{ __('navigation.accruals') }}</span>
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse" aria-expanded="false"> --}}

@can('عرض اتفاقية خدمة')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'contract']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.service_agreement') }}
        </a>
    </li>
@endcan
@can('عرض مصروفات مستحقة')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'accured_expense']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.accured_expenses') }}
        </a>
    </li>
@endcan
@can('عرض ايرادات مستحقة')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'accured_income']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.accured_revenues') }}
        </a>
    </li>
@endcan
@can('عرض احتساب عمولة بنكية')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'bank_commission']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.bank_commission_calculation') }}
        </a>
    </li>
@endcan
@can('عرض عقد بيع')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'sales_contract']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.sales_contract') }}
        </a>
    </li>
@endcan
@can('عرض توزيع الارباح علي الشركا')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'partner_profit_sharing']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.partner_profit_sharing') }}
        </a>
    </li>
    {{-- </ul> --}}

    {{-- </li> --}}
@endcan
{{-- @endcanany --}}
