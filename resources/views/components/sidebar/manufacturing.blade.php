@can('عرض فاتورة تصنيع')
    {{-- <li class="li-main">
        <a href="javascript: void(0);">
            <i data-feather="grid" class="align-self-center menu-icon"></i>
            <span>{{ __('navigation.manufacturing') }}</span>
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a> --}}
    <ul class="sub-menu mm-collapse" aria-expanded="false">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('manufacturing.create') }}">
                <i class="ti-control-record"></i>{{ __('navigation.manufacturing_invoice') }}
            </a>
        </li>
        {{-- </ul>
    </li> --}}
    @endcan
