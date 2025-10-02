@can('عرض اهلاك الاصل')
    <li class="li-main">
        <a href="javascript: void(0);" class="has-arrow waves-effect waves-dark">
            <i data-feather="calculator" style="color:#28a745" class="align-self-center menu-icon"></i>
            <span>{{ __('إدارة إهلاك الأصول') }}</span>
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse" aria-expanded="false">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('depreciation.index') }}">
                    <i class="ti-control-record"></i>{{ __('إدارة الإهلاك') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('depreciation.report') }}">
                    <i class="ti-control-record"></i>{{ __('تقرير الإهلاك') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('multi-vouchers.create', ['type' => 'depreciation']) }}">
                    <i class="ti-control-record"></i>{{ __('قيد إهلاك') }}
                </a>
            </li>
        </ul>
    </li>
@endcan