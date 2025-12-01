<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Models\Employee;

new class extends Component {
    public int $employeeId;
    public string $activeTab = 'personal';

    public function mount(int $employeeId): void
    {
        // Authorization check
        abort_unless(auth()->user()->can('view Hr-Employees'), 403, __('hr.unauthorized_action'));

        $this->employeeId = $employeeId;
    }

    /**
     * Load basic employee data (always needed)
     */
    #[Computed]
    public function employee()
    {
        return Employee::with([
            'job:id,title',
            'department:id,title',
            'shift:id,name,start_time,end_time',
            'media'
        ])->findOrFail($this->employeeId);
    }

    /**
     * Load location data (only when location tab is active)
     */
    #[Computed]
    public function employeeWithLocation()
    {
        return Employee::with([
            'country:id,title',
            'city:id,title',
            'state:id,title',
            'town:id,title'
        ])->findOrFail($this->employeeId);
    }

    /**
     * Load KPI data (only when KPI tab is active)
     */
    #[Computed]
    public function employeeWithKpis()
    {
        return Employee::with('kpis')->findOrFail($this->employeeId);
    }

    /**
     * Load leave balances (only when leave balances tab is active)
     */
    #[Computed]
    public function employeeWithLeaveBalances()
    {
        return Employee::with('leaveBalances.leaveType')->findOrFail($this->employeeId);
    }

    /**
     * Load accounting data (only when accounting tab is active)
     */
    #[Computed]
    public function employeeWithAccount()
    {
        return Employee::with('account.haveParent')->findOrFail($this->employeeId);
    }
}; ?>

<div style="font-family: 'Cairo', sans-serif; direction: rtl;" x-data="{ 
    activeViewTab: 'personal', 
    isLightboxVisible: false, 
    previewImageUrl: '',
    loadedTabs: new Set(['personal']),
    switchViewTab(tab) {
        this.activeViewTab = tab;
        this.loadedTabs.add(tab);
        // Trigger Livewire to load data for this tab
        @this.set('activeTab', tab);
    }
}">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 font-hold fw-bold">
                        <i class="fas fa-user me-2"></i>{{ __('hr.view_employee') }}
                    </h5>
                    <div class="d-flex gap-2">
                        @can('edit Hr-Employees')
                            <a href="{{ route('employees.edit', $employeeId) }}"
                               class="btn btn-success"
                               x-data="{ loadingEdit: false }"
                               @click.prevent="if (!loadingEdit) { loadingEdit = true; window.location.href='{{ route('employees.edit', $employeeId) }}'; }">
                                <span x-show="!loadingEdit">
                                    <i class="fas fa-edit me-2"></i>{{ __('hr.edit') }}
                                </span>
                                <span x-show="loadingEdit" style="display: none;">
                                    <i class="fas fa-spinner fa-spin me-2"></i>{{ __('hr.loading') }}
                                </span>
                            </a>
                        @endcan
                        <a href="{{ route('employees.index') }}"
                           class="btn btn-secondary"
                           x-data="{ loadingBack: false }"
                           @click.prevent="if (!loadingBack) { loadingBack = true; window.location.href='{{ route('employees.index') }}'; }">
                            <span x-show="!loadingBack">
                                <i class="fas fa-arrow-left me-2"></i>{{ __('hr.back_to_list') }}
                            </span>
                            <span x-show="loadingBack" style="display: none;">
                                <i class="fas fa-spinner fa-spin me-2"></i>{{ __('hr.please_wait') }}
                            </span>
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if ($this->employee)
                        @include('livewire.hr-management.employees.partials.view.employee-view')
                    @else
                        <div class="alert alert-danger">
                            <strong>{{ __('hr.error') }}:</strong> {{ __('hr.no_employee_data_loaded') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Lightbox for Image Preview -->
    <template x-if="isLightboxVisible">
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
            style="z-index: 10000; background-color: rgba(0,0,0,0.9);"
            @click="isLightboxVisible = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <button type="button" 
                class="btn-close btn-close-white position-absolute top-0 end-0 m-4"
                @click="isLightboxVisible = false"
                aria-label="{{ __('hr.close') }}"></button>
            <img :src="previewImageUrl" 
                class="img-fluid" 
                style="max-height: 90vh; max-width: 90vw;"
                @click.stop
                alt="{{ __('hr.employee_image') }}">
        </div>
    </template>
</div>

@push('styles')
@include('livewire.hr-management.employees.partials.view.style')
@endpush

