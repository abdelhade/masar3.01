@can('عرض قائمة الخصومات المسموح بها')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('discounts.general-statistics') }}">
            <i class="ti-control-record"></i>{{ __('Discounts.Statistics') }}
        </a>
    </li>
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
