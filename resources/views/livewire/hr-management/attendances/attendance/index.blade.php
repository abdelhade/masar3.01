<?php

use Livewire\Volt\Component;
use App\Models\Attendance;
use Livewire\WithPagination;
use App\Models\Employee;
use Livewire\Attributes\Computed;
use Carbon\Carbon;

new class extends Component {
    use WithPagination;
    // bootstrap pagination
    protected $paginationTheme = 'bootstrap';

    public string $search_employee_name = '';
    public string $search_employee_id = '';
    public string $search_fingerprint_name = '';
    public $date_from = null;
    public $date_to = null;

    // CRUD state
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $editId = null;
    public $deleteId = null;
    public $form = [
        'employee_id' => '',
        'employee_attendance_finger_print_id' => '',
        'employee_attendance_finger_print_name' => '',
        'type' => 'check_in',
        'date' => '',
        'time' => '',
        'status' => 'pending',
        'notes' => '',
    ];

    public function mount()
    {
        $this->search_employee_name = '';
        $this->search_employee_id = '';
        $this->search_fingerprint_name = '';
        $this->date_from = null;
        $this->date_to = null;
    }

    public function updatedSearchEmployeeName()
    {
        $this->resetPage();
    }
    public function updatedSearchEmployeeId()
    {
        $this->resetPage();
    }
    public function updatedSearchFingerprintName()
    {
        $this->resetPage();
    }
    public function updatedDateFrom()
    {
        $this->resetPage();
    }
    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search_employee_name = '';
        $this->search_employee_id = '';
        $this->search_fingerprint_name = '';
        $this->date_from = null;
        $this->date_to = null;
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Attendance::query();

        if ($this->search_employee_name) {
            $query->whereHas('employee', function ($q) {
                $q->where('name', 'like', '%' . $this->search_employee_name . '%');
            });
        }
        if ($this->search_employee_id) {
            $query->where('employee_id', $this->search_employee_id);
        }
        if ($this->search_fingerprint_name) {
            $query->where('employee_attendance_finger_print_name', 'like', '%' . $this->search_fingerprint_name . '%');
        }
        if ($this->date_from && $this->date_to) {
            $query->whereBetween('date', [$this->date_from, $this->date_to]);
        } elseif ($this->date_from) {
            $query->where('date', '>=', $this->date_from);
        } elseif ($this->date_to) {
            $query->where('date', '<=', $this->date_to);
        }

        return [
            'attendances' => $query
                ->with(['employee', 'user'])
                ->latest()
                ->paginate(10),
        ];
    }

    public function create()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }
    public function store()
    {
        $this->validate([
            'form.employee_id' => 'required|exists:employees,id',
            'form.employee_attendance_finger_print_id' => 'required|integer',
            'form.employee_attendance_finger_print_name' => 'required|string',
            'form.type' => 'required|in:check_in,check_out',
            'form.date' => 'required|date',
            'form.time' => 'required',
            'form.status' => 'required|in:pending,approved,rejected',
            'form.notes' => 'nullable|string',
        ]);
        
        $data = $this->form;
        
        Attendance::create($data);
        $this->showCreateModal = false;
        $this->resetForm();
        session()->flash('success', __('تم إضافة الحضور بنجاح'));
    }
    public function edit($id)
    {
        $attendance = Attendance::findOrFail($id);
        if ($attendance->status === 'approved') {
            session()->flash('error', __('لا يمكن تعديل سجل حضور معتمد'));
            return;
        }
        $this->editId = $id;
        $this->form = [
            'employee_id' => $attendance->employee_id,
            'employee_attendance_finger_print_id' => $attendance->employee_attendance_finger_print_id,
            'employee_attendance_finger_print_name' => $attendance->employee_attendance_finger_print_name,
            'type' => $attendance->type,
            'date' => $attendance->date ? Carbon::parse($attendance->date)->format('Y-m-d') : '',
            'time' => $attendance->time,
            'status' => $attendance->status,
            'notes' => $attendance->notes,
        ];
        $this->showEditModal = true;
    }
    public function update()
    {
        $attendance = Attendance::findOrFail($this->editId);
        if ($attendance->status === 'approved') {
            session()->flash('error', __('لا يمكن تعديل سجل حضور معتمد'));
            $this->showEditModal = false;
            return;
        }
        $this->validate([
            'form.employee_id' => 'required|exists:employees,id',
            'form.employee_attendance_finger_print_id' => 'required|integer',
            'form.employee_attendance_finger_print_name' => 'required|string',
            'form.type' => 'required|in:check_in,check_out',
            'form.date' => 'required|date',
            'form.time' => 'required',
            'form.status' => 'required|in:pending,approved,rejected',
            'form.notes' => 'nullable|string',
        ]);
        
        $data = $this->form;
        
        $attendance->update($data);
        $this->showEditModal = false;
        $this->resetForm();
        session()->flash('success', __('تم تعديل الحضور بنجاح'));
    }
    public function confirmDelete($id)
    {
        $attendance = Attendance::findOrFail($id);
        if ($attendance->status === 'approved') {
            session()->flash('error', __('لا يمكن حذف سجل حضور معتمد'));
            return;
        }
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }
    public function delete()
    {
        $attendance = Attendance::findOrFail($this->deleteId);
        if ($attendance->status === 'approved') {
            session()->flash('error', __('لا يمكن حذف سجل حضور معتمد'));
            $this->showDeleteModal = false;
            return;
        }
        $attendance->delete();
        $this->showDeleteModal = false;
        session()->flash('success', __('تم حذف الحضور بنجاح'));
    }
    public function resetForm()
    {
        $this->form = [
            'employee_id' => '',
            'employee_attendance_finger_print_id' => '',
            'employee_attendance_finger_print_name' => '',
            'type' => 'check_in',
            'date' => now()->format('Y-m-d'),
            'time' => '',
            'status' => 'pending',
            'notes' => '',
        ];
        $this->editId = null;
        $this->deleteId = null;
    }
    public function getEmployeesProperty()
    {
        return Employee::orderBy('name')->get();
    }
    public function updatedFormEmployeeId($value)
    {
        $employee = Employee::find($value);
        $this->form['employee_attendance_finger_print_id'] = $employee?->finger_print_id ?? '';
        $this->form['employee_attendance_finger_print_name'] = $employee?->finger_print_name ?? '';
    }
}; ?>

<div dir="rtl" style="font-family: 'Cairo', sans-serif;">
    <div class="row mb-3">
        @can('إضافة البصمات')
            <div class="col-12 d-flex justify-content-end">
                <button class="btn btn-primary font-family-cairo fw-bold" wire:click="create">
                    <i class="las la-plus"></i> {{ __('إضافة حضور') }}
                </button>
            </div>
        @endcan


    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 font-family-cairo fw-bold">{{ __('سجلات الحضور') }}</h5>
                    <div class="row w-100 align-items-center">
                        <div class="col-md-2">
                            <input type="text" class="form-control font-family-cairo"
                                placeholder="{{ __('اسم الموظف') }}"
                                wire:model.live.debounce.500ms="search_employee_name">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control font-family-cairo"
                                placeholder="{{ __('رقم الموظف') }}"
                                wire:model.live.debounce.500ms="search_employee_id">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control font-family-cairo"
                                placeholder="{{ __('اسم البصمة') }}"
                                wire:model.live.debounce.500ms="search_fingerprint_name">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control font-family-cairo" wire:model.live="date_from"
                                placeholder="{{ __('من تاريخ') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control font-family-cairo" wire:model.live="date_to"
                                placeholder="{{ __('إلى تاريخ') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-center mt-2 mt-md-0">

                            <button type="button" class="btn btn-outline-secondary font-family-cairo fw-bold w-100"
                                wire:click="clearFilters">
                                <i class="las la-broom me-1"></i> {{ __('مسح الفلاتر') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table
                            class="table table-striped table-hover table-bordered table-light text-center align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="font-family-cairo fw-bold">{{ __('رقم') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('اسم الموظف') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('رقم الموظف') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('اسم البصمة') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('النوع') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('التاريخ') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('الوقت') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('الموقع') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('الحالة') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('ملاحظات') }}</th>
                                    @canany(['حذف البصمات', 'تعديل البصمات'])
                                        <th class="font-family-cairo fw-bold">{{ __('الإجراءات') }}</th>
                                    @endcanany

                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $attendance)
                                    <tr>
                                        <td class="font-family-cairo fw-bold">{{ $attendance->id }}</td>
                                        <td class="font-family-cairo fw-bold">{{ $attendance->employee->name ?? '-' }}
                                        </td>
                                        <td class="font-family-cairo fw-bold">{{ $attendance->employee_id }}</td>
                                        <td class="font-family-cairo fw-bold">
                                            {{ $attendance->employee_attendance_finger_print_name }}
                                        </td>
                                        <td class="font-family-cairo fw-bold">
                                            {{ $attendance->type == 'check_in' ? __('دخول') : __('خروج') }}
                                        </td>
                                        <td class="font-family-cairo fw-bold">{{ $attendance->date->format('Y-m-d') }}
                                        </td>
                                        <td class="font-family-cairo fw-bold">{{ $attendance->time }}
                                        </td>
                                        <td class="font-family-cairo fw-bold">-</td>
                                        <td class="font-family-cairo fw-bold">
                                            @if ($attendance->status == 'pending')
                                                <span
                                                    class="badge bg-warning font-family-cairo">{{ __('قيد المراجعة') }}</span>
                                            @elseif($attendance->status == 'approved')
                                                <span
                                                    class="badge bg-success font-family-cairo">{{ __('معتمد') }}</span>
                                            @else
                                                <span
                                                    class="badge bg-danger font-family-cairo">{{ __('مرفوض') }}</span>
                                            @endif
                                        </td>
                                        <td class="font-family-cairo fw-bold">{{ $attendance->notes ?? '-' }}</td>
                                        @canany(['حذف البصمات', 'تعديل البصمات'])
                                            <td class="font-family-cairo fw-bold">
                                                @if ($attendance->status !== 'approved')
                                                    @can('تعديل البصمات')
                                                        <button class="btn btn-sm btn-info me-1 font-family-cairo"
                                                            wire:click="edit({{ $attendance->id }})">{{ __('تعديل') }}</button>
                                                    @endcan
                                                    @can('حذف البصمات')
                                                        <button class="btn btn-sm btn-danger font-family-cairo"
                                                            wire:click="confirmDelete({{ $attendance->id }})">{{ __('حذف') }}</button>
                                                    @endcan
                                                @else
                                                    <span class="text-muted">{{ __('غير قابل للتعديل/الحذف') }}</span>
                                                @endif
                                            </td>
                                        @endcanany

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center font-family-cairo fw-bold">
                                            {{ __('لا توجد سجلات حضور') }}

                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $attendances->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>


    </div>
    {{-- Create Modal --}}
    @if ($showCreateModal || $showEditModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-family-cairo">{{ __('إضافة حضور') }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showCreateModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="store">
                            <div class="mb-3">
                                <label class="form-label font-family-cairo">{{ __('الموظف') }}</label>
                                <select class="form-select font-family-cairo fw-bold font-14"
                                    wire:model.live="form.employee_id">
                                    <option class="text-muted font-family-cairo fw-bold font-14" value="">
                                        {{ __('اختر الموظف') }}
                                    </option>
                                    @foreach ($this->employees as $employee)
                                        <option class="font-family-cairo fw-bold font-14" value="{{ $employee->id }}">
                                            {{ $employee->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('form.employee_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo">{{ __('رقم البصمة') }}</label>
                                <input type="text" class="form-control font-family-cairo"
                                    value="{{ $this->form['employee_attendance_finger_print_id'] }}" disabled>
                                @error('form.employee_attendance_finger_print_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo">{{ __('اسم البصمة') }}</label>
                                <input type="text" class="form-control font-family-cairo"
                                    value="{{ $this->form['employee_attendance_finger_print_name'] }}" disabled>
                                @error('form.employee_attendance_finger_print_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo">{{ __('النوع') }}</label>
                                <select class="form-select font-family-cairo fw-bold font-14"
                                    wire:model.live="form.type">
                                    <option class="font-family-cairo fw-bold font-14" value="check_in">
                                        {{ __('دخول') }}
                                    </option>
                                    <option class="font-family-cairo fw-bold font-14" value="check_out">
                                        {{ __('خروج') }}
                                    </option>
                                </select>
                                @error('form.type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo">{{ __('التاريخ') }}</label>
                                <input type="date" class="form-control font-family-cairo"
                                    wire:model.live="form.date">
                                @error('form.date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14">{{ __('الوقت') }}</label>
                                <input type="time" wire:model="form.time"
                                    class="form-control
                                        @error('form.time') <span class=" text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14">{{ __('الحالة') }}</label>
                                <select class="form-select font-family-cairo fw-bold font-14"
                                    wire:model.live="form.status">
                                    <option class="font-family-cairo fw-bold font-14" value="pending">
                                        {{ __('قيد المراجعة') }}
                                    </option>
                                    <option class="font-family-cairo fw-bold font-14" value="approved">
                                        {{ __('معتمد') }}
                                    </option>
                                    <option class="font-family-cairo fw-bold font-14" value="rejected">
                                        {{ __('مرفوض') }}
                                    </option>
                                </select>
                                @error('form.status')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14">{{ __('ملاحظات') }}</label>
                                <textarea class="form-control font-family-cairo fw-bold font-14" wire:model.live="form.notes"></textarea>
                                @error('form.notes')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary font-family-cairo"
                                    wire:click="$set('showCreateModal', false)">{{ __('إلغاء') }}</button>
                                <button type="submit"
                                    class="btn btn-primary font-family-cairo">{{ __('حفظ') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
    {{-- Edit Modal --}}
    @if ($showEditModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-family-cairo">{{ __('تعديل الحضور') }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showEditModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="update">
                            {{-- Same fields as create modal, but bound to form and update --}}
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14">{{ __('الموظف') }}</label>
                                <select class="form-select font-family-cairo fw-bold font-14"
                                    wire:model.live="form.employee_id">
                                    <option class="text-muted font-family-cairo fw-bold font-14" value="">
                                        {{ __('اختر الموظف') }}
                                    </option>
                                    @foreach ($this->employees as $employee)
                                        <option class="font-family-cairo fw-bold font-14"
                                            value="{{ $employee->id }}">
                                            {{ $employee->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('form.employee_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14">{{ __('رقم البصمة') }}</label>
                                <input type="text" class="form-control font-family-cairo fw-bold font-14"
                                    value="{{ $this->form['employee_attendance_finger_print_id'] }}" disabled>
                                @error('form.employee_attendance_finger_print_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14">{{ __('اسم البصمة') }}</label>
                                <input type="text" class="form-control font-family-cairo fw-bold font-14"
                                    value="{{ $this->form['employee_attendance_finger_print_name'] }}" disabled>
                                @error('form.employee_attendance_finger_print_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14">{{ __('النوع') }}</label>
                                <select class="form-select font-family-cairo fw-bold font-14"
                                    wire:model.live="form.type">
                                    <option class="font-family-cairo fw-bold font-14" value="check_in">
                                        {{ __('دخول') }}
                                    </option>
                                    <option class="font-family-cairo fw-bold font-14" value="check_out">
                                        {{ __('خروج') }}
                                    </option>
                                </select>
                                @error('form.type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14">{{ __('التاريخ') }}</label>
                                <input type="date" class="form-control font-family-cairo fw-bold font-14"
                                    wire:model.live="form.date">
                                @error('form.date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14">{{ __('الوقت') }}</label>
                                <input type="time" class="form-control font-family-cairo fw-bold font-14"
                                    wire:model.live="form.time">
                                @error('form.time')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14">{{ __('الحالة') }}</label>
                                <select class="form-select font-family-cairo fw-bold font-14"
                                    wire:model.live="form.status">
                                    <option class="font-family-cairo fw-bold font-14" value="pending">
                                        {{ __('قيد المراجعة') }}
                                    </option>
                                    <option class="font-family-cairo fw-bold font-14" value="approved">
                                        {{ __('معتمد') }}
                                    </option>
                                    <option class="font-family-cairo fw-bold font-14" value="rejected">
                                        {{ __('مرفوض') }}
                                    </option>
                                </select>
                                @error('form.status')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14">{{ __('ملاحظات') }}</label>
                                <textarea class="form-control font-family-cairo fw-bold font-14" wire:model.live="form.notes"></textarea>
                                @error('form.notes')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary font-family-cairo"
                                    wire:click="$set('showEditModal', false)">{{ __('إلغاء') }}</button>
                                <button type="submit"
                                    class="btn btn-primary font-family-cairo">{{ __('حفظ التعديلات') }}</button>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if ($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-family-cairo">{{ __('تأكيد الحذف') }}</h5>
                        <button type="button" class="btn-close"
                            wire:click="$set('showDeleteModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <p class="font-family-cairo">{{ __('هل أنت متأكد من حذف هذا السجل؟') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary font-family-cairo"
                            wire:click="$set('showDeleteModal', false)">{{ __('إلغاء') }}</button>
                        <button type="button" class="btn btn-danger font-family-cairo"
                            wire:click="delete">{{ __('حذف') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>


</div>
