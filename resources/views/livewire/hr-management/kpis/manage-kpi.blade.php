<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use App\Models\Kpi;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Computed;

new class extends Component {
    use WithPagination;

    public ?Kpi $editing = null;

    #[Rule('required|min:3|max:255|unique:kpis,name')]
    public string $name = '';

    #[Rule('nullable|max:1000')]
    public string $description = '';

    public string $search = '';

    /**
     * Reset pagination when search changes.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Get filtered KPIs list.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    #[Computed]
    public function kpis()
    {
        return Kpi::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    /**
     * Save KPI (create or update).
     */
    public function save(): void
    {
        if ($this->editing) {
            $this->validate([
                'name' => 'required|min:3|max:255|unique:kpis,name,' . $this->editing->id,
                'description' => 'nullable|max:1000',
            ]);

            $this->editing->update([
                'name' => $this->name,
                'description' => $this->description,
            ]);

            session()->flash('success', __('hr.kpi_updated_successfully'));
            $this->dispatch('kpi-updated');
        } else {
            $validated = $this->validate();
            Kpi::create($validated);
            session()->flash('success', __('hr.kpi_created_successfully'));
            $this->dispatch('kpi-created');
        }

        $this->reset('editing', 'name', 'description');
    }

    /**
     * Open edit modal and load KPI data.
     *
     * @param Kpi $kpi
     */
    public function edit(Kpi $kpi): void
    {
        $this->editing = $kpi;
        $this->name = $kpi->name;
        $this->description = $kpi->description ?? '';
    }

    /**
     * Cancel edit and reset form.
     */
    public function cancelEdit(): void
    {
        $this->reset('editing', 'name', 'description');
    }

    /**
     * Delete KPI.
     *
     * @param Kpi $kpi
     */
    public function delete(Kpi $kpi): void
    {
        $kpi->delete();
        session()->flash('success', __('hr.kpi_deleted_successfully'));
        $this->dispatch('kpi-deleted');
    }
}; ?>

<div class="container-fluid">

    <!-- Search and Create Button -->

    <div class="row mb-3">
        <div class="col-lg-3">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <div class="position-relative">
                    <input type="text" 
                           wire:model.live.debounce.300ms="search" 
                           class="form-control font-family-cairo"
                           placeholder="{{ __('hr.search_kpis') }}">

                </div>
            </div>

            <div class="col-lg-6 mt-3">
                @can('create KPIS')
                    <button type="button" class="btn btn-primary font-family-cairo fw-bold" data-bs-toggle="modal" data-bs-target="#kpiFormModal">
                        <i class="fas fa-plus me-2"></i>{{ __('hr.add_new_kpi') }}
                    </button>
                @endcan
            </div>
        </div>


    </div>

    <!-- KPIs List -->
    <div class="row">
        <div class="col-12">

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-striped text-center mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">

                                <tr>
                                    <th class="font-family-cairo fw-bold">#</th>
                                    <th class="font-family-cairo fw-bold">{{ __('hr.kpi_name') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('hr.description') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('hr.created_at') }}</th>
                                    @canany(['edit KPIS', 'delete KPIS'])
                                        <th class="font-family-cairo fw-bold">{{ __('hr.actions') }}</th>
                                    @endcanany


                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kpis as $kpi)
                                    <tr>
                                        <td>{{ $kpi->id }}</td>
                                        <td>{{ $kpi->name }}</td>
                                        <td>{{ $kpi->description }}</td>
                                        <td>{{ $kpi->created_at->format('Y-m-d') }}</td>
                                        @canany(['edit KPIS', 'delete KPIS'])
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @can('edit KPIS')
                                                        <button type="button" 
                                                                wire:click="edit({{ $kpi->id }})"
                                                                class="btn btn-sm btn-success me-2" 
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#kpiFormModal"
                                                                title="{{ __('hr.edit') }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    @endcan
                                                    @can('delete KPIS')
                                                        <button type="button" 
                                                                wire:click="delete({{ $kpi->id }})"
                                                                wire:confirm="{{ __('hr.confirm_delete_kpi') }}"
                                                                class="btn btn-sm btn-danger"
                                                                title="{{ __('hr.delete') }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endcan
                                                </div>
                                            </td>
                                        @endcanany
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->canany(['edit KPIS', 'delete KPIS']) ? '5' : '4' }}" 
                                            class="text-center font-family-cairo fw-bold py-4">
                                            <div class="alert alert-info mb-0">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('hr.no_kpis_found') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $kpis->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Form Modal -->
    <div class="modal fade" id="kpiFormModal" tabindex="-1" aria-labelledby="kpiFormModalLabel" aria-hidden="true"
        wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit="save">
                    <div class="modal-header">
                        <h5 class="modal-title font-family-cairo fw-bold" id="kpiFormModalLabel">
                            {{ $editing ? __('hr.edit_kpi') : __('hr.add_new_kpi') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label font-family-cairo fw-bold">{{ __('hr.kpi_name') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" 
                                   wire:model.blur="name"
                                   class="form-control @error('name') is-invalid @enderror font-family-cairo" 
                                   id="name"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label font-family-cairo fw-bold">{{ __('hr.description') }}</label>
                            <textarea wire:model.blur="description" 
                                      class="form-control @error('description') is-invalid @enderror font-family-cairo" 
                                      id="description"
                                      rows="3"></textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary font-family-cairo"
                            data-bs-dismiss="modal">{{ __('hr.cancel') }}</button>
                        <button type="submit" class="btn btn-primary font-family-cairo">
                            {{ $editing ? __('hr.update') : __('hr.save') }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Success Alert -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    {{ session('success') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        $wire.on('kpi-created', () => {
            bootstrap.Modal.getInstance(document.getElementById('kpiFormModal')).hide();
            showToast('{{ __('hr.kpi_created_successfully') }}');
        });

        $wire.on('kpi-updated', () => {
            bootstrap.Modal.getInstance(document.getElementById('kpiFormModal')).hide();
            showToast('{{ __('hr.kpi_updated_successfully') }}');
        });

        $wire.on('kpi-deleted', () => {
            showToast('{{ __('hr.kpi_deleted_successfully') }}');
        });

        function showToast(message) {
            const toast = new bootstrap.Toast(document.getElementById('successToast'));
            document.querySelector('#successToast .toast-body').textContent = message;
            toast.show();
        }
    </script>
@endscript
