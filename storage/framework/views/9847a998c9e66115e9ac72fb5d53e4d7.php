<?php

use Livewire\Volt\Component;
use App\Models\Varibal;

?>

<div>
    <!--  -->
    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $varibals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $varibalId => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <li class="nav-item">
                <a class="nav-link font-family-cairo fw-bold" href="<?php echo e(route('varibalValues.index', $varibalId)); ?>">
                    <i class="ti-control-record"></i><?php echo e($name); ?>

                </a>
            </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

</div><?php /**PATH D:\laragon\www\massar1.02\resources\views\livewire/item-management/varibals/varibalslinks.blade.php ENDPATH**/ ?>