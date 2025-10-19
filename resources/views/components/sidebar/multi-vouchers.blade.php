<li class="nav-item">
    <a class="nav-link" href="{{ route('multi-vouchers.statistics') }}">
        <i class="ti-control-record"></i>{{ __('Multi vouchers Statistics') }}
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ route('depreciation.index') }}">
        <i class="ti-control-record"></i>قائمة الاصول
    </a>
</li>
@can('عرض اهلاك الاصل')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'depreciation']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.depreciation') }}
        </a>
    </li>
@endcan
@can('عرض بيع الاصول')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'sell_asset']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.sell_asset') }}
        </a>
    </li>
@endcan
@can('عرض شراء اصل')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'buy_asset']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.buy_asset') }}
        </a>
    </li>
@endcan
@can('عرض زيادة في قيمة الاصل')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'increase_asset_value']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.increase_asset_value') }}
        </a>
    </li>
@endcan
@can('عرض نقص في قيمة الاصل')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'decrease_asset_value']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.decrease_asset_value') }}
        </a>
    </li>
@endcan
