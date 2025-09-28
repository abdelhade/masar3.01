{{-- <li class="li-main">
    <a href="javascript: void(0);">
        <i data-feather="truck" class="align-self-center menu-icon"></i>
        <span>{{ __('navigation.daily_progress') }}</span>
        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
    </a>
    <ul class="sub-menu mm-collapse" aria-expanded="false"> --}}
<li class="nav-item">
    <a class="nav-link" href="{{ route('project.types.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.project_types') }}
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ route('clients.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.clients') }}
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ route('employees.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.employees') }}
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ route('work.items.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.work_items') }}
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ route('project.template.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.project_template') }}
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ route('progress.projcet.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.projects') }}
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ route('daily.progress.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.daily_progress') }}
    </a>
</li>

{{-- </ul>
</li> --}}
