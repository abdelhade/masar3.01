<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use App\Models\EmployeesJob;
use Livewire\WithPagination;


new class extends Component {
    use WithPagination;

    public string $title = '';
    public ?string $description = null;
    public ?int $jobId = null;
    public bool $showModal = false;
    public bool $isEdit = false;
    public string $search = '';

    /**
     * Get validation rules for job form.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:2|max:255|unique:employees_jobs,title,' . $this->jobId,
            'description' => 'nullable|string|max:255',
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
     * Get filtered jobs list.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, EmployeesJob>
     */
    /**
     * Get filtered jobs list.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, EmployeesJob>
     */
    public function getJobsProperty()
    {
        return EmployeesJob::query()
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Open create modal and reset form.
     */
    public function create(): void
    {
        $this->resetValidation();
        $this->reset(['title', 'description', 'jobId']);
        $this->isEdit = false;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    /**
     * Open edit modal and load job data.
     *
     * @param int $id
     */
    public function edit(int $id): void
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

    /**
     * Save job (create or update).
     */
    public function save(): void
    {
        $validated = $this->validate();
        if ($this->isEdit) {
            EmployeesJob::findOrFail($this->jobId)->update($validated);
            session()->flash('success', __('hr.job_updated_successfully'));
        } else {
            EmployeesJob::create($validated);
            session()->flash('success', __('hr.job_created_successfully'));
        }
        $this->showModal = false;
        $this->dispatch('closeModal');
        $this->reset(['title', 'description', 'jobId', 'isEdit']);
    }

    /**
     * Delete job.
     *
     * @param int $id
     */
    public function delete(int $id): void
    {
        $job = EmployeesJob::findOrFail($id);
        $job->delete();
        session()->flash('success', __('hr.job_deleted_successfully'));
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
            <div class="d-flex justify-content-between align-items-center mb-2">
                @can('create Jobs')
                    <button wire:click="create" type="button" class="btn btn-primary font-family-cairo fw-bold">
                        <i class="fas fa-plus me-2"></i>
                        {{ __('hr.add_job') }}
                    </button>
                @endcan
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control w-auto"
                    style="min-width:200px" placeholder="{{ __('hr.search_by_title') }}">
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="jobs-table" filename="jobs-table" excel-label="تصدير Excel"
                            pdf-label="تصدير PDF" print-label="طباعة" />

                        <table id="jobs-table" class="table table-striped text-center mb-0" style="min-width: 1200px;">
                            <thead class="table-light align-middle">
                                <tr>

                                    <th class="font-family-cairo fw-bold">#</th>
                                    <th class="font-family-cairo fw-bold">{{ __('hr.title') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('hr.description') }}</th>
                                    @canany(['edit Jobs', 'delete Jobs'])
                                        <th class="font-family-cairo fw-bold">{{ __('hr.actions') }}</th>
                                    @endcanany


                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->jobs as $job)
                                    <tr>

                                        <td class="font-family-cairo fw-bold">{{ $loop->iteration }}</td>
                                        <td class="font-family-cairo fw-bold">{{ $job->title }}</td>
                                        <td class="font-family-cairo fw-bold">{{ $job->description ?? '-' }}</td>
                                        @canany(['edit Jobs', 'delete Jobs'])
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @can('edit Jobs')
                                                        <button type="button" wire:click="edit({{ $job->id }})"
                                                            class="btn btn-success btn-sm" title="{{ __('hr.edit') }}">
                                                            <i class="las la-edit fa-lg"></i>
                                                        </button>
                                                    @endcan
                                                    @can('delete Jobs')
                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            wire:click="delete({{ $job->id }})"
                                                            wire:confirm="{{ __('hr.confirm_delete_job') }}"
                                                            title="{{ __('hr.delete') }}">
                                                            <i class="las la-trash fa-lg"></i>
                                                        </button>
                                                    @endcan
                                                </div>
                                            </td>
                                        @endcanany


                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->canany(['edit Jobs', 'delete Jobs']) ? '4' : '3' }}"
                                            class="text-center font-family-cairo fw-bold py-4">
                                            <div class="alert alert-info mb-0">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('hr.no_jobs_found') }}
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
    <div class="modal fade" wire:ignore.self id="jobModal" tabindex="-1" aria-labelledby="jobModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-family-cairo fw-bold" id="jobModalLabel">
                        {{ $isEdit ? __('hr.edit_job') : __('hr.add_job') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label for="title" class="form-label font-family-cairo fw-bold">{{ __('hr.title') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control @error('title') is-invalid @enderror font-family-cairo fw-bold"
                                id="title" wire:model.blur="title" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="description"
                                class="form-label font-family-cairo fw-bold">{{ __('hr.description') }}</label>
                            <input type="text"
                                class="form-control @error('description') is-invalid @enderror font-family-cairo fw-bold"
                                id="description" wire:model.blur="description">
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">{{ __('hr.cancel') }}</button>
                            <button type="submit"
                                class="btn btn-primary">{{ $isEdit ? __('hr.update') : __('hr.save') }}</button>
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


            modalElement.addEventListener('hidden.bs.modal', function () {
                modalInstance = null;
            });
        });
    </script>
</div>