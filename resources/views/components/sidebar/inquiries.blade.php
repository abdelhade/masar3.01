<li class="li-main">
    <a href="javascript: void(0);">
        <i data-feather="settings" class="align-self-center menu-icon"></i>
        <span>{{ __('Inquiries') }}</span>
        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
    </a>
    <ul class="sub-menu mm-collapse" aria-expanded="false">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('inquiry.sources.index') }}">
                <i class="ti-control-record"></i>{{ __('Inquiries Source') }}
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('work.types.index') }}">
                <i class="ti-control-record"></i>{{ __('Work Types') }}
            </a>
        </li>
    </ul>
</li>
