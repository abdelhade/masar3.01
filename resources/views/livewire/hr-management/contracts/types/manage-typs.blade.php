<?php

use Livewire\Volt\Component;
use App\Models\ContractType;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $contractTypeId = null;
    public $name = '';
    public $description = '';
    public $showModal = false;
    public $isEdit = false;
    public string $search = '';

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
    }

    public function with(): array
    {
        return [
            'contractTypes' => ContractType::where('name', 'like', '%' . $this->search . '%')
                ->orderByDesc('id')
                ->paginate(10),
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['name', 'description', 'contractTypeId']);
        $this->isEdit = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $contractType = ContractType::findOrFail($id);
        $this->contractTypeId = $contractType->id;
        $this->name = $contractType->name;
        $this->description = $contractType->description;
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->isEdit) {
            ContractType::find($this->contractTypeId)->update($validated);
            session()->flash('success', __('Contract type updated successfully.'));
        } else {
            ContractType::create($validated);
            session()->flash('success', __('Contract type created successfully.'));
        }

        $this->showModal = false;
        $this->reset(['name', 'description', 'contractTypeId']);
    }

    public function delete($id)
    {
        ContractType::findOrFail($id)->delete();
        session()->flash('success', __('Contract type deleted successfully.'));
    }
}; ?>


<div class="container" style="direction: rtl; font-family: 'Cairo', sans-serif;">
    @can('إنشاء أنواع العقود')
        <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
            <button class="btn btn-primary" wire:click="create">
                <i class="las la-plus"></i> {{ __('Add Contract Type') }}
            </button>
        </div>

        @if (session()->has('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                    x-on:click="show = false"></button>
            </div>
        @endif

        <div class="mb-3 col-md-4">
            <input type="text" class="form-control" style="font-family: 'Cairo', sans-serif;"
                placeholder="{{ __('Search by name...') }}" wire:model.live="search">
        </div>

    </div>

    <div class="card">
        <div class="card-body">


            <table class="table table-striped mb-0" style="min-width: 1200px;">
                <thead class="table-light text-center align-middle">

                    <tr>
                        <th class="font-family-cairo fw-bold font-14 text-center">{{ __('Name') }}</th>
                        <th class="font-family-cairo fw-bold font-14 text-center">{{ __('Description') }}</th>
                        <th class="font-family-cairo fw-bold font-14 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contractTypes as $type)
                        <tr>
                            <td class="font-family-cairo fw-bold font-14 text-center">{{ $type->name }}</td>
                            <td class="font-family-cairo fw-bold font-14 text-center">{{ $type->description }}</td>
                            <td class="font-family-cairo fw-bold font-14 text-center">
                                <button class="btn btn-success btn-icon-square-sm me-1"
                                    wire:click="edit({{ $type->id }})">
                                    <i class="las la-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-icon-square-sm"wire:click="delete({{ $type->id }})"
                                    wire:confirm="{{ __('Are you sure you want to delete this contract type?') }}">
                                    <i class="las la-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">
                                <div class="alert alert-info py-3 mb-0" style="font-size: 1.2rem; font-weight: 500;">
                                    <i class="las la-info-circle me-2"></i>
                                    لا توجد بيانات
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endcan


@if (session()->has('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
        class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
            x-on:click="show = false"></button>
    </div>
@endif
@can('البحث عن أنواع العقود')
    <div class="mb-3 col-md-4">
        <input type="text" class="form-control" style="font-family: 'Cairo', sans-serif;"
            placeholder="{{ __('Search by name...') }}" wire:model.live="search">
    </div>
@endcan


<table class="table table-bordered table-striped text-center align-middle">
    <thead class="table-light">
        <tr>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Description') }}</th>
            @can('إجراء العمليات على أنواع العقود')
                <th>{{ __('Actions') }}</th>
            @endcan

        </tr>
    </thead>
    <tbody>
        @forelse ($contractTypes as $type)
            <tr>
                <td>{{ $type->name }}</td>
                <td>{{ $type->description }}</td>
                @can('إجراء العمليات على أنواع العقود')
                    <td>
                        <button class="btn btn-md btn-warning me-1" wire:click="edit({{ $type->id }})">
                            <i class="las la-edit"></i>
                        </button>
                        <button class="btn btn-md btn-danger" wire:click="delete({{ $type->id }})"
                            wire:confirm="{{ __('Are you sure you want to delete this contract type?') }}">
                            <i class="las la-trash"></i>
                        </button>
                    </td>
                @endcan

            </tr>
        @empty
            <tr>
                <td colspan="3">{{ __('No contract types found.') }}</td>
            </tr>
        @endforelse
    </tbody>
</table>
>>>>>>> origin/main

<div class="mt-4">
    {{ $contractTypes->links() }}
</div>

<!-- Modal -->

<div class="modal fade @if ($showModal) show d-block @endif" tabindex="-1"
    style="background: rgba(0,0,0,0.5);" @if ($showModal) aria-modal="true" role="dialog" @endif>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $isEdit ? __('Edit Contract Type') : __('Add Contract Type') }}</h5>
                <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
            </div>
            <form wire:submit.prevent="save">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Name') }}</label>
                        <input type="text" class="form-control" wire:model="name" required>
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Description') }}</label>
                        <textarea class="form-control" wire:model="description"></textarea>
                        @error('description')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        wire:click="$set('showModal', false)">{{ __('Cancel') }}</button>
                    < <button type="submit" class="btn btn-primary">{{ $isEdit ? __('Update') : __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
