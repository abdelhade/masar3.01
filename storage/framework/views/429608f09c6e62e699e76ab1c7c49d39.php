<?php

use Livewire\Volt\Component;
use App\Models\Attendance;
use Livewire\WithPagination;
use App\Models\Employee;
use Livewire\Attributes\Computed;
use Carbon\Carbon;

?>

<div dir="rtl" style="font-family: 'Cairo', sans-serif;">
    <div class="row mb-3">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('إضافة البصمات')): ?>
            <div class="col-12 d-flex justify-content-end">
                <button class="btn btn-primary font-family-cairo fw-bold" wire:click="create">
                    <i class="las la-plus"></i> <?php echo e(__('إضافة حضور')); ?>

                </button>
            </div>
        <?php endif; ?>


    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 font-family-cairo fw-bold"><?php echo e(__('سجلات الحضور')); ?></h5>
                    <div class="row w-100 align-items-center">
                        <div class="col-md-2">
                            <input type="text" class="form-control font-family-cairo"
                                placeholder="<?php echo e(__('اسم الموظف')); ?>"
                                wire:model.live.debounce.500ms="search_employee_name">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control font-family-cairo"
                                placeholder="<?php echo e(__('رقم الموظف')); ?>"
                                wire:model.live.debounce.500ms="search_employee_id">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control font-family-cairo"
                                placeholder="<?php echo e(__('اسم البصمة')); ?>"
                                wire:model.live.debounce.500ms="search_fingerprint_name">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control font-family-cairo" wire:model.live="date_from"
                                placeholder="<?php echo e(__('من تاريخ')); ?>">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control font-family-cairo" wire:model.live="date_to"
                                placeholder="<?php echo e(__('إلى تاريخ')); ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-center mt-2 mt-md-0">

                            <button type="button" class="btn btn-outline-secondary font-family-cairo fw-bold w-100"
                                wire:click="clearFilters">
                                <i class="las la-broom me-1"></i> <?php echo e(__('مسح الفلاتر')); ?>

                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table
                            class="table table-striped table-hover table-bordered table-light text-center align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="font-family-cairo fw-bold"><?php echo e(__('رقم')); ?></th>
                                    <th class="font-family-cairo fw-bold"><?php echo e(__('اسم الموظف')); ?></th>
                                    <th class="font-family-cairo fw-bold"><?php echo e(__('رقم الموظف')); ?></th>
                                    <th class="font-family-cairo fw-bold"><?php echo e(__('اسم البصمة')); ?></th>
                                    <th class="font-family-cairo fw-bold"><?php echo e(__('النوع')); ?></th>
                                    <th class="font-family-cairo fw-bold"><?php echo e(__('التاريخ')); ?></th>
                                    <th class="font-family-cairo fw-bold"><?php echo e(__('الوقت')); ?></th>
                                    <th class="font-family-cairo fw-bold"><?php echo e(__('الموقع')); ?></th>
                                    <th class="font-family-cairo fw-bold"><?php echo e(__('المشروع')); ?></th>
                                    <th class="font-family-cairo fw-bold"><?php echo e(__('الحالة')); ?></th>
                                    <th class="font-family-cairo fw-bold"><?php echo e(__('ملاحظات')); ?></th>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['حذف البصمات', 'تعديل البصمات'])): ?>
                                        <th class="font-family-cairo fw-bold"><?php echo e(__('الإجراءات')); ?></th>
                                    <?php endif; ?>

                                </tr>
                            </thead>
                            <tbody>
                                <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $attendances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="font-family-cairo fw-bold"><?php echo e($attendance->id); ?></td>
                                        <td class="font-family-cairo fw-bold"><?php echo e($attendance->employee->name ?? '-'); ?>

                                        </td>
                                        <td class="font-family-cairo fw-bold"><?php echo e($attendance->employee_id); ?></td>
                                        <td class="font-family-cairo fw-bold">
                                            <?php echo e($attendance->employee_attendance_finger_print_name); ?>

                                        </td>
                                        <td class="font-family-cairo fw-bold">
                                            <?php echo e($attendance->type == 'check_in' ? __('دخول') : __('خروج')); ?>

                                        </td>
                                        <td class="font-family-cairo fw-bold"><?php echo e($attendance->date->format('Y-m-d')); ?>

                                        </td>
                                        <td class="font-family-cairo fw-bold"><?php echo e($attendance->time); ?>

                                        </td>
                                        <td class="font-family-cairo fw-bold"><?php echo e($attendance->location_address ?? '-'); ?></td>
                                        <td class="font-family-cairo fw-bold"><?php echo e($attendance->project_code ?? '-'); ?></td>
                                        <td class="font-family-cairo fw-bold">
                                            <!--[if BLOCK]><![endif]--><?php if($attendance->status == 'pending'): ?>
                                                <span
                                                    class="badge bg-warning font-family-cairo"><?php echo e(__('قيد المراجعة')); ?></span>
                                            <?php elseif($attendance->status == 'approved'): ?>
                                                <span
                                                    class="badge bg-success font-family-cairo"><?php echo e(__('معتمد')); ?></span>
                                            <?php else: ?>
                                                <span
                                                    class="badge bg-danger font-family-cairo"><?php echo e(__('مرفوض')); ?></span>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </td>
                                        <td class="font-family-cairo fw-bold"><?php echo e($attendance->notes ?? '-'); ?></td>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['حذف البصمات', 'تعديل البصمات'])): ?>
                                            <td class="font-family-cairo fw-bold">
                                                <!--[if BLOCK]><![endif]--><?php if($attendance->status !== 'approved'): ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('تعديل البصمات')): ?>
                                                        <button class="btn btn-sm btn-info me-1 font-family-cairo"
                                                            wire:click="edit(<?php echo e($attendance->id); ?>)"><?php echo e(__('تعديل')); ?></button>
                                                    <?php endif; ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('حذف البصمات')): ?>
                                                        <button class="btn btn-sm btn-danger font-family-cairo"
                                                            wire:click="confirmDelete(<?php echo e($attendance->id); ?>)"><?php echo e(__('حذف')); ?></button>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted"><?php echo e(__('غير قابل للتعديل/الحذف')); ?></span>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </td>
                                        <?php endif; ?>

                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="10" class="text-center font-family-cairo fw-bold">
                                            <?php echo e(__('لا توجد سجلات حضور')); ?>


                                        </td>
                                    </tr>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        <?php echo e($attendances->links('pagination::bootstrap-5')); ?>

                    </div>
                </div>
            </div>
        </div>


    </div>
    
    <!--[if BLOCK]><![endif]--><?php if($showCreateModal || $showEditModal): ?>
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-family-cairo"><?php echo e(__('إضافة حضور')); ?></h5>
                        <button type="button" class="btn-close" wire:click="$set('showCreateModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="store">
                            <div class="mb-3">
                                <label class="form-label font-family-cairo"><?php echo e(__('الموظف')); ?></label>
                                <select class="form-select font-family-cairo fw-bold font-14"
                                    wire:model.live="form.employee_id">
                                    <option class="text-muted font-family-cairo fw-bold font-14" value="">
                                        <?php echo e(__('اختر الموظف')); ?>

                                    </option>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option class="font-family-cairo fw-bold font-14" value="<?php echo e($employee->id); ?>">
                                            <?php echo e($employee->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo"><?php echo e(__('رقم البصمة')); ?></label>
                                <input type="text" class="form-control font-family-cairo"
                                    value="<?php echo e($this->form['employee_attendance_finger_print_id']); ?>" disabled>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.employee_attendance_finger_print_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo"><?php echo e(__('اسم البصمة')); ?></label>
                                <input type="text" class="form-control font-family-cairo"
                                    value="<?php echo e($this->form['employee_attendance_finger_print_name']); ?>" disabled>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.employee_attendance_finger_print_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo"><?php echo e(__('النوع')); ?></label>
                                <select class="form-select font-family-cairo fw-bold font-14"
                                    wire:model.live="form.type">
                                    <option class="font-family-cairo fw-bold font-14" value="check_in">
                                        <?php echo e(__('دخول')); ?>

                                    </option>
                                    <option class="font-family-cairo fw-bold font-14" value="check_out">
                                        <?php echo e(__('خروج')); ?>

                                    </option>
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo"><?php echo e(__('التاريخ')); ?></label>
                                <input type="date" class="form-control font-family-cairo"
                                    wire:model.live="form.date">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14"><?php echo e(__('الوقت')); ?></label>
                                <input type="time" wire:model="form.time"
                                    class="form-control
                                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class=" text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14"><?php echo e(__('الحالة')); ?></label>
                                <select class="form-select font-family-cairo fw-bold font-14"
                                    wire:model.live="form.status">
                                    <option class="font-family-cairo fw-bold font-14" value="pending">
                                        <?php echo e(__('قيد المراجعة')); ?>

                                    </option>
                                    <option class="font-family-cairo fw-bold font-14" value="approved">
                                        <?php echo e(__('معتمد')); ?>

                                    </option>
                                    <option class="font-family-cairo fw-bold font-14" value="rejected">
                                        <?php echo e(__('مرفوض')); ?>

                                    </option>
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14"><?php echo e(__('ملاحظات')); ?></label>
                                <textarea class="form-control font-family-cairo fw-bold font-14" wire:model.live="form.notes"></textarea>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary font-family-cairo"
                                    wire:click="$set('showCreateModal', false)"><?php echo e(__('إلغاء')); ?></button>
                                <button type="submit"
                                    class="btn btn-primary font-family-cairo"><?php echo e(__('حفظ')); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    
    <!--[if BLOCK]><![endif]--><?php if($showEditModal): ?>
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-family-cairo"><?php echo e(__('تعديل الحضور')); ?></h5>
                        <button type="button" class="btn-close" wire:click="$set('showEditModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="update">
                            
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14"><?php echo e(__('الموظف')); ?></label>
                                <select class="form-select font-family-cairo fw-bold font-14"
                                    wire:model.live="form.employee_id">
                                    <option class="text-muted font-family-cairo fw-bold font-14" value="">
                                        <?php echo e(__('اختر الموظف')); ?>

                                    </option>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option class="font-family-cairo fw-bold font-14"
                                            value="<?php echo e($employee->id); ?>">
                                            <?php echo e($employee->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14"><?php echo e(__('رقم البصمة')); ?></label>
                                <input type="text" class="form-control font-family-cairo fw-bold font-14"
                                    value="<?php echo e($this->form['employee_attendance_finger_print_id']); ?>" disabled>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.employee_attendance_finger_print_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14"><?php echo e(__('اسم البصمة')); ?></label>
                                <input type="text" class="form-control font-family-cairo fw-bold font-14"
                                    value="<?php echo e($this->form['employee_attendance_finger_print_name']); ?>" disabled>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.employee_attendance_finger_print_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14"><?php echo e(__('النوع')); ?></label>
                                <select class="form-select font-family-cairo fw-bold font-14"
                                    wire:model.live="form.type">
                                    <option class="font-family-cairo fw-bold font-14" value="check_in">
                                        <?php echo e(__('دخول')); ?>

                                    </option>
                                    <option class="font-family-cairo fw-bold font-14" value="check_out">
                                        <?php echo e(__('خروج')); ?>

                                    </option>
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14"><?php echo e(__('التاريخ')); ?></label>
                                <input type="date" class="form-control font-family-cairo fw-bold font-14"
                                    wire:model.live="form.date">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14"><?php echo e(__('الوقت')); ?></label>
                                <input type="time" class="form-control font-family-cairo fw-bold font-14"
                                    wire:model.live="form.time">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14"><?php echo e(__('الحالة')); ?></label>
                                <select class="form-select font-family-cairo fw-bold font-14"
                                    wire:model.live="form.status">
                                    <option class="font-family-cairo fw-bold font-14" value="pending">
                                        <?php echo e(__('قيد المراجعة')); ?>

                                    </option>
                                    <option class="font-family-cairo fw-bold font-14" value="approved">
                                        <?php echo e(__('معتمد')); ?>

                                    </option>
                                    <option class="font-family-cairo fw-bold font-14" value="rejected">
                                        <?php echo e(__('مرفوض')); ?>

                                    </option>
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14"><?php echo e(__('ملاحظات')); ?></label>
                                <textarea class="form-control font-family-cairo fw-bold font-14" wire:model.live="form.notes"></textarea>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['form.notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary font-family-cairo"
                                    wire:click="$set('showEditModal', false)"><?php echo e(__('إلغاء')); ?></button>
                                <button type="submit"
                                    class="btn btn-primary font-family-cairo"><?php echo e(__('حفظ التعديلات')); ?></button>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    
    <!--[if BLOCK]><![endif]--><?php if($showDeleteModal): ?>
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-family-cairo"><?php echo e(__('تأكيد الحذف')); ?></h5>
                        <button type="button" class="btn-close"
                            wire:click="$set('showDeleteModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <p class="font-family-cairo"><?php echo e(__('هل أنت متأكد من حذف هذا السجل؟')); ?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary font-family-cairo"
                            wire:click="$set('showDeleteModal', false)"><?php echo e(__('إلغاء')); ?></button>
                        <button type="button" class="btn btn-danger font-family-cairo"
                            wire:click="delete"><?php echo e(__('حذف')); ?></button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

</div>


</div><?php /**PATH D:\Laragon\laragon\www\massar1.02\resources\views\livewire/hr-management/attendances/attendance/index.blade.php ENDPATH**/ ?>