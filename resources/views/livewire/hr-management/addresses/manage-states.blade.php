<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use App\Models\State;
use App\Models\Country;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component {
    use WithPagination;

    public string $title = '';
    public ?int $country_id = null;
    public ?int $stateId = null;
    public bool $showModal = false;
    public bool $isEdit = false;
    public string $search = '';

    /**
     * Get validation rules for state form.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:2|max:255|unique:states,title,' . $this->stateId,
            'country_id' => 'required|exists:countries,id',
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
     * Get all countries for dropdown.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Country>
     */
    #[Computed]
    public function countries()
    {
        return Country::query()->orderBy('title')->get();
    }

    /**
     * Get filtered states list.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, State>
     */
    #[Computed]
    public function states()
    {
        return State::query()
            ->with('country')
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
        $this->reset(['title', 'country_id', 'stateId']);
        $this->isEdit = false;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    /**
     * Open edit modal and load state data.
     *
     * @param int $id
     */
    public function edit(int $id): void
    {
        $this->resetValidation();
        $state = State::findOrFail($id);
        $this->stateId = $state->id;
        $this->title = $state->title;
        $this->country_id = $state->country_id;
        $this->isEdit = true;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    /**
     * Save state (create or update).
     */
    public function save(): void
    {
        $validated = $this->validate();

        if ($this->isEdit) {
            State::findOrFail($this->stateId)->update($validated);
            session()->flash('success', __('State updated successfully.'));
        } else {
            State::create($validated);
            session()->flash('success', __('State created successfully.'));
        }

        $this->showModal = false;
        $this->dispatch('closeModal');
        $this->reset(['title', 'country_id', 'stateId', 'isEdit']);
    }

    /**
     * Delete state.
     *
     * @param int $id
     */
    public function delete(int $id): void
    {
        $state = State::findOrFail($id);
        $state->delete();
        session()->flash('success', __('State deleted successfully.'));
    }
}; ?>

<div class="states-management" style="font-family: 'Cairo', sans-serif; direction: rtl;">
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
                    @can('create States')
                        <button wire:click="create" type="button" class="btn btn-primary font-family-cairo fw-bold">
                            <i class="fas fa-plus me-2"></i>
                            {{ __('Add State') }}
                        </button>
                    @endcan
                    <input type="text" 
                           wire:model.live.debounce.300ms="search" 
                           class="form-control w-auto" 
                           style="min-width: 200px;" 
                           placeholder="{{ __('Search by name...') }}">
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th class="font-family-cairo fw-bold">#</th>
                                    <th class="font-family-cairo fw-bold">{{ __('Name') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('Country') }}</th>
                                    @canany(['edit States', 'delete States'])
                                        <th class="font-family-cairo fw-bold">{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->states as $state)
                                    <tr>
                                        <td class="font-family-cairo fw-bold text-center">{{ $loop->iteration }}</td>
                                        <td class="font-family-cairo fw-bold text-center">{{ $state->title }}</td>
                                        <td class="font-family-cairo fw-bold text-center">{{ $state->country->title ?? '-' }}</td>
                                        @canany(['edit States', 'delete States'])
                                            <td class="font-family-cairo fw-bold text-center">
                                                <div class="btn-group" role="group">
                                                    @can('edit States')
                                                        <button type="button" 
                                                                wire:click="edit({{ $state->id }})"
                                                                class="btn btn-success btn-sm"
                                                                title="{{ __('Edit') }}">
                                                            <i class="las la-edit"></i>
                                                        </button>
                                                    @endcan
                                                    @can('delete States')
                                                        <button type="button" 
                                                                wire:click="delete({{ $state->id }})"
                                                                wire:confirm="{{ __('Are you sure you want to delete this state?') }}"
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
                                        <td colspan="{{ auth()->user()->canany(['edit States', 'delete States']) ? '4' : '3' }}" 
                                            class="text-center font-family-cairo fw-bold py-4">
                                            <div class="alert alert-info mb-0">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('No states found.') }}
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
         id="stateModal" 
         tabindex="-1" 
         aria-labelledby="stateModalLabel"
         aria-hidden="true" 
         data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-family-cairo fw-bold" id="stateModalLabel">
                        {{ $isEdit ? __('Edit State') : __('Add State') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label for="title" class="form-label font-family-cairo fw-bold">
                                {{ __('Name') }} <span class="text-danger">*</span>
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
                            <label for="country_id" class="form-label font-family-cairo fw-bold">
                                {{ __('Country') }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-control @error('country_id') is-invalid @enderror font-family-cairo fw-bold"
                                    id="country_id" 
                                    wire:model.blur="country_id" 
                                    required>
                                <option value="">{{ __('Select Country') }}</option>
                                @foreach ($this->countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->title }}</option>
                                @endforeach
                            </select>
                            @error('country_id')
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
            const modalElement = document.getElementById('stateModal');

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
