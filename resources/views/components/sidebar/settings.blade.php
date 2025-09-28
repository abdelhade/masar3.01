{{-- <li class="li-main">
    <a href="javascript: void(0);">
        <i data-feather="settings" class="align-self-center menu-icon"></i>
        <span>{{ __('navigation.settings') }}</span>
        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
    </a>
    <ul class="sub-menu mm-collapse" aria-expanded="false"> --}}
<li class="nav-item">
    <a class="nav-link" href="{{ route('barcode.print.settings.edit') }}">
        <i class="ti-control-record"></i>{{ __('navigation.barcode_settings') }}
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ route('export-settings') }}">
        <i class="ti-control-record"></i>{{ __('navigation.data_backup') }}
    </a>
</li>
{{-- </ul>
</li> --}}
