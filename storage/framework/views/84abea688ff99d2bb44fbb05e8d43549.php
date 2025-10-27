
<div class="container-fluid" style="direction: rtl;">
    <!-- Navigation Tabs - Alpine.js -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link font-family-cairo fw-bold"
                    :class="{ 'active': activeTab === 'personal' }"
                    @click="switchTab('personal')"
                    type="button">
                <i class="fas fa-user me-2"></i><?php echo e(__('البيانات الشخصية')); ?>

            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link font-family-cairo fw-bold"
                    :class="{ 'active': activeTab === 'location' }"
                    @click="switchTab('location')"
                    type="button">
                <i class="fas fa-map-marker-alt me-2"></i><?php echo e(__('الموقع')); ?>

            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link font-family-cairo fw-bold"
                    :class="{ 'active': activeTab === 'job' }"
                    @click="switchTab('job')"
                    type="button">
                <i class="fas fa-briefcase me-2"></i><?php echo e(__('الوظيفة')); ?>

            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link font-family-cairo fw-bold"
                    :class="{ 'active': activeTab === 'salary' }"
                    @click="switchTab('salary')"
                    type="button">
                <i class="fas fa-money-bill-wave me-2"></i><?php echo e(__('المرتبات')); ?>

            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link font-family-cairo fw-bold"
                    :class="{ 'active': activeTab === 'attendance' }"
                    @click="switchTab('attendance')"
                    type="button">
                <i class="fas fa-clock me-2"></i><?php echo e(__('الحضور')); ?>

            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link font-family-cairo fw-bold"
                    :class="{ 'active': activeTab === 'kpi' }"
                    @click="switchTab('kpi')"
                    type="button">
                <i class="fas fa-chart-line me-2"></i><?php echo e(__('معدلات الأداء')); ?>

            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Personal Information Tab -->
        <div x-show="activeTab === 'personal'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             style="display: none;">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-primary text-white py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold">
                                <i class="fas fa-user me-2"></i><?php echo e(__('البيانات الأساسية')); ?>

                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('الاسم الكامل')); ?>

                                        <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" wire:model.defer="name"
                                        placeholder="<?php echo e(__('أدخل الاسم الكامل')); ?>">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('البريد الإلكتروني')); ?>

                                        <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" wire:model.defer="email"
                                        placeholder="<?php echo e(__('أدخل البريد الإلكتروني')); ?>">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('رقم الهاتف')); ?>

                                        <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" wire:model.defer="phone"
                                        placeholder="<?php echo e(__('أدخل رقم الهاتف')); ?>">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('رقم الهوية')); ?></label>
                                    <input type="text" class="form-control" wire:model.defer="nationalId"
                                        placeholder="<?php echo e(__('أدخل رقم الهوية')); ?>">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['nationalId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('النوع')); ?></label>
                                    <select class="form-select" wire:model.defer="gender">
                                        <option value=""><?php echo e(__('اختر النوع')); ?></option>
                                        <option value="male"><?php echo e(__('ذكر')); ?></option>
                                        <option value="female"><?php echo e(__('أنثى')); ?></option>
                                    </select>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['gender'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('تاريخ الميلاد')); ?></label>
                                    <input type="date" class="form-control" wire:model.defer="date_of_birth">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['date_of_birth'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('الحالة الاجتماعية')); ?></label>
                                    <select class="form-select" wire:model.defer="marital_status">
                                        <option value=""><?php echo e(__('اختر الحالة')); ?></option>
                                        <option value="غير متزوج"><?php echo e(__('غير متزوج')); ?></option>
                                        <option value="متزوج"><?php echo e(__('متزوج')); ?></option>
                                        <option value="مطلق"><?php echo e(__('مطلق')); ?></option>
                                        <option value="أرمل"><?php echo e(__('أرمل')); ?></option>
                                    </select>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['marital_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('مستوى التعليم')); ?></label>
                                    <select class="form-select" wire:model.defer="education">
                                        <option value=""><?php echo e(__('اختر المستوى')); ?></option>
                                        <option value="دبلوم"><?php echo e(__('دبلوم')); ?></option>
                                        <option value="بكالوريوس"><?php echo e(__('بكالوريوس')); ?></option>
                                        <option value="ماجستير"><?php echo e(__('ماجستير')); ?></option>
                                        <option value="دكتوراه"><?php echo e(__('دكتوراه')); ?></option>
                                    </select>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['education'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('معلومات إضافية')); ?></label>
                                    <textarea class="form-control" rows="3" wire:model.defer="information"
                                        placeholder="<?php echo e(__('أدخل أي معلومات إضافية...')); ?>"></textarea>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['information'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-success text-white py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold">
                                <i class="fas fa-image me-2"></i><?php echo e(__('الصورة الشخصية')); ?>

                            </h6>
                        </div>
                        <div class="card-body text-center py-3">
                            <div class="mb-3">
                                <div class="position-relative d-inline-block">
                                    <template x-if="imagePreview">
                                        <img :src="imagePreview"
                                            alt="<?php echo e(__('صورة الموظف')); ?>"
                                            class="rounded-circle border border-3 border-light shadow"
                                            style="width: 120px; height: 120px; object-fit: cover;">
                                    </template>
                                    <template x-if="!imagePreview && isEdit && currentImageUrl">
                                        <div style="position: relative;">
                                            <img :src="currentImageUrl"
                                                alt="<?php echo e(__('صورة الموظف')); ?>"
                                                class="rounded-circle border border-3 border-light shadow"
                                                style="width: 120px; height: 120px; object-fit: cover; display: none;"
                                                x-on:load="
                                                    $el.style.display = 'block';
                                                    $el.nextElementSibling.style.display = 'none';
                                                    $el.nextElementSibling.nextElementSibling.style.display = 'none';
                                                "
                                                x-on:error="
                                                    $el.style.display = 'none';
                                                    $el.nextElementSibling.style.display = 'none';
                                                    $el.nextElementSibling.nextElementSibling.style.display = 'block';
                                                ">
                                            
                                            <!-- Loading indicator -->
                                            <div style="display: block; text-align: center; padding: 20px;">
                                                <div class="spinner-border text-primary rounded-circle" role="status" style="width: 120px; height: 120px;">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Placeholder (shown on error) -->
                                            <div style="display: none; text-align: center;">
                                                <img src="<?php echo e(asset('assets/images/avatar-placeholder.svg')); ?>"
                                                    alt="<?php echo e(__('صورة الموظف')); ?>"
                                                    class="rounded-circle border border-3 border-light shadow"
                                                    style="width: 120px; height: 120px; object-fit: cover;">
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!imagePreview && (!isEdit || !currentImageUrl)">
                                        <img src="<?php echo e(asset('assets/images/avatar-placeholder.svg')); ?>"
                                            alt="<?php echo e(__('صورة الموظف')); ?>"
                                            class="rounded-circle border border-3 border-light shadow"
                                            style="width: 120px; height: 120px; object-fit: cover;">
                                    </template>
                                </div>
                            </div>
                            <div class="mb-3">
                                <input type="file" class="form-control" wire:model="image" 
                                       @change="handleImageChange($event)" accept="image/jpeg,image/png,image/jpg,image/gif">
                                
                                <!-- File Info -->
                                <div x-show="selectedFileName" x-transition class="alert alert-info py-2 mt-2" style="font-size: 0.85rem;">
                                    <i class="fas fa-file-image me-1"></i>
                                    <span x-text="selectedFileName"></span>
                                </div>

                                <!-- Upload Progress -->
                                <div wire:loading wire:target="image" class="progress mt-2" style="height: 20px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                         role="progressbar" style="width: 100%">
                                        <span class="fw-bold"><?php echo e(__('جاري الرفع...')); ?></span>
                                    </div>
                                </div>

                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php echo e(__('الحد الأقصى: 2 ميجابايت')); ?> | <?php echo e(__('الأنواع: JPG, PNG, GIF')); ?>

                                </small>

                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="alert alert-danger py-2 mt-2" role="alert">
                                        <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                    </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold text-dark"><?php echo e(__('حالة الموظف')); ?>

                                    <span class="text-danger">*</span></label>
                                <select class="form-select font-family-cairo fw-bold" wire:model.defer="status">
                                    <option value=""><?php echo e(__('اختر الحالة')); ?></option>
                                    <option value="مفعل"><?php echo e(__('مفعل')); ?></option>
                                    <option value="معطل"><?php echo e(__('معطل')); ?></option>
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="text-danger small mt-1">
                                        <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                    </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Tab -->
        <div x-show="activeTab === 'location'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             style="display: none;">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient-info text-white py-2">
                    <h6 class="card-title mb-0 font-family-cairo fw-bold">
                        <i class="fas fa-map-marker-alt me-2"></i><?php echo e(__('الموقع الجغرافي')); ?>

                    </h6>
                </div>
                <div class="card-body py-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark"><?php echo e(__('البلد')); ?></label>
                            <select class="form-select" wire:model.defer="country_id">
                                <option value=""><?php echo e(__('اختر البلد')); ?></option>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($country->id); ?>"><?php echo e($country->title); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </select>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['country_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                </div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark"><?php echo e(__('المحافظة')); ?></label>
                            <select class="form-select" wire:model.defer="state_id">
                                <option value=""><?php echo e(__('اختر المحافظة')); ?></option>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($state->id); ?>"><?php echo e($state->title); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </select>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['state_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                </div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark"><?php echo e(__('المدينة')); ?></label>
                            <select class="form-select" wire:model.defer="city_id">
                                <option value=""><?php echo e(__('اختر المدينة')); ?></option>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($city->id); ?>"><?php echo e($city->title); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </select>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['city_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                </div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark"><?php echo e(__('المنطقة')); ?></label>
                            <select class="form-select" wire:model.defer="town_id">
                                <option value=""><?php echo e(__('اختر المنطقة')); ?></option>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $towns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $town): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($town->id); ?>"><?php echo e($town->title); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </select>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['town_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                </div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Tab -->
        <div x-show="activeTab === 'job'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             style="display: none;">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-warning text-white py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold">
                                <i class="fas fa-briefcase me-2"></i><?php echo e(__('الوظيفة والقسم')); ?>

                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('الوظيفة')); ?></label>
                                    <select class="form-select" wire:model.defer="job_id">
                                        <option value=""><?php echo e(__('اختر الوظيفة')); ?></option>
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($job->id); ?>"><?php echo e($job->title); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    </select>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['job_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('القسم')); ?></label>
                                    <select class="form-select" wire:model.defer="department_id">
                                        <option value=""><?php echo e(__('اختر القسم')); ?></option>
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($department->id); ?>"><?php echo e($department->title); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    </select>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['department_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('المستوى الوظيفي')); ?></label>
                                    <select class="form-select" wire:model.defer="job_level">
                                        <option value=""><?php echo e(__('اختر المستوى')); ?></option>
                                        <option value="مبتدئ"><?php echo e(__('مبتدئ')); ?></option>
                                        <option value="متوسط"><?php echo e(__('متوسط')); ?></option>
                                        <option value="محترف"><?php echo e(__('محترف')); ?></option>
                                    </select>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['job_level'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-secondary text-white py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold">
                                <i class="fas fa-calendar-alt me-2"></i><?php echo e(__('تواريخ التوظيف')); ?>

                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('تاريخ التوظيف')); ?></label>
                                    <input type="date" class="form-control" wire:model.defer="date_of_hire">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['date_of_hire'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('تاريخ الانتهاء')); ?></label>
                                    <input type="date" class="form-control" wire:model.defer="date_of_fire">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['date_of_fire'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Salary Tab -->
        <div x-show="activeTab === 'salary'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             style="display: none;">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-success text-white py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold">
                                <i class="fas fa-money-bill-wave me-2"></i><?php echo e(__('المرتب الأساسي')); ?>

                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('المرتب')); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text">ر.س</span>
                                        <input type="number" class="form-control" wire:model.defer="salary"
                                            placeholder="0.00" step="0.01">
                                    </div>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['salary'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('نوع الاستحقاق')); ?></label>
                                    <select class="form-select" wire:model.defer="salary_type">
                                        <option value=""><?php echo e(__('اختر نوع الاستحقاق')); ?></option>
                                        <option value="ساعات عمل فقط"><?php echo e(__('ساعات عمل فقط')); ?></option>
                                        <option value="ساعات عمل و إضافي يومى"><?php echo e(__('ساعات عمل و إضافي يومى')); ?></option>
                                        <option value="ساعات عمل و إضافي للمده"><?php echo e(__('ساعات عمل و إضافي للمده')); ?></option>
                                        <option value="حضور فقط"><?php echo e(__('حضور فقط')); ?></option>
                                        <option value="إنتاج فقط"><?php echo e(__('إنتاج فقط')); ?></option>
                                    </select>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['salary_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-info text-white py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold">
                                <i class="fas fa-calculator me-2"></i><?php echo e(__('حسابات إضافية')); ?>

                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('الساعة الإضافي تحسب ك')); ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control"
                                            wire:model.defer="additional_hour_calculation" placeholder="0.00" step="0.01">
                                        <span class="input-group-text">ساعة</span>
                                    </div>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['additional_hour_calculation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('اليوم الإضافي يحسب ك')); ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control"
                                            wire:model.defer="additional_day_calculation" placeholder="0.00" step="0.01">
                                        <span class="input-group-text">يوم</span>
                                    </div>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['additional_day_calculation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Tab -->
        <div x-show="activeTab === 'attendance'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             style="display: none;">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-primary text-white py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold">
                                <i class="fas fa-clock me-2"></i><?php echo e(__('نظام الحضور')); ?>

                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('الشيفت')); ?></label>
                                    <select class="form-select" wire:model.defer="shift_id">
                                        <option value=""><?php echo e(__('اختر الشيفت')); ?></option>
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $shifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($shift->id); ?>">
                                                <?php echo e($shift->start_time); ?> - <?php echo e($shift->end_time); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    </select>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['shift_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('رقم البصمة')); ?></label>
                                    <input type="number" class="form-control" wire:model.defer="finger_print_id"
                                        placeholder="<?php echo e(__('أدخل رقم البصمة')); ?>" min="0">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['finger_print_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('الاسم في البصمة')); ?></label>
                                    <input type="text" class="form-control" wire:model.defer="finger_print_name"
                                        placeholder="<?php echo e(__('أدخل الاسم في البصمة')); ?>">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['finger_print_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-warning text-white py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold">
                                <i class="fas fa-mobile-alt me-2"></i><?php echo e(__('نظام الهاتف المحمول')); ?>

                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('باسورد الهاتف')); ?></label>
                                    <div class="input-group">
                                        <input :type="showPassword ? 'text' : 'password'" class="form-control"
                                            wire:model.defer="password" 
                                            :placeholder="$wire.isEdit ? '<?php echo e(__('اتركه فارغاً للحفاظ على الباسورد الحالي')); ?>' : '<?php echo e(__('أدخل باسورد الهاتف')); ?>'">
                                        <button class="btn btn-outline-secondary" type="button" @click="togglePassword()">
                                            <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                    </div>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Tab -->
        <div x-show="activeTab === 'kpi'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             style="display: none;">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient-primary text-white py-2">
                    <h6 class="card-title mb-0 font-family-cairo fw-bold">
                        <i class="fas fa-chart-line me-2"></i><?php echo e(__('معدلات الأداء للموظف')); ?>

                    </h6>
                </div>
                <div class="card-body py-3">
                    <!-- إضافة معدل أداء جديد -->
                    <div class="card border-primary mb-3">
                        <div class="card-header bg-light py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold text-primary">
                                <i class="fas fa-plus me-2"></i><?php echo e(__('إضافة معدل أداء جديد')); ?>

                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold text-dark"><?php echo e(__('اختر معدل الأداء')); ?>

                                        <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <div class="input-group">
                                            <input type="text" class="form-control"
                                                :value="selectedKpiId ? getKpiName(selectedKpiId) : kpiSearch"
                                                @input="kpiSearch = $event.target.value; selectedKpiId = ''; kpiSearchOpen = true"
                                                @click="kpiSearchOpen = true"
                                                @keydown.escape="kpiSearchOpen = false"
                                                @keydown.arrow-down.prevent="navigateKpiDown()"
                                                @keydown.arrow-up.prevent="navigateKpiUp()"
                                                @keydown.enter.prevent="selectCurrentKpi()"
                                                :placeholder="selectedKpiId ? '' : '<?php echo e(__('ابحث عن معدل الأداء...')); ?>'"
                                                autocomplete="off">
                                            <button class="btn btn-outline-secondary" type="button"
                                                @click="kpiSearchOpen = !kpiSearchOpen">
                                                <i class="fas" :class="kpiSearchOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" type="button"
                                                x-show="selectedKpiId"
                                                @click="clearKpiSelection()"
                                                title="<?php echo e(__('مسح الاختيار')); ?>">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>

                                        <!-- Dropdown Results -->
                                        <div x-show="kpiSearchOpen && filteredKpis.length > 0"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            class="position-absolute w-100 bg-white border rounded shadow-lg mt-1"
                                            style="z-index: 1000; max-height: 250px; overflow-y: auto;"
                                            @click.away="kpiSearchOpen = false">
                                            <template x-for="(kpi, index) in filteredKpis" :key="kpi.id">
                                                <div class="p-2 border-bottom cursor-pointer"
                                                    @click="selectKpi(kpi)"
                                                    :class="kpiSearchIndex === index ? 'bg-primary text-white' : 'hover-bg-light'"
                                                    style="cursor: pointer;">
                                                    <div class="fw-bold" x-text="kpi.name"></div>
                                                    <small x-text="kpi.description" x-show="kpi.description"></small>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- No Results -->
                                        <div x-show="kpiSearchOpen && kpiSearch && filteredKpis.length === 0"
                                            class="position-absolute w-100 bg-white border rounded shadow-lg mt-1 p-3 text-center text-muted"
                                            style="z-index: 1000;">
                                            <i class="fas fa-search me-2"></i><?php echo e(__('لا توجد نتائج')); ?>

                                        </div>
                                    </div>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['selected_kpi_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($message); ?>

                                        </div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary btn-lg w-100"
                                        @click="$wire.addKpi()" wire:loading.attr="disabled" 
                                        :disabled="!selectedKpiId">
                                        <span wire:loading.remove wire:target="addKpi">
                                            <i class="fas fa-plus me-2"></i><?php echo e(__('إضافة')); ?>

                                        </span>
                                        <span wire:loading wire:target="addKpi">
                                            <i class="fas fa-spinner fa-spin me-2"></i><?php echo e(__('جاري الإضافة...')); ?>

                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- معدلات الأداء المضافة -->
                    <template x-if="kpiIds.length > 0">
                        <div>
                            <h6 class="fw-bold text-dark mb-3">
                                <i class="fas fa-list me-2"></i><?php echo e(__('معدلات الأداء المضافة')); ?>

                            </h6>
                            <div class="row g-3 mb-3">
                                <template x-for="kpiId in kpiIds" :key="kpiId">
                                    <div class="col-md-4 col-sm-6">
                                        <div class="card border-success h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div class="flex-grow-1">
                                                        <h6 class="card-title fw-bold text-success mb-1"
                                                            x-text="getKpiName(kpiId)"></h6>
                                                        <small class="text-muted" x-text="getKpiDescription(kpiId)"></small>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                                        @click="$wire.removeKpi(kpiId)" title="<?php echo e(__('حذف')); ?>">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                <div class="mt-2">
                                                    <label class="form-label fw-bold text-dark small"><?php echo e(__('الوزن النسبي')); ?></label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control" :value="kpiWeights[kpiId] || 0"
                                                            @input="kpiWeights[kpiId] = parseInt($event.target.value) || 0"
                                                            min="0" max="100" step="1">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- مؤشر المجموع -->
                            <div class="alert py-2"
                                :class="{
                                    'alert-success': totalKpiWeight === 100,
                                    'alert-danger': totalKpiWeight > 100,
                                    'alert-warning': totalKpiWeight < 100
                                }">
                                <i class="fas me-2"
                                    :class="{
                                        'fa-check-circle': totalKpiWeight === 100,
                                        'fa-times-circle': totalKpiWeight > 100,
                                        'fa-exclamation-triangle': totalKpiWeight < 100
                                    }"></i>
                                <span x-text="weightMessage"></span>
                            </div>

                            <!-- Progress Bar -->
                            <div class="card shadow-sm"
                                :class="{
                                    'border-success': totalKpiWeight === 100,
                                    'border-danger': totalKpiWeight > 100,
                                    'border-warning': totalKpiWeight < 100
                                }">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="card-title fw-bold mb-0"
                                            :class="{
                                                'text-success': totalKpiWeight === 100,
                                                'text-danger': totalKpiWeight > 100,
                                                'text-warning': totalKpiWeight < 100
                                            }">
                                            <i class="fas fa-calculator me-2"></i><?php echo e(__('المجموع الحالي للأوزان')); ?>

                                        </h6>
                                        <span class="badge text-white"
                                            :class="{
                                                'bg-success': totalKpiWeight === 100,
                                                'bg-danger': totalKpiWeight > 100,
                                                'bg-warning': totalKpiWeight < 100
                                            }"
                                            x-text="totalKpiWeight + '%'"></span>
                                    </div>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar"
                                            :style="'width: ' + Math.min(totalKpiWeight, 100) + '%'"
                                            :class="{
                                                'bg-success': totalKpiWeight === 100,
                                                'bg-danger': totalKpiWeight > 100,
                                                'bg-warning': totalKpiWeight < 100
                                            }">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- رسالة عند عدم وجود KPIs -->
                    <template x-if="kpiIds.length === 0">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php echo e(__('لم يتم إضافة أي معدلات أداء بعد. استخدم النموذج أعلاه لإضافة معدلات الأداء.')); ?>

                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH D:\laragon\www\massar1.02\resources\views/livewire/hr-management/employees/partials/employee-form.blade.php ENDPATH**/ ?>