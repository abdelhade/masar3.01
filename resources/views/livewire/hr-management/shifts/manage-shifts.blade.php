<?php

use Livewire\Volt\Component;
use App\Models\Shift;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $shiftId = null;
    public $name = '';
    public $start_time = '';
    public $end_time = '';
    public $shift_type = '';
    public $notes = '';
    public $days = [];
    public $showModal = false;
    public $isEdit = false;
    public $search = '';
    public $shifts;

    public $shiftTypes = [];
    public $weekDays = [];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:60|unique:shifts,name,' . $this->shiftId,
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'shift_type' => 'required|in:morning,evening,night',
            'days' => 'required|array|min:1',
            'notes' => 'nullable|string',
        ];
    }

    protected function messages()
    {
        return [
            'name.required' => __('The name field is required.'),
            'name.string' => __('The name field must be a string.'),
            'name.max' => __('The name field must be less than 60 characters.'),
            'name.unique' => __('The name field must be unique.'),
            'start_time.required' => __('The start time field is required.'),
            'end_time.required' => __('The end time field is required.'),
            'end_time.after' => __('The end time field must be after the start time field.'),
            'shift_type.required' => __('The shift type field is required.'),
            'shift_type.in' => __('The shift type field must be a valid shift type.'),
            'days.required' => __('The days field is required.'),
            'days.array' => __('The days field must be an array.'),
            'days.min' => __('The days field must have at least 1 day.'),
            'notes.string' => __('The notes field must be a string.'),
        ];
    }

    public function mount()
    {
        $this->shiftTypes = [
            'morning' => __('Morning'),
            'evening' => __('Evening'),
            'night' => __('Night'),
        ];
        $this->weekDays = [
            'saturday' => __('Saturday'),
            'sunday' => __('Sunday'),
            'monday' => __('Monday'),
            'tuesday' => __('Tuesday'),
            'wednesday' => __('Wednesday'),
            'thursday' => __('Thursday'),
            'friday' => __('Friday'),
        ];
        $this->loadShifts();
    }

    public function loadShifts()
    {
        $this->shifts = Shift::when($this->search, fn($q) => $q->where('notes', 'like', "%{$this->search}%"))->orderByDesc('id')->get();
    }

    public function updatedSearch()
    {
        $this->loadShifts();
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['start_time', 'end_time', 'shift_type', 'notes', 'days', 'shiftId', 'name']);
        $this->isEdit = false;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    public function edit($id)
    {
        $this->resetValidation();
        $shift = Shift::findOrFail($id);
        $this->shiftId = $shift->id;
        $this->name = $shift->name;
        $this->start_time = $shift->start_time;
        $this->end_time = $shift->end_time;
        $this->shift_type = $shift->shift_type;
        $this->notes = $shift->notes;
        $this->days = json_decode($shift->days, true);
        $this->isEdit = true;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    public function save()
    {
        $validated = $this->validate();
        $validated['days'] = json_encode($this->days);
        if ($this->isEdit) {
            Shift::find($this->shiftId)->update($validated);
            session()->flash('success', __('Shift updated successfully.'));
        } else {
            Shift::create($validated);
            session()->flash('success', __('Shift created successfully.'));
        }
        $this->showModal = false;
        $this->dispatch('closeModal');
        $this->resetPage();
        $this->loadShifts();
    }

    public function delete($id)
    {
        Shift::findOrFail($id)->delete();
        session()->flash('success', __('Shift deleted successfully.'));
        $this->resetPage();
        $this->loadShifts();
    }
}; ?>


<div class="" style="direction: rtl; font-family: 'Cairo', sans-serif;">
    <div class="d-flex justify-content-between align-items-center mb-2">
        @can('إضافة الورديات')
            <button class="btn btn-primary" wire:click="create">
                <i class="las la-plus"></i> {{ __('Add Shift') }}
            </button>
        @endcan
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
                placeholder="{{ __('Search by notes...') }}" wire:model.live="search">
        </div>
    </div>


    <div class="card ">

        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto;">

                <x-table-export-actions table-id="shifts-table" filename="shifts-table" excel-label="تصدير Excel"
                    pdf-label="تصدير PDF" print-label="طباعة" />

                <table id="shifts-table" class="table text-center table-striped mb-0" style="min-width: 1200px;">
                    <thead class="table-light text-center align-middle">
                        <tr>
                            <th class="font-family-cairo fw-bold">{{ __('Name') }}</th>
                            <th class="font-family-cairo fw-bold">{{ __('Start Time') }}</th>
                            <th class="font-family-cairo fw-bold">{{ __('End Time') }}</th>
                            <th class="font-family-cairo fw-bold">{{ __('Shift Type') }}</th>
                            <th class="font-family-cairo fw-bold">{{ __('Days') }}</th>
                            <th class="font-family-cairo fw-bold">{{ __('Notes') }}</th>
                            @canany(['حذف الورديات', 'تعديل الورديات'])
                                <th class="font-family-cairo fw-bold">{{ __('Actions') }}</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($shifts as $shift)
                            <tr>
                                <td class="font-family-cairo fw-bold">{{ $shift->name }}</td>
                                <td class="font-family-cairo fw-bold">{{ $shift->start_time }}</td>
                                <td class="font-family-cairo fw-bold">{{ $shift->end_time }}</td>
                                <td class="font-family-cairo fw-bold">
                                    {{ $shiftTypes[$shift->shift_type] ?? $shift->shift_type }}</td>
                                <td class="font-family-cairo fw-bold">
                                    @foreach (json_decode($shift->days, true) as $day)
                                        <span class="badge bg-info">{{ $weekDays[$day] ?? $day }}</span>
                                    @endforeach
                                </td>
                                <td class="font-family-cairo fw-bold">{{ $shift->notes }}</td>
                                @canany(['حذف الورديات', 'تعديل الورديات'])
                                    <td class="font-family-cairo fw-bold">
                                        @can('تعديل الورديات')
                                            <button class="btn btn-md btn-success me-1" wire:click="edit({{ $shift->id }})">
                                                <i class="las la-edit"></i>
                                            </button>
                                        @endcan
                                        @can('حذف الورديات')
                                            <button class="btn btn-md btn-danger" wire:click="delete({{ $shift->id }})"
                                                onclick="return confirm('{{ __('Are you sure you want to delete this shift?') }}')">
                                                <i class="las la-trash"></i>
                                            </button>
                                        @endcan

                                    </td>
                                @endcan

                            </tr>

                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="alert alert-info py-3 mb-0"
                                        style="font-size: 1.2rem; font-weight: 500;">
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
    </div>

    <!-- Modal -->

    <div class="modal fade @if ($showModal) show d-block @endif" tabindex="-1"
        style="background: rgba(0,0,0,0.5);" @if ($showModal) aria-modal="true" role="dialog" @endif>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEdit ? __('Edit Shift') : __('Add Shift') }}</h5>
                    <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Name') }}</label>
                            <input type="text" class="form-control" wire:model.defer="name" required>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Start Time') }}</label>
                            <input type="time" class="form-control" wire:model.defer="start_time" required>
                            @error('start_time')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('End Time') }}</label>
                            <input type="time" class="form-control" wire:model.defer="end_time" required>
                            @error('end_time')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Shift Type') }}</label>
                            <select class="form-select" wire:model.defer="shift_type" required>
                                <option value="">{{ __('Select shift type') }}</option>
                                @foreach ($shiftTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('shift_type')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Days') }}</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($weekDays as $key => $label)
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="checkbox" id="day_{{ $key }}"
                                            value="{{ $key }}" wire:model.defer="days">

                                        <label class="form-check-label"
                                            for="day_{{ $key }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                            @error('days')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea class="form-control" wire:model.defer="notes"></textarea>
                            @error('notes')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            wire:click="$set('showModal', false)">{{ __('Cancel') }}</button>

                        <button type="submit"
                            class="btn btn-primary">{{ $isEdit ? __('Update') : __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
