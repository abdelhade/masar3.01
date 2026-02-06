<li class="menu-title mt-2">{{ __('Vouchers Module') }}</li>

@can('view vouchers-statistics')
<li class="nav-item">
    <a class="nav-link d-flex align-items-center gap-2 font-hold fw-bold transition-base {{ request()->routeIs('vouchers.statistics') ? 'active' : '' }}" 
       href="{{ route('vouchers.statistics') }}"
       style="{{ request()->routeIs('vouchers.statistics') ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3;' : '' }}">
        <i class="las la-chart-bar font-18"></i>{{ __('Vouchers Statistics') }}
    </a>
</li>
@endcan

@can('view recipt')
    <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 font-hold fw-bold transition-base {{ request()->routeIs('vouchers.index') && request('type') == 'receipt' ? 'active' : '' }}" 
           href="{{ route('vouchers.index', ['type' => 'receipt']) }}"
           style="{{ request()->routeIs('vouchers.index') && request('type') == 'receipt' ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3;' : '' }}">
            <i class="las la-file-invoice-dollar font-18"></i>{{ __('navigation.general_receipt_voucher') }}
        </a>
    </li>
@endcan

@can('view payment')
    <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 font-hold fw-bold transition-base {{ request()->routeIs('vouchers.index') && request('type') == 'payment' ? 'active' : '' }}" 
           href="{{ route('vouchers.index', ['type' => 'payment']) }}"
           style="{{ request()->routeIs('vouchers.index') && request('type') == 'payment' ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3;' : '' }}">
            <i class="las la-money-bill-wave font-18"></i>{{ __('navigation.general_payment_voucher') }}
        </a>
    </li>
@endcan

@can('view exp-payment')
    <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 font-hold fw-bold transition-base {{ request()->routeIs('vouchers.index') && request('type') == 'exp-payment' ? 'active' : '' }}" 
           href="{{ route('vouchers.index', ['type' => 'exp-payment']) }}"
           style="{{ request()->routeIs('vouchers.index') && request('type') == 'exp-payment' ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3;' : '' }}">
            <i class="las la-hand-holding-usd font-18"></i>{{ __('navigation.general_payment_voucher_for_expenses') }}
        </a>
    </li>
@endcan

<li class="menu-title mt-3">{{ __('Multi Vouchers') }}</li>

@can('view multi-payment')
    <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 font-hold fw-bold transition-base {{ request()->routeIs('multi-vouchers.index') && request('type') == 'multi_payment' ? 'active' : '' }}" 
           href="{{ route('multi-vouchers.index', ['type' => 'multi_payment']) }}"
           style="{{ request()->routeIs('multi-vouchers.index') && request('type') == 'multi_payment' ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3;' : '' }}">
            <i class="las la-coins font-18"></i>{{ __('navigation.multi_payment_voucher') }}
        </a>
    </li>
@endcan

@can('view multi-receipt')
    <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 font-hold fw-bold transition-base {{ request()->routeIs('multi-vouchers.index') && request('type') == 'multi_receipt' ? 'active' : '' }}" 
           href="{{ route('multi-vouchers.index', ['type' => 'multi_receipt']) }}"
           style="{{ request()->routeIs('multi-vouchers.index') && request('type') == 'multi_receipt' ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3;' : '' }}">
            <i class="las la-wallet font-18"></i>{{ __('navigation.multi_receipt_voucher') }}
        </a>
    </li>
@endcan
