<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Employee;
use App\Models\Country;
use App\Models\City;
use App\Models\State;
use App\Models\Town;
use App\Models\Department;
use App\Models\EmployeesJob;
use App\Models\Shift;
use App\Models\Kpi;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

?>

<div style="font-family: 'Cairo', sans-serif; direction: rtl;" x-data="employeeManager({
    showModal: $wire.entangle('showModal'),
    showViewModal: $wire.entangle('showViewModal'),
    kpiIds: $wire.entangle('kpi_ids'),
    kpiWeights: $wire.entangle('kpi_weights'),
    selectedKpiId: $wire.entangle('selected_kpi_id'),
    currentImageUrl: $wire.entangle('currentImageUrl'),
    kpis: <?php echo \Illuminate\Support\Js::from($kpis)->toHtml() ?>,
    isEdit: $wire.entangle('isEdit')
})" x-init="init()">

    <!-- Notification Container -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999; margin-top: 60px;">
        <template x-for="notification in notifications" :key="notification.id">
            <div class="alert mb-2 shadow-lg"
                :class="{
                    'alert-success': notification.type === 'success',
                    'alert-danger': notification.type === 'error',
                    'alert-info': notification.type === 'info'
                }"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-full"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-x-0"
                x-transition:leave-end="opacity-0 transform translate-x-full" role="alert">
                <i class="fas me-2"
                    :class="{
                        'fa-check-circle': notification.type === 'success',
                        'fa-exclamation-circle': notification.type === 'error',
                        'fa-info-circle': notification.type === 'info'
                    }"></i>
                <span x-text="notification.message"></span>
            </div>
        </template>
    </div>

    <div class="row">
        <!--[if BLOCK]><![endif]--><?php if(session()->has('success')): ?>
            <div class="alert alert-success" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('إضافة الموظفيين')): ?>
                        <button wire:click="create" type="button" class="btn btn-primary font-family-cairo fw-bold">
                            <?php echo e(__('إضافة موظف')); ?>

                            <i class="fas fa-plus me-2"></i>
                        </button>
                    <?php endif; ?>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control w-auto"
                        style="min-width:200px" placeholder="<?php echo e(__('بحث بالاسم...')); ?>">
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive" style="overflow-x: auto;">
                            <?php if (isset($component)) { $__componentOriginal6b7091aaeeb1e8e2000046e4bdf85bc7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b7091aaeeb1e8e2000046e4bdf85bc7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-export-actions','data' => ['tableId' => 'employee-table','filename' => 'employee-table','excelLabel' => 'تصدير Excel','pdfLabel' => 'تصدير PDF','printLabel' => 'طباعة']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-export-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['table-id' => 'employee-table','filename' => 'employee-table','excel-label' => 'تصدير Excel','pdf-label' => 'تصدير PDF','print-label' => 'طباعة']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6b7091aaeeb1e8e2000046e4bdf85bc7)): ?>
<?php $attributes = $__attributesOriginal6b7091aaeeb1e8e2000046e4bdf85bc7; ?>
<?php unset($__attributesOriginal6b7091aaeeb1e8e2000046e4bdf85bc7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6b7091aaeeb1e8e2000046e4bdf85bc7)): ?>
<?php $component = $__componentOriginal6b7091aaeeb1e8e2000046e4bdf85bc7; ?>
<?php unset($__componentOriginal6b7091aaeeb1e8e2000046e4bdf85bc7); ?>
<?php endif; ?>

                            <table id="employee-table" class="table table-striped text-center mb-0"
                                style="min-width: 1200px;">
                                <thead class="table-light align-middle">
                                    <tr>
                                        <th class="font-family-cairo fw-bold">#</th>
                                        <th class="font-family-cairo fw-bold"><?php echo e(__('الاسم')); ?></th>
                                        <th class="font-family-cairo fw-bold"><?php echo e(__('البريد الإلكتروني')); ?></th>
                                        <th class="font-family-cairo fw-bold"><?php echo e(__('رقم الهاتف')); ?></th>
                                        <th class="font-family-cairo fw-bold"><?php echo e(__('القسم')); ?></th>
                                        <th class="font-family-cairo fw-bold"><?php echo e(__('الوظيفة')); ?></th>
                                        <th class="font-family-cairo fw-bold"><?php echo e(__('الحالة')); ?></th>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['تعديل الموظفيين', 'حذف الموظفيين'])): ?>
                                            <th class="font-family-cairo fw-bold"><?php echo e(__('إجراءات')); ?></th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $this->employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td class="font-family-cairo fw-bold"><?php echo e($loop->iteration); ?></td>
                                            <td class="font-family-cairo fw-bold"><?php echo e($employee->name); ?></td>
                                            <td class="font-family-cairo fw-bold"><?php echo e($employee->email); ?></td>
                                            <td class="font-family-cairo fw-bold"><?php echo e($employee->phone); ?></td>
                                            <td class="font-family-cairo fw-bold">
                                                <?php echo e(optional($employee->department)->title); ?></td>
                                            <td class="font-family-cairo fw-bold"><?php echo e(optional($employee->job)->title); ?>

                                            </td>
                                            <td class="font-family-cairo fw-bold"><?php echo e($employee->status); ?></td>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['تعديل الموظفيين', 'حذف الموظفيين'])): ?>
                                                <td>
                                                    <button wire:click="view(<?php echo e($employee->id); ?>)"
                                                        class="btn btn-info btn-sm me-1" title="<?php echo e(__('عرض')); ?>">
                                                        <i class="las la-eye fa-lg"></i>
                                                    </button>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('تعديل الموظفيين')): ?>
                                                        <a wire:click="edit(<?php echo e($employee->id); ?>)"
                                                            class="btn btn-success btn-sm me-1" title="<?php echo e(__('تعديل')); ?>">
                                                            <i class="las la-edit fa-lg"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('حذف الموظفيين')): ?>
                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            wire:click="delete(<?php echo e($employee->id); ?>)"
                                                            onclick="confirm('هل أنت متأكد من حذف هذا الموظف؟') || event.stopImmediatePropagation()"
                                                            title="<?php echo e(__('حذف')); ?>">
                                                            <i class="las la-trash fa-lg"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="9" class="text-center">
                                                <div class="alert alert-info py-3 mb-0"
                                                    style="font-size: 1.2rem; font-weight: 500;">
                                                    <i class="las la-info-circle me-2"></i>
                                                    لا توجد بيانات
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </tbody>
                            </table>
                            <div class="mt-3">
                                <?php echo e($this->employees->links()); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal (Create/Edit) - Pure Alpine.js -->
        <template x-if="showModal">
            <div>
                <!-- Backdrop -->
                <div class="modal-backdrop fade show" @click="closeEmployeeModal()"></div>

                <!-- Modal -->
                <div class="modal fade show" style="display: block; z-index: 1056;" tabindex="-1" role="dialog"
                    @click.self="closeEmployeeModal()">
                    <div class="modal-dialog modal-fullscreen" role="document">
                        <div class="modal-content">
                            <!-- Modal Header -->
                            <div class="modal-header">
                                <h5 class="modal-title font-family-cairo fw-bold">
                                    <span
                                        x-text="isEdit ? '<?php echo e(__('تعديل موظف')); ?>' : '<?php echo e(__('إضافة موظف')); ?>'"></span>
                                </h5>
                                <button type="button" class="btn-close m-3" @click="closeEmployeeModal()"
                                    aria-label="إغلاق"></button>
                            </div>

                            <div class="modal-body">
                                <!--[if BLOCK]><![endif]--><?php if($errors->any()): ?>
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li><?php echo e($error); ?></li>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                        </ul>
                                    </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                                <form wire:submit.prevent="save" @keydown.enter.prevent="">
                                    <?php echo $__env->make('livewire.hr-management.employees.partials.employee-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </form>
                            </div>

                            <!-- Modal Footer -->
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-secondary btn-md"
                                    @click="closeEmployeeModal()">
                                    <?php echo e(__('إلغاء')); ?>

                                </button>
                                <button type="button" class="btn btn-primary btn-md" @click="$wire.save()">
                                    <span x-text="isEdit ? '<?php echo e(__('تحديث')); ?>' : '<?php echo e(__('حفظ')); ?>'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- View Employee Modal - Pure Alpine.js -->
        <template x-if="showViewModal">
            <div>
                <!-- Backdrop -->
                <div class="modal-backdrop fade show" @click="closeViewEmployeeModal()"></div>

                <!-- Modal -->
                <div class="modal fade show" style="display: block; z-index: 1056;" tabindex="-1" role="dialog"
                    @click.self="closeViewEmployeeModal()">
                    <div class="modal-dialog modal-fullscreen" role="document">
                        <div class="modal-content">
                            <!-- Modal Header -->
                            <div class="modal-header">
                                <h5 class="modal-title font-family-cairo fw-bold">
                                    <?php echo e(__('عرض تفاصيل الموظف')); ?>

                                </h5>
                                <button type="button" class="btn-close m-3" @click="closeViewEmployeeModal()"
                                    aria-label="إغلاق"></button>
                            </div>

                            <div class="modal-body">
                                <!--[if BLOCK]><![endif]--><?php if($viewEmployee): ?>
                                    <?php echo $__env->make('livewire.hr-management.employees.partials.employee-view', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                <?php else: ?>
                                    <div class="alert alert-danger">
                                        <strong>Error:</strong> No employee data loaded
                                    </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>

                            <!-- Modal Footer -->
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-secondary btn-md"
                                    @click="closeViewEmployeeModal()">
                                    <?php echo e(__('إغلاق')); ?>

                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php echo e($employee->id); ?> - <?php echo e($employee->image_url); ?> <br>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
</div>

<!-- Alpine.js Component Definition -->
<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('employeeManager', (config) => ({
                // State synced with Livewire
                showModal: config.showModal,
                showViewModal: config.showViewModal,
                kpiIds: config.kpiIds,
                kpiWeights: config.kpiWeights,
                selectedKpiId: config.selectedKpiId,
                currentImageUrl: config.currentImageUrl,
                isEdit: config.isEdit,

                // Local state
                kpis: config.kpis,
                activeTab: 'personal',
                notifications: [],
                imagePreview: null,
                showPassword: false,
                selectedFileName: '',
                selectedFileSize: '',
                isDragging: false,
                imageLoading: false,

                // KPI Search state
                kpiSearch: '',
                kpiSearchOpen: false,
                kpiSearchIndex: -1,

                // Computed
                get totalKpiWeight() {
                    let total = 0;
                    this.kpiIds.forEach(kpiId => {
                        total += parseInt(this.kpiWeights[kpiId]) || 0;
                    });
                    return total;
                },

                get weightStatus() {
                    if (this.totalKpiWeight === 100) return 'success';
                    if (this.totalKpiWeight > 100) return 'danger';
                    return 'warning';
                },

                get weightMessage() {
                    if (this.totalKpiWeight === 100) {
                        return 'ممتاز! تم اكتمال النسبة بنجاح. يمكنك الآن حفظ البيانات.';
                    } else if (this.totalKpiWeight > 100) {
                        return `المجموع الحالي ${this.totalKpiWeight}% أكبر من 100%. يرجى تقليل الأوزان.`;
                    } else {
                        return `المجموع الحالي ${this.totalKpiWeight}% أقل من 100%. يرجى إكمال الأوزان.`;
                    }
                },

                get availableKpis() {
                    return this.kpis.filter(kpi => !this.kpiIds.includes(kpi.id));
                },

                get filteredKpis() {
                    if (!this.kpiSearch) return this.availableKpis;
                    const search = this.kpiSearch.toLowerCase();
                    return this.availableKpis.filter(kpi =>
                        kpi.name.toLowerCase().includes(search) ||
                        (kpi.description && kpi.description.toLowerCase().includes(search))
                    );
                },

                // Methods
                init() {
                    // Listen for Livewire notifications
                    this.$wire.on('notify', (data) => {
                        this.addNotification(data.type, data.message);
                    });

                    // Listen for KPI added event to clear selection
                    this.$wire.on('kpiAdded', () => {
                        this.clearKpiSelection();
                        console.log('✅ KPI added, selection cleared');
                    });

                    // Watch for modal body overflow
                    this.$watch('showModal', (value) => {
                        document.body.classList.toggle('modal-open', value);
                    });

                    this.$watch('showViewModal', (value) => {
                        document.body.classList.toggle('modal-open', value);
                    });
                },

                addNotification(type, message) {
                    const id = Date.now();
                    this.notifications.push({
                        id,
                        type,
                        message
                    });
                    setTimeout(() => {
                        this.notifications = this.notifications.filter(n => n.id !== id);
                    }, 3000);
                },

                closeEmployeeModal() {
                    this.showModal = false;
                    this.resetImagePreview();
                    this.$wire.closeModal();
                },

                closeViewEmployeeModal() {
                    this.showViewModal = false;
                    this.$wire.closeView();
                },

                switchTab(tab) {
                    this.activeTab = tab;
                },

                // Image handling
                handleImageChange(event) {
                    const file = event.target.files[0];
                    if (file) {
                        // Validate file type
                        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                        if (!validTypes.includes(file.type)) {
                            this.addNotification('error',
                                'نوع الملف غير مدعوم. يرجى اختيار صورة (JPG, PNG, GIF)');
                            event.target.value = '';
                            return;
                        }

                        // Validate file size (2MB = 2 * 1024 * 1024 bytes)
                        const maxSize = 2 * 1024 * 1024;
                        if (file.size > maxSize) {
                            this.addNotification('error',
                                'حجم الصورة كبير جداً. الحد الأقصى 2 ميجابايت');
                            event.target.value = '';
                            return;
                        }

                        // Set file info
                        this.selectedFileName = file.name;
                        this.selectedFileSize = this.formatFileSize(file.size);

                        // Create preview
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.imagePreview = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                handleImageDrop(event) {
                    const file = event.dataTransfer.files[0];
                    if (file) {
                        // Validate file type
                        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                        if (!validTypes.includes(file.type)) {
                            this.addNotification('error',
                                'نوع الملف غير مدعوم. يرجى اختيار صورة (JPG, PNG, GIF)');
                            return;
                        }

                        // Validate file size
                        const maxSize = 2 * 1024 * 1024;
                        if (file.size > maxSize) {
                            this.addNotification('error',
                                'حجم الصورة كبير جداً. الحد الأقصى 2 ميجابايت');
                            return;
                        }

                        // Set file to input and trigger Livewire upload
                        const input = document.getElementById('employee-image-input');
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        input.files = dataTransfer.files;

                        // Trigger change event for Livewire
                        input.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));

                        // Set file info
                        this.selectedFileName = file.name;
                        this.selectedFileSize = this.formatFileSize(file.size);

                        // Create preview
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.imagePreview = e.target.result;
                        };
                        reader.readAsDataURL(file);

                        this.addNotification('success', 'تم اختيار الصورة بنجاح');
                    }
                },

                removeImage() {
                    // Clear preview and file info
                    this.imagePreview = null;
                    this.selectedFileName = '';
                    this.selectedFileSize = '';
                    this.currentImageUrl = null;

                    // Clear file input
                    const input = document.getElementById('employee-image-input');
                    if (input) {
                        input.value = '';
                    }

                    // Clear Livewire model
                    this.$wire.set('image', null);

                    this.addNotification('info', 'تم حذف الصورة');
                },

                formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
                },

                resetImagePreview() {
                    this.imagePreview = null;
                    this.selectedFileName = '';
                    this.selectedFileSize = '';
                    this.isDragging = false;
                    this.imageLoading = false;
                },

                togglePassword() {
                    this.showPassword = !this.showPassword;
                },

                // KPI Management
                getKpiName(kpiId) {
                    const kpi = this.kpis.find(k => k.id == kpiId);
                    return kpi ? kpi.name : '';
                },

                getKpiDescription(kpiId) {
                    const kpi = this.kpis.find(k => k.id == kpiId);
                    return kpi && kpi.description ? kpi.description.substring(0, 50) + '...' : '';
                },

                selectKpi(kpi) {
                    this.selectedKpiId = kpi.id;
                    this.kpiSearchOpen = false;
                    this.kpiSearch = '';
                    this.kpiSearchIndex = -1;
                },

                clearKpiSelection() {
                    this.selectedKpiId = '';
                    this.kpiSearch = '';
                },

                navigateKpiDown() {
                    if (this.kpiSearchIndex < this.filteredKpis.length - 1) {
                        this.kpiSearchIndex++;
                    }
                },

                navigateKpiUp() {
                    if (this.kpiSearchIndex > 0) {
                        this.kpiSearchIndex--;
                    }
                },

                selectCurrentKpi() {
                    if (this.kpiSearchIndex >= 0 && this.kpiSearchIndex < this.filteredKpis.length) {
                        this.selectKpi(this.filteredKpis[this.kpiSearchIndex]);
                    }
                }
            }));
        });
    </script>
<?php $__env->stopPush(); ?><?php /**PATH D:\laragon\www\massar1.02\resources\views\livewire/hr-management/employees/manage-employee.blade.php ENDPATH**/ ?>