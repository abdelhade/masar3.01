<li class="nav-item">
    <a class="nav-link" href="{{ route('mysettings.index') }}">
        <i class="ti-control-record"></i>{{ __('Settings') }}
    </a>
</li>

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
