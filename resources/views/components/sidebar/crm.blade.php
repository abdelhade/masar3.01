{{-- @canany(['عرض العملااء', 'عرض مصدر الفرص', 'عرض جهات اتصال الشركات', 'عرض حالات الفرص', 'عرض الفرص'])
    <li class="li-main">
        <a href="javascript: void(0);">
            <i data-feather="grid" class="align-self-center menu-icon"></i>
            <span>{{ __('navigation.crm') }}</span>
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse" aria-expanded="false"> --}}

<li class="nav-item">
    <a class="nav-link" href="{{ route('statistics.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.statistics') }}
    </a>
</li>
@can('عرض العملااء')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('clients.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.clients') }}
        </a>
    </li>
@endcan
@can('عرض مصدر الفرص')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('chance-sources.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.chance_sources') }}
        </a>
    </li>
@endcan
@can('عرض جهات اتصال الشركات')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('client-contacts.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.client_contacts') }}
        </a>
    </li>
@endcan
@can('عرض حالات الفرص')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('lead-status.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.lead_statuses') }}
        </a>
    </li>
@endcan
@can('عرض الفرص')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('leads.board') }}">
            <i class="ti-control-record"></i>{{ __('navigation.leads') }}

        </a>
    </li>
@endcan

@can('عرض الفرص')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('tasks.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.tasks') }}

        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('tasks.types.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.task_types') }}

        </a>
    </li>
@endcan
<li class="nav-item">
    <a class="nav-link" href="{{ route('activities.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.activities') }}
    </a>
</li>
{{-- </ul>
    </li>
@endcanany --}}
