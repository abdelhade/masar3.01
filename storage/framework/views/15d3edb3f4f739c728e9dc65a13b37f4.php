
<div class="container-fluid" style="direction: rtl;">
    <!--[if BLOCK]><![endif]--><?php if($viewEmployee): ?>
        <div class="row">
            <!-- بيانات شخصية -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white py-2">
                        <h6 class="card-title mb-0 fw-bold font-family-cairo">
                            <i class="fas fa-user me-2"></i><?php echo e(__('بيانات شخصية')); ?>

                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('الاسم')); ?>:</label>
                                <p class="form-control-plaintext"><?php echo e($viewEmployee->name); ?></p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('البريد الإلكتروني')); ?>:</label>
                                <p class="form-control-plaintext"><?php echo e($viewEmployee->email); ?></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('رقم الهاتف')); ?>:</label>
                                <p class="form-control-plaintext"><?php echo e($viewEmployee->phone); ?></p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('النوع')); ?>:</label>
                                <p class="form-control-plaintext">
                                    <?php echo e($viewEmployee->gender == 'male' ? __('ذكر') : ($viewEmployee->gender == 'female' ? __('أنثى') : __('غير محدد'))); ?>

                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('تاريخ الميلاد')); ?>:</label>
                                <p class="form-control-plaintext">
                                    <?php echo e($viewEmployee->date_of_birth ? $viewEmployee->date_of_birth->format('Y-m-d') : __('غير محدد')); ?>

                                </p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('الحالة')); ?>:</label>
                                <p class="form-control-plaintext">
                                    <span
                                        class="badge <?php echo e($viewEmployee->status == 'مفعل' ? 'bg-success' : 'bg-danger'); ?>">
                                        <?php echo e($viewEmployee->status); ?>

                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('رقم الهوية')); ?>:</label>
                                <p class="form-control-plaintext"><?php echo e($viewEmployee->nationalId ?? __('غير محدد')); ?></p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('الحالة الاجتماعية')); ?>:</label>
                                <p class="form-control-plaintext"><?php echo e($viewEmployee->marital_status ?? __('غير محدد')); ?>

                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('مستوى التعليم')); ?>:</label>
                                <p class="form-control-plaintext"><?php echo e($viewEmployee->education ?? __('غير محدد')); ?></p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('المستوى الوظيفي')); ?>:</label>
                                <p class="form-control-plaintext"><?php echo e($viewEmployee->job_level ?? __('غير محدد')); ?></p>
                            </div>
                        </div>
                        <!--[if BLOCK]><![endif]--><?php if($viewEmployee->information): ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('معلومات إضافية')); ?>:</label>
                                <p class="form-control-plaintext"><?php echo e($viewEmployee->information); ?></p>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    </div>
                </div>
            </div>

            <!-- بيانات تفصيلية (الموقع) -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info text-white py-2">
                        <h6 class="card-title mb-0 fw-bold font-family-cairo">
                            <i class="fas fa-map-marker-alt me-2"></i><?php echo e(__('بيانات الموقع')); ?>

                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('البلد')); ?>:</label>
                                <p class="form-control-plaintext">
                                    <?php echo e($viewEmployee->country?->title ?? __('غير محدد')); ?>

                                </p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('المحافظة')); ?>:</label>
                                <p class="form-control-plaintext">
                                    <?php echo e($viewEmployee->state?->title ?? __('غير محدد')); ?>

                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('المدينة')); ?>:</label>
                                <p class="form-control-plaintext">
                                    <?php echo e($viewEmployee->city?->title ?? __('غير محدد')); ?>

                                </p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('المنطقة')); ?>:</label>
                                <p class="form-control-plaintext">
                                    <?php echo e($viewEmployee->town?->title ?? __('غير محدد')); ?>

                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                
                <div class="card border-0 shadow-sm mb-3 mt-3 text-center">
                    <label class="form-label fw-bold text-dark"><?php echo e(__('صورة الموظف')); ?>:</label>
                    <div class="mt-2">
                        <?php
                            $employeeImage = $viewEmployee->image_url;
                            $hasImage = $viewEmployee->hasMedia('employee_images') && $employeeImage;
                        ?>
                        <!--[if BLOCK]><![endif]--><?php if($hasImage): ?>
                            <div id="employee-image-container" class="d-inline-block" style="position: relative;">
                                
                                <!-- Loading indicator -->
                                <div id="image-loading" style="display: block; text-align: center; padding: 20px;">
                                    <div class="spinner-border text-primary rounded-circle" role="status" style="width: 200px; height: 200px;">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">جاري تحميل الصورة...</p>
                                </div>
                                <img id="employee-image" 
                                     src="<?php echo e($employeeImage); ?>" 
                                     alt="<?php echo e($viewEmployee->name); ?>" 
                                     class="rounded-circle border border-3 border-light shadow" 
                                     style="width: 200px; height: 200px; object-fit: cover; display: none;"
                                     onload="
                                        this.style.display = 'block';
                                        document.getElementById('image-loading').style.display = 'none';
                                        document.getElementById('image-placeholder').style.display = 'none';
                                        clearTimeout(window.imageLoadTimeout);
                                     "
                                     onerror="
                                        this.style.display = 'none';
                                        document.getElementById('image-loading').style.display = 'none';
                                        document.getElementById('image-placeholder').style.display = 'block';
                                        clearTimeout(window.imageLoadTimeout);
                                     ">
                                
                                <!-- Placeholder (shown on error) -->
                                <div id="image-placeholder" style="display: none; text-align: center;">
                                    <img src="<?php echo e(asset('assets/images/avatar-placeholder.svg')); ?>" 
                                         alt="<?php echo e($viewEmployee->name); ?>" 
                                         class="rounded-circle border border-3 border-light shadow" 
                                         style="width: 200px; height: 200px; object-fit: cover;">
                                </div>
                            </div>
                            
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-check-circle text-success me-1"></i>
                                    <?php echo e(__('صورة محفوظة')); ?>

                                </small>
                            </div>
                            
                            <script>
                                // Set timeout for image loading (5 seconds)
                                window.imageLoadTimeout = setTimeout(function() {
                                    const loading = document.getElementById('image-loading');
                                    const placeholder = document.getElementById('image-placeholder');
                                    const image = document.getElementById('employee-image');
                                    
                                    if (loading && placeholder && image) {
                                        loading.style.display = 'none';
                                        placeholder.style.display = 'block';
                                        image.style.display = 'none';
                                    }
                                }, 5000);
                            </script>
                        <?php else: ?>
                            <img src="<?php echo e(asset('assets/images/avatar-placeholder.svg')); ?>" 
                                 alt="<?php echo e($viewEmployee->name); ?>" 
                                 class="img-thumbnail rounded-circle border border-3 border-light shadow" 
                                 style="max-width: 200px; max-height: 200px; object-fit: cover;">
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-image me-1"></i>
                                    <?php echo e(__('لا توجد صورة محفوظة')); ?>

                                </small>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- بيانات وظيفة -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-warning text-white py-2">
                        <h6 class="card-title mb-0 fw-bold font-family-cairo">
                            <i class="fas fa-briefcase me-2"></i><?php echo e(__('بيانات الوظيفة')); ?>

                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('الوظيفة')); ?>:</label>
                                <p class="form-control-plaintext">
                                    <?php echo e($viewEmployee->job?->title ?? __('غير محدد')); ?>

                                </p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('القسم')); ?>:</label>
                                <p class="form-control-plaintext">
                                    <?php echo e($viewEmployee->department?->title ?? __('غير محدد')); ?>

                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('الراتب')); ?>:</label>
                                <p class="form-control-plaintext">
                                    <?php echo e($viewEmployee->salary ? number_format($viewEmployee->salary, 2) . ' ر.س' : __('غير محدد')); ?>

                                </p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('نوع الاستحقاق')); ?>:</label>
                                <p class="form-control-plaintext">
                                    <?php echo e($viewEmployee->salary_type ?? __('غير محدد')); ?>

                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('تاريخ التوظيف')); ?>:</label>
                                <p class="form-control-plaintext">
                                    <?php echo e($viewEmployee->date_of_hire ? $viewEmployee->date_of_hire->format('Y-m-d') : __('غير محدد')); ?>

                                </p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('تاريخ الانتهاء')); ?>:</label>
                                <p class="form-control-plaintext">
                                    <?php echo e($viewEmployee->date_of_fire ? $viewEmployee->date_of_fire->format('Y-m-d') : __('غير محدد')); ?>

                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- بيانات المرتبات والحضور -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white py-2">
                        <h6 class="card-title mb-0 fw-bold font-family-cairo">
                            <i class="fas fa-money-bill-wave me-2"></i><?php echo e(__('بيانات المرتبات والحضور')); ?>

                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('الشيفت')); ?>:</label>
                                <p class="form-control-plaintext">
                                    <?php echo e($viewEmployee->shift ? $viewEmployee->shift->start_time . ' - ' . $viewEmployee->shift->end_time : __('غير محدد')); ?>

                                </p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('رقم البصمة')); ?>:</label>
                                <p class="form-control-plaintext">
                                    <?php echo e($viewEmployee->finger_print_id ?? __('غير محدد')); ?>

                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('الاسم في البصمة')); ?>:</label>
                                <p class="form-control-plaintext">
                                    <?php echo e($viewEmployee->finger_print_name ?? __('غير محدد')); ?>

                                </p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('باسورد الهاتف')); ?>:</label>
                                <p class="form-control-plaintext">
                                    <?php echo e($viewEmployee->password ? '********' : __('غير محدد')); ?>

                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('الساعة الإضافي تحسب ك')); ?>:</label>
                                <p class="form-control-plaintext">
                                    <?php echo e($viewEmployee->additional_hour_calculation ? $viewEmployee->additional_hour_calculation . ' ساعة' : __('غير محدد')); ?>

                                </p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('اليوم الإضافي يحسب ك')); ?>:</label>
                                <p class="form-control-plaintext">
                                    <?php echo e($viewEmployee->additional_day_calculation ? $viewEmployee->additional_day_calculation . ' يوم' : __('غير محدد')); ?>

                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- معدلات الأداء -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-gradient-primary text-white py-2">
                        <h6 class="card-title mb-0 fw-bold font-family-cairo">
                            <i class="fas fa-chart-line me-2"></i><?php echo e(__('معدلات الأداء')); ?>

                        </h6>
                    </div>
                    <div class="card-body">
                        <!--[if BLOCK]><![endif]--><?php if($viewEmployee && $viewEmployee->kpis->count() > 0): ?>
                            <div class="row g-3">
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $viewEmployee->kpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-success h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title fw-bold text-success mb-0">
                                                        <?php echo e($kpi->name); ?>

                                                    </h6>
                                                    <span class="badge bg-primary fs-6">
                                                        <?php echo e($kpi->pivot->weight_percentage); ?>%
                                                    </span>
                                                </div>
                                                <!--[if BLOCK]><![endif]--><?php if($kpi->description): ?>
                                                    <p class="card-text text-muted small mb-0">
                                                        <?php echo e($kpi->description); ?>

                                                    </p>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>

                            <!-- مجموع الأوزان -->
                            <div class="mt-3">
                                <?php
                                    $totalWeight = $viewEmployee->kpis->sum('pivot.weight_percentage');
                                ?>
                                <div class="alert <?php echo e($totalWeight == 100 ? 'alert-success' : 'alert-warning'); ?> mb-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold">
                                            <i class="fas fa-calculator me-2"></i><?php echo e(__('المجموع الكلي:')); ?>

                                        </span>
                                        <span
                                            class="badge <?php echo e($totalWeight == 100 ? 'bg-success' : 'bg-warning'); ?> fs-5">
                                            <?php echo e($totalWeight); ?>%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info text-center mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                <?php echo e(__('لا توجد معدلات أداء محددة لهذا الموظف.')); ?>

                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo e(__('لا توجد بيانات لعرضها.')); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH D:\laragon\www\massar1.02\resources\views/livewire/hr-management/employees/partials/employee-view.blade.php ENDPATH**/ ?>