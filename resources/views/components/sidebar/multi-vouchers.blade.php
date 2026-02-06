@can('view depreciation')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('depreciation.index') }}">
            <i class="ti-control-record"></i>{{ __('Assets List') }}
        </a>
    </li>
@endcan

@can('view depreciation')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'depreciation']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.depreciation') }}
        </a>
    </li>
@endcan

@can('view sell-asset')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'sell_asset']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.sell_asset') }}
        </a>
    </li>
@endcan

@can('view buy-asset')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'buy_asset']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.buy_asset') }}
        </a>
    </li>
@endcan

@can('view increase-asset-value')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'increase_asset_value']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.increase_asset_value') }}
        </a>
    </li>
@endcan

@can('view decrease-asset-value')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'decrease_asset_value']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.decrease_asset_value') }}
        </a>
    </li>
@endcan
