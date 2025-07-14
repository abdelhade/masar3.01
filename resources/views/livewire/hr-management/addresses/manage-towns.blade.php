<?php

use Livewire\Volt\Component;
use App\Models\Town;
use App\Models\City;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $towns;
    public $name = '';
    public $city_id = '';
    public $townId = null;
    public $showModal = false;
    public $isEdit = false;
    public $search = '';
    public $cities = [];

    public function rules()
    {
        return [
            'name' => 'required|string|min:2|max:200|unique:towns,name,' . $this->townId,
            'city_id' => 'required|exists:cities,id',
        ];
    }

    public function mount()
    {
        $this->cities = City::all();
        $this->loadTowns();
    }

    public function updatingSearch()
    {
        $this->resetPage();
        $this->loadTowns();
    }

    public function loadTowns()
    {
        $this->towns = Town::with('city')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderByDesc('id')->get();
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['name', 'city_id', 'townId']);
        $this->isEdit = false;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    public function edit($id)
    {
        $this->resetValidation();
        $town = Town::findOrFail($id);
        $this->townId = $town->id;
        $this->name = $town->name;
        $this->city_id = $town->city_id;
        $this->isEdit = true;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    public function save()
    {
        $validated = $this->validate();
        if ($this->isEdit) {
            Town::find($this->townId)->update($validated);
            session()->flash('success', __('تم تحديث البلدة بنجاح.'));
        } else {
            Town::create($validated);
            session()->flash('success', __('تم إضافة البلدة بنجاح.'));
        }
        $this->showModal = false;
        $this->dispatch('closeModal');
        $this->loadTowns();
    }

    public function delete($id)
    {
        $town = Town::findOrFail($id);
        $town->delete();
        session()->flash('success', __('تم حذف البلدة بنجاح.'));
        $this->loadTowns();
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
                    @can('إنشاء المناطق')
                        <button wire:click="create" type="button" class="btn btn-primary font-family-cairo fw-bold">
                            {{ __('إضافة بلدة') }}
                            <i class="fas fa-plus me-2"></i>
                        </button>
                    @endcan
                    @can('البحث عن المناطق')
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control w-auto"
                            style="min-width:200px" placeholder="{{ __('بحث بالاسم...') }}">
                    @endcan

                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th class="font-family-cairo fw-bold">#</th>
                                    <th class="font-family-cairo fw-bold">{{ __('الاسم') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('المدينة') }}</th>
                                    @can('إجراء العمليات على المناطق')
                                        <th class="font-family-cairo fw-bold">{{ __('الإجراءات') }}</th>
                                    @endcan

                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($towns as $town)
                                    <tr>
                                        <td class="font-family-cairo fw-bold">{{ $loop->iteration }}</td>
                                        <td class="font-family-cairo fw-bold">{{ $town->title }}</td>
                                        <td class="font-family-cairo fw-bold">{{ $town->city->title ?? '' }}</td>
                                        @can('إجراء العمليات على المناطق')
                                            <td>
                                                @can('تعديل المناطق')
                                                    <a wire:click="edit({{ $town->id }})" class="btn btn-success btn-sm">
                                                        <i class="las la-edit fa-lg"></i>
                                                    </a>
                                                @endcan
                                                @can('حذف المناطق')
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        wire:click="delete({{ $town->id }})"
                                                        onclick="confirm('هل أنت متأكد من حذف هذه البلدة؟') || event.stopImmediatePropagation()">
                                                        <i class="las la-trash fa-lg"></i>
                                                    </button>
                                                @endcan

                                            </td>
                                        @endcan

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center font-family-cairo fw-bold">
                                            {{ __('لا توجد بلدات.') }}</td>
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
    <div class="modal fade" wire:ignore.self id="townModal" tabindex="-1" aria-labelledby="townModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-family-cairo fw-bold" id="townModalLabel">
                        {{ $isEdit ? __('تعديل البلدة') : __('إضافة بلدة') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label for="name" class="form-label font-family-cairo fw-bold">{{ __('الاسم') }}</label>
                            <input type="text"
                                class="form-control @error('name') is-invalid @enderror font-family-cairo fw-bold"
                                id="name" wire:model.defer="name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="city_id"
                                class="form-label font-family-cairo fw-bold">{{ __('المدينة') }}</label>
                            <select
                                class="form-control @error('city_id') is-invalid @enderror font-family-cairo fw-bold"
                                id="city_id" wire:model.defer="city_id" required>
                                <option value="">{{ __('اختر المدينة') }}</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->title }}</option>
                                @endforeach
                            </select>
                            @error('city_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">{{ __('إلغاء') }}</button>
                            <button type="submit"
                                class="btn btn-primary">{{ $isEdit ? __('تحديث') : __('حفظ') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            let modalInstance = null;
            const modalElement = document.getElementById('townModal');

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