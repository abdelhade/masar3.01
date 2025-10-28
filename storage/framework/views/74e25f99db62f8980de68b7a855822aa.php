<div class="row">
    <div class="col-sm-12">
                        <?php $__currentLoopData = $items ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(isset($item['url'])): ?>
                                 /   <a href="<?php echo e($item['url']); ?>"><?php echo e($item['label']); ?></a>
                                <?php else: ?>
                                   / <?php echo e($item['label']); ?>

                                <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
<?php /**PATH D:\Laragon\laragon\www\massar1.02\resources\views/components/breadcrumb.blade.php ENDPATH**/ ?>