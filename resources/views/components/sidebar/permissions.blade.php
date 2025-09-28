{{-- @canany(['عرض الادوار', 'عرض المدراء'])
    <li class="li-main">
        <a href="javascript: void(0);">
            <i data-feather="grid" class="align-self-center menu-icon"></i>
            <span>{{ __('navigation.permissions') }}</span>
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse" aria-expanded="false"> --}}
{{-- @can('عرض الادوار')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('roles.index', ['type' => 30]) }}">
                                    <i class="ti-control-record"></i>{{ __('navigation.roles') }}
                                </a>
                            </li>
                        @endcan --}}
@can('عرض المدراء')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('users.index', ['type' => 31]) }}">
            <i class="ti-control-record"></i>{{ __('navigation.managers') }}
        </a>
    </li>
@endcan
{{-- </ul>
    </li>
@endcanany --}}
