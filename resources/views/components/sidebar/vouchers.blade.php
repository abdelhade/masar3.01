@can('view vouchers-statistics')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('vouchers.statistics') }}">
            <i class="ti-control-record"></i>{{ __('Vouchers Statistics') }}
        </a>
    </li>
@endcan


@can('view recipt')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('vouchers.index', ['type' => 'receipt']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.general_receipt_voucher') }}
        </a>
    </li>
@endcan

@can('view payment')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('vouchers.index', ['type' => 'payment']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.general_payment_voucher') }}
        </a>
    </li>
@endcan

@can('view exp-payment')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('vouchers.index', ['type' => 'exp-payment']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.general_payment_voucher_for_expenses') }}
        </a>
    </li>
@endcan

<hr class="my-3 border-secondary">

@can('view multi-payment')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.index', ['type' => 'multi_payment']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.multi_payment_voucher') }}
        </a>
    </li>
@endcan

@can('view multi-receipt')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.index', ['type' => 'multi_receipt']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.multi_receipt_voucher') }}
        </a>
    </li>
@endcan
