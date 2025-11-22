<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use App\Models\City;
use App\Models\State;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component {
    use WithPagination;

    public string $title = '';
    public ?int $state_id = null;
    public ?int $cityId = null;
    public bool $showModal = false;
    public bool $isEdit = false;
    public string $search = '';

    /**
     * Get validation rules for city form.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:2|max:255|unique:cities,title,' . $this->cityId,
            'state_id' => 'required|exists:states,id',
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
     * Get all states for dropdown.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, State>
     */
    #[Computed]
    public function states()
    {
        return State::query()->orderBy('title')->get();
    }

    /**
     * Get filtered cities list.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, City>
     */
    #[Computed]
    public function cities()
    {
        return City::query()
            ->with('state')
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
        $this->reset(['title', 'state_id', 'cityId']);
        $this->isEdit = false;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    /**
     * Open edit modal and load city data.
     *
     * @param int $id
     */
    public function edit(int $id): void
    {
        $this->resetValidation();
        $city = City::findOrFail($id);
        $this->cityId = $city->id;
        $this->title = $city->title;
        $this->state_id = $city->state_id;
        $this->isEdit = true;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    /**
     * Save city (create or update).
     */
    public function save(): void
    {
        $validated = $this->validate();

        if ($this->isEdit) {
            City::findOrFail($this->cityId)->update($validated);
            session()->flash('success', __('City updated successfully.'));
        } else {
            City::create($validated);
            session()->flash('success', __('City created successfully.'));
        }

        $this->showModal = false;
        $this->dispatch('closeModal');
        $this->reset(['title', 'state_id', 'cityId', 'isEdit']);
    }

    /**
     * Delete city.
     *
     * @param int $id
     */
    public function delete(int $id): void
    {
        $city = City::findOrFail($id);
        $city->delete();
        session()->flash('success', __('City deleted successfully.'));
    }
}; ?>

<div class="cities-management" style="font-family: 'Cairo', sans-serif; direction: rtl;">
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
                    @can('create Cities')
                        <button wire:click="create" type="button" class="btn btn-primary font-family-cairo fw-bold">
                            <i class="fas fa-plus me-2"></i>
                            {{ __('Add City') }}
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
                                    <th class="font-family-cairo fw-bold">{{ __('State') }}</th>
                                    @canany(['edit Cities', 'delete Cities'])
                                        <th class="font-family-cairo fw-bold">{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->cities as $city)
                                    <tr>
                                        <td class="font-family-cairo fw-bold text-center">{{ $loop->iteration }}</td>
                                        <td class="font-family-cairo fw-bold text-center">{{ $city->title }}</td>
                                        <td class="font-family-cairo fw-bold text-center">{{ $city->state->title ?? '-' }}</td>
                                        @canany(['edit Cities', 'delete Cities'])
                                            <td class="font-family-cairo fw-bold text-center">
                                                <div class="btn-group" role="group">
                                                    @can('edit Cities')
                                                        <button type="button" 
                                                                wire:click="edit({{ $city->id }})"
                                                                class="btn btn-success btn-sm"
                                                                title="{{ __('Edit') }}">
                                                            <i class="las la-edit"></i>
                                                        </button>
                                                    @endcan
                                                    @can('delete Cities')
                                                        <button type="button" 
                                                                wire:click="delete({{ $city->id }})"
                                                                wire:confirm="{{ __('Are you sure you want to delete this city?') }}"
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
                                        <td colspan="{{ auth()->user()->canany(['edit Cities', 'delete Cities']) ? '4' : '3' }}" 
                                            class="text-center font-family-cairo fw-bold py-4">
                                            <div class="alert alert-info mb-0">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('No cities found.') }}
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
         id="cityModal" 
         tabindex="-1" 
         aria-labelledby="cityModalLabel"
         aria-hidden="true" 
         data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-family-cairo fw-bold" id="cityModalLabel">
                        {{ $isEdit ? __('Edit City') : __('Add City') }}
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
                            <label for="state_id" class="form-label font-family-cairo fw-bold">
                                {{ __('State') }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-control @error('state_id') is-invalid @enderror font-family-cairo fw-bold"
                                    id="state_id" 
                                    wire:model.blur="state_id" 
                                    required>
                                <option value="">{{ __('Select State') }}</option>
                                @foreach ($this->states as $state)
                                    <option value="{{ $state->id }}">{{ $state->title }}</option>
                                @endforeach
                            </select>
                            @error('state_id')
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
            const modalElement = document.getElementById('cityModal');

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
