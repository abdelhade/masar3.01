{{-- <li class="li-main">
    <a href="javascript: void(0);">
        <i data-feather="truck" class="align-self-center menu-icon"></i>
        <span>{{ __('navigation.shipping_management') }}</span>
        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
    </a>
    <ul class="sub-menu mm-collapse" aria-expanded="false"> --}}
<li class="nav-item">
    <a class="nav-link" href="{{ route('companies.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.shipping_companies') }}
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ route('drivers.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.drivers') }}
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ route('orders.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.orders') }}
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ route('shipments.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.shipments') }}
    </a>
</li>

{{-- </ul>
</li> --}}
