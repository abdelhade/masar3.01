@can('عرض المدراء')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('users.index', ['type' => 31]) }}">
            <i class="ti-control-record"></i>{{ __('navigation.managers') }}
        </a>
    </li>
@endcan
