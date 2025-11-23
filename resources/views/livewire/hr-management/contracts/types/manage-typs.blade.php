<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use App\Models\ContractType;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component {
    use WithPagination;

    public ?int $contractTypeId = null;
    public string $name = '';
    public ?string $description = null;
    public bool $showModal = false;
    public bool $isEdit = false;
    public string $search = '';

    /**
     * Get validation rules for contract type form.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
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
     * Get filtered contract types list.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    #[Computed]
    public function contractTypes()
    {
        return ContractType::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderByDesc('id')
            ->paginate(10);
    }

    /**
     * Open create modal and reset form.
     */
    public function create(): void
    {
        $this->resetValidation();
        $this->reset(['name', 'description', 'contractTypeId']);
        $this->isEdit = false;
        $this->showModal = true;
        $this->dispatch('show-create-modal');
    }

    /**
     * Open edit modal and load contract type data.
     *
     * @param int $id
     */
    public function edit(int $id): void
    {
        $this->resetValidation();
        $contractType = ContractType::findOrFail($id);
        $this->contractTypeId = $contractType->id;
        $this->name = $contractType->name;
        $this->description = $contractType->description;
        $this->isEdit = true;
        $this->showModal = true;
        $this->dispatch('show-edit-modal');
    }

    /**
     * Save contract type (create or update).
     */
    public function save(): void
    {
        $validated = $this->validate();

        if ($this->isEdit) {
            ContractType::findOrFail($this->contractTypeId)->update($validated);
            session()->flash('success', __('hr.contract_type_updated_successfully'));
        } else {
            ContractType::create($validated);
            session()->flash('success', __('hr.contract_type_created_successfully'));
        }

        $this->showModal = false;
        $this->reset(['name', 'description', 'contractTypeId', 'isEdit']);
        $this->dispatch('hide-modals');
    }

    /**
     * Delete contract type.
     *
     * @param int $id
     */
    public function delete(int $id): void
    {
        ContractType::findOrFail($id)->delete();
        session()->flash('success', __('hr.contract_type_deleted_successfully'));
    }

    /**
     * Close modal and reset form.
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['name', 'description', 'contractTypeId', 'isEdit']);
        $this->dispatch('hide-modals');
    }
}; ?>

<div class="container-fluid">
    @if (session()->has('success'))
        <div class="alert alert-success" x-data x-init="setTimeout(() => $el.remove(), 3000)">
            {{ session('success') }}
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-1">
        @can('create Contract Types')
            <button class="btn btn-primary font-family-cairo fw-bold" wire:click="create">
                <i class="las la-plus me-2"></i> {{ __('hr.add_contract_type') }}
            </button>
        @endcan
        <div class="mb-3">
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   class="form-control font-family-cairo" 
                   placeholder="{{ __('hr.search_by_name') }}">
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto;">
                <x-table-export-actions table-id="contracts-type-table" filename="contracts-type-table"
                    excel-label="تصدير Excel" pdf-label="تصدير PDF" print-label="طباعة" />

                <table id="contracts-type-table" class="table table-striped text-center mb-0"
                    style="min-width: 1200px;">
                    <thead class="table-light text-center align-middle">
                        <tr>
                            <th class="font-family-cairo fw-bold">#</th>
                            <th class="font-family-cairo fw-bold">{{ __('hr.title') }}</th>
                            <th class="font-family-cairo fw-bold">{{ __('hr.description') }}</th>
                            @canany(['edit Contract Types', 'delete Contract Types'])
                                <th class="font-family-cairo fw-bold">{{ __('hr.actions') }}</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->contractTypes as $index => $type)
                            <tr>
                                <td>{{ $this->contractTypes->firstItem() + $index }}</td>
                                <td>{{ $type->name }}</td>
                                <td>{{ $type->description }}</td>

                                @canany(['edit Contract Types', 'delete Contract Types'])
                                    <td>
                                        <div class="btn-group" role="group">
                                            @can('edit Contract Types')
                                                <button type="button" 
                                                        class="btn btn-success btn-sm me-1" 
                                                        wire:click="edit({{ $type->id }})"
                                                        title="{{ __('hr.edit') }}">
                                                    <i class="las la-edit"></i>
                                                </button>
                                            @endcan
                                            @can('delete Contract Types')
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm" 
                                                        wire:click="delete({{ $type->id }})"
                                                        wire:confirm="{{ __('hr.confirm_delete_contract_type') }}"
                                                        title="{{ __('hr.delete') }}">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                @endcanany
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->canany(['edit Contract Types', 'delete Contract Types']) ? '4' : '3' }}" 
                                    class="text-center font-family-cairo fw-bold py-4">
                                    <div class="alert alert-info mb-0">
                                        <i class="las la-info-circle me-2"></i>
                                        {{ __('hr.no_contract_types_found') }}
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $this->contractTypes->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <!-- Modal for Create/Edit -->
    <div wire:ignore.self class="modal fade" id="contractTypeModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <form wire:submit.prevent="save">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-family-cairo fw-bold">{{ $isEdit ? __('hr.edit_contract_type') : __('hr.add_contract_type') }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label font-family-cairo fw-bold">{{ __('hr.title') }} <span class="text-danger">*</span></label>
                            <input wire:model.blur="name" type="text" class="form-control font-family-cairo" id="name" required>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label font-family-cairo fw-bold">{{ __('hr.description') }}</label>
                            <textarea wire:model.blur="description" class="form-control font-family-cairo" id="description" rows="3"></textarea>
                            @error('description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary font-family-cairo" wire:click="closeModal">{{ __('hr.cancel') }}</button>
                        <button type="submit" class="btn btn-primary font-family-cairo">
                            {{ $isEdit ? __('hr.update') : __('hr.save') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@script
    <script>
        document.addEventListener('livewire:initialized', () => {
            const modal = new bootstrap.Modal(document.getElementById('contractTypeModal'));

            window.addEventListener('show-create-modal', event => {
                modal.show();
            });

            window.addEventListener('show-edit-modal', event => {
                modal.show();
            });

            window.addEventListener('hide-modals', event => {
                modal.hide();
            });
        });
    </script>
@endscript
