<li class="nav-item">
    <a class="nav-link" href="{{ route('vouchers.statistics') }}">
        <i class="ti-control-record"></i>{{ __('Vouchers Statistics') }}
    </a>
</li>

@can('عرض سند قبض')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('vouchers.index', ['type' => 'receipt']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.general_receipt_voucher') }}
        </a>
    </li>
@endcan
@can(' سند دفع عامل')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('vouchers.index', ['type' => 'payment']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.general_payment_voucher') }}
        </a>
    </li>
@endcan
<li class="nav-item">
    <a class="nav-link" href="{{ route('vouchers.index', ['type' => 'payment']) }}">
        <i class="ti-control-record"></i>{{ __('navigation.general_payment_voucher') }}
    </a>
</li>
@can('عرض السندات')
    <li class="nav-item">

        <a class="nav-link" href="{{ route('vouchers.index', ['type' => 'exp-payment']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.general_payment_voucher_for_expenses') }}
        </a>
    </li>
@endcan
@can('عرض سند دفع متعدد')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.index', ['type' => 'multi_payment']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.multi_payment_voucher') }}
        </a>
    </li>
@endcan
@can('عرض سند قبض متعدد')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.index', ['type' => 'multi_receipt']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.multi_receipt_voucher') }}
        </a>
    </li>
@endcan
