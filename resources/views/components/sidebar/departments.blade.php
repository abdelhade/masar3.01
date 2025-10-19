@can('عرض الادارات و الاقسام')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('departments.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.departments') }}
        </a>
    </li>
@endcan
@can('عرض الوظائف')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('jobs.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.jobs') }}
        </a>
    </li>
@endcan
@canany(['عرض الدول', 'عرض المحافظات', 'عرض المدن', 'عرض المناطق'])
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="ti-control-record"></i>{{ __('navigation.addresses') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            @can('عرض الدول')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('countries.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.countries') }}
                    </a>
                </li>
            @endcan
            @can('عرض المحافظات')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('states.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.states') }}
                    </a>
                </li>
            @endcan
            @can('عرض المدن')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('cities.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.cities') }}
                    </a>
                </li>
            @endcan
            @can('عرض المناطق')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('towns.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.towns') }}
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcanany
@can('عرض الورديات')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('shifts.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.shifts') }}
        </a>
    </li>
@endcan
@can('عرض الموظفيين')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('employees.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.employees') }}
        </a>
    </li>
@endcan
@canany(['عرض المعدلات', 'عرض تقييم الموظفين'])
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="ti-control-record"></i>{{ __('navigation.performance_kpis') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            @can('عرض المعدلات')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('kpis.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.kpis') }}
                    </a>
                </li>
            @endcan
            @can(abilities: 'عرض معدلات اداء الموظفين')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('kpis.employeeEvaluation') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.employee_performance_kpis') }}
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcanany
@canany(['عرض انواع العقود', 'عرض العقود'])
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="ti-control-record"></i>{{ __('navigation.contracts') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            @can('عرض انواع العقود')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('contract-types.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.contract_types') }}
                    </a>
                </li>
            @endcan
            @can('عرض العقود')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('contracts.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.contracts') }}
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcanany
@canany(['عرض البصمات', 'عرض معالجه الحضور والانصراف'])
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="ti-control-record"></i>{{ __('navigation.attendance') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            @can('عرض البصمات')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('attendances.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.attendance_records') }}
                    </a>
                </li>
            @endcan
            @can('عرض معالجه الحضور والانصرف')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('attendance.processing') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.attendance_processing') }}
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcanany
{{-- إدارة الإجازات --}}
@canany(['عرض رصيد الإجازات', 'عرض طلبات الإجازة'])
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="ti-control-record"></i>{{ __('navigation.leave_management') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            @can('عرض رصيد الإجازات')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('leaves.balances.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.leave_balances') }}
                    </a>
                </li>
            @endcan
            @can('عرض طلبات الإجازة')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('leaves.requests.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.leave_requests') }}
                    </a>

                </li>
            @endcan
        </ul>
    </li>
@endcanany
{{-- CVs --}}
<li class="nav-item">
    <a class="nav-link font-family-cairo fw-bold" href="{{ route('cvs.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.cv_management') }}
    </a>
</li>
