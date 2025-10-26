<div class="topbar">
    <!-- Navbar -->
    <nav class="navbar-custom">
        <ul class="list-unstyled topbar-nav float-end mb-0">

            <?php if (isset($component)) { $__componentOriginal8dcd08ee89e31d0e441e307d36a8fb36 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8dcd08ee89e31d0e441e307d36a8fb36 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'notifications::components.notifications','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('notifications::notifications'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8dcd08ee89e31d0e441e307d36a8fb36)): ?>
<?php $attributes = $__attributesOriginal8dcd08ee89e31d0e441e307d36a8fb36; ?>
<?php unset($__attributesOriginal8dcd08ee89e31d0e441e307d36a8fb36); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8dcd08ee89e31d0e441e307d36a8fb36)): ?>
<?php $component = $__componentOriginal8dcd08ee89e31d0e441e307d36a8fb36; ?>
<?php unset($__componentOriginal8dcd08ee89e31d0e441e307d36a8fb36); ?>
<?php endif; ?>

            <!-- مبدل اللغة -->
            <li class="me-3">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('language-switcher');

$__html = app('livewire')->mount($__name, $__params, 'lw-3102573402-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            </li>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض التحكم في الاعدادات')): ?>
                <li>
                    <a title="<?php echo e(__('navigation.users')); ?>" href="<?php echo e(route('mysettings.index')); ?>" class="nav-link">
                        <i data-feather="settings" class="text-primary fa-3x"></i>
                    </a>
                </li>
            <?php endif; ?>
            <li>
                <form method="POST" action="<?php echo e(route('logout')); ?>" style="display: inline;">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-lg" title="<?php echo e(__('navigation.logout')); ?>"
                        style="background: none; border: none; ">
                        <i class="fas fa-sign-out-alt fa-3x text-primary"></i>
                    </button>
                </form>
            </li>
        </ul><!--end topbar-nav-->

        <ul class="list-unstyled topbar-nav mb-0">

            <li>
                <button class="nav-link button-menu-mobile">
                    <i data-feather="menu" class="align-self-center topbar-icon fa-2x text-primary"></i>
                </button>
            </li>
            <li>
                <a title="help" href="https://www.updates.elhadeerp.com" class="nav-link" target="_blank">
                    <i class="fas fa-book fa-2x text-primary"></i>
                </a>
            </li>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض المدراء')): ?>
                <li>
                    <a title="<?php echo e(__('navigation.users')); ?>" href="<?php echo e(route('users.index')); ?>" class="nav-link">
                        <i class="fas fa-user fa-2x text-primary"></i>
                    </a>
                </li>
            <?php endif; ?>


            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض التقارير')): ?>
                <li>
                    <a title="<?php echo e(__('navigation.reports')); ?>" href="<?php echo e(route('reports.index')); ?>" class="nav-link">
                        <i class="fas fa-chart-pie fa-2x text-primary"></i>
                    </a>

                </li>
            <?php endif; ?>

            <li>
                <a title="<?php echo e(__('Branches')); ?>" href="<?php echo e(route('branches.index')); ?>" class="nav-link">
                    <i class="fas fa-store fa-2x text-primary"></i>
                </a>

            </li>
        </ul>
    </nav>
    <!-- end navbar-->
</div>
<?php /**PATH D:\laragon\www\massar1.02\resources\views/admin/partials/topbar.blade.php ENDPATH**/ ?>