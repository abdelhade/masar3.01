 <!-- jQuery  -->
  <!-- ضع هذا العنصر في أي مكان في الصفحة (يفضل قبل نهاية الـ body) -->
<audio id="submit-sound" src="<?php echo e(asset('assets/wav/paper_sound.wav')); ?>"></audio>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // استهدف جميع النماذج في الصفحة
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function(event) {
            // شغل الصوت
            var audio = document.getElementById('submit-sound');
            if (audio) {
                audio.currentTime = 0; // إعادة الصوت للبداية
                audio.play();
            }
            // يمكنك إزالة السطر التالي إذا كنت لا تريد منع الإرسال الفعلي للنموذج
            // event.preventDefault();
        });
    });
});
</script>


 <script src="<?php echo e(asset('assets/js/jquery.min.js')); ?>"></script>
 <script src="<?php echo e(asset('assets/js/bootstrap.bundle.min.js')); ?>"></script>
 <script src="<?php echo e(asset('assets/js/metismenu.min.js')); ?>"></script>
 <script src="<?php echo e(asset('assets/js/waves.js')); ?>"></script>
 <script src="<?php echo e(asset('assets/js/feather.min.js')); ?>"></script>
 <script src="<?php echo e(asset('assets/js/simplebar.min.js')); ?>"></script>
 <script src="<?php echo e(asset('assets/js/moment.js')); ?>"></script>
 <script src="<?php echo e(asset('assets/plugins/daterangepicker/daterangepicker.js')); ?>"></script>
 <script src="<?php echo e(asset('assets/plugins/apex-charts/apexcharts.min.js')); ?>"></script>
 <script src="<?php echo e(asset('assets/plugins/jvectormap/jquery-jvectormap-2.0.2.min.js')); ?>"></script>
 <script src="<?php echo e(asset('assets/plugins/jvectormap/jquery-jvectormap-us-aea-en.js')); ?>"></script>
 <script src="<?php echo e(asset('assets/pages/jquery.analytics_dashboard.init.js')); ?>"></script>
 <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

 <!-- Select2 Language Support -->
 <?php if(app()->getLocale() === 'ar'): ?>
     <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/ar.js"></script>
 <?php elseif(app()->getLocale() === 'tr'): ?>
     <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/tr.js"></script>
 <?php else: ?>
     <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/en.js"></script>
 <?php endif; ?>

 <!-- Tom Select JS -->
 <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

 <!-- App js -->
 <script src="<?php echo e(asset('assets/js/app.js')); ?>"></script>
 <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

 <!-- Livewire Scripts -->
 <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>


 <?php echo $__env->yieldPushContent('scripts'); ?>
<?php /**PATH D:\Laragon\laragon\www\massar1.02\resources\views/admin/partials/scripts.blade.php ENDPATH**/ ?>