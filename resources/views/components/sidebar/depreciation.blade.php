@can('view asset-depreciation')
    <li class="li-main">
        <a href="javascript: void(0);" class="has-arrow waves-effect waves-dark">
            <i data-feather="calculator" style="color:#28a745" class="align-self-center menu-icon"></i>
            <span>{{ __('Asset Depreciation Management') }}</span>
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse" aria-expanded="false">
            @can('view depreciation-management')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('depreciation.index') }}">
                        <i class="ti-control-record"></i>{{ __('Depreciation Management') }}
                    </a>
                </li>
            @endcan

            @can('view Depreciation Schedules')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('depreciation.schedule') }}">
                        <i class="ti-control-record"></i>{{ __('Depreciation Schedule') }}
                    </a>
                </li>
            @endcan

            @can('view Depreciation Schedules')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('depreciation.report') }}">
                        <i class="ti-control-record"></i>{{ __('Depreciation Report') }}
                    </a>
                </li>
            @endcan

            @can('create depreciation')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'depreciation']) }}">
                        <i class="ti-control-record"></i>{{ __('Depreciation Entry') }}
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcan
