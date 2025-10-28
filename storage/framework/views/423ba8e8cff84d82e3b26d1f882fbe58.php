<?php

use Livewire\Volt\Component;
use App\Models\Note;

?>

<div>
    <!--  -->
    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $notes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $noteId => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $permission = 'عرض ' . $name;
        ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check($permission)): ?>
            <li class="nav-item">
                <a class="nav-link font-family-cairo fw-bold" href="<?php echo e(route('notes.noteDetails', $noteId)); ?>">
                    <i class="ti-control-record"></i><?php echo e($name); ?>

                </a>
            </li>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

</div><?php /**PATH D:\laragon\www\massar1.02\resources\views\livewire/item-management/notes/notesNames.blade.php ENDPATH**/ ?>