{{-- Shared Layout for Create/Edit Employee Forms --}}
@php
    $title = $title ?? __('hr.add_employee');
    $isEdit = $isEdit ?? false;
@endphp


<div style="font-family: 'Cairo', sans-serif; direction: rtl;" wire:ignore.self>

    <!-- Notification Container -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999; margin-top: 60px;"
         x-data="{
             notifications: [],
             init() {
                 // Listen for Livewire notify events
                 if (window.Livewire) {
                     Livewire.on('notify', (data) => {
                         const notification = {
                             id: Date.now() + Math.random(),
                             type: data[0]?.type || 'info',
                             message: data[0]?.message || ''
                         };
                         this.notifications.push(notification);
                         
                         // Auto-remove after 5 seconds
                         setTimeout(() => {
                             const index = this.notifications.findIndex(n => n.id === notification.id);
                             if (index > -1) {
                                 this.notifications.splice(index, 1);
                             }
                         }, 5000);
                     });
                 }
             }
         }">
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
        <div class="col-lg-12">
            <div class="card" 
                 x-data="{ isRedirecting: false }"
                 @employee-redirect-started.window="isRedirecting = true">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 font-hold fw-bold">
                        <i class="fas {{ $isEdit ? 'fa-user-edit' : 'fa-user-plus' }} me-2"></i>{{ $title }}
                    </h5>
                    <a href="{{ route('employees.index') }}"
                       class="btn btn-secondary position-relative"
                       x-data="{ goingBack: false }"
                       wire:loading.attr="disabled" wire:target="save"
                       wire:loading.class="opacity-50 cursor-not-allowed pointer-events-none"
                       :disabled="isRedirecting"
                       :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': isRedirecting }"
                       @click.prevent="if (!goingBack && !isRedirecting) { goingBack = true; window.location.href='{{ route('employees.index') }}'; }"
                    >
                        <template x-if="!goingBack">
                            <span>
                                <i class="fas fa-arrow-left me-2"></i>{{ __('hr.back_to_list') }}
                            </span>
                        </template>
                        <template x-if="goingBack">
                            <span>
                                <i class="fas fa-spinner fa-spin me-2"></i>{{ __('hr.please_wait') }}
                            </span>
                        </template>
                        <span wire:loading wire:target="save" class="d-none">
                            <i class="fas fa-spinner fa-spin me-2"></i>{{ __('hr.please_wait') }}
                        </span>
                    </a>
                </div>

                <div class="card-body">
                    @php
                        // Get Livewire errors only (Livewire handles validation errors)
                        $livewireErrors = $this->getErrorBag();
                        $hasErrors = $livewireErrors->any();
                    @endphp
                    @if ($hasErrors)
                        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert" aria-live="polite" wire:key="validation-errors-{{ now()->timestamp }}">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                                <div class="flex-grow-1">
                                    <h6 class="alert-heading mb-2">
                                        {{ __('hr.validation_errors') }}
                                        <span class="badge bg-danger ms-2">
                                            {{ $livewireErrors->count() }}
                                        </span>
                                    </h6>
                                    <ul class="mb-0 ps-3">
                                        @foreach ($livewireErrors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('hr.close') }}"></button>
                            </div>
                        </div>
                    @endif

                    <form wire:submit.prevent="save" wire:key="employee-form-{{ $isEdit ? 'edit' : 'create' }}">
                        @include('livewire.hr-management.employees.partials.form.employee-form', ['isEdit' => $isEdit])
                    </form>
                </div>

                <!-- Action Buttons - Outside Tabs -->
                <div class="card-footer bg-light border-top" 
                     x-data="{ isRedirecting: false }"
                     @employee-redirect-started.window="isRedirecting = true">
                    <div class="d-flex justify-content-center gap-3" 
                         wire:loading.class="pointer-events-none" 
                         wire:target="save"
                         :class="{ 'pointer-events-none': isRedirecting }">
                        <a href="{{ route('employees.index') }}"
                           class="btn btn-secondary btn-lg"
                           x-data="{ cancelling: false }"
                           wire:loading.attr="disabled" wire:target="save"
                           wire:loading.class="opacity-50 cursor-not-allowed pointer-events-none"
                           :disabled="isRedirecting"
                           :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': isRedirecting }"
                           @click.prevent="if (!cancelling && !isRedirecting) { cancelling = true; window.location.href='{{ route('employees.index') }}'; }"
                        >
                            <span x-show="!cancelling">
                                <i class="fas fa-times me-2"></i>{{ __('hr.cancel') }}
                            </span>
                            <span x-show="cancelling" style="display: none;">
                                <i class="fas fa-spinner fa-spin me-2"></i>{{ __('hr.please_wait') }}
                            </span>
                            <span wire:loading wire:target="save" class="d-none">
                                <i class="fas fa-spinner fa-spin me-2"></i>{{ __('hr.please_wait') }}
                            </span>
                        </a>
                        <button type="button" 
                            class="btn btn-main btn-lg" 
                            wire:click="save"
                            wire:loading.attr="disabled" 
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            :disabled="isRedirecting"
                            :class="{ 'opacity-50 cursor-not-allowed': isRedirecting }">
                            <span wire:loading.remove wire:target="save" x-show="!isRedirecting">
                                <i class="fas fa-save me-2"></i>{{ $isEdit ? __('hr.update') : __('hr.save') }}
                            </span>
                            <span wire:loading wire:target="save">
                                <i class="fas fa-spinner fa-spin me-2"></i>{{ __('hr.saving') }}
                            </span>
                            <span x-show="isRedirecting" style="display: none;">
                                <i class="fas fa-spinner fa-spin me-2"></i>{{ __('hr.redirecting') }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
@include('livewire.hr-management.employees.partials.style.style')
@endpush

@push('scripts')
<script>
// Simple JavaScript for employee form - No Alpine needed
function handleEmployeeImageChange(input) {
    if (!input || !input.files || !input.files[0]) {
        return;
    }
    
    const file = input.files[0];
    const preview = document.getElementById('employee-image-preview');
    const fileInfo = document.getElementById('file-info');
    const fileName = document.getElementById('file-name');
    
    if (file && preview && fileInfo && fileName) {
        // Show file info
        fileName.textContent = file.name + ' (' + (file.size / 1024).toFixed(2) + ' KB)';
        fileInfo.style.display = 'block';
        
        // Preview image
        const reader = new FileReader();
        reader.onload = function(e) {
            if (preview) {
                preview.src = e.target.result;
            }
        };
        reader.readAsDataURL(file);
    }
}

// Toggle password visibility
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (!input || !icon) {
        return;
    }
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endpush

