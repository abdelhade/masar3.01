{{-- @canany([
    'عرض قائمة الخصومات المسموح بها',
    'عرض قائمة الخصومات المكتسبة',
    'عرض خصم مسموح به',
    'عرض خصم
    مكتسب',
    ])
    <li class="li-main">
        <a href="javascript: void(0);">
            <i data-feather="percent" style="color:#f6c23e" class="align-self-center menu-icon"></i>
            <span>{{ __('navigation.discounts') }}</span>
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse" aria-expanded="false"> --}}
            @can('عرض قائمة الخصومات المسموح بها')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('discounts.index', ['type' => 30]) }}">
                        <i class="ti-control-record"></i>{{ __('navigation.allowed_discounts') }}
                    </a>
                </li>
            @endcan
            @can('عرض قائمة الخصومات المكتسبة')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('discounts.index', ['type' => 31]) }}">
                        <i class="ti-control-record"></i>{{ __('navigation.earned_discounts') }}
                    </a>
                </li>
            @endcan
            @can('عرض خصم مسموح به')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('discounts.create', ['type' => 30, 'q' => md5(30)]) }}">
                        <i class="ti-control-record"></i>{{ __('navigation.allowed_discount') }}
                    </a>
                </li>
            @endcan
            @can('عرض خصم مكتسب')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('discounts.create', ['type' => 31, 'q' => md5(31)]) }}">
                        <i class="ti-control-record"></i>{{ __('navigation.earned_discount') }}
                    </a>
                </li>
            @endcan

        {{-- </ul>
    </li>
@endcanany --}}
