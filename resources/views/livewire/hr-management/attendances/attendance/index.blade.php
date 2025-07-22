<?php

use Livewire\Volt\Component;
use App\Models\Attendance;
use Livewire\WithPagination;
use App\Models\Employee;
use Livewire\Attributes\Computed;

new class extends Component {
    use WithPagination;

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
        'location' => '',
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
            $query->where('finger_print_employee_name', 'like', '%' . $this->search_fingerprint_name . '%');
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
            'form.location' => 'nullable|string',
            'form.status' => 'required|in:pending,approved,rejected',
            'form.notes' => 'nullable|string',
        ]);
        Attendance::create([...$this->form]);
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
            'date' => $attendance->date?->format('Y-m-d'),
            'time' => $attendance->time?->format('H:i:s'),
            'location' => $attendance->location,
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
            'form.location' => 'nullable|string',
            'form.status' => 'required|in:pending,approved,rejected',
            'form.notes' => 'nullable|string',
        ]);
        $attendance->update($this->form);
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
            'time' => now()->format('H:i:s'),
            'location' => '',
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
        {{-- @can('إنشاء البصمات') --}}
        <div class="">
            <button class="btn btn-primary font-family-cairo fw-bold" wire:click="create">
                <i class="las la-plus"></i> {{ __('إضافة حضور') }}
            </button>
        </div>
        {{-- @endcan --}}

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
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>{{ __('رقم') }}</th>
                                    <th>{{ __('اسم الموظف') }}</th>
                                    <th>{{ __('رقم الموظف') }}</th>
                                    <th>{{ __('اسم البصمة') }}</th>
                                    <th>{{ __('النوع') }}</th>
                                    <th>{{ __('التاريخ') }}</th>
                                    <th>{{ __('الوقت') }}</th>
                                    <th>{{ __('الموقع') }}</th>
                                    <th>{{ __('الحالة') }}</th>
                                    <th>{{ __('ملاحظات') }}</th>
                                    @can('إجراء العمليات على البصمات')
                                        <th>{{ __('الإجراءات') }}</th>
                                    @endcan
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($attendances as $attendance)
                                    <tr>
                                        <td class="text-center fw-bold">{{ $attendance->id }}</td>
                                        <td class="text-center fw-bold">{{ $attendance->employee->name ?? '-' }}</td>
                                        <td class="text-center fw-bold">{{ $attendance->employee_id }}</td>
                                        <td class="text-center fw-bold">
                                            {{ $attendance->employee_attendance_finger_print_name }}</td>
                                        <td class="text-center fw-bold">
                                            {{ $attendance->type == 'check_in' ? __('دخول') : __('خروج') }}
                                        </td>
                                        <td class="text-center fw-bold">{{ $attendance->date->format('Y-m-d') }}</td>
                                        <td class="text-center fw-bold">{{ $attendance->time->format('H:i:s') }}</td>
                                        <td class="text-center fw-bold">{{ $attendance->location ?? '-' }}</td>
                                        <td class="text-center fw-bold">
                                            @if ($attendance->status == 'pending')
                                                <span class="badge bg-warning">{{ __('قيد المراجعة') }}</span>
                                            @elseif($attendance->status == 'approved')
                                                <span class="badge bg-success">{{ __('معتمد') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('مرفوض') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center fw-bold">{{ $attendance->notes ?? '-' }}</td>

                                        @can('إجراء العمليات على البصمات')
                                            <td class="text-center fw-bold">
                                                @if ($attendance->status !== 'approved')
                                                    @can('تعديل البصمات')
                                                        <button class="btn btn-sm btn-info me-1"
                                                            wire:click="edit({{ $attendance->id }})">
                                                            {{ __('تعديل') }}
                                                        </button>
                                                    @endcan
                                                    @can('حذف البصمات')
                                                        <button class="btn btn-sm btn-danger"
                                                            wire:click="confirmDelete({{ $attendance->id }})">
                                                            {{ __('حذف') }}
                                                        </button>
                                                    @endcan
                                                @else
                                                    <span class="text-muted">{{ __('غير قابل للتعديل/الحذف') }}</span>
                                                @endif
                                            </td>
                                        @endcan
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center">
                                            <div class="alert alert-info py-3 mb-0 font-family-cairo fw-bold">
                                                <i class="las la-info-circle me-2"></i> {{ __('لا توجد سجلات حضور') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $attendances->links() }}
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
                        <h5 class="modal-title font-family-cairo">
                            {{ $showCreateModal ? __('إضافة حضور') : __('تعديل الحضور') }}
                        </h5>
                        <button type="button" class="btn-close"
                            wire:click="$set('{{ $showCreateModal ? 'showCreateModal' : 'showEditModal' }}', false)">
                        </button>
                    </div>

                    <div class="modal-body">
                        <form wire:submit.prevent="{{ $showCreateModal ? 'store' : 'update' }}">
                            {{-- الصف الأول --}}
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-family-cairo">الموظف</label>
                                    <select class="form-select font-family-cairo fw-bold font-14"
                                        wire:model.live="form.employee_id">
                                        <option value="">اختر الموظف</option>
                                        @foreach ($this->employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.employee_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-family-cairo">رقم البصمة</label>
                                    <input type="text" class="form-control font-family-cairo"
                                        value="{{ $form['employee_attendance_finger_print_id'] }}" disabled>
                                    @error('form.employee_attendance_finger_print_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-family-cairo">اسم البصمة</label>
                                    <input type="text" class="form-control font-family-cairo"
                                        value="{{ $form['employee_attendance_finger_print_name'] }}" disabled>
                                    @error('form.employee_attendance_finger_print_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- الصف الثاني --}}
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-family-cairo">النوع</label>
                                    <select class="form-select font-family-cairo fw-bold font-14"
                                        wire:model.live="form.type">
                                        <option value="check_in">دخول</option>
                                        <option value="check_out">خروج</option>
                                    </select>
                                    @error('form.type')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-family-cairo">التاريخ</label>
                                    <input type="date" class="form-control font-family-cairo"
                                        wire:model.live="form.date">
                                    @error('form.date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-family-cairo">الوقت</label>
                                    <input type="time" class="form-control font-family-cairo"
                                        wire:model.live="form.time">
                                    @error('form.time')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- الصف الثالث --}}
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-family-cairo">الموقع</label>
                                    <input type="text" class="form-control font-family-cairo"
                                        wire:model.live="form.location">
                                    @error('form.location')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-family-cairo">الحالة</label>
                                    <select class="form-select font-family-cairo fw-bold font-14"
                                        wire:model.live="form.status">
                                        <option value="pending">قيد المراجعة</option>
                                        <option value="approved">معتمد</option>
                                        <option value="rejected">مرفوض</option>
                                    </select>
                                    @error('form.status')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-family-cairo">ملاحظات</label>
                                    <textarea class="form-control font-family-cairo" wire:model.live="form.notes"></textarea>
                                    @error('form.notes')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- الأزرار --}}
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary font-family-cairo"
                                    wire:click="$set('{{ $showCreateModal ? 'showCreateModal' : 'showEditModal' }}', false)">
                                    إلغاء
                                </button>
                                <button type="submit" class="btn btn-primary font-family-cairo">
                                    {{ $showCreateModal ? 'حفظ' : 'حفظ التعديلات' }}
                                </button>
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
