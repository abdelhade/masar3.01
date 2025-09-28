<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Employee;
use App\Models\Country;
use App\Models\City;
use App\Models\State;
use App\Models\Town;
use App\Models\Department;
use App\Models\EmployeesJob;
use App\Models\Shift;

new class extends Component {
    use WithPagination, WithFileUploads;

    public $search = '';
    public $showModal = false;
    public $isEdit = false;
    public $employeeId = null;
    public $showViewModal = false;
    public $viewEmployee = null;
    public $countries = [],
        $cities = [],
        $states = [],
        $towns = [],
        $departments = [],
        $jobs = [],
        $shifts = [];

    // Employee fields
    public $name,
        $email,
        $phone,
        $image,
        $gender,
        $date_of_birth,
        $nationalId,
        $marital_status,
        $education,
        $information,
        $status = 'مفعل';
    public $country_id, $city_id, $state_id, $town_id;
    public $job_id, $department_id, $date_of_hire, $date_of_fire, $job_level, $salary, $finger_print_id, $finger_print_name, $salary_type, $shift_id, $password, $additional_hour_calculation, $additional_day_calculation;

    public function rules()
    {
        return [
            'name' => 'required|string|unique:employees,name,' . $this->employeeId,
            'email' => 'required|email|unique:employees,email,' . $this->employeeId,
            'phone' => 'required|string|unique:employees,phone,' . $this->employeeId,
            'image' => 'nullable|image',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date',
            'nationalId' => 'nullable|string|unique:employees,nationalId,' . $this->employeeId,
            'marital_status' => 'nullable',
            'education' => 'nullable',
            'information' => 'nullable|string',
            'status' => 'required|in:مفعل,معطل',
            'country_id' => 'nullable|exists:countries,id',
            'city_id' => 'nullable|exists:cities,id',
            'state_id' => 'nullable|exists:states,id',
            'town_id' => 'nullable|exists:towns,id',
            'job_id' => 'nullable|exists:employees_jobs,id',
            'department_id' => 'nullable|exists:departments,id',
            'date_of_hire' => 'nullable|date',
            'date_of_fire' => 'nullable|date',
            'job_level' => 'nullable',
            'salary' => 'nullable|numeric',
            'finger_print_id' => 'nullable|integer|unique:employees,finger_print_id,' . $this->employeeId,
            'finger_print_name' => 'nullable|string|unique:employees,finger_print_name,' . $this->employeeId,
            'salary_type' => 'nullable',
            'shift_id' => 'nullable|exists:shifts,id',
            'password' => 'nullable|string',
            'additional_hour_calculation' => 'nullable|numeric',
            'additional_day_calculation' => 'nullable|numeric',
        ];
    }

    public function mount()
    {
        $this->countries = Country::all();
        $this->cities = City::all();
        $this->states = State::all();
        $this->towns = Town::all();
        $this->departments = Department::all();
        $this->jobs = EmployeesJob::all();
        $this->shifts = Shift::all();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getEmployeesProperty()
    {
        return Employee::when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))->orderByDesc('id')->paginate(10);
    }

    public function create()
    {
        $this->resetValidation();
        $this->resetEmployeeFields();
        $this->isEdit = false;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    public function edit($id)
    {
        $this->resetValidation();
        $employee = Employee::findOrFail($id);
        $this->employeeId = $employee->id;
        foreach (['name', 'email', 'phone', 'image', 'gender', 'date_of_birth', 'nationalId', 'marital_status', 'education', 'information', 'status', 'country_id', 'city_id', 'state_id', 'town_id', 'job_id', 'department_id', 'date_of_hire', 'date_of_fire', 'job_level', 'salary', 'finger_print_id', 'finger_print_name', 'salary_type', 'shift_id', 'password', 'additional_hour_calculation', 'additional_day_calculation'] as $field) {
            // If the field is a date, format it as Y-m-d, otherwise assign directly
            if (in_array($field, ['date_of_birth', 'date_of_hire', 'date_of_fire'])) {
                $this->$field = $employee->$field ? $employee->$field->format('Y-m-d') : null;
            } else {
                $this->$field = $employee->$field;
            }
        }
        $this->isEdit = true;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    public function save()
    {
        try {
            // Convert finger_print_id to integer before validation
            if ($this->finger_print_id !== null && $this->finger_print_id !== '') {
                $this->finger_print_id = (int)$this->finger_print_id;
            }
            
            $validated = $this->validate();
            if ($this->isEdit && $this->employeeId) {
                Employee::find($this->employeeId)->update($validated);
                session()->flash('success', __('تم تحديث الموظف بنجاح.'));
            } else {
                Employee::create($validated);
                session()->flash('success', __('تم إنشاء الموظف بنجاح.'));
            }
            $this->showModal = false;
            $this->dispatch('closeModal');
        } catch (\Throwable $th) {
            session()->flash('error', __('حدث خطأ ما.'));
            Log::error($th);
        }
    }

    public function delete($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
        session()->flash('success', __('تم حذف الموظف بنجاح.'));
    }

    public function resetEmployeeFields()
    {
        foreach (['employeeId', 'name', 'email', 'phone', 'image', 'gender', 'date_of_birth', 'nationalId', 'marital_status', 'education', 'information', 'status', 'country_id', 'city_id', 'state_id', 'town_id', 'job_id', 'department_id', 'date_of_hire', 'date_of_fire', 'job_level', 'salary', 'finger_print_id', 'finger_print_name', 'salary_type', 'shift_id', 'password', 'additional_hour_calculation', 'additional_day_calculation'] as $field) {
            $this->$field = null;
        }
        $this->status = 'مفعل';
    }

    public function view($id)
    {
        $this->viewEmployee = Employee::with(['country', 'city', 'state', 'town', 'job', 'department', 'shift'])->find($id);
        $this->showViewModal = true;
        $this->dispatch('showViewModal');
    }

    public function closeView()
    {
        $this->showViewModal = false;
        $this->viewEmployee = null;
        $this->dispatch('hideViewModal');
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
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    @can('إضافة الموظفيين')
                        <button wire:click="create" type="button" class="btn btn-primary font-family-cairo fw-bold">
                            {{ __('إضافة موظف') }}
                            <i class="fas fa-plus me-2"></i>
                        </button>
                    @endcan
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control w-auto"
                        style="min-width:200px" placeholder="{{ __('بحث بالاسم...') }}">
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive" style="overflow-x: auto;">

                            <x-table-export-actions table-id="employee-table" filename="employee-table"
                                excel-label="تصدير Excel" pdf-label="تصدير PDF" print-label="طباعة" />

                            <table id="employee-table" class="table table-striped text-center mb-0"
                                style="min-width: 1200px;">
                                <thead class="table-light align-middle">
                                    <tr>
                                        <th class="font-family-cairo fw-bold">#</th>
                                        <th class="font-family-cairo fw-bold">{{ __('الاسم') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('البريد الإلكتروني') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('رقم الهاتف') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('القسم') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('الوظيفة') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('الحالة') }}</th>
                                        @canany(['تعديل الموظفيين', 'حذف الموظفيين'])
                                            <th class="font-family-cairo fw-bold">{{ __('إجراءات') }}</th>
                                        @endcanany


                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($this->employees as $employee)
                                        <tr>

                                            <td class="font-family-cairo fw-bold">{{ $loop->iteration }}</td>
                                            <td class="font-family-cairo fw-bold">{{ $employee->name }}</td>
                                            <td class="font-family-cairo fw-bold">{{ $employee->email }}</td>
                                            <td class="font-family-cairo fw-bold">{{ $employee->phone }}</td>
                                            <td class="font-family-cairo fw-bold">
                                                {{ optional($employee->department)->title }}</td>
                                            <td class="font-family-cairo fw-bold">{{ optional($employee->job)->title }}
                                            </td>
                                            <td class="font-family-cairo fw-bold">{{ $employee->status }}</td>

                                            @canany(['تعديل الموظفيين', 'حذف الموظفيين'])
                                                <td>
                                                    <button wire:click="view({{ $employee->id }})"
                                                        class="btn btn-info btn-sm me-1" title="{{ __('عرض') }}">
                                                        <i class="las la-eye fa-lg"></i>
                                                    </button>
                                                    @can('تعديل الموظفيين')
                                                        <a wire:click="edit({{ $employee->id }})"
                                                            class="btn btn-success btn-sm me-1" title="{{ __('تعديل') }}">
                                                            <i class="las la-edit fa-lg"></i>
                                                        </a>
                                                    @endcan
                                                    @can('حذف الموظفيين')
                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            wire:click="delete({{ $employee->id }})"
                                                            onclick="confirm('هل أنت متأكد من حذف هذا الموظف؟') || event.stopImmediatePropagation()"
                                                            title="{{ __('حذف') }}">
                                                            <i class="las la-trash fa-lg"></i>
                                                        </button>
                                                    @endcan
                                                </td>
                                            @endcanany

                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">
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
                            <div class="mt-3">
                                {{ $this->employees->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal (Create/Edit) -->
        <div class="modal fade" wire:ignore.self id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel"
            aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-family-cairo fw-bold" id="employeeModalLabel">
                            {{ $isEdit ? __('تعديل موظف') : __('إضافة موظف') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="container-fluid" style="direction: rtl;">
                                <div class="row">
                                    <!-- بيانات شخصية -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card">
                                            <div class="card-header bg-primary text-white card-title">
                                                {{ __('بيانات شخصية') }}</div>
                                            <div class="card-body">
                                                <div class="row mb-2">
                                                    <div class="col-6">
                                                        <label>{{ __('الاسم') }}</label>
                                                        <input type="text" class="form-control"
                                                            wire:model.defer="name"
                                                            placeholder="{{ __('أدخل الاسم') }}">
                                                        @error('name')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-6">
                                                        <label>{{ __('البريد الإلكتروني') }}</label>
                                                        <input type="email" class="form-control"
                                                            wire:model.defer="email"
                                                            placeholder="{{ __('أدخل البريد الإلكتروني') }}">
                                                        @error('email')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-6">
                                                        <label>{{ __('رقم الهاتف') }}</label>
                                                        <input type="text" class="form-control"
                                                            wire:model.defer="phone"
                                                            placeholder="{{ __('أدخل رقم الهاتف') }}">
                                                        @error('phone')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-6">
                                                        <label>{{ __('صورة') }}</label>
                                                        <input type="file" class="form-control"
                                                            wire:model="image">
                                                        @error('image')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-6">
                                                        <label>{{ __('النوع') }}</label>
                                                        <select class="form-select font-family-cairo fw-bold font-14"
                                                            wire:model.defer="gender">
                                                            <option class="font-family-cairo fw-bold" value="">
                                                                {{ __('اختر') }}</option>
                                                            <option class="font-family-cairo fw-bold" value="male">
                                                                {{ __('ذكر') }}</option>
                                                            <option class="font-family-cairo fw-bold" value="female">
                                                                {{ __('أنثى') }}</option>
                                                        </select>
                                                        @error('gender')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-6">
                                                        <label>{{ __('تاريخ الميلاد') }}</label>
                                                        <input type="date"
                                                            class="form-control font-family-cairo fw-bold"
                                                            wire:model.defer="date_of_birth">
                                                        @error('date_of_birth')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <label>{{ __('معلومات') }}</label>
                                                    <textarea class="form-control font-family-cairo fw-bold font-12" wire:model.defer="information"
                                                        placeholder="{{ __('معلومات...') }}"></textarea>
                                                    @error('information')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="mb-2">
                                                    <label>{{ __('الحالة') }}</label>
                                                    <select class="form-select font-family-cairo fw-bold font-14"
                                                        wire:model.defer="status">
                                                        <option class="font-family-cairo fw-bold" value="">
                                                            {{ __('اختر') }}</option>
                                                        <option class="font-family-cairo fw-bold" value="مفعل">
                                                            {{ __('مفعل') }}</option>
                                                        <option class="font-family-cairo fw-bold" value="معطل">
                                                            {{ __('معطل') }}</option>
                                                    </select>
                                                    @error('status')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- بيانات تفصيلية -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card">
                                            <div class="card-header bg-primary text-white card-title">
                                                {{ __('بيانات تفصيلية') }}</div>
                                            <div class="card-body">
                                                <div class="mb-2">
                                                    <label>{{ __('البلد') }}</label>
                                                    <select class="form-select font-family-cairo fw-bold font-14"
                                                        wire:model.defer="country_id">
                                                        <option class="font-family-cairo fw-bold" value="">
                                                            {{ __('اختر') }}</option>
                                                        @foreach ($countries as $country)
                                                            <option class="font-family-cairo fw-bold"
                                                                value="{{ $country->id }}">{{ $country->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('country_id')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="mb-2">
                                                    <label>{{ __('المحافظة') }}</label>
                                                    <select class="form-select font-family-cairo fw-bold font-14"
                                                        wire:model.defer="state_id">
                                                        <option class="font-family-cairo fw-bold" value="">
                                                            {{ __('اختر') }}</option>
                                                        @foreach ($states as $state)
                                                            <option class="font-family-cairo fw-bold"
                                                                value="{{ $state->id }}">{{ $state->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('state_id')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="mb-2">
                                                    <label>{{ __('المدينة') }}</label>
                                                    <select class="form-select font-family-cairo fw-bold font-14"
                                                        wire:model.defer="city_id">
                                                        <option class="font-family-cairo fw-bold" value="">
                                                            {{ __('اختر') }}</option>
                                                        @foreach ($cities as $city)
                                                            <option class="font-family-cairo fw-bold"
                                                                value="{{ $city->id }}">{{ $city->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('city_id')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="mb-2">
                                                    <label>{{ __('المنطقة') }}</label>
                                                    <select class="form-select font-family-cairo fw-bold font-14"
                                                        wire:model.defer="town_id">
                                                        <option class="font-family-cairo fw-bold" value="">
                                                            {{ __('اختر') }}</option>
                                                        @foreach ($towns as $town)
                                                            <option class="font-family-cairo fw-bold"
                                                                value="{{ $town->id }}">{{ $town->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('town_id')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <!-- المرتبات -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card">
                                            <div class="card-header bg-primary text-white card-title">
                                                {{ __('المرتبات') }}
                                            </div>
                                            <div class="card-body">
                                                <div class="row mb-2">
                                                    <div class="col-6">
                                                        <label>{{ __('الشيفت') }}</label>
                                                        <select class="form-select font-family-cairo fw-bold font-14"
                                                            wire:model.defer="shift_id">
                                                            <option class="font-family-cairo fw-bold" value="">
                                                                {{ __('اختر') }}</option>
                                                            @foreach ($shifts as $shift)
                                                                <option class="font-family-cairo fw-bold"
                                                                    value="{{ $shift->id }}">
                                                                    {{ $shift->start_time }}
                                                                    - {{ $shift->end_time }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('shift_id')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-6">
                                                        <label>{{ __('المرتب') }}</label>
                                                        <input type="text" class="form-control"
                                                            wire:model.defer="salary"
                                                            placeholder="{{ __('أدخل المرتب') }}">
                                                        @error('salary')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-6">
                                                        <label>{{ __('نوع الاستحقاق') }}</label>
                                                        <select class="form-select font-family-cairo fw-bold font-14"
                                                            wire:model.defer="salary_type">
                                                            <option class="font-family-cairo fw-bold" value="">
                                                                {{ __('اختر') }}</option>
                                                            <option class="font-family-cairo fw-bold"
                                                                value="ساعات عمل فقط">{{ __('ساعات عمل فقط') }}
                                                            </option>
                                                            <option class="font-family-cairo fw-bold"
                                                                value="ساعات عمل و إضافي يومى">
                                                                {{ __('ساعات عمل و إضافي يومى') }}</option>
                                                            <option class="font-family-cairo fw-bold"
                                                                value="ساعات عمل و إضافي للمده">
                                                                {{ __('ساعات عمل و إضافي للمده') }}</option>
                                                            <option class="font-family-cairo fw-bold"
                                                                value="حضور فقط">
                                                                {{ __('حضور فقط') }}</option>
                                                            <option class="font-family-cairo fw-bold"
                                                                value="إنتاج فقط">
                                                                {{ __('إنتاج فقط') }}</option>
                                                        </select>
                                                        @error('salary_type')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-6">
                                                        <label>{{ __('رقم البصمة') }}</label>
                                                        <input type="number" class="form-control"
                                                            wire:model.defer="finger_print_id"
                                                            placeholder="{{ __('أدخل') }}">
                                                        @error('finger_print_id')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-6">
                                                        <label>{{ __('الاسم في البصمة') }}</label>
                                                        <input type="text" class="form-control"
                                                            wire:model.defer="finger_print_name"
                                                            placeholder="{{ __('أدخل') }}">
                                                        @error('finger_print_name')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-6">
                                                        <label>{{ __('باسورد') }}</label>
                                                        <input type="text" class="form-control"
                                                            wire:model.defer="password"
                                                            placeholder="{{ __('باسورد الهاتف') }}">
                                                        @error('password')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-6">
                                                        <label>{{ __('الساعة الإضافي تحسب ك') }}</label>
                                                        <input type="text" class="form-control"
                                                            wire:model.defer="additional_hour_calculation"
                                                            placeholder="0.00">
                                                        @error('additional_hour_calculation')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-6">
                                                        <label>{{ __('اليوم الإضافي يحسب ك') }}</label>
                                                        <input type="text" class="form-control"
                                                            wire:model.defer="additional_day_calculation"
                                                            placeholder="0.00">
                                                        @error('additional_day_calculation')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- بيانات وظيفة -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card">
                                            <div class="card-header bg-primary text-white card-title">
                                                {{ __('بيانات وظيفة') }}</div>
                                            <div class="card-body">
                                                <div class="row mb-2">
                                                    <div class="col-6">
                                                        <label>{{ __('الوظيفة') }}</label>
                                                        <select class="form-select font-family-cairo fw-bold font-14"
                                                            wire:model.defer="job_id">
                                                            <option class="font-family-cairo fw-bold" value="">
                                                                {{ __('اختر') }}</option>
                                                            @foreach ($jobs as $job)
                                                                <option class="font-family-cairo fw-bold"
                                                                    value="{{ $job->id }}">{{ $job->title }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('job_id')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-6">
                                                        <label>{{ __('المستوى الوظيفي') }}</label>
                                                        <select class="form-select font-family-cairo fw-bold font-14"
                                                            wire:model.defer="job_level">
                                                            <option class="font-family-cairo fw-bold" value="">
                                                                {{ __('اختر') }}</option>
                                                            <option class="font-family-cairo fw-bold" value="مبتدئ">
                                                                {{ __('مبتدئ') }}</option>
                                                            <option class="font-family-cairo fw-bold" value="متوسط">
                                                                {{ __('متوسط') }}</option>
                                                            <option class="font-family-cairo fw-bold" value="محترف">
                                                                {{ __('محترف') }}</option>
                                                        </select>
                                                        @error('job_level')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-6">
                                                        <label>{{ __('القسم') }}</label>
                                                        <select class="form-select font-family-cairo fw-bold font-14"
                                                            wire:model.defer="department_id">
                                                            <option class="font-family-cairo fw-bold" value="">
                                                                {{ __('اختر') }}</option>
                                                            @foreach ($departments as $department)
                                                                <option class="font-family-cairo fw-bold"
                                                                    value="{{ $department->id }}">
                                                                    {{ $department->title }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('department_id')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-6">
                                                        <label>{{ __('مستوى التعليم') }}</label>
                                                        <select class="form-select font-family-cairo fw-bold font-14"
                                                            wire:model.defer="education">
                                                            <option class="font-family-cairo fw-bold" value="">
                                                                {{ __('اختر') }}</option>
                                                            <option class="font-family-cairo fw-bold" value="دكتوراه">
                                                                {{ __('دكتوراه') }}</option>
                                                            <option class="font-family-cairo fw-bold"
                                                                value="بكالوريوس">
                                                                {{ __('بكالوريوس') }}</option>
                                                            <option class="font-family-cairo fw-bold" value="دبلوم">
                                                                {{ __('دبلوم') }}</option>
                                                            <option class="font-family-cairo fw-bold" value="ماجستير">
                                                                {{ __('ماجستير') }}</option>
                                                        </select>
                                                        @error('education')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-6">
                                                        <label>{{ __('وقت التوظيف') }}</label>
                                                        <input type="date" class="form-control"
                                                            wire:model.defer="date_of_hire">
                                                        @error('date_of_hire')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-6">
                                                        <label>{{ __('وقت الانتهاء') }}</label>
                                                        <input type="date" class="form-control"
                                                            wire:model.defer="date_of_fire">
                                                        @error('date_of_fire')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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

        <!-- View Employee Modal -->
        <div class="modal fade" wire:ignore.self id="viewEmployeeModal" tabindex="-1" aria-labelledby="viewEmployeeModalLabel"
            aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-family-cairo fw-bold" id="viewEmployeeModalLabel">
                            {{ __('عرض تفاصيل الموظف') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if($viewEmployee)
                            <div class="container-fluid" style="direction: rtl;">
                                <div class="row">
                                    <!-- بيانات شخصية -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card">
                                            <div class="card-header bg-primary text-white card-title">
                                                {{ __('بيانات شخصية') }}
                                            </div>
                                            <div class="card-body">
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('الاسم') }}:</label>
                                                        <p class="form-control-plaintext">{{ $viewEmployee->name }}</p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('البريد الإلكتروني') }}:</label>
                                                        <p class="form-control-plaintext">{{ $viewEmployee->email }}</p>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('رقم الهاتف') }}:</label>
                                                        <p class="form-control-plaintext">{{ $viewEmployee->phone }}</p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('النوع') }}:</label>
                                                        <p class="form-control-plaintext">
                                                            {{ $viewEmployee->gender == 'male' ? __('ذكر') : ($viewEmployee->gender == 'female' ? __('أنثى') : __('غير محدد')) }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('تاريخ الميلاد') }}:</label>
                                                        <p class="form-control-plaintext">
                                                            {{ $viewEmployee->date_of_birth ? $viewEmployee->date_of_birth->format('Y-m-d') : __('غير محدد') }}
                                                        </p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('الحالة') }}:</label>
                                                        <p class="form-control-plaintext">
                                                            <span class="badge {{ $viewEmployee->status == 'مفعل' ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $viewEmployee->status }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                                @if($viewEmployee->information)
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">{{ __('معلومات إضافية') }}:</label>
                                                        <p class="form-control-plaintext">{{ $viewEmployee->information }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- بيانات تفصيلية -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card">
                                            <div class="card-header bg-primary text-white card-title">
                                                {{ __('بيانات تفصيلية') }}
                                            </div>
                                            <div class="card-body">
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('البلد') }}:</label>
                                                        <p class="form-control-plaintext">{{ $viewEmployee->country?->title ?? __('غير محدد') }}</p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('المحافظة') }}:</label>
                                                        <p class="form-control-plaintext">{{ $viewEmployee->state?->title ?? __('غير محدد') }}</p>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('المدينة') }}:</label>
                                                        <p class="form-control-plaintext">{{ $viewEmployee->city?->title ?? __('غير محدد') }}</p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('المنطقة') }}:</label>
                                                        <p class="form-control-plaintext">{{ $viewEmployee->town?->title ?? __('غير محدد') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- بيانات وظيفة -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card">
                                            <div class="card-header bg-primary text-white card-title">
                                                {{ __('بيانات وظيفة') }}
                                            </div>
                                            <div class="card-body">
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('الوظيفة') }}:</label>
                                                        <p class="form-control-plaintext">{{ $viewEmployee->job?->title ?? __('غير محدد') }}</p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('القسم') }}:</label>
                                                        <p class="form-control-plaintext">{{ $viewEmployee->department?->title ?? __('غير محدد') }}</p>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('المستوى الوظيفي') }}:</label>
                                                        <p class="form-control-plaintext">{{ $viewEmployee->job_level ?? __('غير محدد') }}</p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('مستوى التعليم') }}:</label>
                                                        <p class="form-control-plaintext">{{ $viewEmployee->education ?? __('غير محدد') }}</p>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('تاريخ التوظيف') }}:</label>
                                                        <p class="form-control-plaintext">
                                                            {{ $viewEmployee->date_of_hire ? $viewEmployee->date_of_hire->format('Y-m-d') : __('غير محدد') }}
                                                        </p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('تاريخ الانتهاء') }}:</label>
                                                        <p class="form-control-plaintext">
                                                            {{ $viewEmployee->date_of_fire ? $viewEmployee->date_of_fire->format('Y-m-d') : __('غير محدد') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- بيانات المرتبات -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card">
                                            <div class="card-header bg-primary text-white card-title">
                                                {{ __('بيانات المرتبات') }}
                                            </div>
                                            <div class="card-body">
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('الشيفت') }}:</label>
                                                        <p class="form-control-plaintext">
                                                            {{ $viewEmployee->shift ? $viewEmployee->shift->start_time . ' - ' . $viewEmployee->shift->end_time : __('غير محدد') }}
                                                        </p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('المرتب') }}:</label>
                                                        <p class="form-control-plaintext">{{ $viewEmployee->salary ?? __('غير محدد') }}</p>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('نوع الاستحقاق') }}:</label>
                                                        <p class="form-control-plaintext">{{ $viewEmployee->salary_type ?? __('غير محدد') }}</p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('رقم البصمة') }}:</label>
                                                        <p class="form-control-plaintext">{{ $viewEmployee->finger_print_id ?? __('غير محدد') }}</p>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('الاسم في البصمة') }}:</label>
                                                        <p class="form-control-plaintext">{{ $viewEmployee->finger_print_name ?? __('غير محدد') }}</p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('باسورد الهاتف') }}:</label>
                                                        <p class="form-control-plaintext">{{ $viewEmployee->password ?? __('غير محدد') }}</p>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('الساعة الإضافي تحسب ك') }}:</label>
                                                        <p class="form-control-plaintext">{{ $viewEmployee->additional_hour_calculation ?? __('غير محدد') }}</p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold">{{ __('اليوم الإضافي يحسب ك') }}:</label>
                                                        <p class="form-control-plaintext">{{ $viewEmployee->additional_day_calculation ?? __('غير محدد') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeView">{{ __('إغلاق') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('livewire:initialized', () => {
                let modalInstance = null;
                let viewModalInstance = null;
                const modalElement = document.getElementById('employeeModal');
                const viewModalElement = document.getElementById('viewEmployeeModal');

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

                Livewire.on('showViewModal', () => {
                    if (!viewModalInstance) {
                        viewModalInstance = new bootstrap.Modal(viewModalElement);
                    }
                    viewModalInstance.show();
                });

                Livewire.on('hideViewModal', () => {
                    if (viewModalInstance) {
                        viewModalInstance.hide();
                    }
                });

                modalElement.addEventListener('hidden.bs.modal', function() {
                    modalInstance = null;
                });

                viewModalElement.addEventListener('hidden.bs.modal', function() {
                    viewModalInstance = null;
                });
            });
        </script>
    </div>
