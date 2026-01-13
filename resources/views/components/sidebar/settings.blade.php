@can('view settings')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('mysettings.index') }}">
            <i class="ti-control-record"></i>{{ __('Settings') }}
        </a>
    </li>
@endcan

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

{{-- @can('view currencies') --}}
<li class="nav-item">
    <a class="nav-link" href="{{ route('currencies.index') }}">
        <i class="ti-control-record"></i>{{ __('Currencies') }}
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ route('settings.currency-exchange.index') }}">
        <i class="ti-control-record"></i>{{ __('Currencies Exchange') }}
    </a>
</li>
{{-- @endcan --}}
