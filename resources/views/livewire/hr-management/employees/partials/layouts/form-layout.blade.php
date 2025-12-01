{{-- Shared Layout for Create/Edit Employee Forms --}}
@php
    $title = $title ?? __('hr.add_employee');
    $isEdit = $isEdit ?? false;
@endphp

<div style="font-family: 'Cairo', sans-serif; direction: rtl;" x-data="employeeFormManager({
    kpiIds: $wire.entangle('kpi_ids'),
    kpiWeights: $wire.entangle('kpi_weights'),
    selectedKpiId: $wire.entangle('selected_kpi_id'),
    leaveBalances: $wire.entangle('leave_balances'),
    selectedLeaveTypeId: $wire.entangle('selected_leave_type_id'),
    currentImageUrl: $wire.entangle('currentImageUrl'),
    kpis: @js($kpis),
    leaveTypes: @js($leaveTypes),
    isEdit: {{ $isEdit ? 'true' : 'false' }}
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
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 font-hold fw-bold">
                        <i class="fas {{ $isEdit ? 'fa-user-edit' : 'fa-user-plus' }} me-2"></i>{{ $title }}
                    </h5>
                    <a href="{{ route('employees.index') }}"
                       class="btn btn-secondary position-relative"
                       x-data="{ goingBack: false }"
                       @click.prevent="goingBack = true; window.location.href='{{ route('employees.index') }}';"
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
                    </a>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert" aria-live="polite">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                                <div class="flex-grow-1">
                                    <h6 class="alert-heading mb-2">
                                        {{ __('hr.validation_errors') }}
                                        <span class="badge bg-danger ms-2">{{ $errors->count() }}</span>
                                    </h6>
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('hr.close') }}"></button>
                            </div>
                        </div>
                    @endif

                    <form wire:submit.prevent="save">
                        @include('livewire.hr-management.employees.partials.form.employee-form')
                    </form>
                </div>

                <!-- Action Buttons - Outside Tabs -->
                <div class="card-footer bg-light border-top">
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('employees.index') }}"
                           class="btn btn-secondary btn-lg"
                           x-data="{ cancelling: false }"
                           @click.prevent="if (!cancelling) { cancelling = true; window.location.href='{{ route('employees.index') }}'; }"
                        >
                            <span x-show="!cancelling">
                                <i class="fas fa-times me-2"></i>{{ __('hr.cancel') }}
                            </span>
                            <span x-show="cancelling" style="display: none;">
                                <i class="fas fa-spinner fa-spin me-2"></i>{{ __('hr.cancelling') }}
                            </span>
                        </a>
                        <button type="button" 
                            class="btn btn-main btn-lg" 
                            wire:click="save"
                            wire:loading.attr="disabled" 
                            wire:loading.class="opacity-50 cursor-not-allowed">
                            <span wire:loading.remove wire:target="save">
                                <i class="fas fa-save me-2"></i>{{ $isEdit ? __('hr.update') : __('hr.save') }}
                            </span>
                            <span wire:loading wire:target="save">
                                <i class="fas fa-spinner fa-spin me-2"></i>{{ __('hr.saving') }}
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
    @vite('resources/js/components/employee-form-scripts.js')
@endpush

