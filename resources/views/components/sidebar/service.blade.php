{{-- <li class="li-main">
    <a href="javascript: void(0);">
        <i data-feather="settings" class="align-self-center menu-icon"></i>
        <span>{{ __('navigation.maintenance') }}</span>
        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
    </a>
    <ul class="sub-menu mm-collapse" aria-expanded="false"> --}}
<li class="nav-item">

    <a class="nav-link" href="{{ route('service.types.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.service_types') }}
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ route('maintenances.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.maintenances') }}
    </a>
</li>

{{-- </ul>
</li> --}}
