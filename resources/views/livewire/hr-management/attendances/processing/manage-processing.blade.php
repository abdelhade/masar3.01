<?php

use Livewire\Volt\Component;
use App\Models\AttendanceProcessing;
use App\Models\AttendanceProcessingDetail;
use App\Models\Employee;
use App\Models\Department;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;

new class extends Component {
    use WithPagination;

    // فلاتر البحث
    public string $search_employee_name = '';
    public string $search_employee_id = '';
    public string $search_department_name = '';
    public $date_from = null;
    public $date_to = null;

    // CRUD state
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $editId = null;
    public $deleteId = null;
    public $form = [
        'processing_type' => 'single', // single, multiple, department
        'employee_id' => null,
        'employee_department_id' => null,
        'employee_ids' => [],
        'employee_ids_department_ids' => [],
        'department_id' => null,
        'from_date' => '',
        'to_date' => '',
        'notes' => null,
    ];

    public function mount()
    {
        $this->resetFilters();
    }

    public function updatedSearchEmployeeName()
    {
        $this->resetPage();
    }
    public function updatedSearchEmployeeId()
    {
        $this->resetPage();
    }
    public function updatedSearchDepartmentName()
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

    public function resetFilters()
    {
        $this->search_employee_name = '';
        $this->search_employee_id = '';
        $this->search_department_name = '';
        $this->date_from = null;
        $this->date_to = null;
        $this->resetPage();
    }

    public function with(): array
    {
        $query = AttendanceProcessing::query();

        if ($this->search_employee_name) {
            $query->whereHas('employee', function ($q) {
                $q->where('name', 'like', '%' . $this->search_employee_name . '%');
            });
        }
        if ($this->search_employee_id) {
            $query->whereHas('employee', function ($q) {
                $q->where('employee_id', $this->search_employee_id);
            });
        }
        if ($this->search_department_name) {
            $query->whereHas('department', function ($q) {
                $q->where('title', 'like', '%' . $this->search_department_name . '%');
            });
        }
        if ($this->date_from && $this->date_to) {
            $query->whereBetween('date', [$this->date_from, $this->date_to]);
        } elseif ($this->date_from) {
            $query->where('date', '>=', $this->date_from);
        } elseif ($this->date_to) {
            $query->where('date', '<=', $this->date_to);
        }

        return [
            'processings' => $query
                ->with(['employee', 'department'])
                ->latest()
                ->paginate(10),
            'employees' => Employee::orderBy('id')->get(),
            'departments' => Department::orderBy('id')->get(),
        ];
    }

    public function create()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }
    public function store()
    {
        try {
            DB::beginTransaction();
            $this->validate($this->rules());
            // سيتم بناء منطق الإنشاء لاحقًا حسب نوع المعالجة
            if (isset($this->form['employee_id']) && $this->form['employee_id'] != '') {
                $data = [
                    'employee_id' => $this->form['employee_id'],
                    'department_id' => Employee::find($this->form['employee_id'])->department_id,
                    'type' => 'single',
                    'start_date' => date('Y-m-d', strtotime($this->form['from_date'])),
                    'end_date' => date('Y-m-d', strtotime($this->form['to_date'])),
                    'notes' => $this->form['notes'] ?? null,
                ];

                $attendances = Attendance::where('employee_id', $this->form['employee_id'])
                    ->whereBetween('date', [$this->form['from_date'], $this->form['to_date']])
                    ->get();
                $attendances = $attendances->groupBy('date');
                $processed_attendances = $attendances->map(function ($day_attendances, $date) {
                    $check_ins = $day_attendances->where('type', 'check_in');
                    $check_outs = $day_attendances->where('type', 'check_out');

                    $first_check_in = $check_ins->sortBy('time')->first();
                    $last_check_out = $check_outs->sortByDesc('time')->first();

                    $total_hours = null;
                    if ($first_check_in && $last_check_out) {
                        $check_in_time = \Carbon\Carbon::parse($first_check_in->time);
                        $check_out_time = \Carbon\Carbon::parse($last_check_out->time);
                        $total_hours = $check_in_time->diffInHours($check_out_time, false);
                    }

                    return [
                        'date' => $date,
                        'first_check_in' => $first_check_in ? $first_check_in->time : null,
                        'last_check_out' => $last_check_out ? $last_check_out->time : null,
                        'total_hours' => $total_hours,
                    ];
                });

                dd($processed_attendances);
                // $attendance_processing = AttendanceProcessing::create($data);

                // $attendance_processing_detail = AttendanceProcessingDetail::create([
                //     'attendance_processing_id' => $attendance_processing->id,
                //     'employee_id' => $this->form['employee_id'],
                //     'department_id' => $this->form['employee_department_id'],
                //     'attendance_date' => $this->form['from_date'],
                // ]);
            }
            // if(isset($this->form['employee_ids']) && count($this->form['employee_ids']) > 0){
            //     foreach($this->form['employee_ids'] as $employee_id){
            //         $this->form['employee_ids_department_ids'][$employee_id] = Employee::find($employee_id)->department_id;
            //         $attendance_processing = AttendanceProcessing::create($this->form);
            //         $attendance_processing_detail = AttendanceProcessingDetail::create([
            //             'attendance_processing_id' => $attendance_processing->id,
            //             'employee_id' => $employee_id,
            //             'department_id' => $this->form['employee_ids_department_ids'][$employee_id],
            //             'attendance_date' => $this->form['from_date'],
            //         ]);
            //     }
            // }
            // if(isset($this->form['department_id']) && $this->form['department_id'] != ''){
            //     $attendance_processing = AttendanceProcessing::create($this->form);
            //     $attendance_processing_detail = AttendanceProcessingDetail::create([
            //         'attendance_processing_id' => $attendance_processing->id,
            //         'department_id' => $this->form['department_id'],
            //         'attendance_date' => $this->form['from_date'],
            //     ]);
            // }
            // dd($this->form);
            DB::commit();
            $this->showCreateModal = false;
            $this->resetForm();
            session()->flash('success', __('تمت إضافة معالجة الحضور بنجاح'));
        } catch (\Exception $e) {
            DB::rollBack();
            // log the form data
            Log::error($e->getMessage());
            session()->flash('error', __('حدث خطأ ما'));
        }
    }
    public function edit($id)
    {
        $processing = AttendanceProcessing::findOrFail($id);
        $this->editId = $id;
        // سيتم تعبئة النموذج لاحقًا
        $this->showEditModal = true;
    }
    public function update()
    {
        $processing = AttendanceProcessing::findOrFail($this->editId);
        $this->validate($this->rules());
        // سيتم بناء منطق التعديل لاحقًا
        $this->showEditModal = false;
        $this->resetForm();
        session()->flash('success', __('تم تعديل معالجة الحضور بنجاح'));
    }
    public function confirmDelete($id)
    {
        $processing = AttendanceProcessing::findOrFail($id);
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }
    public function delete()
    {
        $processing = AttendanceProcessing::findOrFail($this->deleteId);
        $processing->delete();
        $this->showDeleteModal = false;
        session()->flash('success', __('تم حذف معالجة الحضور بنجاح'));
    }
    public function resetForm()
    {
        $this->form = [
            'processing_type' => 'single',
            'employee_id' => null,
            'employee_department_id' => null,
            'employee_ids' => [],
            'employee_ids_department_ids' => [],
            'department_id' => null,
            'from_date' => now()->format('Y-m-d'),
            'to_date' => now()->format('Y-m-d'),
            'notes' => null,
        ];
        $this->editId = null;
        $this->deleteId = null;
    }
    public function rules()
    {
        $rules = [
            'form.processing_type' => 'required|in:single,multiple,department',
            'form.from_date' => 'required|date',
            'form.to_date' => 'required|date',
            'form.notes' => 'nullable|string',
        ];
        if ($this->form['processing_type'] === 'single') {
            $rules['form.employee_id'] = 'required|exists:employees,id';
        } elseif ($this->form['processing_type'] === 'multiple') {
            $rules['form.employee_ids'] = 'required|array|min:1';
            $rules['form.employee_ids.*'] = 'exists:employees,id';
        } elseif ($this->form['processing_type'] === 'department') {
            $rules['form.department_id'] = 'required|exists:departments,id';
        }
        return $rules;
    }
}; ?>

<div dir="rtl" style="font-family: 'Cairo', sans-serif;">
    <div class="row mb-3">
        @can('إنشاء معالجة الحضور والانصراف')
            <div class="col-12 d-flex justify-content-end">
                <button class="btn btn-primary font-family-cairo fw-bold" wire:click="create">
                    <i class="las la-plus"></i> {{ __('إضافة معالجة حضور') }}
                </button>
            </div>
        @endcan

    </div>
    <div class="row mb-3">
        <div class="col-md-3">
            <x-tom-select name="filter_employee" :options="$employees->map(fn($p) => ['value' => $p->id, 'text' => $p->name])->toArray()" wire:model.live.debounce.500ms="search_employee_name"
                placeholder="{{ __('اختر الموظف') }}" :search="true" dir="rtl" class="font-family-cairo"
                :multiple="false" :required="true" :syncOnModalOpen="true" :clearOnModalClose="true" :tomOptions="[
        'plugins' => [
            'dropdown_input' => ['class' => 'font-family-cairo fw-bold font-14'],
            'remove_button' => ['title' => 'Remove all selected options'],
        ],
    ]" />
        </div>
        <div class="col-md-3">
            <x-tom-select name="filter_department" :options="$departments->map(fn($p) => ['value' => $p->id, 'text' => $p->title])->toArray()" wire:model.live.debounce.500ms="search_department_name"
                placeholder="{{ __('اختر القسم') }}" :search="true" dir="rtl" class="font-family-cairo"
                :multiple="false" :required="true" :syncOnModalOpen="true" :clearOnModalClose="true" :tomOptions="[
        'plugins' => [
            'dropdown_input' => ['class' => 'font-family-cairo fw-bold font-14'],
            'remove_button' => ['title' => 'Remove all selected options'],
        ],
    ]" />
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
            @can('مسح الفلاتر')
                <button type="button" class="btn btn-outline-secondary font-family-cairo fw-bold w-100"
                    wire:click="resetFilters">
                    <i class="las la-broom me-1"></i> {{ __('مسح الفلاتر') }}
                </button>
            @endcan

        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 font-family-cairo fw-bold">{{ __('سجلات معالجة الحضور') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table
                            class="table table-striped table-hover table-bordered table-light text-center align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('#') }}</th>
                                    <th>{{ __('من تاريخ') }}</th>
                                    <th>{{ __('إلى تاريخ') }}</th>
                                    <th>{{ __('نوع المعالجة') }}</th>
                                    <th>{{ __('الموظف') }}</th>
                                    <th>{{ __('القسم') }}</th>
                                    <th>{{ __('ملاحظات') }}</th>
                                    @can('إجراء العمليات على معالجة الحضور والانصراف')
                                        <th>{{ __('الإجراءات') }}</th>
                                    @endcan

                                </tr>
                            </thead>
                            <tbody>
                                @forelse($processings as $processing)
                                    <tr>
                                        <td>{{ $processing->id }}</td>
                                        <td>{{ $processing->start_date->format('Y-m-d') }}</td>
                                        <td>{{ $processing->end_date->format('Y-m-d') }}</td>
                                        <td>
                                            @if ($processing->type == 'single')
                                                {{ __('موظف واحد') }}
                                            @elseif($processing->type == 'multiple')
                                                {{ __('عدة موظفين') }}
                                            @else
                                                {{ __('قسم') }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($processing->type == 'single')
                                                {{ optional($processing->employee)->name ?? '--' }}
                                            @elseif($processing->type == 'multiple')
                                                @foreach ($processing->details as $detail)
                                                    <span class="badge bg-info m-1">{{ optional($detail->employee)->name }}</span>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @if ($processing->type == 'department' || $processing->type == 'single')
                                                {{ optional($processing->department)->title ?? '--' }}
                                            @endif
                                        </td>
                                        <td>{{ $processing->notes ?? '--' }}</td>
                                        @can('إجراء العمليات على معالجة الحضور والانصراف')
                                            <td>
                                                @can('تعديل معالجة الحضور والانصراف')
                                                    <button class="btn btn-sm btn-info me-1 font-family-cairo"
                                                        wire:click="edit({{ $processing->id }})">{{ __('تعديل') }}</button>
                                                @endcan
                                                @can('حذف معالجة الحضور والانصراف')
                                                    <button class="btn btn-sm btn-danger font-family-cairo"
                                                        wire:click="confirmDelete({{ $processing->id }})">{{ __('حذف') }}</button>
                                                @endcan

                                            </td>
                                        @endcan

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center font-family-cairo fw-bold">
                                            {{ __('لا توجد سجلات معالجة حضور') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $processings->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Create/Edit Modal --}}
    @if ($showCreateModal || $showEditModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-family-cairo">
                            {{ $showCreateModal ? __('إضافة معالجة حضور') : __('تعديل معالجة حضور') }}
                        </h5>
                        <button type="button" class="btn-close"
                            wire:click="$set('showCreateModal', false); $set('showEditModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="{{ $showCreateModal ? 'store' : 'update' }}">
                            <div class="mb-3">
                                <label class="form-label font-family-cairo">{{ __('نوع المعالجة') }}</label>
                                <select class="form-select font-family-cairo fw-bold font-14"
                                    wire:model.live="form.processing_type" id="processing-type-select">
                                    <option value="single">{{ __('موظف واحد') }}</option>
                                    <option value="multiple">{{ __('عدة موظفين') }}</option>
                                    <option value="department">{{ __('قسم') }}</option>
                                </select>
                            </div>
                            <div class="mb-3" x-data="{
                                    type: $wire.form.processing_type,
                                    initTomSelect() {
                                        this.$nextTick(() => {
                                            if (window.tomSelectManager) {
                                                window.tomSelectManager.initializeAll();
                                            }
                                        });
                                    }
                                }" x-init="$watch('type', value => {
                                    $wire.set('form.processing_type', value);
                                    initTomSelect();
                                })">
                                <template x-if="$wire.form.processing_type === 'single'">
                                    <div x-init="initTomSelect()">
                                        <label class="form-label font-family-cairo">{{ __('اختر الموظف') }}</label>
                                        <x-tom-select id="employee-single-select" name="employee_id" :options="collect($employees)
                ->map(
                    fn($employee) => ['value' => $employee->id, 'text' => $employee->name],
                )
                ->toArray()" wireModel="form.employee_id"
                                            placeholder="{{ __('اختر الموظف') }}" class="form-select font-family-cairo"
                                            :allowEmptyOption="false" :search="true" :value="$form['employee_id'] ?? null" :tomOptions="[
                'plugins' => [
                    'dropdown_input' => [
                        'class' => 'font-family-cairo fw-bold font-14',
                    ],
                    'remove_button' => ['title' => 'إزالة المحدد'],
                ],
            ]" />
                                        @error('form.employee_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </template>
                                <template x-if="$wire.form.processing_type === 'multiple'">
                                    <div x-init="initTomSelect()">
                                        <label class="form-label font-family-cairo">{{ __('اختر الموظفين') }}</label>
                                        <x-tom-select id="employee-multi-select" name="employee_ids" :options="collect($employees)
                ->map(
                    fn($employee) => ['value' => $employee->id, 'text' => $employee->name],
                )
                ->toArray()" wireModel="form.employee_ids"
                                            placeholder="{{ __('اختر الموظفين') }}" class="form-select font-family-cairo"
                                            :multiple="true" :allowEmptyOption="false"
                                           :search="true" :value="$form['employee_ids'] ?? []" :tomOptions="[
                'plugins' => [
                    'dropdown_input' => [
                        'class' => 'font-family-cairo fw-bold font-14',
                    ],
                    'remove_button' => ['title' => 'إزالة المحدد'],
                ],
            ]" />
                                        @error('form.employee_ids')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </template>
                                <template x-if="$wire.form.processing_type === 'department'">
                                    <div x-init="initTomSelect()">
                                        <label class="form-label font-family-cairo">{{ __('اختر القسم') }}</label>
                                        <x-tom-select id="department-select" name="department_id" :options="collect($departments)
                ->map(
                    fn($department) => [
                        'value' => $department->id,
                        'text' => $department->title,
                    ],
                )
                ->toArray()"
                                            wireModel="form.department_id" placeholder="{{ __('اختر القسم') }}"
                                            class="form-select font-family-cairo" :allowEmptyOption="false" :search="true"
                                            :value="$form['department_id'] ?? null" :tomOptions="[
                'plugins' => [
                    'dropdown_input' => [
                        'class' => 'font-family-cairo fw-bold font-14',
                    ],
                    'remove_button' => ['title' => 'إزالة المحدد'],
                ],
            ]" />
                                        @error('form.department_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </template>
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo">{{ __('من تاريخ') }}</label>
                                <input type="date" class="form-control font-family-cairo" wire:model.live="form.from_date">
                                @error('form.from_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo">{{ __('إلى تاريخ') }}</label>
                                <input type="date" class="form-control font-family-cairo" wire:model.live="form.to_date">
                                @error('form.to_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo">{{ __('ملاحظات') }}</label>
                                <textarea class="form-control font-family-cairo" wire:model.live="form.notes"></textarea>
                                @error('form.notes')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary font-family-cairo"
                                    wire:click="$set('showCreateModal', false); $set('showEditModal', false)">{{ __('إلغاء') }}</button>
                                <button type="submit" class="btn btn-primary font-family-cairo">{{ __('حفظ') }}</button>
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
                        <button type="button" class="btn-close" wire:click="$set('showDeleteModal', false)"></button>
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