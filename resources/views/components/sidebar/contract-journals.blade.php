@can('view service-agreement')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'contract']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.service_agreement') }}
        </a>
    </li>
@endcan

@can('view accured-expenses')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'accured_expense']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.accured_expenses') }}
        </a>
    </li>
@endcan

@can('view accured-revenues')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'accured_income']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.accured_revenues') }}
        </a>
    </li>
@endcan

@can('view bank-commission')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'bank_commission']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.bank_commission_calculation') }}
        </a>
    </li>
@endcan

@can('view sales-contract')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'sales_contract']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.sales_contract') }}
        </a>
    </li>
@endcan

@can('view partner-profit-sharing')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'partner_profit_sharing']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.partner_profit_sharing') }}
        </a>
    </li>
@endcan
