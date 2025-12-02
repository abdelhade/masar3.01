<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use App\Models\Errand;
use App\Models\Employee;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Computed;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

new class extends Component {
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    // Form fields
    #[Rule('nullable|string|max:255')]
    public ?string $title = null;

    #[Rule('nullable|string|max:2000')]
    public ?string $details = null;

    #[Rule('required|exists:employees,id')]
    public ?int $employee_id = null;

    #[Rule('required|date')]
    public ?string $start_date = null;

    #[Rule('required|date|after_or_equal:start_date')]
    public ?string $end_date = null;

    // UI state
    public string $search = '';
    public ?int $filter_employee_id = null;
    public ?string $filter_start_date = null;
    public ?string $filter_end_date = null;
    public bool $showModal = false;
    public bool $showViewModal = false;
    public ?int $editingErrandId = null;
    public ?int $viewErrandId = null;
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
        $this->employeesList = Employee::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn($emp) => ['id' => $emp->id, 'name' => $emp->name])
            ->toArray();
    }

    public function resetForm(): void
    {
        $this->reset([
            'title', 'details', 'employee_id', 'start_date', 'end_date',
            'editingErrandId'
        ]);
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

    public function updatingFilterStartDate(): void
    {
        $this->resetPage();
    }

    public function updatingFilterEndDate(): void
    {
        $this->resetPage();
    }

    /**
     * Check user permission before performing action.
     * 
     * Using private method to encapsulate permission checking logic.
     * Cannot use 'authorize' as name because it conflicts with Livewire's built-in authorize() method.
     */
    private function checkPermission(string $permission): void
    {
        abort_unless(auth()->user()->can($permission), 403, __('hr.unauthorized_action'));
    }

    public function create(): void
    {
        $this->checkPermission('create Errands');
        $this->resetForm();
        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->editingErrandId) {
            $this->update();
        } else {
            $this->store();
        }
    }

    public function store(): void
    {
        $this->checkPermission('create Errands');
        $this->validate();

        Errand::create([
            'title' => $this->title,
            'details' => $this->details,
            'employee_id' => $this->employee_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'created_by' => Auth::id(),
        ]);

        $this->resetForm();
        $this->showModal = false;
        session()->flash('message', __('hr.errand_created_successfully'));
    }

    public function edit(int $id): void
    {
        $this->checkPermission('edit Errands');
        $errand = Errand::with(['employee', 'created_by', 'updated_by', 'approved_by'])->findOrFail($id);
        $this->editingErrandId = $id;
        $this->title = $errand->title ?? '';
        $this->details = $errand->details ?? '';
        $this->employee_id = $errand->employee_id;
        $this->start_date = $errand->start_date->format('Y-m-d');
        $this->end_date = $errand->end_date->format('Y-m-d');
        $this->showModal = true;
    }

    public function update(): void
    {
        $this->checkPermission('edit Errands');
        
        if (!$this->editingErrandId) {
            session()->flash('error', __('hr.errand_not_found'));
            return;
        }
        
        $this->validate();

        $errand = Errand::findOrFail($this->editingErrandId);
        $errand->update([
            'title' => $this->title,
            'details' => $this->details,
            'employee_id' => $this->employee_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'updated_by' => Auth::id(),
        ]);

        $this->resetForm();
        $this->showModal = false;
        session()->flash('message', __('hr.errand_updated_successfully'));
    }

    public function view(int $id): void
    {
        $this->checkPermission('view Errands');
        $this->viewErrandId = $id;
        $this->showViewModal = true;
    }

    public function delete(int $id): void
    {
        $this->checkPermission('delete Errands');
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function confirmDelete(): void
    {
        $this->checkPermission('delete Errands');
        $errand = Errand::findOrFail($this->deleteId);
        $errand->delete();
        
        $this->showDeleteModal = false;
        $this->deleteId = null;
        session()->flash('message', __('hr.errand_deleted_successfully'));
    }

    public function approve(int $id): void
    {
        $this->checkPermission('Leave Approvals');
        $errand = Errand::findOrFail($id);
        $errand->update([
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        
        session()->flash('message', __('hr.errand_approved_successfully'));
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filter_employee_id', 'filter_start_date', 'filter_end_date']);
    }

    #[Computed]
    public function errands(): LengthAwarePaginator
    {
        return Errand::query()
            ->with(['employee', 'created_by', 'approved_by'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('details', 'like', '%' . $this->search . '%')
                      ->orWhereHas('employee', function ($empQuery) {
                          $empQuery->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->filter_employee_id, function ($query) {
                $query->where('employee_id', $this->filter_employee_id);
            })
            ->when($this->filter_start_date, function ($query) {
                $query->where('start_date', '>=', $this->filter_start_date);
            })
            ->when($this->filter_end_date, function ($query) {
                $query->where('end_date', '<=', $this->filter_end_date);
            })
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function viewErrand(): ?Errand
    {
        if (!$this->viewErrandId) {
            return null;
        }
        return Errand::with(['employee', 'created_by', 'updated_by', 'approved_by'])->find($this->viewErrandId);
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
                    <i class="las la-briefcase text-primary" style="font-size: 2.5rem;"></i>
                </div>
                <div>
                    <h4 class="mb-1">{{ __('hr.errands_management') }}</h4>
                    <p class="text-muted mb-0">{{ __('hr.manage_errands') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-end">
            @can('create Errands')
                <button wire:click="create" 
                        class="btn btn-main btn-lg shadow-sm"
                        wire:loading.attr="disabled"
                        wire:target="create">
                    <span wire:loading.remove wire:target="create">
                        <i class="las la-plus me-2"></i> {{ __('hr.add_new_errand') }}
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
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control ps-5" placeholder="{{ __('hr.search_errands') }}">
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
                <div class="col-md-2">
                    <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                        <i class="las la-filter me-1"></i> {{ __('hr.clear') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Errands Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="las la-list me-2"></i>{{ __('hr.errands_list') }}
                    <span class="badge bg-primary ms-2">{{ $this->errands->total() }}</span>
                </h6>
                <div class="d-flex align-items-center text-muted">
                    <small>{{ __('hr.showing') }} {{ $this->errands->firstItem() ?? 0 }} {{ __('hr.to') }} {{ $this->errands->lastItem() ?? 0 }} {{ __('hr.of') }} {{ $this->errands->total() }} {{ __('hr.results') }}</small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 fw-semibold">{{ __('hr.title') }}</th>
                            <th class="border-0 fw-semibold">{{ __('hr.employee') }}</th>
                            <th class="border-0 fw-semibold">{{ __('hr.start_date') }}</th>
                            <th class="border-0 fw-semibold">{{ __('hr.end_date') }}</th>
                            <th class="border-0 fw-semibold">{{ __('hr.duration') }}</th>
                            <th class="border-0 fw-semibold">{{ __('hr.status') }}</th>
                            <th class="border-0 fw-semibold">{{ __('hr.created_at') }}</th>
                            @canany(['view Errands', 'edit Errands', 'delete Errands', 'approve Errands'])
                                <th class="border-0 fw-semibold text-center">{{ __('hr.actions') }}</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->errands as $errand)
                            <tr wire:key="errand-{{ $errand->id }}">
                                <td>
                                    <div class="fw-semibold">{{ $errand->title ?? __('hr.no_title') }}</div>
                                    @if($errand->details)
                                        <small class="text-muted">{{ Str::limit($errand->details, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info">
                                        {{ $errand->employee->name }}
                                    </span>
                                </td>
                                <td>{{ $errand->start_date->format('Y-m-d') }}</td>
                                <td>{{ $errand->end_date->format('Y-m-d') }}</td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary">
                                        {{ $errand->start_date->diffInDays($errand->end_date) + 1 }} {{ __('hr.days') }}
                                    </span>
                                </td>
                                <td>
                                    @if($errand->approved_at)
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="las la-check-circle me-1"></i> {{ __('hr.approved') }}
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning">
                                            <i class="las la-clock me-1"></i> {{ __('hr.pending') }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $errand->created_at->format('M d, Y') }}</td>
                                @canany(['view Errands', 'edit Errands', 'delete Errands', 'approve Errands'])
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            @can('view Errands')
                                                <button wire:click="view({{ $errand->id }})" 
                                                        class="btn btn-sm btn-outline-info" 
                                                        title="{{ __('hr.view') }}"
                                                        wire:key="view-btn-{{ $errand->id }}"
                                                        wire:loading.attr="disabled"
                                                        wire:target="view({{ $errand->id }})">
                                                    <span wire:loading.remove wire:target="view({{ $errand->id }})">
                                                        <i class="las la-eye"></i>
                                                    </span>
                                                    <span wire:loading wire:target="view({{ $errand->id }})">
                                                        <i class="las la-spinner la-spin"></i>
                                                    </span>
                                                </button>
                                            @endcan
                                            @can('edit Errands')
                                                @if(!$errand->approved_at)
                                                    <button wire:click="edit({{ $errand->id }})" 
                                                            class="btn btn-sm btn-outline-warning" 
                                                            title="{{ __('hr.edit') }}"
                                                            wire:key="edit-btn-{{ $errand->id }}"
                                                            wire:loading.attr="disabled"
                                                            wire:target="edit({{ $errand->id }})">
                                                        <span wire:loading.remove wire:target="edit({{ $errand->id }})">
                                                            <i class="las la-edit"></i>
                                                        </span>
                                                        <span wire:loading wire:target="edit({{ $errand->id }})">
                                                            <i class="las la-spinner la-spin"></i>
                                                        </span>
                                                    </button>
                                                @endif
                                            @endcan
                                            @can('approve Errands')
                                                @if(!$errand->approved_at)
                                                    <button wire:click="approve({{ $errand->id }})" 
                                                            wire:confirm="{{ __('hr.confirm_approve_errand') }}"
                                                            class="btn btn-sm btn-outline-success" 
                                                            title="{{ __('hr.approve') }}"
                                                            wire:key="approve-btn-{{ $errand->id }}"
                                                            wire:loading.attr="disabled"
                                                            wire:target="approve({{ $errand->id }})">
                                                        <span wire:loading.remove wire:target="approve({{ $errand->id }})">
                                                            <i class="las la-check"></i>
                                                        </span>
                                                        <span wire:loading wire:target="approve({{ $errand->id }})">
                                                            <i class="las la-spinner la-spin"></i>
                                                        </span>
                                                    </button>
                                                @endif
                                            @endcan
                                            @can('delete Errands')
                                                <button wire:click="delete({{ $errand->id }})" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        title="{{ __('hr.delete') }}"
                                                        wire:key="delete-btn-{{ $errand->id }}"
                                                        wire:loading.attr="disabled"
                                                        wire:target="delete({{ $errand->id }})">
                                                    <span wire:loading.remove wire:target="delete({{ $errand->id }})">
                                                        <i class="las la-trash"></i>
                                                    </span>
                                                    <span wire:loading wire:target="delete({{ $errand->id }})">
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
                                <td colspan="{{ auth()->user()->canany(['view Errands', 'edit Errands', 'delete Errands', 'approve Errands']) ? 8 : 7 }}" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="las la-briefcase text-muted" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3 mb-2">{{ __('hr.no_errands_found') }}</h5>
                                        <p class="mb-3">{{ __('hr.start_by_adding_first_errand') }}</p>
                                        @can('create Errands')
                                            <button wire:click="create" 
                                                    class="btn btn-main"
                                                    wire:loading.attr="disabled"
                                                    wire:target="create">
                                                <span wire:loading.remove wire:target="create">
                                                    <i class="las la-plus me-2"></i> {{ __('hr.add_first_errand') }}
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
        @if($this->errands->hasPages())
            <div class="card-footer bg-white border-0">
                {{ $this->errands->links() }}
            </div>
        @endif
    </div>

    <!-- Create/Edit Modal - Alpine.js -->
    <div x-data="{ show: @entangle('showModal') }" 
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0" style="background-color: rgba(0, 0, 0, 0.5);"
             @click="show = false"></div>
        
        <!-- Modal -->
        <div class="d-flex align-items-center justify-content-center" style="min-height: 100vh; padding: 1rem;">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full relative z-10"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 @click.stop>
                <div class="modal-header border-bottom p-3">
                    <h5 class="modal-title mb-0">
                        @if($this->editingErrandId)
                            {{ __('hr.edit_errand') }}
                        @else
                            {{ __('hr.add_new_errand') }}
                        @endif
                    </h5>
                    <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
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
                            <label for="title" class="form-label">{{ __('hr.title') }}</label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   wire:model.blur="title">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="details" class="form-label">{{ __('hr.details') }}</label>
                            <textarea class="form-control @error('details') is-invalid @enderror" 
                                      id="details" 
                                      wire:model.blur="details" 
                                      rows="4"></textarea>
                            @error('details')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">{{ __('hr.start_date') }} <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('start_date') is-invalid @enderror" 
                                       id="start_date" 
                                       wire:model.blur="start_date" 
                                       required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">{{ __('hr.end_date') }} <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('end_date') is-invalid @enderror" 
                                       id="end_date" 
                                       wire:model.blur="end_date" 
                                       required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="modal-footer border-top p-3">
                            <button type="button" class="btn btn-secondary" @click="show = false">{{ __('hr.cancel') }}</button>
                            <button type="submit" class="btn btn-main" wire:loading.attr="disabled" wire:target="save">
                                <span wire:loading.remove wire:target="save">
                                    @if($this->editingErrandId)
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

    <!-- View Modal - Alpine.js -->
    <div x-data="{ show: @entangle('showViewModal') }" 
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0" style="background-color: rgba(0, 0, 0, 0.5);"
             @click="show = false"></div>
        
        <!-- Modal -->
        <div class="d-flex align-items-center justify-content-center" style="min-height: 100vh; padding: 1rem;">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full relative z-10"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 @click.stop>
                <div class="modal-header border-bottom p-3">
                    <h5 class="modal-title mb-0">{{ __('hr.errand_details') }}</h5>
                    <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    @if($viewErrand = $this->viewErrand)
                        @php($errand = $viewErrand)
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>{{ __('hr.employee') }}:</strong>
                                <p>
                                    <span class="badge bg-info-subtle text-info">{{ $errand->employee->name }}</span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>{{ __('hr.title') }}:</strong>
                                <p>{{ $errand->title ?? __('hr.no_title') }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>{{ __('hr.start_date') }}:</strong>
                                <p>{{ $errand->start_date->format('Y-m-d') }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>{{ __('hr.end_date') }}:</strong>
                                <p>{{ $errand->end_date->format('Y-m-d') }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>{{ __('hr.duration') }}:</strong>
                                <p>
                                    <span class="badge bg-secondary-subtle text-secondary">
                                        {{ $errand->start_date->diffInDays($errand->end_date) + 1 }} {{ __('hr.days') }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>{{ __('hr.status') }}:</strong>
                                <p>
                                    @if($errand->approved_at)
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="las la-check-circle me-1"></i> {{ __('hr.approved') }}
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning">
                                            <i class="las la-clock me-1"></i> {{ __('hr.pending') }}
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-12 mb-3">
                                <strong>{{ __('hr.details') }}:</strong>
                                <p>{{ $errand->details ?? __('hr.no_details') }}</p>
                            </div>
                            @if($errand->created_by)
                                <div class="col-md-6 mb-3">
                                    <strong>{{ __('hr.created_by') }}:</strong>
                                    <p>{{ $errand->created_by->name ?? __('hr.unknown') }}</p>
                                </div>
                            @endif
                            @if($errand->approved_by)
                                <div class="col-md-6 mb-3">
                                    <strong>{{ __('hr.approved_by') }}:</strong>
                                    <p>{{ $errand->approved_by->name ?? __('hr.unknown') }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>{{ __('hr.approved_at') }}:</strong>
                                    <p>{{ $errand->approved_at->format('Y-m-d H:i') }}</p>
                                </div>
                            @endif
                            <div class="col-md-6 mb-3">
                                <strong>{{ __('hr.created_at') }}:</strong>
                                <p>{{ $errand->created_at->format('Y-m-d H:i') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-secondary" @click="show = false">{{ __('hr.close') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal - Alpine.js -->
    <div x-data="{ show: @entangle('showDeleteModal') }" 
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;"
         wire:ignore>
        <!-- Backdrop -->
        <div class="fixed inset-0" style="background-color: rgba(0, 0, 0, 0.5);"
             @click="show = false"></div>
        
        <!-- Modal -->
        <div class="d-flex align-items-center justify-content-center" style="min-height: 100vh; padding: 1rem;">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full relative z-10"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 @click.stop>
                <div class="modal-header border-bottom p-3">
                    <h5 class="modal-title mb-0">{{ __('hr.confirm_delete_errand') }}</h5>
                    <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p>{{ __('hr.confirm_delete_errand_message') }}</p>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-secondary" @click="show = false">{{ __('hr.cancel') }}</button>
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

</div>

