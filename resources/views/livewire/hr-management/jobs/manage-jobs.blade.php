<?php

use Livewire\Volt\Component;
use App\Models\EmployeesJob;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $jobs;
    public $title = '';
    public $description = '';
    public $jobId = null;
    public $showModal = false;
    public $isEdit = false;
    public $search = '';

    public function rules()
    {
        return [
            'title' => 'required|string|min:2|max:255|unique:employees_jobs,title,' . $this->jobId,
            'description' => 'nullable|string|max:255',
        ];
    }

    public function mount()
    {
        $this->loadJobs();
    }

    public function updatingSearch()
    {
        $this->resetPage();
        $this->loadJobs();
    }

    public function loadJobs()
    {
        $this->jobs = EmployeesJob::when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderByDesc('id')->get();
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['title','description','jobId']);
        $this->isEdit = false;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    public function edit($id)
    {
        $this->resetValidation();
        $job = EmployeesJob::findOrFail($id);
        $this->jobId = $job->id;
        $this->title = $job->title;
        $this->description = $job->description;
        $this->isEdit = true;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    public function save()
    {
        $validated = $this->validate();
        if ($this->isEdit) {
            EmployeesJob::find($this->jobId)->update($validated);
            session()->flash('success', __('Job updated successfully.'));
        } else {
            EmployeesJob::create($validated);
            session()->flash('success', __('Job created successfully.'));
        }
        $this->showModal = false;
        $this->dispatch('closeModal');
        $this->loadJobs();
    }

    public function delete($id)
    {
        $job = EmployeesJob::findOrFail($id);
        $job->delete();
        session()->flash('success', __('Job deleted successfully.'));
        $this->loadJobs();
    }
}; ?>

<div style="font-family: 'Cairo', sans-serif; direction: rtl;">
    <div class="row">
        @if (session()->has('success'))
            <div class="alert alert-success" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                {{ session('success') }}
            </div>
        @endif
        <div class="col-lg-12">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    @can('إنشاء الوظائف')
                    <button wire:click="create" type="button" class="btn btn-primary font-family-cairo fw-bold">
                        {{ __('اضافة وظيفة') }}
                        <i class="fas fa-plus me-2"></i>
                    </button>
                    @endcan
                    @can('البحث عن الوظائف')
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control w-auto" style="min-width:200px" placeholder="{{ __('Search by title...') }}">
                    @endcan

                </div>
            <div class="card">

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>

                                    <th class="font-family-cairo fw-bold">#</th>
                                    <th class="font-family-cairo fw-bold">{{ __('title') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('Description') }}</th>
                                    @can('إجراء العمليات على الوظائف')
                                    <th class="font-family-cairo fw-bold">{{ __('Actions') }}</th>
                                    @endcan

                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($jobs as $job)
                                    <tr>

                                        <td class="font-family-cairo fw-bold">{{ $loop->iteration }}</td>
                                        <td class="font-family-cairo fw-bold">{{ $job->title }}</td>
                                        <td class="font-family-cairo fw-bold">{{ $job->description }}</td>
                                        @can('إجراء العمليات على الوظائف')
                                        <td>
                                            @can('تعديل الوظائف')
                                            <a wire:click="edit({{ $job->id }})" class="btn btn-success btn-sm">
                                                <i class="las la-edit fa-lg"></i>
                                                </a>
                                            @endcan
                                            @can('حذف الوظائف')
                                            <button type="button" class="btn btn-danger btn-sm"
                                                    wire:click="delete({{ $job->id }})"
                                                    onclick="confirm('هل أنت متأكد من حذف هذا الوظيفة؟') || event.stopImmediatePropagation()">
                                                <i class="las la-trash fa-lg"></i>
                                            </button>
                                            @endcan

                                        </td>
                                        @endcan

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center font-family-cairo fw-bold">{{ __('No jobs found.') }}</td>
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
    <div class="modal fade" wire:ignore.self id="jobModal" tabindex="-1" aria-labelledby="jobModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-family-cairo fw-bold" id="jobModalLabel">
                        {{ $isEdit ? __('Edit Job') : __('Add Job') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label for="title" class="form-label font-family-cairo fw-bold">{{ __('title') }}</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror font-family-cairo fw-bold" id="title" wire:model.defer="title" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label font-family-cairo fw-bold">{{ __('Description') }}</label>
                            <input type="text" class="form-control @error('description') is-invalid @enderror font-family-cairo fw-bold" id="description" wire:model.defer="description">
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary">{{ $isEdit ? __('Update') : __('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            let modalInstance = null;
            const modalElement = document.getElementById('jobModal');

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


            modalElement.addEventListener('hidden.bs.modal', function() {
                modalInstance = null;
            });
        });
    </script>
</div>
