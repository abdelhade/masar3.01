{{-- @can('عرض المشاريع')
    <li class="li-main">
        <a href="javascript: void(0);">
            <i data-feather="clipboard" style="color:#6610f2" class="align-self-center menu-icon"></i>
            <span>{{ __('navigation.projects') }}</span>
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse" aria-expanded="false"> --}}
<li class="nav-item">
    <a class="nav-link font-family-cairo fw-bold" href="{{ route('projects.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.projects') }}
    </a>
</li>
<!-- rent -->
<li class="nav-item">
    <a class="nav-link font-family-cairo fw-bold" href="{{ route('rentals.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.rentals') }}
    </a>
</li>
<!-- rent -->
{{-- </ul>
    </li>
@endcan --}}
