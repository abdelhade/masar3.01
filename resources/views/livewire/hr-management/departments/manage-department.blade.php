<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use App\Models\Department;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Collection;

new class extends Component {
    use WithPagination;

    public string $title = '';
    public ?int $parent_id = null;
    public ?int $director_id = null;
    public ?int $deputy_director_id = null;
    public ?string $description = null;
    public ?int $departmentId = null;
    public bool $showModal = false;
    public bool $showHierarchyModal = false;
    public bool $isEdit = false;
    public string $search = '';
    public Collection $employees;

    /**
     * Get validation rules for department form.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:2|max:255|unique:departments,title,' . $this->departmentId,
            'parent_id' => 'nullable|exists:departments,id',
            'director_id' => 'nullable|exists:employees,id',
            'deputy_director_id' => 'nullable|exists:employees,id',
            'description' => 'nullable|string|max:255',
        ];
    }

    /**
     * Initialize component on mount.
     */
    public function mount(): void
    {
        // Component initialized
        
    }

    /**
     * Get employees list.
     *
     * @param int|null $departmentId
     * @return Collection<int, Employee>
     */ 
    #[Computed]
    public function employees($departmentId = null)
    {
        return Employee::query()
            ->when($departmentId, fn($query) => $query->where('department_id', $departmentId))
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Reset pagination when search changes.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Get filtered departments list.
     *
     * @return Collection<int, Department>
     */
    #[Computed]
    public function departments()
    {
        return Department::query()
            ->when($this->search, fn($query) => $query->where('title', 'like', "%{$this->search}%"))
            ->orderByDesc('id')
            ->get();
    }

    // /**
    //  * Get employees list.
    //  *
    //  * @return Collection<int, Employee>
    //  */
    // #[Computed]
    // public function employees($departmentId = null)
    // {
    //     return Employee::query()
    //         ->when($departmentId, fn($query) => $query->where('department_id', $departmentId))
    //         ->orderByDesc('id')
    //         ->get();
    // }   

    /**
     * Get department with hierarchy for view.
     *
     * @return Department|null
     */
    #[Computed]
    public function departmentForHierarchy()
    {
        if (!$this->departmentId) {
            return null;
        }

        return Department::with(['parent', 'childrenRecursive'])
            ->find($this->departmentId);
    }

    /**
     * Open create modal and reset form.
     */
    public function create(): void
    {
        $this->resetValidation();
        $this->reset(['title', 'description', 'departmentId', 'parent_id']);
        $this->isEdit = false;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    /**
     * Open view hierarchy modal and load department data.
     *
     * @param int $id
     */
    public function viewHierarchy(int $id): void
    {
        $this->departmentId = $id;
        $this->showHierarchyModal = true;
        $this->dispatch('showHierarchyModal', departmentId: $id);
    }

    /**
     * Close hierarchy modal.
     */
    public function closeHierarchyModal(): void
    {
        $this->showHierarchyModal = false;
        $this->departmentId = null;
        $this->dispatch('closeHierarchyModal');
    }

    /**
     * Open edit modal and load department data.
     *
     * @param int $id
     */
    public function edit(int $id): void
    {
        $this->resetValidation();
        $department = Department::findOrFail($id);
        $this->departmentId = $department->id;
        $this->title = $department->title;
        $this->parent_id = $department->parent_id;
        $this->director_id = $department->director_id;
        $this->deputy_director_id = $department->deputy_director_id;
        $this->description = $department->description;
        $this->isEdit = true;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    /**
     * Save department (create or update).
     */
    public function save(): void
    {
        $validated = $this->validate();

        if ($this->isEdit) {
            Department::findOrFail($this->departmentId)->update($validated);
            session()->flash('success', __('Department updated successfully.'));
        } else {
            Department::create($validated);
            session()->flash('success', __('Department created successfully.'));
        }

        $this->showModal = false;
        $this->dispatch('closeModal');
        $this->reset(['title', 'description', 'departmentId', 'isEdit', 'parent_id', 'director_id', 'deputy_director_id']);
    }

    /**
     * Delete department.
     *
     * @param int $id
     */
    public function delete(int $id): void
    {
        $department = Department::findOrFail($id);
        $department->delete();
        session()->flash('success', __('Department deleted successfully.'));
    }
}; ?>

<div class="departments-management" style="font-family: 'Cairo', sans-serif; direction: rtl;">
    <div class="row">
        @if (session()->has('success'))
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    @can('create Departments')
                        <button wire:click="create" type="button" class="btn btn-main font-hold fw-bold">
                            <i class="fas fa-plus me-2"></i>
                            {{ __('Add Department') }}
                        </button>
                    @endcan
                    <input type="text" 
                           wire:model.live.debounce.300ms="search" 
                           class="form-control w-auto" 
                           style="min-width: 200px;" 
                           placeholder="{{ __('Search by title...') }}">
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th class="font-hold fw-bold">#</th>
                                    <th class="font-hold fw-bold">{{ __('Title') }}</th>
                                    <th class="font-hold fw-bold">{{ __('Parent') }}</th>
                                    <th class="font-hold fw-bold">{{ __('Director') }}</th>
                                    <th class="font-hold fw-bold">{{ __('Deputy Director') }}</th>
                                    <th class="font-hold fw-bold">{{ __('Description') }}</th>
                                    @canany(['edit Departments', 'delete Departments'])
                                        <th class="font-hold fw-bold">{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->departments as $department)
                                    <tr>
                                        <td class="font-hold fw-bold text-center">{{ $loop->iteration }}</td>
                                        <td class="font-hold fw-bold text-center">{{ $department->title }}</td>
                                        <td class="font-hold fw-bold text-center">{{ $department->parent ? $department->parent->title : '-' }}</td>
                                        <td class="font-hold fw-bold text-center">{{ $department->director ? $department->director->name : '-' }}</td>
                                        <td class="font-hold fw-bold text-center">{{ $department->deputyDirector ? $department->deputyDirector->name : '-' }}</td>
                                        <td class="font-hold fw-bold text-center">{{ $department->description ?? '-' }}</td>
                                        @canany(['edit Departments', 'delete Departments'])
                                            <td class="font-hold fw-bold text-center">
                                                <div class="btn-group" role="group">
                                                    <button type="button" 
                                                            wire:click="viewHierarchy({{ $department->id }})"
                                                            class="btn btn-warning btn-sm"
                                                            title="{{ __('View Hierarchy') }}">
                                                        <i class="las la-sitemap"></i>
                                                    </button>
                                                    @can('edit Departments')
                                                        <button type="button" 
                                                                wire:click="edit({{ $department->id }})"
                                                                class="btn btn-success btn-sm"
                                                                title="{{ __('Edit') }}">
                                                            <i class="las la-edit"></i>
                                                        </button>
                                                    @endcan
                                                    @can('delete Departments')
                                                        <button type="button" 
                                                                wire:click="delete({{ $department->id }})"
                                                                wire:confirm="{{ __('Are you sure you want to delete this department?') }}"
                                                                class="btn btn-danger btn-sm"
                                                                title="{{ __('Delete') }}">
                                                            <i class="las la-trash"></i>
                                                        </button>
                                                    @endcan
                                                </div>
                                            </td>
                                        @endcanany
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->canany(['edit Departments', 'delete Departments']) ? '4' : '3' }}" 
                                            class="text-center font-hold fw-bold py-4">
                                            <div class="alert alert-info mb-0">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('No departments found.') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal (Create/Edit) -->
    <div class="modal fade" 
         wire:ignore.self 
         id="departmentModal" 
         tabindex="-1" 
         aria-labelledby="departmentModalLabel"
         aria-hidden="true" 
         data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-hold fw-bold" id="departmentModalLabel">
                        {{ $isEdit ? __('Edit Department') : __('Add Department') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label for="title" class="form-label font-hold fw-bold">
                                {{ __('Title') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror font-hold fw-bold"
                                   id="title" 
                                   wire:model.blur="title" 
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="parent_id" class="form-label font-hold fw-bold">
                                {{ __('Parent') }}
                            </label>
                            <select class="form-control @error('parent_id') is-invalid @enderror font-hold fw-bold" id="parent_id" wire:model.blur="parent_id">
                                <option value="">{{ __('Select Parent') }}</option>
                                @foreach ($this->departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->title }}</option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="director_id" class="form-label font-hold fw-bold">
                                {{ __('Director') }}
                            </label>
                            <select class="form-control @error('director_id') is-invalid @enderror font-hold fw-bold" id="director_id" wire:model.blur="director_id">
                                <option value="">{{ __('Select Director') }}</option>
                                @forelse ($this->employees($departmentId) as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} - {{ $employee->job->title }}</option>
                                @empty
                                    <option value="">{{ __('No employees found in this department.') }}</option>
                                @endforelse
                            </select>
                            @error('director_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="deputy_director_id" class="form-label font-hold fw-bold">
                                {{ __('Deputy Director') }}
                            </label>
                            <select class="form-control @error('deputy_director_id') is-invalid @enderror font-hold fw-bold" id="deputy_director_id" wire:model.blur="deputy_director_id">
                                <option value="">{{ __('Select Deputy Director') }}</option>
                                @forelse ($this->employees($departmentId) as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} - {{ $employee->job->title }}</option>
                                @empty
                                    <option value="">{{ __('No employees found in this department.') }}</option>
                                @endforelse
                            </select>
                            @error('deputy_director_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label font-hold fw-bold">
                                {{ __('Description') }}
                            </label>
                            <input type="text"
                                   class="form-control @error('description') is-invalid @enderror font-hold fw-bold"
                                   id="description" 
                                   wire:model.blur="description">
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" 
                                    class="btn btn-secondary"
                                    data-bs-dismiss="modal">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="btn btn-main">
                                {{ $isEdit ? __('Update') : __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal (View for hierarchy) -->
    <div class="modal fade" 
         wire:ignore.self 
         id="hierarchyModal" 
         tabindex="-1" 
         aria-labelledby="hierarchyModalLabel" 
         aria-hidden="true" 
         data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-hold fw-bold" id="hierarchyModalLabel">
                        {{ __('Hierarchy') }}
                        {{ $this->departmentId ? Department::find($this->departmentId)->title : 'No department selected' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeHierarchyModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="hierarchyTree">
                        @if ($this->departmentForHierarchy)
                            @include('hr-management.departments.partials.hierarchy-tree', ['department' => $this->departmentForHierarchy])
                        @else
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('No department selected') }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeHierarchyModal">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('livewire:initialized', () => {
            let modalInstances = {
                departmentModal: null,
                hierarchyModal: null,
            };
            const modalElement = document.getElementById('departmentModal');
            const hierarchyModalElement = document.getElementById('hierarchyModal');
            Livewire.on('showHierarchyModal', (departmentId) => {
                if (hierarchyModalElement) {
                    if (!modalInstances.hierarchyModal) {
                        modalInstances.hierarchyModal = new bootstrap.Modal(hierarchyModalElement);
                    }
                    modalInstances.hierarchyModal.show();
                }
            });
            Livewire.on('closeHierarchyModal', () => {
                if (hierarchyModalElement && modalInstances.hierarchyModal) {
                    modalInstances.hierarchyModal.hide();
                }
            });

            Livewire.on('showModal', () => {
                if (!modalInstances.departmentModal) {
                    modalInstances.departmentModal = new bootstrap.Modal(modalElement);
                }
                modalInstances.departmentModal.show();
            });

            Livewire.on('closeModal', () => {
                if (modalInstances.departmentModal) {
                    modalInstances.departmentModal.hide();
                }
            });

            modalElement.addEventListener('hidden.bs.modal', () => {
                modalInstances.departmentModal = null;
                @this.call('$refresh');
            });
            hierarchyModalElement.addEventListener('hidden.bs.modal', () => {
                modalInstances.hierarchyModal = null;
                @this.call('$refresh');
            });
        });
    </script>
</div>
