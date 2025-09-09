@canany([
    'عرض احتساب الاضافي للموظفين',
    'عرض احتساب خصم للموظفين',
    'عرض احتساب تأمينات',
    'عرض احتساب ضريبة
    دخل',
    ])
    <li class="li-main">
        <a href="javascript: void(0);">
            <i data-feather="user-check" style="color:#17a2b8" class="align-self-center menu-icon"></i>
            <span>{{ __('navigation.employee_salaries') }}</span>
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse" aria-expanded="false">
            @can('عرض احتساب الثابت للموظفين')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'salary_calculation']) }}">
                        <i class="ti-control-record"></i>{{ __('navigation.fixed_salary_calculation') }}
                    </a>
                </li>
            @endcan
            @can('عرض احتساب الاضافي للموظفين')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'extra_calc']) }}">
                        <i class="ti-control-record"></i>{{ __('navigation.extra_salary_calculation') }}
                    </a>
                </li>
            @endcan
            @can('عرض احتساب خصم للموظفين')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'discount_calc']) }}">
                        <i class="ti-control-record"></i>{{ __('navigation.discount_salary_calculation') }}
                    </a>
                </li>
            @endcan
            @can('عرض احتساب تأمينات')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'insurance_calc']) }}">
                        <i class="ti-control-record"></i>{{ __('navigation.insurance_calculation') }}
                    </a>
                </li>
            @endcan
            @can('عرض احتساب ضريبة دخل')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'tax_calc']) }}">
                        <i class="ti-control-record"></i>{{ __('navigation.tax_calculation') }}
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcanany


@canany([
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
        <ul class="sub-menu mm-collapse" aria-expanded="false">

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
            </ul>

        </li>
    @endcan
@endcanany


@canany([
    'عرض اهلاك الاصل',
    'عرض بيع الاصول',
    'عرض شراء اصل',
    'عرض زيادة في قيمة الاصل',
    'عرض نقص في قيمة
    الاصل',
    ])
    <li class="li-main">
        <a href="javascript: void(0);">
            <i data-feather="hard-drive" style="color:#e83e8c" class="align-self-center menu-icon"></i>
            <span>{{ __('navigation.asset_operations') }}</span>
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse" aria-expanded="false">
            @can('عرض اهلاك الاصل')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'depreciation']) }}">
                        <i class="ti-control-record"></i>{{ __('navigation.depreciation') }}
                    </a>
                </li>
            @endcan
            @can('عرض بيع الاصول')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'sell_asset']) }}">
                        <i class="ti-control-record"></i>{{ __('navigation.sell_asset') }}
                    </a>
                </li>
            @endcan
            @can('عرض شراء اصل')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'buy_asset']) }}">
                        <i class="ti-control-record"></i>{{ __('navigation.buy_asset') }}
                    </a>
                </li>
            @endcan
            @can('عرض زيادة في قيمة الاصل')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'increase_asset_value']) }}">
                        <i class="ti-control-record"></i>{{ __('navigation.increase_asset_value') }}
                    </a>
                </li>
            @endcan
            @can('عرض نقص في قيمة الاصل')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'decrease_asset_value']) }}">
                        <i class="ti-control-record"></i>{{ __('navigation.decrease_asset_value') }}
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcanany
