{{-- @canany([
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
        <ul class="sub-menu mm-collapse" aria-expanded="false"> --}}
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
{{-- </ul>
    </li>
@endcanany --}}
