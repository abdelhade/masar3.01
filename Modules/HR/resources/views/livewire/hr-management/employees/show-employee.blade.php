<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Modules\HR\Models\Employee;

new class extends Component {
    public int $employeeId;
    public string $activeTab = 'personal';

    public function mount(int $employeeId): void
    {
        // Authorization check
        abort_unless(auth()->user()->can('view Employees'), 403, __('hr.unauthorized_action'));

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
            'lineManager:id,name',
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

    /**
     * Load other details including covenants (only when other details tab is active)
     */
    #[Computed]
    public function employeeWithOtherDetails()
    {
        return Employee::with('covenants')->findOrFail($this->employeeId);
    }
}; ?>

<div style="font-family: 'Cairo', sans-serif; direction: rtl;">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 font-hold fw-bold">
                        <i class="fas fa-user me-2"></i>{{ __('hr.view_employee') }}
                    </h5>
                    <div class="d-flex gap-2">
                        @can('edit Employees')
                            <a href="{{ route('employees.edit', $employeeId) }}" class="btn btn-success">
                                <i class="fas fa-edit me-2"></i>{{ __('hr.edit') }}
                            </a>
                        @endcan
                        <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>{{ __('hr.back_to_list') }}
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if ($this->employee)
                        @include('hr::livewire.hr-management.employees.partials.view.employee-view')
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
    <div id="imageLightbox" class="position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
        style="z-index: 10000; background-color: rgba(0,0,0,0.9); display: none;"
        onclick="closeLightbox()">
        <button type="button" 
            class="btn-close btn-close-white position-absolute top-0 end-0 m-4"
            onclick="closeLightbox()"
            aria-label="{{ __('hr.close') }}"></button>
        <img id="lightboxImage" 
            src="" 
            class="img-fluid" 
            style="max-height: 90vh; max-width: 90vw;"
            onclick="event.stopPropagation()"
            alt="{{ __('hr.employee_image') }}">
    </div>
</div>

@push('styles')
@include('hr::livewire.hr-management.employees.partials.view.style')
@endpush

@push('scripts')
<script>
// Simple lightbox functions
function openLightbox(imageUrl) {
    document.getElementById('lightboxImage').src = imageUrl;
    document.getElementById('imageLightbox').style.display = 'flex';
}

function closeLightbox() {
    document.getElementById('imageLightbox').style.display = 'none';
}

// Close lightbox on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLightbox();
    }
});
</script>
@endpush

