<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use App\Models\Shift;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public ?int $shiftId = null;
    public string $name = '';
    public string $start_time = '';
    public string $end_time = '';
    public ?string $beginning_check_in = null;
    public ?string $ending_check_in = null;
    public ?string $beginning_check_out = null;
    public ?string $ending_check_out = null;
    public int $allowed_late_minutes = 0;
    public int $allowed_early_leave_minutes = 0;
    public string $shift_type = '';
    public ?string $notes = null;
    public array $days = [];
    public bool $showModal = false;
    public bool $isEdit = false;
    public string $search = '';

    public array $shiftTypes = [];
    public array $weekDays = [];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:60|unique:shifts,name,' . $this->shiftId,
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'beginning_check_in' => 'nullable',
            'ending_check_in' => 'nullable',
            'beginning_check_out' => 'nullable',
            'ending_check_out' => 'nullable',
            'allowed_late_minutes' => 'nullable|integer|min:0',
            'allowed_early_leave_minutes' => 'nullable|integer|min:0',
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

    /**
     * Initialize component on mount.
     */
    public function mount(): void
    {
        $this->shiftTypes = [
            'morning' => __('hr.morning'),
            'evening' => __('hr.evening'),
            'night' => __('hr.night'),
        ];
        $this->weekDays = [
            'saturday' => __('hr.saturday'),
            'sunday' => __('hr.sunday'),
            'monday' => __('hr.monday'),
            'tuesday' => __('hr.tuesday'),
            'wednesday' => __('hr.wednesday'),
            'thursday' => __('hr.thursday'),
            'friday' => __('hr.friday'),
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
     * Get filtered shifts list.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Shift>
     */
    public function getShiftsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return Shift::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderByDesc('id')
            ->get();
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['start_time', 'end_time', 'beginning_check_in', 'ending_check_in', 'beginning_check_out', 'ending_check_out', 'allowed_late_minutes', 'allowed_early_leave_minutes', 'shift_type', 'notes', 'days', 'shiftId', 'name']);
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
        $this->beginning_check_in = $shift->beginning_check_in;
        $this->ending_check_in = $shift->ending_check_in;
        $this->beginning_check_out = $shift->beginning_check_out;
        $this->ending_check_out = $shift->ending_check_out;
        $this->allowed_late_minutes = $shift->allowed_late_minutes ?? 0;
        $this->allowed_early_leave_minutes = $shift->allowed_early_leave_minutes ?? 0;
        $this->shift_type = $shift->shift_type;
        $this->notes = $shift->notes;
        $this->days = json_decode($shift->days, true);
        $this->isEdit = true;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    /**
     * Save shift (create or update).
     */
    public function save(): void
    {
        $validated = $this->validate();
        $validated['days'] = json_encode($this->days);
        if ($this->isEdit) {
            Shift::findOrFail($this->shiftId)->update($validated);
            session()->flash('success', __('hr.shift_updated_successfully'));
        } else {
            Shift::create($validated);
            session()->flash('success', __('hr.shift_created_successfully'));
        }
        $this->showModal = false;
        $this->dispatch('closeModal');
        $this->reset(['start_time', 'end_time', 'beginning_check_in', 'ending_check_in', 'beginning_check_out', 'ending_check_out', 'allowed_late_minutes', 'allowed_early_leave_minutes', 'shift_type', 'notes', 'days', 'shiftId', 'name', 'isEdit']);
    }

    /**
     * Delete shift.
     *
     * @param int $id
     */
    public function delete(int $id): void
    {
        Shift::findOrFail($id)->delete();
        session()->flash('success', __('hr.shift_deleted_successfully'));
    }
}; ?>


<div class="" style="direction: rtl; font-family: 'Cairo', sans-serif;">
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" x-on:click="show = false"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-2">
        @can('create Shifts')
            <button class="btn btn-primary font-family-cairo fw-bold" wire:click="create">
                <i class="las la-plus me-2"></i> {{ __('hr.add_shift') }}
            </button>
        @endcan
        <div class="mb-3 col-md-4">
            <input type="text" 
                   class="form-control font-family-cairo" 
                   placeholder="{{ __('hr.search_by_title') }}" 
                   wire:model.live.debounce.300ms="search">
        </div>
    </div>


    <div class="card ">

        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto;">

                <x-table-export-actions table-id="shifts-table" filename="shifts-table" excel-label="تصدير Excel"
                    pdf-label="تصدير PDF" print-label="طباعة" />

                <table id="shifts-table" class="table text-center table-striped mb-0 overflow-x-auto">
                    <thead class="table-light text-center align-middle">
                        <tr>
                            <th class="font-family-cairo fw-bold">{{ __('Name') }}</th>
                            <th class="font-family-cairo fw-bold">{{ __('Start Time') }}</th>
                            <th class="font-family-cairo fw-bold">{{ __('Beginning Check In') }}</th>
                            <th class="font-family-cairo fw-bold">{{ __('Ending Check In') }}</th>
                            <th class="font-family-cairo fw-bold">{{ __('Allowed Late Minutes') }}</th>
                            <th class="font-family-cairo fw-bold">{{ __('End Time') }}</th>
                            <th class="font-family-cairo fw-bold">{{ __('Beginning Check Out') }}</th>
                            <th class="font-family-cairo fw-bold">{{ __('Ending Check Out') }}</th>
                            <th class="font-family-cairo fw-bold">{{ __('Allowed Early Leave Minutes') }}</th>
                            <th class="font-family-cairo fw-bold">{{ __('Shift Type') }}</th>
                            <th class="font-family-cairo fw-bold">{{ __('Days') }}</th>
                            <th class="font-family-cairo fw-bold">{{ __('Notes') }}</th>
                            @canany(['edit Shifts', 'delete Shifts'])
                                <th class="font-family-cairo fw-bold">{{ __('hr.actions') }}</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->shifts as $shift)
                            <tr>
                                <td class="font-family-cairo fw-bold">{{ $shift->name }}</td>
                                <td class="font-family-cairo fw-bold">{{ $shift->start_time }}</td>
                                <td class="font-family-cairo fw-bold">{{ $shift->beginning_check_in ?? '-' }}</td>
                                <td class="font-family-cairo fw-bold">{{ $shift->ending_check_in ?? '-' }}</td>
                                <td class="font-family-cairo fw-bold">{{ $shift->allowed_late_minutes ?? '-' }}</td>
                                <td class="font-family-cairo fw-bold">{{ $shift->end_time }}</td>
                                <td class="font-family-cairo fw-bold">{{ $shift->beginning_check_out ?? '-' }}</td>
                                <td class="font-family-cairo fw-bold">{{ $shift->ending_check_out ?? '-' }}</td>
                                <td class="font-family-cairo fw-bold">{{ $shift->allowed_early_leave_minutes ?? '-' }}</td>
                                <td class="font-family-cairo fw-bold">
                                    {{ $shiftTypes[$shift->shift_type] ?? $shift->shift_type }}</td>
                                <td class="font-family-cairo fw-bold">
                                    @foreach (json_decode($shift->days, true) as $day)
                                        <span class="badge bg-info">{{ $weekDays[$day] ?? $day }}</span>
                                    @endforeach
                                </td>
                                <td class="font-family-cairo fw-bold">{{ $shift->notes }}</td>
                                @canany(['edit Shifts', 'delete Shifts'])
                                    <td class="font-family-cairo fw-bold">
                                        <div class="btn-group" role="group">
                                            @can('edit Shifts')
                                                <button type="button" 
                                                        class="btn btn-md btn-success me-1" 
                                                        wire:click="edit({{ $shift->id }})"
                                                        title="{{ __('hr.edit') }}">
                                                    <i class="las la-edit"></i>
                                                </button>
                                            @endcan
                                            @can('delete Shifts')
                                                <button type="button" 
                                                        class="btn btn-md btn-danger" 
                                                        wire:click="delete({{ $shift->id }})"
                                                        wire:confirm="{{ __('hr.confirm_delete_shift') }}"
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
                                <td colspan="{{ auth()->user()->canany(['edit Shifts', 'delete Shifts']) ? '13' : '12' }}" 
                                    class="text-center font-family-cairo fw-bold py-4">
                                    <div class="alert alert-info mb-0">
                                        <i class="las la-info-circle me-2"></i>
                                        {{ __('hr.no_shifts_found') }}
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
                    <h5 class="modal-title font-family-cairo fw-bold">{{ $isEdit ? __('hr.edit_shift') : __('hr.add_shift') }}</h5>
                    <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label font-family-cairo fw-bold">{{ __('hr.shift_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control font-family-cairo" wire:model.blur="name" required>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Start Time') }}</label>
                            <input type="time" class="form-control" wire:model.blur="start_time" required>
                            @error('start_time')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Beginning Check In') }}</label>
                            <input type="time" class="form-control" wire:model.blur="beginning_check_in">
                            @error('beginning_check_in')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Ending Check In') }}</label>
                            <input type="time" class="form-control" wire:model.blur="ending_check_in">
                            @error('ending_check_in')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Allowed Late Minutes') }}</label>
                            <input type="number" class="form-control" wire:model.blur="allowed_late_minutes" min="0" placeholder="{{ __('Minutes allowed after check-in start time') }}">
                            @error('allowed_late_minutes')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">{{ __('Time allowed in minutes after check-in start time before counting as late') }}</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('End Time') }}</label>
                            <input type="time" class="form-control" wire:model.blur="end_time" required>
                            @error('end_time')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Beginning Check Out') }}</label>
                            <input type="time" class="form-control" wire:model.blur="beginning_check_out">
                            @error('beginning_check_out')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Ending Check Out') }}</label>
                            <input type="time" class="form-control" wire:model.blur="ending_check_out">
                            @error('ending_check_out')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Allowed Early Leave Minutes') }}</label>
                            <input type="number" class="form-control" wire:model.blur="allowed_early_leave_minutes" min="0" placeholder="{{ __('Minutes allowed before check-out end time') }}">
                            @error('allowed_early_leave_minutes')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">{{ __('Time allowed in minutes before check-out end time before counting as early leave') }}</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Shift Type') }}</label>
                            <select class="form-select" wire:model.blur="shift_type" required>
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
                                            value="{{ $key }}" wire:model.blur="days">

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
                            <textarea class="form-control" wire:model.blur="notes"></textarea>
                            @error('notes')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary font-family-cairo"
                            wire:click="$set('showModal', false)">{{ __('hr.cancel') }}</button>
                        <button type="submit"
                            class="btn btn-primary font-family-cairo">{{ $isEdit ? __('hr.update') : __('hr.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
