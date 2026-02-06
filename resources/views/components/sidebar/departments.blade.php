{{-- قسم إدارة الأقسام --}}
@can('view Departments')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('departments.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.departments') }}
        </a>
    </li>
@endcan

{{-- قسم إدارة الوظائف --}}
@can('view Jobs')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('jobs.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.jobs') }}
        </a>
    </li>
@endcan

{{-- قسم إدارة العناوين (الدول، المحافظات، المدن، الأحياء) --}}
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

{{-- قسم إدارة الورديات --}}
@can('view Shifts')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('shifts.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.shifts') }}
        </a>
    </li>
@endcan

{{-- قسم إدارة الموظفين --}}
@can('view Employees')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('employees.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.employees') }}
        </a>
    </li>
@endcan

{{-- قسم مؤشرات الأداء وتقييم الموظفين --}}
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

{{-- قسم إدارة الحضور والانصراف --}}
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
            {{-- Mobile Fingerprint Login --}}
            @can('view Mobile-fingerprint')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('mobile.employee-login') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.mobile_fingerprint_login') }}
                    </a>
                </li>
            @endcan
        </ul>

    </li>
@endcanany

{{-- قسم إدارة الرواتب والسلف والخصومات والمكافآت --}}
<li class="nav-item has-submenu">
    <a class="nav-link" href="javascript: void(0);">
        <i class="ti-control-record"></i>{{ __('navigation.employees_payroll') }}
        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
    </a>
    <ul class="sub-menu mm-collapse">
        @can('view Attendance Processing')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('hr.attendance.processing') }}">
                    <i class="ti-control-record"></i>{{ __('navigation.attendance_processing') }}
                </a>
            </li>
        @endcan
        <li class="nav-item">
            <a class="nav-link" href="{{ route('hr.flexible-salary.processing.index') }}">
                <i class="ti-control-record"></i>{{ __('navigation.flexible_salary_processing') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('hr.employee-advances.index') }}">
                <i class="ti-control-record"></i>{{ __('navigation.employee_advances') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('hr.employee-deductions-rewards.index') }}">
                <i class="ti-control-record"></i>{{ __('navigation.employee_deductions_rewards') }}
            </a>
        </li>
    </ul>
</li>

{{-- قسم إدارة الإجازات --}}
@canany(['view Leave Types', 'view Leave Balances', 'view Leave Requests', 'create Leave Balances', 'edit Leave
    Balances', 'create Leave Requests', 'edit Leave Requests'])
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="ti-control-record"></i>{{ __('navigation.leave_management') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            @can('view Leave Types')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('hr.leaves.types.manage') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.leave_types') }}
                    </a>
                </li>
            @endcan
            <!-- @can('view Leave Balances')
        <li class="nav-item">
                            <a class="nav-link" href="{{ route('hr.leaves.balances.index') }}">
                                <i class="ti-control-record"></i>{{ __('navigation.leave_balances') }}
                            </a>
                        </li>
    @endcan -->
            <!-- @can('create Leave Balances')
        <li class="nav-item">
                            <a class="nav-link" href="{{ route('hr.leaves.balances.create') }}">
                                <i class="ti-control-record"></i>{{ __('navigation.create_leave_balance') }}
                            </a>
                        </li>
    @endcan -->
            @can('view Leave Requests')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('hr.leaves.requests.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.leave_requests') }}
                    </a>
                </li>
            @endcan
            @can('create Leave Requests')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('hr.leaves.requests.create') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.create_leave_request') }}
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcanany

{{-- قسم إدارة التوظيف --}}
@canany(['view Contract Types', 'view CVs', 'view Contracts', 'view Job Postings', 'view Interviews', 'view
    Onboardings', 'view Terminations'])
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="ti-control-record"></i>{{ __('recruitment.recruitment_management') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('recruitment.dashboard') }}">
                    <i class="ti-control-record"></i>{{ __('recruitment.recruitment_dashboard') }}
                </a>
            </li>
            @can('view Contract Types')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('recruitment.contract-types.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.contract_types') }}
                    </a>
                </li>
            @endcan
            @can('view Job Postings')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('recruitment.job-postings.index') }}">
                        <i class="ti-control-record"></i>{{ __('recruitment.job_postings') }}
                    </a>
                </li>
            @endcan
            @can('view CVs')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('recruitment.cvs.index') }}">
                        <i class="ti-control-record"></i>{{ __('recruitment.cvs') }}
                    </a>
                </li>
            @endcan
            @can('view Interviews')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('recruitment.interviews.index') }}">
                        <i class="ti-control-record"></i>{{ __('recruitment.interviews') }}
                    </a>
                </li>
            @endcan
            @can('view Interviews')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('recruitment.interviews.calendar') }}">
                        <i class="ti-control-record"></i>{{ __('recruitment.interview_calendar') }}
                    </a>
                </li>
            @endcan
            @can('view Contracts')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('recruitment.contracts.index') }}">
                        <i class="ti-control-record"></i>{{ __('recruitment.contracts') }}
                    </a>
                </li>
            @endcan
            @can('view Onboardings')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('recruitment.onboardings.index') }}">
                        <i class="ti-control-record"></i>{{ __('recruitment.onboardings') }}
                    </a>
                </li>
            @endcan
            @can('view Terminations')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('recruitment.terminations.index') }}">
                        <i class="ti-control-record"></i>{{ __('recruitment.terminations') }}
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcanany

{{-- قسم إدارة العهد --}}
@can('view Covenants')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('covenants.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.covenants') }}
        </a>
    </li>
@endcan

{{-- قسم إدارة مأموريات العمل --}}
@can('view Errands')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('errands.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.errands') }}
        </a>
    </li>
@endcan

{{-- قسم إدارة أذونات العمل --}}
@can('view Work Permissions')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('work-permissions.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.work_permissions') }}
        </a>
    </li>
@endcan

{{-- قسم إعدادات الموارد البشرية --}}
@canany(['view HR Settings', 'edit HR Settings'])
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="ti-control-record"></i>{{ __('navigation.hr_settings') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            @can('view HR Settings')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('hr.settings.index') }}">
                        <i class="ti-control-record"></i>{{ __('navigation.view_hr_settings') }}
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcanany

{{-- route name:  hr.attendance.reports.project --}}

<li class="nav-item">
    <a class="nav-link" href="{{ route('hr.attendance.reports.project') }}">
        <i class="ti-control-record"></i>{{ __('hr.project_attendance_report') }}
    </a>
</li>
