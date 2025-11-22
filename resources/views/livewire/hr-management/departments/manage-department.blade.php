<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use App\Models\Department;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component {
    use WithPagination;

    public string $title = '';
    public ?string $description = null;
    public ?int $departmentId = null;
    public bool $showModal = false;
    public bool $isEdit = false;
    public string $search = '';

    /**
     * Get validation rules for department form.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:2|max:255|unique:departments,title,' . $this->departmentId,
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
     * Reset pagination when search changes.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Get filtered departments list.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Department>
     */
    #[Computed]
    public function departments()
    {
        return Department::query()
            ->when($this->search, fn($query) => $query->where('title', 'like', "%{$this->search}%"))
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Open create modal and reset form.
     */
    public function create(): void
    {
        $this->resetValidation();
        $this->reset(['title', 'description', 'departmentId']);
        $this->isEdit = false;
        $this->showModal = true;
        $this->dispatch('showModal');
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
        $this->reset(['title', 'description', 'departmentId', 'isEdit']);
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
                        <button wire:click="create" type="button" class="btn btn-primary font-family-cairo fw-bold">
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
                                    <th class="font-family-cairo fw-bold">#</th>
                                    <th class="font-family-cairo fw-bold">{{ __('Title') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('Description') }}</th>
                                    @canany(['edit Departments', 'delete Departments'])
                                        <th class="font-family-cairo fw-bold">{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->departments as $department)
                                    <tr>
                                        <td class="font-family-cairo fw-bold text-center">{{ $loop->iteration }}</td>
                                        <td class="font-family-cairo fw-bold text-center">{{ $department->title }}</td>
                                        <td class="font-family-cairo fw-bold text-center">{{ $department->description ?? '-' }}</td>
                                        @canany(['edit Departments', 'delete Departments'])
                                            <td class="font-family-cairo fw-bold text-center">
                                                <div class="btn-group" role="group">
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
                                            class="text-center font-family-cairo fw-bold py-4">
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
                    <h5 class="modal-title font-family-cairo fw-bold" id="departmentModalLabel">
                        {{ $isEdit ? __('Edit Department') : __('Add Department') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label for="title" class="form-label font-family-cairo fw-bold">
                                {{ __('Title') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror font-family-cairo fw-bold"
                                   id="title" 
                                   wire:model.blur="title" 
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label font-family-cairo fw-bold">
                                {{ __('Description') }}
                            </label>
                            <input type="text"
                                   class="form-control @error('description') is-invalid @enderror font-family-cairo fw-bold"
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
                            <button type="submit" class="btn btn-primary">
                                {{ $isEdit ? __('Update') : __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            let modalInstance = null;
            const modalElement = document.getElementById('departmentModal');

            if (!modalElement) {
                return;
            }

            Livewire.on('showModal', () => {
                if (!modalInstance) {
                    modalInstance = new bootstrap.Modal(modalElement);
                }
                modalInstance.show();
            });

            Livewire.on('closeModal', () => {
                if (modalInstance) {
                    modalInstance.hide();
                }
            });

            modalElement.addEventListener('hidden.bs.modal', () => {
                modalInstance = null;
                @this.call('$refresh');
            });
        });
    </script>
</div>
