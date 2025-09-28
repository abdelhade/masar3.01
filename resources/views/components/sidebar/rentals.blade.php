{{-- <li class="li-main">
    <a href="javascript: void(0);">
        <i data-feather="settings" class="align-self-center menu-icon"></i>
        <span>{{ __('navigation.rent_management') }}</span>
        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
    </a>
    <ul class="sub-menu mm-collapse" aria-expanded="false"> --}}
<li class="nav-item">
    <a class="nav-link" href="{{ route('rentals.buildings.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.rent_building') }}
    </a>
</li>


<li class="nav-item">

    <a class="nav-link" href="{{ route('rentals.leases.index') }}">

        <i class="ti-control-record"></i>{{ __('navigation.rent_contracts') }}
    </a>
</li>
{{-- </ul>
</li> --}}
