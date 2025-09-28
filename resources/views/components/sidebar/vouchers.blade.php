{{-- @canany(['عرض احتساب الثابت للموظفين', 'عرض السندات', 'عرض سند دفع', 'عرض سند دفع متعدد', 'عرض سند قبض'])
    <li class="li-main">
        <a href="javascript: void(0);">
            <i data-feather="file-text" style="color:#fd7e14" class="align-self-center menu-icon"></i>
            <span>{{ __('navigation.vouchers') }}</span>
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse" aria-expanded="false"> --}}
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
{{--
        </ul>
    </li>
@endcanany --}}
