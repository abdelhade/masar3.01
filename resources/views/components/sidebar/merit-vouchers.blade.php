@can('view salary-calculation')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'salary_calculation']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.fixed_salary_calculation') }}
        </a>
    </li>
@endcan

@can('view extra-salary-calculation')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'extra_calc']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.extra_salary_calculation') }}
        </a>
    </li>
@endcan

@can('view discount-salary-calculation')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'discount_calc']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.discount_salary_calculation') }}
        </a>
    </li>
@endcan

@can('view insurance-calculation')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'insurance_calc']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.insurance_calculation') }}
        </a>
    </li>
@endcan

@can('view tax-calculation')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'tax_calc']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.tax_calculation') }}
        </a>
    </li>
@endcan
