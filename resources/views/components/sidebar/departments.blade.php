@can('view Departments')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('departments.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.departments') }}
        </a>
    </li>
@endcan
@can('view Jobs')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('jobs.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.jobs') }}
        </a>
    </li>
@endcan
@canany(['view Countries', 'view States', 'view Cities', 'view Towns'])
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="ti-control-record"></i>{{ __('navigation.addresses') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            @can('view Countries')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('countries.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.countries') }}
                    </a>
                </li>
            @endcan
            @can('view States')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('states.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.states') }}
                    </a>
                </li>
            @endcan
            @can('view Cities')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('cities.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.cities') }}
                    </a>
                </li>
            @endcan
            @can('view Towns')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('towns.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.towns') }}
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcanany
@can('view Shifts')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('shifts.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.shifts') }}
        </a>
    </li>
@endcan
    @can('view Employees')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('employees.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.employees') }}
        </a>
    </li>
@endcan
@canany(['view KPIs', 'view Employee Evaluations'])
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="ti-control-record"></i>{{ __('navigation.performance_kpis') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            @can('view KPIs')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('kpis.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.kpis') }}
                    </a>
                </li>
            @endcan
            @can('view Employee Evaluations')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('kpis.employeeEvaluation') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.employee_performance_kpis') }}
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcanany
@canany(['view Contract Types', 'view Contracts'])
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="ti-control-record"></i>{{ __('navigation.contracts') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            @can('view Contract Types')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('contract-types.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.contract_types') }}
                    </a>
                </li>
            @endcan
            @can('view Contracts')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('contracts.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.contracts') }}
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcanany
@canany(['view Attendances', 'view Attendance Processing'])
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="ti-control-record"></i>{{ __('navigation.attendance') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            @can('view Attendances')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('attendances.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.attendance_records') }}
                    </a>
                </li>
            @endcan
            @can('view Attendance Processing')
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
@canany(['view Leave Balances', 'view Leave Requests'])
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="ti-control-record"></i>{{ __('navigation.leave_management') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('leaves.types.manage') }}">
                    <i class="ti-control-record"></i>{{ __('navigation.leave_types') }}
                </a>
            </li>
            @can('view Leave Balances')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('leaves.balances.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.leave_balances') }}
                    </a>
                </li>
            @endcan
            @can('view Leave Requests')
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
@can('view CVs')
<li class="nav-item">
    <a class="nav-link font-family-cairo fw-bold" href="{{ route('cvs.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.cv_management') }}
        </a>
    </li>
@endcan
