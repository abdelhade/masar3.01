<?php

use Livewire\Volt\Component;
use App\Models\Department;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $departments;
    public $title = '';
    public $description = '';
    public $departmentId = null;
    public $showModal = false;
    public $isEdit = false;
    public $search = '';

    public function rules()
    {
        return [
            'title' => 'required|string|min:2|max:255|unique:departments,title,' . $this->departmentId,
            'description' => 'nullable|string|max:255',
        ];
    }

    public function mount()
    {
        $this->loadDepartments();
    }

    public function updatingSearch()
    {
        $this->resetPage();
        $this->loadDepartments();
    }

    public function loadDepartments()
    {
        $this->departments = Department::when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderByDesc('id')->get();
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['title', 'description', 'departmentId']);
        $this->isEdit = false;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    public function edit($id)
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

    public function save()
    {
        $validated = $this->validate();
        if ($this->isEdit) {
            Department::find($this->departmentId)->update($validated);
            session()->flash('success', __('Department updated successfully.'));
        } else {
            Department::create($validated);
            session()->flash('success', __('Department created successfully.'));
        }
        $this->showModal = false;
        $this->dispatch('closeModal');
        $this->loadDepartments();
    }

    public function delete($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();
        session()->flash('success', __('Department deleted successfully.'));
        $this->loadDepartments();
    }
}; ?>

<div style="font-family: 'Cairo', sans-serif; direction: rtl;">
    <div class="row">
        @if (session()->has('success'))
            <div class="alert alert-success" x-data="{ show: true }" x-show="show"
                x-init="setTimeout(() => show = false, 3000)">
                {{ session('success') }}
            </div>
        @endif
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    @can('إنشاء الأقسام')
                        <button wire:click="create" type="button" class="btn btn-primary font-family-cairo fw-bold">
                            {{ __('Add Department') }}
                            <i class="fas fa-plus me-2"></i>
                        </button>
                    @endcan
                    @can('البحث عن الاقسام')
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control w-auto"
                            style="min-width:200px" placeholder="{{ __('Search by title...') }}">
                    @endcan

                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th class="font-family-cairo fw-bold">#</th>
                                    <th class="font-family-cairo fw-bold">{{ __('title') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('Description') }}</th>
                                    @can('إجراء العمليات على الأقسام')
                                        <th class="font-family-cairo fw-bold">{{ __('Actions') }}</th>
                                    @endcan

                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($departments as $department)
                                    <tr>
                                        <td class="font-family-cairo fw-bold">{{ $loop->iteration }}</td>
                                        <td class="font-family-cairo fw-bold">{{ $department->title }}</td>
                                        <td class="font-family-cairo fw-bold">{{ $department->description }}</td>
                                        @can('إجراء العمليات على الأقسام')
                                            <td>
                                                @can('تعديل الأقسام')
                                                    <a wire:click="edit({{ $department->id }})" class="btn btn-success btn-sm">
                                                        <i class="las la-edit fa-lg"></i>
                                                    </a>
                                                @endcan
                                                @can('حذف الأقسام')
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        wire:click="delete({{ $department->id }})"
                                                        onclick="confirm('هل أنت متأكد من حذف هذا القسم؟') || event.stopImmediatePropagation()">
                                                        <i class="las la-trash fa-lg"></i>
                                                    </button>
                                                @endcan

                                            </td>
                                        @endcan

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center font-family-cairo fw-bold">
                                            {{ __('No departments found.') }}</td>
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
    <div class="modal fade" wire:ignore.self id="departmentModal" tabindex="-1" aria-labelledby="departmentModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
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
                            <label for="title" class="form-label font-family-cairo fw-bold">{{ __('title') }}</label>
                            <input type="text"
                                class="form-control @error('title') is-invalid @enderror font-family-cairo fw-bold"
                                id="title" wire:model.defer="title" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="description"
                                class="form-label font-family-cairo fw-bold">{{ __('Description') }}</label>
                            <input type="text"
                                class="form-control @error('description') is-invalid @enderror font-family-cairo fw-bold"
                                id="description" wire:model.defer="description">
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit"
                                class="btn btn-primary">{{ $isEdit ? __('Update') : __('Save') }}</button>
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


            modalElement.addEventListener('hidden.bs.modal', function () {
                modalInstance = null;
            });
        });
    </script>
</div>