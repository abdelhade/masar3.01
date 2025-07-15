<?php

use Livewire\Volt\Component;
use App\Models\Country;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $countries;
    public $title = '';
    public $countryId = null;
    public $showModal = false;
    public $isEdit = false;
    public $search = '';

    public function rules()
    {
        return [
            'title' => 'required|string|min:2|max:255|unique:countries,title,' . $this->countryId,
        ];
    }

    public function mount()
    {
        $this->loadCountries();
    }

    public function updatingSearch()
    {
        $this->resetPage();
        $this->loadCountries();
    }

    public function loadCountries()
    {
        $this->countries = Country::when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))->orderByDesc('id')->get();
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['title', 'countryId']);
        $this->isEdit = false;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    public function edit($id)
    {
        $this->resetValidation();
        $country = Country::findOrFail($id);
        $this->countryId = $country->id;
        $this->title = $country->title;
        $this->isEdit = true;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    public function save()
    {
        $validated = $this->validate();
        if ($this->isEdit) {
            Country::find($this->countryId)->update($validated);
            session()->flash('success', __('تم تحديث الدولة بنجاح.'));
        } else {
            Country::create($validated);
            session()->flash('success', __('تم إضافة الدولة بنجاح.'));
        }
        $this->showModal = false;
        $this->dispatch('closeModal');
        $this->loadCountries();
    }

    public function delete($id)
    {
        $country = Country::findOrFail($id);
        $country->delete();
        session()->flash('success', __('تم حذف الدولة بنجاح.'));
        $this->loadCountries();
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
            <div class="m-2 d-flex justify-content-between align-items-center">
                <button wire:click="create" type="button" class="btn btn-primary font-family-cairo fw-bold">
                    {{ __('إضافة دولة') }}
                    <i class="fas fa-plus me-2"></i>
                </button>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control w-auto"
                    style="min-width:200px" placeholder="{{ __('بحث بالاسم...') }}">

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        @can('إنشاء الدول')
                            <button wire:click="create" type="button" class="btn btn-primary font-family-cairo fw-bold">
                                {{ __('إضافة دولة') }}
                                <i class="fas fa-plus me-2"></i>
                            </button>
                        @endcan
                        @can('البحث عن الدول')
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control w-auto"
                                style="min-width:200px" placeholder="{{ __('بحث بالاسم...') }}">
                        @endcan

                    </div>
                    <div class="card">

                        <div class="card-body">
                            <div class="table-responsive" style="overflow-x: auto;">
                                <table class="table table-striped mb-0" style="min-width: 1200px;">
                                    <thead class="table-light text-center align-middle">

                                        <tr>
                                            <th class="font-family-cairo fw-bold">#</th>
                                            <th class="font-family-cairo fw-bold">{{ __('الاسم') }}</th>
                                            @can('إجراء العمليات على الدول')
                                                <th class="font-family-cairo fw-bold">{{ __('الإجراءات') }}</th>
                                            @endcan

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($countries as $country)
                                            <tr>
                                                <td class="font-family-cairo fw-bold font-14 text-center">
                                                    {{ $loop->iteration }}</td>
                                                <td class="font-family-cairo fw-bold font-14 text-center">
                                                    {{ $country->title }}</td>
                                                <td class="font-family-cairo fw-bold font-14 text-center">
                                                    <a wire:click="edit({{ $country->id }})"
                                                        class="btn btn-success btn-icon-square-sm">
                                                        <i class="las la-edit fa-lg"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-danger btn-icon-square-sm"
                                                        wire:click="delete({{ $country->id }})"
                                                        onclick="confirm('هل أنت متأكد من حذف هذه الدولة؟') || event.stopImmediatePropagation()">
                                                        <i class="las la-trash fa-lg"></i>
                                                    </button>
                                                </td>

                                                <td class="font-family-cairo fw-bold">{{ $loop->iteration }}</td>
                                                <td class="font-family-cairo fw-bold">{{ $country->title }}</td>
                                                @can('إجراء العمليات على الدول')
                                                    <td>
                                                        @can('تعديل الدول')
                                                            <a wire:click="edit({{ $country->id }})"
                                                                class="btn btn-success btn-sm">
                                                                <i class="las la-edit fa-lg"></i>
                                                            </a>
                                                        @endcan
                                                        @can('حذف الدول')
                                                            <button type="button" class="btn btn-danger btn-sm"
                                                                wire:click="delete({{ $country->id }})"
                                                                onclick="confirm('هل أنت متأكد من حذف هذه الدولة؟') || event.stopImmediatePropagation()">
                                                                <i class="las la-trash fa-lg"></i>
                                                            </button>
                                                        @endcan
                                                    </td>
                                                @endcan

                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center">
                                                    <div class="alert alert-info py-3 mb-0"
                                                        style="font-size: 1.2rem; font-weight: 500;">
                                                        <i class="las la-info-circle me-2"></i>
                                                        لا توجد بيانات
                                                    </div>
                                                </td>

                                                <td colspan="3" class="text-center font-family-cairo fw-bold">
                                                    {{ __('لا توجد دول.') }}</td>
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
            <div class="modal fade" wire:ignore.self id="countryModal" tabindex="-1"
                aria-labelledby="countryModalLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title font-family-cairo fw-bold" id="countryModalLabel">
                                {{ $isEdit ? __('تعديل الدولة') : __('إضافة دولة') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form wire:submit.prevent="save">
                                <div class="mb-3">
                                    <label for="title"
                                        class="form-label font-family-cairo fw-bold">{{ __('الاسم') }}</label>
                                    <label for="title"
                                        class="form-label font-family-cairo fw-bold">{{ __('الاسم') }}</label>
                                    <input type="text"
                                        class="form-control @error('title') is-invalid @enderror font-family-cairo fw-bold"
                                        id="title" wire:model.defer="title" required>
                                    @error('title')
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
                    const modalElement = document.getElementById('countryModal');

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
