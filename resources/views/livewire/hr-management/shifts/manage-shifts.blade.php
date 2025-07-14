<?php

use Livewire\Volt\Component;
use App\Models\Shift;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $shiftId = null;
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

    public function rules()
    {
        return [
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'shift_type' => 'required|in:morning,evening,night',
            'days' => 'required|array|min:1',
            'notes' => 'nullable|string',
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
        $this->shifts = Shift::when($this->search, fn($q) => $q->where('notes', 'like', "%{$this->search}%"))
            ->orderByDesc('id')->get();
    }

    public function updatedSearch()
    {
        $this->loadShifts();
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['start_time', 'end_time', 'shift_type', 'notes', 'days', 'shiftId']);
        $this->isEdit = false;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    public function edit($id)
    {
        $this->resetValidation();
        $shift = Shift::findOrFail($id);
        $this->shiftId = $shift->id;
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

<div class="container" style="direction: rtl; font-family: 'Cairo', sans-serif;">
    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
        @can('إنشاء الورديات')
            <button class="btn btn-primary" wire:click="create">
                <i class="las la-plus"></i> {{ __('Add Shift') }}
            </button>
        @endcan

    </div>

    @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
            class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                x-on:click="show = false"></button>
        </div>
    @endif
    @can('البحث عن الورديات')
        <div class="mb-3 col-md-4">
            <input type="text" class="form-control" style="font-family: 'Cairo', sans-serif;"
                placeholder="{{ __('Search by notes...') }}" wire:model.live="search">
        </div>
    @endcan


    <table class="table table-bordered table-striped text-center align-middle">
        <thead class="table-light">
            <tr>
                <th>{{ __('Start Time') }}</th>
                <th>{{ __('End Time') }}</th>
                <th>{{ __('Shift Type') }}</th>
                <th>{{ __('Days') }}</th>
                <th>{{ __('Notes') }}</th>
                @can('إجراء العمليات على الورديات')
                    <th>{{ __('Actions') }}</th>
                @endcan
            </tr>
        </thead>
        <tbody>
            @forelse ($shifts as $shift)
                <tr>
                    <td>{{ $shift->start_time }}</td>
                    <td>{{ $shift->end_time }}</td>
                    <td>{{ $shiftTypes[$shift->shift_type] ?? $shift->shift_type }}</td>
                    <td>
                        @foreach (json_decode($shift->days, true) as $day)
                            <span class="badge bg-info">{{ $weekDays[$day] ?? $day }}</span>
                        @endforeach
                    </td>
                    <td>{{ $shift->notes }}</td>
                    @can('إجراء العمليات على الورديات')
                        <td>
                            @can('تعديل الورديات')
                                <button class="btn btn-md btn-warning me-1" wire:click="edit({{ $shift->id }})">
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
                    <td colspan="6">{{ __('No shifts found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Modal -->
    <div class="modal fade @if($showModal) show d-block @endif" tabindex="-1" style="background: rgba(0,0,0,0.5);"
        @if($showModal) aria-modal="true" role="dialog" @endif>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEdit ? __('Edit Shift') : __('Add Shift') }}</h5>
                    <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Start Time') }}</label>
                            <input type="time" class="form-control" wire:model.defer="start_time" required>
                            @error('start_time') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('End Time') }}</label>
                            <input type="time" class="form-control" wire:model.defer="end_time" required>
                            @error('end_time') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Shift Type') }}</label>
                            <select class="form-select" wire:model.defer="shift_type" required>
                                <option value="">{{ __('Select shift type') }}</option>
                                @foreach($shiftTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('shift_type') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Days') }}</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($weekDays as $key => $label)
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="checkbox" id="day_{{ $key }}"
                                            value="{{ $key }}" wire:model.defer="days">
                                        <label class="form-check-label" for="day_{{ $key }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                            @error('days') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea class="form-control" wire:model.defer="notes"></textarea>
                            @error('notes') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            wire:click="$set('showModal', false)">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ $isEdit ? __('Update') : __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>