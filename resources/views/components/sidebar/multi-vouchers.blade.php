{{-- @canany([
    'عرض اهلاك الاصل',
    'عرض بيع الاصول',
    'عرض شراء اصل',
    'عرض زيادة في قيمة الاصل',
    'عرض نقص في قيمة
    الاصل',
])
    <li class="li-main">
        <a href="javascript: void(0);">
            <i data-feather="hard-drive" style="color:#e83e8c" class="align-self-center menu-icon"></i>
            <span>{{ __('navigation.asset_operations') }}</span>
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse" aria-expanded="false"> --}}
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
{{-- </ul>
    </li>
@endcanany --}}
