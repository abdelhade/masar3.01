<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض الادارات و الاقسام')): ?>
    <li class="nav-item">
        <a class="nav-link" href="<?php echo e(route('departments.index')); ?>">
            <i class="ti-control-record"></i><?php echo e(__('navigation.departments')); ?>

        </a>
    </li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض الوظائف')): ?>
    <li class="nav-item">
        <a class="nav-link" href="<?php echo e(route('jobs.index')); ?>">
            <i class="ti-control-record"></i><?php echo e(__('navigation.jobs')); ?>

        </a>
    </li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['عرض الدول', 'عرض المحافظات', 'عرض المدن', 'عرض المناطق'])): ?>
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="ti-control-record"></i><?php echo e(__('navigation.addresses')); ?>

            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض الدول')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('countries.index')); ?>">
                        <i class="ti-control-record"></i><?php echo e(__('navigation.countries')); ?>

                    </a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض المحافظات')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('states.index')); ?>">
                        <i class="ti-control-record"></i><?php echo e(__('navigation.states')); ?>

                    </a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض المدن')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('cities.index')); ?>">
                        <i class="ti-control-record"></i><?php echo e(__('navigation.cities')); ?>

                    </a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض المناطق')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('towns.index')); ?>">
                        <i class="ti-control-record"></i><?php echo e(__('navigation.towns')); ?>

                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض الورديات')): ?>
    <li class="nav-item">
        <a class="nav-link" href="<?php echo e(route('shifts.index')); ?>">
            <i class="ti-control-record"></i><?php echo e(__('navigation.shifts')); ?>

        </a>
    </li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض الموظفيين')): ?>
    <li class="nav-item">
        <a class="nav-link" href="<?php echo e(route('employees.index')); ?>">
            <i class="ti-control-record"></i><?php echo e(__('navigation.employees')); ?>

        </a>
    </li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['عرض المعدلات', 'عرض تقييم الموظفين'])): ?>
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="ti-control-record"></i><?php echo e(__('navigation.performance_kpis')); ?>

            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض المعدلات')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('kpis.index')); ?>">
                        <i class="ti-control-record"></i><?php echo e(__('navigation.kpis')); ?>

                    </a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(abilities: 'عرض معدلات اداء الموظفين')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('kpis.employeeEvaluation')); ?>">
                        <i class="ti-control-record"></i><?php echo e(__('navigation.employee_performance_kpis')); ?>

                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['عرض انواع العقود', 'عرض العقود'])): ?>
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="ti-control-record"></i><?php echo e(__('navigation.contracts')); ?>

            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض انواع العقود')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('contract-types.index')); ?>">
                        <i class="ti-control-record"></i><?php echo e(__('navigation.contract_types')); ?>

                    </a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض العقود')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('contracts.index')); ?>">
                        <i class="ti-control-record"></i><?php echo e(__('navigation.contracts')); ?>

                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['عرض البصمات', 'عرض معالجه الحضور والانصراف'])): ?>
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="ti-control-record"></i><?php echo e(__('navigation.attendance')); ?>

            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض البصمات')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('attendances.index')); ?>">
                        <i class="ti-control-record"></i><?php echo e(__('navigation.attendance_records')); ?>

                    </a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض معالجه الحضور والانصرف')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('attendance.processing')); ?>">
                        <i class="ti-control-record"></i><?php echo e(__('navigation.attendance_processing')); ?>

                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['عرض رصيد الإجازات', 'عرض طلبات الإجازة'])): ?>
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="ti-control-record"></i><?php echo e(__('navigation.leave_management')); ?>

            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض رصيد الإجازات')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('leaves.balances.index')); ?>">
                        <i class="ti-control-record"></i><?php echo e(__('navigation.leave_balances')); ?>

                    </a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض طلبات الإجازة')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('leaves.requests.index')); ?>">
                        <i class="ti-control-record"></i><?php echo e(__('navigation.leave_requests')); ?>

                    </a>

                </li>
            <?php endif; ?>
        </ul>
    </li>
<?php endif; ?>

<li class="nav-item">
    <a class="nav-link font-family-cairo fw-bold" href="<?php echo e(route('cvs.index')); ?>">
        <i class="ti-control-record"></i><?php echo e(__('navigation.cv_management')); ?>

    </a>
</li>
<?php /**PATH D:\Laragon\laragon\www\massar1.02\resources\views/components/sidebar/departments.blade.php ENDPATH**/ ?>