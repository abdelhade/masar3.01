<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use App\Models\LeaveType;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component {
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public string $name = '';
    public string $code = '';
    public bool $is_paid = false;
    public bool $requires_approval = false;
    public int $max_per_request_days = 0;
    public float $accrual_rate_per_month = 0.0;
    public int $carry_over_limit_days = 0;
    public bool $showModal = false;
    public bool $isEdit = false;
    public string $search = '';
    public ?int $leaveTypeId = null;

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:leave_types,name,' . $this->leaveTypeId,
            'code' => 'required|string|max:255|unique:leave_types,code,' . $this->leaveTypeId,
            'is_paid' => 'required|boolean',
            'requires_approval' => 'required|boolean',
            'max_per_request_days' => 'required|integer|min:0',
            'accrual_rate_per_month' => 'required|numeric|min:0',
            'carry_over_limit_days' => 'required|integer|min:0',
        ];
    }

    /**
     * Reset pagination when search changes.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Get filtered leave types list.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, LeaveType>
     */
    #[Computed]
    public function leaveTypes()
    {
        return LeaveType::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderByDesc('id')
            ->get();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->isEdit = false;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->code = '';
        $this->is_paid = false;
        $this->requires_approval = false;
        $this->max_per_request_days = 0;
        $this->accrual_rate_per_month = 0;
        $this->carry_over_limit_days = 0;
        $this->leaveTypeId = null;
        $this->resetErrorBag();
    }

    /**
     * Open edit modal and load leave type data.
     *
     * @param int $id
     */
    public function edit(int $id): void
    {
        $leaveType = LeaveType::findOrFail($id);
        $this->leaveTypeId = $leaveType->id;
        $this->name = $leaveType->name;
        $this->code = $leaveType->code;
        $this->is_paid = (bool) $leaveType->is_paid;
        $this->requires_approval = (bool) $leaveType->requires_approval;
        $this->max_per_request_days = (int) $leaveType->max_per_request_days;
        $this->accrual_rate_per_month = (float) $leaveType->accrual_rate_per_month;
        $this->carry_over_limit_days = (int) $leaveType->carry_over_limit_days;
        $this->isEdit = true;
        $this->showModal = true;
    }

    /**
     * Save leave type (create or update).
     */
    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => $this->code,
            'is_paid' => $this->is_paid,
            'requires_approval' => $this->requires_approval,
            'max_per_request_days' => $this->max_per_request_days,
            'accrual_rate_per_month' => $this->accrual_rate_per_month,
            'carry_over_limit_days' => $this->carry_over_limit_days,
        ];

        if ($this->isEdit) {
            LeaveType::findOrFail($this->leaveTypeId)->update($data);
            session()->flash('message', __('hr.leave_type_updated_successfully'));
        } else {
            LeaveType::create($data);
            session()->flash('message', __('hr.leave_type_created_successfully'));
        }

        $this->closeModal();
    }

    /**
     * Delete leave type.
     *
     * @param int $id
     */
    public function delete(int $id): void
    {
        LeaveType::findOrFail($id)->delete();
        session()->flash('message', __('hr.leave_type_deleted_successfully'));
    }
    
}; ?>

<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0 font-family-cairo fw-bold">{{ __('hr.leave_types') }}</h2>
                    <p class="text-muted mb-0 font-family-cairo">{{ __('hr.leave_management') }}</p>
                </div>
                @can('create Leave Types')
                    <button type="button" class="btn btn-primary font-family-cairo fw-bold" wire:click="openModal">
                        <i class="fas fa-plus me-2"></i>{{ __('hr.add_leave_type') }}
                    </button>
                @endcan
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" 
                       class="form-control font-family-cairo" 
                       placeholder="{{ __('hr.search_by_name') }}" 
                       wire:model.live.debounce.300ms="search">
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Leave Types Table -->
    <div class="card">
        <div class="card-body">
            @if($this->leaveTypes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th class="font-family-cairo fw-bold">{{ __('hr.title') }}</th>
                                <th class="font-family-cairo fw-bold">{{ __('Code') }}</th>
                                <th class="font-family-cairo fw-bold">{{ __('Paid') }}</th>
                                <th class="font-family-cairo fw-bold">{{ __('Requires Approval') }}</th>
                                <th class="font-family-cairo fw-bold">{{ __('Max Per Request') }}</th>
                                <th class="font-family-cairo fw-bold">{{ __('Accrual Rate/Month') }}</th>
                                <th class="font-family-cairo fw-bold">{{ __('Carry Over Limit') }}</th>
                                @canany(['edit Leave Types', 'delete Leave Types'])
                                    <th class="font-family-cairo fw-bold">{{ __('hr.actions') }}</th>
                                @endcanany
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($this->leaveTypes as $leaveType)
                                <tr>
                                    <td>
                                        <strong>{{ $leaveType->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $leaveType->code }}</span>
                                    </td>
                                    <td>
                                        @if($leaveType->is_paid)
                                            <span class="badge bg-success font-family-cairo">{{ __('Yes') }}</span>
                                        @else
                                            <span class="badge bg-warning font-family-cairo">{{ __('No') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($leaveType->requires_approval)
                                            <span class="badge bg-info font-family-cairo">{{ __('Yes') }}</span>
                                        @else
                                            <span class="badge bg-secondary font-family-cairo">{{ __('No') }}</span>
                                        @endif
                                    </td>
                                    <td class="font-family-cairo fw-bold">{{ $leaveType->max_per_request_days }} {{ __('hr.days') }}</td>
                                    <td class="font-family-cairo fw-bold">{{ $leaveType->accrual_rate_per_month }} {{ __('hr.days') }}</td>
                                    <td class="font-family-cairo fw-bold">{{ $leaveType->carry_over_limit_days }} {{ __('hr.days') }}</td>
                                    @canany(['edit Leave Types', 'delete Leave Types'])
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('edit Leave Types')
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-primary font-family-cairo" 
                                                            wire:click="edit({{ $leaveType->id }})" 
                                                            title="{{ __('hr.edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                @endcan
                                                @can('delete Leave Types')
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger font-family-cairo" 
                                                            wire:click="delete({{ $leaveType->id }})"
                                                            wire:confirm="{{ __('hr.confirm_delete_leave_type') }}"
                                                            title="{{ __('hr.delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    @endcanany
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 font-family-cairo">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted fw-bold">{{ __('hr.no_leave_types_found') }}</h5>
                    <p class="text-muted">{{ __('hr.add_leave_type') }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal for Create/Edit -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-family-cairo fw-bold">
                            {{ $isEdit ? __('hr.edit_leave_type') : __('hr.add_leave_type') }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">اسم نوع الإجازة <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" wire:model.blur="name" placeholder="مثال: إجازة سنوية">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="code" class="form-label">كود نوع الإجازة <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                           id="code" wire:model.blur="code" placeholder="مثال: AL">
                                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="max_per_request_days" class="form-label">الحد الأقصى للطلب (أيام) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('max_per_request_days') is-invalid @enderror" 
                                           id="max_per_request_days" wire:model.blur="max_per_request_days" min="0">
                                    @error('max_per_request_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="accrual_rate_per_month" class="form-label">معدل التراكم/شهر (أيام) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('accrual_rate_per_month') is-invalid @enderror" 
                                           id="accrual_rate_per_month" wire:model.blur="accrual_rate_per_month" min="0">
                                    @error('accrual_rate_per_month') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="carry_over_limit_days" class="form-label">حد التحويل (أيام) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('carry_over_limit_days') is-invalid @enderror" 
                                           id="carry_over_limit_days" wire:model.blur="carry_over_limit_days" min="0">
                                    @error('carry_over_limit_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_paid" wire:model.blur="is_paid">
                                        <label class="form-check-label" for="is_paid">
                                            إجازة مدفوعة
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="requires_approval" wire:model.blur="requires_approval">
                                        <label class="form-check-label" for="requires_approval">
                                            تتطلب موافقة
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary font-family-cairo" wire:click="closeModal">{{ __('hr.cancel') }}</button>
                            <button type="submit" class="btn btn-primary font-family-cairo" wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    {{ $isEdit ? __('hr.update') : __('hr.save') }}
                                </span>
                                <span wire:loading>
                                    <i class="fas fa-spinner fa-spin"></i> {{ __('hr.saving') }}
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
