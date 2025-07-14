<?php

use Livewire\Volt\Component;
use App\Models\Kpi;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;

new class extends Component {
    use WithPagination;

    public ?Kpi $editing = null;

    #[Rule('required|min:3|max:255|unique:kpis,name')]
    public string $name = '';

    #[Rule('nullable|max:1000')]
    public string $description = '';

    public string $search = '';

    public function with(): array
    {
        return [
            'kpis' => Kpi::where('name', 'like', '%' . $this->search . '%')
                ->orWhere('description', 'like', '%' . $this->search . '%')
                ->orderBy('created_at', 'desc')
                ->paginate(10),
        ];
    }

    public function save(): void
    {
        if ($this->editing) {
            // $this->authorize('update', $this->editing);

            $this->validate([
                'name' => 'required|min:3|max:255|unique:kpis,name,' . $this->editing->id,
                'description' => 'nullable|max:1000',
            ]);

            $this->editing->update([
                'name' => $this->name,
                'description' => $this->description,
            ]);

            $this->dispatch('kpi-updated');
        } else {
            // $this->authorize('create', Kpi::class);

            $validated = $this->validate();

            Kpi::create($validated);

            $this->dispatch('kpi-created');
        }

        $this->reset('editing', 'name', 'description');
    }

    public function edit(Kpi $kpi): void
    {
        // $this->authorize('update', $kpi);

        $this->editing = $kpi;
        $this->name = $kpi->name;
        $this->description = $kpi->description;
    }

    public function cancelEdit(): void
    {
        $this->reset('editing', 'name', 'description');
    }

    public function delete(Kpi $kpi): void
    {
        // $this->authorize('delete', $kpi);

        $kpi->delete();
        $this->dispatch('kpi-deleted');
    }
}; ?>

<div class="container-fluid">

    <!-- Search and Create Button -->
    <div class="row mb-3">
        <div class="col-lg-3">
            @can('البحث عن المعدلات')
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <div class="position-relative">
                        <input type="text" wire:model.live="search" class="form-control"
                            placeholder="{{ __('Search KPIs...') }}">

                    </div>
                </div>
            @endcan

            <div class="col-lg-6 mt-3">
                @can('إنشاء المعدلات')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kpiFormModal">
                        <i class="fas fa-plus me-2"></i>{{ __('Add New KPI') }}
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
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                    @can('إجراء العمليات على المعدلات')
                                        <th>{{ __('Actions') }}</th>
                                    @endcan

                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kpis as $kpi)
                                    <tr>
                                        <td>{{ $kpi->id }}</td>
                                        <td>{{ $kpi->name }}</td>
                                        <td>{{ $kpi->description }}</td>
                                        <td>{{ $kpi->created_at->format('Y-m-d') }}</td>
                                        @can('إجراء العمليات على المعدلات')
                                            <td>
                                                @can('تعديل المعدلات')
                                                    <button wire:click="edit({{ $kpi->id }})" class="btn btn-sm btn-info me-2"
                                                        data-bs-toggle="modal" data-bs-target="#kpiFormModal">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                @endcan
                                                @can('حذف المعدلات')
                                                    <button wire:click="delete({{ $kpi->id }})" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('{{ __('Are you sure you want to delete this KPI?') }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endcan
                                            </td>
                                        @endcan

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">{{ __('No KPIs found') }}</td>
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
                        <h5 class="modal-title" id="kpiFormModalLabel">
                            {{ $editing ? __('Edit KPI') : __('Create New KPI') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Name') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" wire:model="name"
                                class="form-control @error('name') is-invalid @enderror" id="name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('Description') }}</label>
                            <textarea wire:model="description"
                                class="form-control @error('description') is-invalid @enderror" id="description"
                                rows="3"></textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            {{ $editing ? __('Update') : __('Create') }}
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
        showToast('{{ __("KPI created successfully") }}');
    });

    $wire.on('kpi-updated', () => {
        bootstrap.Modal.getInstance(document.getElementById('kpiFormModal')).hide();
        showToast('{{ __("KPI updated successfully") }}');
    });

    $wire.on('kpi-deleted', () => {
        showToast('{{ __("KPI deleted successfully") }}');
    });

    function showToast(message) {
        const toast = new bootstrap.Toast(document.getElementById('successToast'));
        document.querySelector('#successToast .toast-body').textContent = message;
        toast.show();
    }
</script>
@endscript