@can('view Discounts')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('discounts.general-statistics') }}">
            <i class="ti-control-record"></i>{{ __('Discounts.Statistics') }}
        </a>
    </li>
@endcan
