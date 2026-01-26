<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Computed;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    // Form fields
    #[Rule('required|exists:employees,id')]
    public ?int $employee_id = null;

    #[Rule('required|date')]
    public ?string $date = null;

    #[Rule('nullable|in:pending,approved,rejected')]
    public string $status = 'pending';

    // UI state
    public string $search = '';
    public ?int $filter_employee_id = null;
    public ?string $filter_status = null;
    public ?string $filter_start_date = null;
    public ?string $filter_end_date = null;
    public bool $showModal = false;
    public bool $showViewModal = false;
    public ?int $editingWorkPermissionId = null;
    public ?int $viewWorkPermissionId = null;
    public ?int $deleteId = null;
    public bool $showDeleteModal = false;
    public array $employeesList = [];

    protected array $queryString = ['search'];

    public function mount(): void
    {
        $this->resetForm();
        $this->loadEmployees();
    }

    public function loadEmployees(): void
    {
        $this->employeesList = \Modules\HR\Models\Employee::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn($emp) => ['id' => $emp->id, 'name' => $emp->name])
            ->toArray();
    }

    public function resetForm(): void
    {
        $this->reset([
            'employee_id', 'date', 'status',
            'editingWorkPermissionId'
        ]);
        $this->status = 'pending';
        $this->resetValidation();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterEmployeeId(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStartDate(): void
    {
        $this->resetPage();
    }

    public function updatingFilterEndDate(): void
    {
        $this->resetPage();
    }

    private function checkPermission(string $permission): void
    {
        abort_unless(auth()->user()->can($permission), 403, __('hr.unauthorized_action'));
    }

    public function create(): void
    {
        $this->checkPermission('create Work Permissions');
        $this->resetForm();
        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->editingWorkPermissionId) {
            $this->update();
        } else {
            $this->store();
        }
    }

    public function store(): void
    {
        $this->checkPermission('create Work Permissions');
        $this->validate();

        \Modules\HR\Models\WorkPermission::create([
            'employee_id' => $this->employee_id,
            'date' => $this->date,
            'status' => $this->status,
            'created_by' => Auth::id(),
        ]);

        $this->resetForm();
        $this->showModal = false;
        session()->flash('message', __('hr.work_permission_created_successfully'));
    }

    public function edit(int $id): void
    {
        $this->checkPermission('edit Work Permissions');
        $workPermission = \Modules\HR\Models\WorkPermission::with(['employee', 'created_by', 'updated_by', 'approved_by'])->findOrFail($id);
        $this->editingWorkPermissionId = $id;
        $this->employee_id = $workPermission->employee_id;
        $this->date = $workPermission->date->format('Y-m-d');
        $this->status = $workPermission->status;
        $this->showModal = true;
    }

    public function update(): void
    {
        $this->checkPermission('edit Work Permissions');
        
        if (!$this->editingWorkPermissionId) {
            session()->flash('error', __('hr.work_permission_not_found'));
            return;
        }
        
        $this->validate();

        $workPermission = \Modules\HR\Models\WorkPermission::findOrFail($this->editingWorkPermissionId);
        $workPermission->update([
            'employee_id' => $this->employee_id,
            'date' => $this->date,
            'status' => $this->status,
            'updated_by' => Auth::id(),
        ]);

        $this->resetForm();
        $this->showModal = false;
        session()->flash('message', __('hr.work_permission_updated_successfully'));
    }

    public function view(int $id): void
    {
        $this->checkPermission('view Work Permissions');
        $this->viewWorkPermissionId = $id;
        $this->showViewModal = true;
    }

    public function delete(int $id): void
    {
        $this->checkPermission('delete Work Permissions');
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function confirmDelete(): void
    {
        $this->checkPermission('delete Work Permissions');
        $workPermission = \Modules\HR\Models\WorkPermission::findOrFail($this->deleteId);
        $workPermission->delete();
        
        $this->showDeleteModal = false;
        $this->deleteId = null;
        session()->flash('message', __('hr.work_permission_deleted_successfully'));
    }

    public function approve(int $id): void
    {
        $this->checkPermission('Leave Approvals');
        $workPermission = \Modules\HR\Models\WorkPermission::findOrFail($id);
        $workPermission->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        
        session()->flash('message', __('hr.work_permission_approved_successfully'));
    }

    public function reject(int $id): void
    {
        $this->checkPermission('Leave Rejections');
        $workPermission = \Modules\HR\Models\WorkPermission::findOrFail($id);
        $workPermission->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        
        session()->flash('message', __('hr.work_permission_rejected_successfully'));
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filter_employee_id', 'filter_status', 'filter_start_date', 'filter_end_date']);
    }

    #[Computed]
    public function workPermissions(): LengthAwarePaginator
    {
        return \Modules\HR\Models\WorkPermission::query()
            ->with(['employee', 'created_by', 'approved_by'])
            ->when($this->search, function ($query) {
                $query->whereHas('employee', function ($empQuery) {
                    $empQuery->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filter_employee_id, function ($query) {
                $query->where('employee_id', $this->filter_employee_id);
            })
            ->when($this->filter_status, function ($query) {
                $query->where('status', $this->filter_status);
            })
            ->when($this->filter_start_date, function ($query) {
                $query->where('date', '>=', $this->filter_start_date);
            })
            ->when($this->filter_end_date, function ($query) {
                $query->where('date', '<=', $this->filter_end_date);
            })
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function viewWorkPermission(): ?\Modules\HR\Models\WorkPermission
    {
        if (!$this->viewWorkPermissionId) {
            return null;
        }
        return \Modules\HR\Models\WorkPermission::with(['employee', 'created_by', 'updated_by', 'approved_by'])->find($this->viewWorkPermissionId);
    }

    #[Computed]
    public function statusOptions(): array
    {
        return [
            'pending' => __('hr.pending'),
            'approved' => __('hr.approved'),
            'rejected' => __('hr.rejected'),
        ];
    }
}; ?>

<div>
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="las la-sign-out-alt text-primary" style="font-size: 2.5rem;"></i>
                </div>
                <div>
                    <h4 class="mb-1">{{ __('hr.work_permissions_management') }}</h4>
                    <p class="text-muted mb-0">{{ __('hr.manage_work_permissions') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-end">
            @can('create Work Permissions')
                <button wire:click="create" 
                        class="btn btn-main btn-lg shadow-sm"
                        wire:loading.attr="disabled"
                        wire:target="create">
                    <span wire:loading.remove wire:target="create">
                        <i class="las la-plus me-2"></i> {{ __('hr.add_new_work_permission') }}
                    </span>
                    <span wire:loading wire:target="create">
                        <i class="las la-spinner la-spin me-2"></i> {{ __('hr.opening') }}...
                    </span>
                </button>
            @endcan
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="position-relative">
                        <i class="las la-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control ps-5" placeholder="{{ __('hr.search_work_permissions') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select wire:model.live.debounce.500ms="filter_employee_id" class="form-select">
                        <option value="">{{ __('hr.all_employees') }}</option>
                        @foreach($employeesList ?? [] as $employee)
                            <option value="{{ $employee['id'] }}">{{ $employee['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live.debounce.500ms="filter_status" class="form-select">
                        <option value="">{{ __('hr.all_status') }}</option>
                        @foreach($this->statusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" 
                           class="form-control" 
                           wire:model.live.debounce.500ms="filter_start_date" 
                           placeholder="{{ __('hr.start_date') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" 
                           class="form-control" 
                           wire:model.live.debounce.500ms="filter_end_date" 
                           placeholder="{{ __('hr.end_date') }}">
                </div>
                <div class="col-md-1">
                    <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                        
                        <i class="las la-filter me-1"></i>{{ __('hr.clear') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Work Permissions Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="las la-list me-2"></i>{{ __('hr.work_permissions_list') }}
                    <span class="badge bg-primary ms-2">{{ $this->workPermissions->total() }}</span>
                </h6>
                <div class="d-flex align-items-center text-muted">
                    <small>{{ __('hr.showing') }} {{ $this->workPermissions->firstItem() ?? 0 }} {{ __('hr.to') }} {{ $this->workPermissions->lastItem() ?? 0 }} {{ __('hr.of') }} {{ $this->workPermissions->total() }} {{ __('hr.results') }}</small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 fw-semibold">{{ __('hr.employee') }}</th>
                            <th class="border-0 fw-semibold">{{ __('hr.date') }}</th>
                            <th class="border-0 fw-semibold">{{ __('hr.status') }}</th>
                            <th class="border-0 fw-semibold">{{ __('hr.approved_by') }}</th>
                            <th class="border-0 fw-semibold">{{ __('hr.created_at') }}</th>
                            @canany(['view Work Permissions', 'edit Work Permissions', 'delete Work Permissions', 'approve Work Permissions'])
                                <th class="border-0 fw-semibold text-center">{{ __('hr.actions') }}</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->workPermissions as $workPermission)
                            <tr wire:key="work-permission-{{ $workPermission->id }}">
                                <td>
                                    <span class="badge bg-info-subtle text-info">
                                        {{ $workPermission->employee->name }}
                                    </span>
                                </td>
                                <td>{{ $workPermission->date->format('Y-m-d') }}</td>
                                <td>
                                    @if($workPermission->status === 'approved')
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="las la-check-circle me-1"></i> {{ __('hr.approved') }}
                                        </span>
                                    @elseif($workPermission->status === 'rejected')
                                        <span class="badge bg-danger-subtle text-danger">
                                            <i class="las la-times-circle me-1"></i> {{ __('hr.rejected') }}
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning">
                                            <i class="las la-clock me-1"></i> {{ __('hr.pending') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($workPermission->approved_by)
                                        <small>{{ $workPermission->approved_by->name ?? __('hr.unknown') }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $workPermission->created_at->format('M d, Y') }}</td>
                                @canany(['view Work Permissions', 'edit Work Permissions', 'delete Work Permissions', 'approve Work Permissions'])
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            @can('view Work Permissions')
                                                <button wire:click="view({{ $workPermission->id }})" 
                                                        class="btn btn-sm btn-outline-info" 
                                                        title="{{ __('hr.view') }}"
                                                        wire:key="view-btn-{{ $workPermission->id }}"
                                                        wire:loading.attr="disabled"
                                                        wire:target="view({{ $workPermission->id }})">
                                                    <span wire:loading.remove wire:target="view({{ $workPermission->id }})">
                                                        <i class="las la-eye"></i>
                                                    </span>
                                                    <span wire:loading wire:target="view({{ $workPermission->id }})">
                                                        <i class="las la-spinner la-spin"></i>
                                                    </span>
                                                </button>
                                            @endcan
                                            @can('edit Work Permissions')
                                                @if($workPermission->status === 'pending')
                                                    <button wire:click="edit({{ $workPermission->id }})" 
                                                            class="btn btn-sm btn-outline-warning" 
                                                            title="{{ __('hr.edit') }}"
                                                            wire:key="edit-btn-{{ $workPermission->id }}"
                                                            wire:loading.attr="disabled"
                                                            wire:target="edit({{ $workPermission->id }})">
                                                        <span wire:loading.remove wire:target="edit({{ $workPermission->id }})">
                                                            <i class="las la-edit"></i>
                                                        </span>
                                                        <span wire:loading wire:target="edit({{ $workPermission->id }})">
                                                            <i class="las la-spinner la-spin"></i>
                                                        </span>
                                                    </button>
                                                @endif
                                            @endcan
                                            @can('approve Work Permissions')
                                                @if($workPermission->status === 'pending')
                                                    <button wire:click="approve({{ $workPermission->id }})" 
                                                            wire:confirm="{{ __('hr.confirm_approve_work_permission') }}"
                                                            class="btn btn-sm btn-outline-success" 
                                                            title="{{ __('hr.approve') }}"
                                                            wire:key="approve-btn-{{ $workPermission->id }}"
                                                            wire:loading.attr="disabled"
                                                            wire:target="approve({{ $workPermission->id }})">
                                                        <span wire:loading.remove wire:target="approve({{ $workPermission->id }})">
                                                            <i class="las la-check"></i>
                                                        </span>
                                                        <span wire:loading wire:target="approve({{ $workPermission->id }})">
                                                            <i class="las la-spinner la-spin"></i>
                                                        </span>
                                                    </button>
                                                    <button wire:click="reject({{ $workPermission->id }})" 
                                                            wire:confirm="{{ __('hr.confirm_reject_work_permission') }}"
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="{{ __('hr.reject') }}"
                                                            wire:key="reject-btn-{{ $workPermission->id }}"
                                                            wire:loading.attr="disabled"
                                                            wire:target="reject({{ $workPermission->id }})">
                                                        <span wire:loading.remove wire:target="reject({{ $workPermission->id }})">
                                                            <i class="las la-times"></i>
                                                        </span>
                                                        <span wire:loading wire:target="reject({{ $workPermission->id }})">
                                                            <i class="las la-spinner la-spin"></i>
                                                        </span>
                                                    </button>
                                                @endif
                                            @endcan
                                            @can('delete Work Permissions')
                                                <button wire:click="delete({{ $workPermission->id }})" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        title="{{ __('hr.delete') }}"
                                                        wire:key="delete-btn-{{ $workPermission->id }}"
                                                        wire:loading.attr="disabled"
                                                        wire:target="delete({{ $workPermission->id }})">
                                                    <span wire:loading.remove wire:target="delete({{ $workPermission->id }})">
                                                        <i class="las la-trash"></i>
                                                    </span>
                                                    <span wire:loading wire:target="delete({{ $workPermission->id }})">
                                                        <i class="las la-spinner la-spin"></i>
                                                    </span>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                @endcanany
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->canany(['view Work Permissions', 'edit Work Permissions', 'delete Work Permissions', 'approve Work Permissions']) ? 6 : 5 }}" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="las la-sign-out-alt text-muted" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3 mb-2">{{ __('hr.no_work_permissions_found') }}</h5>
                                        <p class="mb-3">{{ __('hr.start_by_adding_first_work_permission') }}</p>
                                        @can('create Work Permissions')
                                            <button wire:click="create" 
                                                    class="btn btn-main"
                                                    wire:loading.attr="disabled"
                                                    wire:target="create">
                                                <span wire:loading.remove wire:target="create">
                                                    <i class="las la-plus me-2"></i> {{ __('hr.add_first_work_permission') }}
                                                </span>
                                                <span wire:loading wire:target="create">
                                                    <i class="las la-spinner la-spin me-2"></i> {{ __('hr.opening') }}...
                                                </span>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($this->workPermissions->hasPages())
            <div class="card-footer bg-white border-0">
                {{ $this->workPermissions->links() }}
            </div>
        @endif
    </div>

    <!-- Create/Edit Modal - Bootstrap -->
    @if($showModal)
        <div class="modal fade show" 
             id="workPermissionModal" 
             tabindex="-1" 
             aria-labelledby="workPermissionModalLabel" 
             aria-hidden="false"
             style="display: block; z-index: 1055; background-color: rgba(0, 0, 0, 0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow-lg">
                    <div class="modal-header border-bottom p-3">
                        <h5 class="modal-title mb-0" id="workPermissionModalLabel">
                            @if($this->editingWorkPermissionId)
                                {{ __('hr.edit_work_permission') }}
                            @else
                                {{ __('hr.add_new_work_permission') }}
                            @endif
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label for="employee_id" class="form-label">{{ __('hr.employee') }} <span class="text-danger">*</span></label>
                            <select class="form-select @error('employee_id') is-invalid @enderror" 
                                    id="employee_id" 
                                    wire:model.blur="employee_id" 
                                    required>
                                <option value="">{{ __('hr.select_employee') }}</option>
                                @foreach($employeesList ?? [] as $employee)
                                    <option value="{{ $employee['id'] }}">{{ $employee['name'] }}</option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="date" class="form-label">{{ __('hr.date') }} <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control @error('date') is-invalid @enderror" 
                                   id="date" 
                                   wire:model.blur="date" 
                                   required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($editingWorkPermissionId)
                            <div class="mb-3">
                                <label for="status" class="form-label">{{ __('hr.status') }}</label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" 
                                        wire:model.blur="status">
                                    @foreach($this->statusOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                            <div class="modal-footer border-top p-3">
                                <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">{{ __('hr.cancel') }}</button>
                                <button type="submit" class="btn btn-main" wire:loading.attr="disabled" wire:target="save">
                                    <span wire:loading.remove wire:target="save">
                                        @if($this->editingWorkPermissionId)
                                            {{ __('hr.update') }}
                                        @else
                                            {{ __('hr.save') }}
                                        @endif
                                    </span>
                                    <span wire:loading wire:target="save">
                                        <i class="las la-spinner la-spin me-1"></i> {{ __('hr.saving') }}...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- View Modal - Bootstrap -->
    @if($showViewModal)
        <div class="modal fade show" 
             id="viewWorkPermissionModal" 
             tabindex="-1" 
             aria-labelledby="viewWorkPermissionModalLabel" 
             aria-hidden="false"
             style="display: block; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1055; overflow-x: hidden; overflow-y: auto;">
            <div class="modal-backdrop fade show" style="position: fixed; top: 0; left: 0; z-index: 1040; width: 100vw; height: 100vh; background-color: rgba(0, 0, 0, 0.5);"></div>
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="position: relative; z-index: 1055;">
                <div class="modal-content shadow-lg">
                    <div class="modal-header border-bottom p-3">
                        <h5 class="modal-title mb-0" id="viewWorkPermissionModalLabel">{{ __('hr.work_permission_details') }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showViewModal', false)" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        @if($viewWorkPermission = $this->viewWorkPermission)
                            @php($workPermission = $viewWorkPermission)
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>{{ __('hr.employee') }}:</strong>
                                    <p>
                                        <span class="badge bg-info-subtle text-info">{{ $workPermission->employee->name }}</span>
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>{{ __('hr.date') }}:</strong>
                                    <p>{{ $workPermission->date->format('Y-m-d') }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>{{ __('hr.status') }}:</strong>
                                    <p>
                                        @if($workPermission->status === 'approved')
                                            <span class="badge bg-success-subtle text-success">
                                                <i class="las la-check-circle me-1"></i> {{ __('hr.approved') }}
                                            </span>
                                        @elseif($workPermission->status === 'rejected')
                                            <span class="badge bg-danger-subtle text-danger">
                                                <i class="las la-times-circle me-1"></i> {{ __('hr.rejected') }}
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning">
                                                <i class="las la-clock me-1"></i> {{ __('hr.pending') }}
                                            </span>
                                        @endif
                                    </p>
                                </div>
                                @if($workPermission->created_by)
                                    <div class="col-md-6 mb-3">
                                        <strong>{{ __('hr.created_by') }}:</strong>
                                        <p>{{ $workPermission->created_by->name ?? __('hr.unknown') }}</p>
                                    </div>
                                @endif
                                @if($workPermission->approved_by)
                                    <div class="col-md-6 mb-3">
                                        <strong>{{ __('hr.approved_by') }}:</strong>
                                        <p>{{ $workPermission->approved_by->name ?? __('hr.unknown') }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong>{{ __('hr.approved_at') }}:</strong>
                                        <p>{{ $workPermission->approved_at->format('Y-m-d H:i') }}</p>
                                    </div>
                                @endif
                                <div class="col-md-6 mb-3">
                                    <strong>{{ __('hr.created_at') }}:</strong>
                                    <p>{{ $workPermission->created_at->format('Y-m-d H:i') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer border-top p-3">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showViewModal', false)">{{ __('hr.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal - Bootstrap -->
    @if($showDeleteModal)
        <div class="modal fade show" 
             id="deleteWorkPermissionModal" 
             tabindex="-1" 
             aria-labelledby="deleteWorkPermissionModalLabel" 
             aria-hidden="false"
             style="display: block; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1055; overflow-x: hidden; overflow-y: auto;">
            <div class="modal-backdrop fade show" style="position: fixed; top: 0; left: 0; z-index: 1040; width: 100vw; height: 100vh; background-color: rgba(0, 0, 0, 0.5);"></div>
            <div class="modal-dialog modal-dialog-centered" style="position: relative; z-index: 1055;">
                <div class="modal-content shadow-lg">
                    <div class="modal-header border-bottom p-3">
                        <h5 class="modal-title mb-0" id="deleteWorkPermissionModalLabel">{{ __('hr.confirm_delete_work_permission') }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showDeleteModal', false)" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p>{{ __('hr.confirm_delete_work_permission_message') }}</p>
                    </div>
                    <div class="modal-footer border-top p-3">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showDeleteModal', false)">{{ __('hr.cancel') }}</button>
                        <button type="button" class="btn btn-danger" wire:click="confirmDelete" wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                <i class="las la-trash me-1"></i> {{ __('hr.delete') }}
                            </span>
                            <span wire:loading>
                                <i class="las la-spinner la-spin me-1"></i> {{ __('hr.deleting') }}...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

