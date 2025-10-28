<li class="nav-item">
    <a class="nav-link font-family-cairo fw-bold" href="<?php echo e(route('items.statistics')); ?>">
        <i class="ti-control-record"></i><?php echo e(__('Items Statistics')); ?>

    </a>
</li>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض الوحدات')): ?>
    <li class="nav-item">
        <a class="nav-link font-family-cairo fw-bold" href="<?php echo e(route('units.index')); ?>">
            <i class="ti-control-record"></i><?php echo e(__('navigation.units')); ?>

        </a>
    </li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض الأصناف')): ?>
    <li class="nav-item">
        <a class="nav-link font-family-cairo fw-bold" href="<?php echo e(route('items.index')); ?>">
            <i class="ti-control-record"></i><?php echo e(__('navigation.items')); ?>

        </a>
    </li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('عرض الأسعار')): ?>
    <li class="nav-item">
        <a class="nav-link font-family-cairo fw-bold" href="<?php echo e(route('prices.index')); ?>">
            <i class="ti-control-record"></i><?php echo e(__('navigation.prices')); ?>

        </a>
    </li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any([
    'عرض الاماكن',
    'عرض التصنيفات',
    'عرض
    المجموعات',
    ])): ?>
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('item-management.notes.notesNames', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-153311968-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    <li class="nav-item">
        <a class="nav-link font-family-cairo fw-bold" href="<?php echo e(route('varibals.index')); ?>">
            <i class="ti-control-record"></i><?php echo e(__('navigation.varibals')); ?>

        </a>
    </li>
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('item-management.varibals.varibalslinks', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-153311968-1', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
<?php endif; ?>
<?php /**PATH D:\laragon\www\massar1.02\resources\views/components/sidebar/items.blade.php ENDPATH**/ ?>