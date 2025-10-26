<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض المدراء')): ?>
    <li class="nav-item">
        <a class="nav-link" href="<?php echo e(route('users.index', ['type' => 31])); ?>">
            <i class="ti-control-record"></i><?php echo e(__('navigation.managers')); ?>

        </a>
    </li>
<?php endif; ?>
<?php /**PATH D:\laragon\www\massar1.02\resources\views/components/sidebar/permissions.blade.php ENDPATH**/ ?>