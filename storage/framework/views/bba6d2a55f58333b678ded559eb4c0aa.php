<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
<?php echo $__env->make('admin.partials.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<body class="">
    
    <?php if (! empty(trim($__env->yieldContent('sidebar')))): ?>
        
        <div class="left-sidenav">
            <div class="menu-content h-100" data-simplebar>
                <ul class="metismenu left-sidenav-menu">
                    
                    <li class="menu-label my-2">
                        <a href="<?php echo e(route('home')); ?>"><?php echo e(config('public_settings.campany_name')); ?></a>
                    </li>

                    <li class="nav-item border-bottom pb-1 mb-2">
                        <a href="<?php echo e(route('admin.dashboard')); ?>"
                            class="nav-link d-flex align-items-center gap-2 font-family-cairo fw-bold">
                            <i data-feather="home" style="color:#4e73df" class="menu-icon"></i>
                            <?php echo e(__('navigation.home')); ?>

                        </a>
                    </li>

                    
                    <?php echo $__env->yieldContent('sidebar'); ?>
                </ul>
            </div>
        </div>
    <?php else: ?>
        
        <?php echo $__env->make('admin.partials.sidebar-default', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <div class="page-wrapper">
        <?php echo $__env->make('admin.partials.topbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="page-content">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="row">
                        <?php echo $__env->make('sweetalert::alert', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php echo $__env->yieldContent('content'); ?>
                    </div>
                </div>
            </div>
            <?php echo $__env->make('admin.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
    <?php echo $__env->make('admin.partials.scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>

</html>
<?php /**PATH D:\Laragon\laragon\www\massar1.02\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>