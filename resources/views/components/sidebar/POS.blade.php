<li class="li-main">
    <a href="javascript: void(0);">
        <i data-feather="shopping-cart" class="align-self-center menu-icon"></i>
        <span>{{ __('POS') }}</span>
        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
    </a>
    <ul class="sub-menu mm-collapse" aria-expanded="false">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('pos.index') }}">
                <i class="ti-control-record"></i>{{ __('navigation.point_of_sale') }}
            </a>
        </li>
    </ul>
</li>
