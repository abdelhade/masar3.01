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
use App\Models\Kpi;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
        $shifts = [],
        $kpis = [];

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

    // KPI fields
    public $kpi_ids = [],
        $kpi_weights = [];
    public $selected_kpi_id = '';
    
    // Image URL for current employee
    public $currentImageUrl = null;

    protected function rules()
    {
        return [
            'name' => 'required|string|unique:employees,name,' . $this->employeeId,
            'email' => 'required|email|unique:employees,email,' . $this->employeeId,
            'phone' => 'required|string|unique:employees,phone,' . $this->employeeId,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date',
            'nationalId' => 'nullable|string|unique:employees,nationalId,' . $this->employeeId,
            'marital_status' => 'nullable|in:غير متزوج,متزوج,مطلق,أرمل',
            'education' => 'nullable|in:دبلوم,بكالوريوس,ماجستير,دكتوراه',
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
            'password' => $this->isEdit ? 'nullable|string|min:6' : 'required|string|min:6',
            'additional_hour_calculation' => 'nullable|numeric',
            'additional_day_calculation' => 'nullable|numeric',
            'kpi_ids' => 'nullable|array',
            'kpi_ids.*' => 'exists:kpis,id',
            'kpi_weights' => 'nullable|array',
            'kpi_weights.*' => 'nullable|integer|min:0|max:100',
            'selected_kpi_id' => 'nullable|exists:kpis,id',
        ];
    }

    protected function messages()
    {
        return [
            'name.required' => 'الاسم مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'marital_status.in' => 'الحالة الاجتماعية غير موجودة.',
            'education.in' => 'مستوى التعليم غير موجود',
            'status.required' => 'الحالة مطلوبة.',
            'status.in' => 'الحالة غير موجودة.',
            'name.unique' => 'الاسم مستخدم من قبل بواسطة موظف آخر بالفعل اكتب اسم آخر.',
            'email.unique' => 'البريد الإلكتروني مستخدم من قبل بواسطة موظف آخر بالفعل اكتب بريد آخر.',
            'phone.unique' => 'رقم الهاتف مستخدم من قبل بواسطة موظف آخر بالفعل اكتب رقم هاتف آخر.',
            'nationalId.unique' => 'رقم الهوية مستخدم من قبل بواسطة موظف آخر بالفعل اكتب رقم هوية آخر.',
            'finger_print_id.integer' => 'رقم البصمة يجب أن يكون رقماً صحيحاً.',
            'finger_print_id.unique' => 'رقم البصمة مستخدم من قبل بواسطة موظف آخر بالفعل اكتب رقم بصمة آخر.',
            'finger_print_name.unique' => 'اسم البصمة مستخدم من قبل بواسطة موظف آخر بالفعل اكتب اسم بصمة آخر.',
            'finger_print_name.string' => 'اسم البصمة يجب أن يكون نصاً.',
            'finger_print_name.max' => 'اسم البصمة يجب أن يكون أقل من 255 حرفاً.',
            'finger_print_name.min' => 'اسم البصمة يجب أن يكون أكثر من 3 حرفاً.',
            'kpi_ids.array' => 'معدلات الأداء يجب أن تكون قائمة.',
            'kpi_ids.*.exists' => 'معدل الأداء المحدد غير موجود.',
            'kpi_weights.array' => 'أوزان معدلات الأداء يجب أن تكون قائمة.',
            'kpi_weights.*.integer' => 'الوزن النسبي يجب أن يكون رقماً صحيحاً.',
            'kpi_weights.*.min' => 'الوزن النسبي يجب أن يكون 0 أو أكثر.',
            'kpi_weights.*.max' => 'الوزن النسبي يجب أن يكون 100 أو أقل.',
            'selected_kpi_id.exists' => 'معدل الأداء المحدد غير موجود.',
            'image.image' => 'الملف المرفق يجب أن يكون صورة.',
            'image.mimes' => 'نوع الصورة يجب أن يكون: jpeg, png, jpg, gif.',
            'image.max' => 'حجم الصورة يجب أن يكون أقل من 2 ميجابايت.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.',
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
        $this->kpis = Kpi::all();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getEmployeesProperty()
    {
        return Employee::with('media')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function create()
    {
        $this->resetValidation();
        $this->resetEmployeeFields();
        $this->image = null;
        $this->isEdit = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $employee = Employee::with('media')->findOrFail($id);
        $this->employeeId = $employee->id;
        
        // Set current image URL for Alpine.js with URL correction for Laragon
        $this->currentImageUrl = $employee->getFirstMediaUrlFixed('employee_images') ?: null;
        
        foreach (['name', 'email', 'phone', 'image', 'gender', 'date_of_birth', 'nationalId', 'marital_status', 'education', 'information', 'status', 'country_id', 'city_id', 'state_id', 'town_id', 'job_id', 'department_id', 'date_of_hire', 'date_of_fire', 'job_level', 'salary', 'finger_print_id', 'finger_print_name', 'salary_type', 'shift_id', 'additional_hour_calculation', 'additional_day_calculation', 'kpi_ids', 'kpi_weights'] as $field) {
            if (in_array($field, ['date_of_birth', 'date_of_hire', 'date_of_fire'])) {
                $this->$field = $employee->$field ? $employee->$field->format('Y-m-d') : null;
            } else {
                $this->$field = $employee->$field;
            }
        }
        
        // Clear any previous upload and don't load password in edit mode - leave it empty for security
        $this->password = null;

        // Load employee KPIs
        $this->kpi_ids = $employee->kpis->pluck('id')->toArray();
        $this->kpi_weights = [];
        foreach ($employee->kpis as $kpi) {
            $this->kpi_weights[$kpi->id] = $kpi->pivot->weight_percentage;
        }

        $this->isEdit = true;
        $this->showModal = true;
    }

    public function save()
    {
        // Convert finger_print_id to integer before validation
        if ($this->finger_print_id !== null && $this->finger_print_id !== '') {
            $this->finger_print_id = (int) $this->finger_print_id;
        }

        // Fix image validation - handle Livewire's empty array behavior
        if (is_array($this->image) && empty($this->image)) {
            $this->image = null;
        }

        // Validate the data
        $validated = $this->validate();

        // Custom validation for KPI weights - must equal exactly 100%
        if (!empty($this->kpi_ids) && !empty($this->kpi_weights)) {
            $totalWeight = 0;
            $selectedKpis = 0;

            foreach ($this->kpi_ids as $kpiId) {
                if (isset($this->kpi_weights[$kpiId]) && $this->kpi_weights[$kpiId] > 0) {
                    $totalWeight += $this->kpi_weights[$kpiId];
                    $selectedKpis++;
                }
            }

            if ($selectedKpis > 0 && $totalWeight != 100) {
                $this->addError('kpi_weights', 'مجموع الأوزان النسبية لمعدلات الأداء يجب أن يكون 100% بالضبط. المجموع الحالي: ' . $totalWeight . '%');
                return;
            }
        }

        try {
            // Hash password if it exists and is not empty
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                // In edit mode, if password is empty, don't update it
                if ($this->isEdit) {
                    unset($validated['password']);
                } else {
                    // In create mode, password is required
                unset($validated['password']);
                }
            }

            // Handle image upload using Spatie Media Library
            $imageFile = null;
            if (isset($validated['image']) && $validated['image']) {
                $imageFile = $validated['image'];
                unset($validated['image']); // Remove from validated data
            }

            // Remove KPI fields from validated data
            unset($validated['kpi_ids'], $validated['kpi_weights']);

            if ($this->isEdit && $this->employeeId) {
                $employee = Employee::find($this->employeeId);
                $employee->update($validated);

                // Sync KPIs with weights
                $kpiData = [];
                foreach ($this->kpi_ids as $kpiId) {
                    if (isset($this->kpi_weights[$kpiId]) && $this->kpi_weights[$kpiId] > 0) {
                        $kpiData[$kpiId] = ['weight_percentage' => $this->kpi_weights[$kpiId]];
                    }
                }
                $employee->kpis()->sync($kpiData);

                session()->flash('success', __('تم تحديث الموظف بنجاح.'));
            } else {
                $employee = Employee::create($validated);

                // Sync KPIs with weights
                $kpiData = [];
                foreach ($this->kpi_ids as $kpiId) {
                    if (isset($this->kpi_weights[$kpiId]) && $this->kpi_weights[$kpiId] > 0) {
                        $kpiData[$kpiId] = ['weight_percentage' => $this->kpi_weights[$kpiId]];
                    }
                }
                $employee->kpis()->sync($kpiData);

                session()->flash('success', __('تم إنشاء الموظف بنجاح.'));
            }

            // Handle image upload (single approach for both create and edit)
            if ($imageFile) {
                // Clear existing images for edit mode
                if ($this->isEdit) {
                    $employee->clearMediaCollection('employee_images');
                }
                
                // Add new image
                $employee->addMedia($imageFile->getRealPath())
                    ->usingName($imageFile->getClientOriginalName())
                    ->toMediaCollection('employee_images');
            }
            $this->showModal = false;
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
        foreach (['employeeId', 'name', 'email', 'phone', 'image', 'gender', 'date_of_birth', 'nationalId', 'marital_status', 'education', 'information', 'status', 'country_id', 'city_id', 'state_id', 'town_id', 'job_id', 'department_id', 'date_of_hire', 'date_of_fire', 'job_level', 'salary', 'finger_print_id', 'finger_print_name', 'salary_type', 'shift_id', 'password', 'additional_hour_calculation', 'additional_day_calculation', 'selected_kpi_id'] as $field) {
            $this->$field = null;
        }

        $this->kpi_ids = [];
        $this->kpi_weights = [];
        $this->status = 'مفعل';
        $this->image = null;
    }

    public function view($id)
    {
        $this->viewEmployee = Employee::with(['country', 'city', 'state', 'town', 'job', 'department', 'shift', 'kpis', 'media'])->find($id);
        $this->showViewModal = true;
    }

    public function closeView()
    {
        $this->showViewModal = false;
        $this->viewEmployee = null;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->resetEmployeeFields();
        $this->image = null;
        
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function addKpi()
    {
        if ($this->selected_kpi_id) {
            if (!is_array($this->kpi_ids)) {
                $this->kpi_ids = [];
            }
            if (!is_array($this->kpi_weights)) {
                $this->kpi_weights = [];
            }

            if (!in_array($this->selected_kpi_id, $this->kpi_ids)) {
                $this->kpi_ids[] = $this->selected_kpi_id;
                $this->kpi_weights[$this->selected_kpi_id] = 0;
                $this->selected_kpi_id = '';
                
                // Dispatch event to clear Alpine.js selection
                $this->dispatch('kpiAdded');

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => __('تم إضافة معدل الأداء بنجاح.'),
                ]);
            } else {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => __('هذا معدل الأداء مضاف بالفعل.'),
                ]);
            }
        } else {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('يرجى اختيار معدل الأداء.'),
            ]);
        }
    }

    public function removeKpi($kpiId)
    {
        if (!is_array($this->kpi_ids)) {
            $this->kpi_ids = [];
        }
        if (!is_array($this->kpi_weights)) {
            $this->kpi_weights = [];
        }

        $this->kpi_ids = array_filter($this->kpi_ids, function ($id) use ($kpiId) {
            return $id != $kpiId;
        });
        unset($this->kpi_weights[$kpiId]);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('تم حذف معدل الأداء بنجاح.'),
        ]);
    }
}; ?>

<div style="font-family: 'Cairo', sans-serif; direction: rtl;" 
     x-data="employeeManager({
        showModal: $wire.entangle('showModal'),
        showViewModal: $wire.entangle('showViewModal'),
        kpiIds: $wire.entangle('kpi_ids'),
        kpiWeights: $wire.entangle('kpi_weights'),
        selectedKpiId: $wire.entangle('selected_kpi_id'),
        currentImageUrl: $wire.entangle('currentImageUrl'),
        kpis: @js($kpis),
        isEdit: $wire.entangle('isEdit')
     })" 
     x-init="init()">

    <!-- Notification Container -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999; margin-top: 60px;">
        <template x-for="notification in notifications" :key="notification.id">
            <div class="alert mb-2 shadow-lg"
                :class="{
                    'alert-success': notification.type === 'success',
                    'alert-danger': notification.type === 'error',
                    'alert-info': notification.type === 'info'
                }"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-full"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-x-0"
                x-transition:leave-end="opacity-0 transform translate-x-full" 
                role="alert">
                <i class="fas me-2"
                    :class="{
                        'fa-check-circle': notification.type === 'success',
                        'fa-exclamation-circle': notification.type === 'error',
                        'fa-info-circle': notification.type === 'info'
                    }"></i>
                <span x-text="notification.message"></span>
            </div>
        </template>
    </div>

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

        <!-- Modal (Create/Edit) - Pure Alpine.js -->
        <template x-if="showModal">
            <div>
                <!-- Backdrop -->
                <div class="modal-backdrop fade show" @click="closeEmployeeModal()"></div>
                
                <!-- Modal -->
                <div class="modal fade show" 
                     style="display: block; z-index: 1056;" 
             tabindex="-1"
             role="dialog"
                     @click.self="closeEmployeeModal()">
                    <div class="modal-dialog modal-fullscreen" role="document">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                            <h5 class="modal-title font-family-cairo fw-bold">
                                <span x-text="isEdit ? '{{ __('تعديل موظف') }}' : '{{ __('إضافة موظف') }}'"></span>
                        </h5>
                            <button type="button" class="btn-close m-3" @click="closeEmployeeModal()" aria-label="إغلاق"></button>
                    </div>
                        
                        <div class="modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                            
                            <form wire:submit.prevent="save" @keydown.enter.prevent="">
                                @include('livewire.hr-management.employees.partials.employee-form')
                            </form>
                                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-secondary btn-md" @click="closeEmployeeModal()">
                            {{ __('إلغاء') }}
                        </button>
                            <button type="button" class="btn btn-primary btn-md" @click="$wire.save()">
                                <span x-text="isEdit ? '{{ __('تحديث') }}' : '{{ __('حفظ') }}'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
            </div>
        </template>

        <!-- View Employee Modal - Pure Alpine.js -->
        <template x-if="showViewModal">
            <div>
                <!-- Backdrop -->
                <div class="modal-backdrop fade show" @click="closeViewEmployeeModal()"></div>
                
                <!-- Modal -->
                <div class="modal fade show" 
                     style="display: block; z-index: 1056;" 
             tabindex="-1"
             role="dialog"
                     @click.self="closeViewEmployeeModal()">
                    <div class="modal-dialog modal-fullscreen" role="document">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                            <h5 class="modal-title font-family-cairo fw-bold">
                            {{ __('عرض تفاصيل الموظف') }}
                        </h5>
                            <button type="button" class="btn-close m-3" @click="closeViewEmployeeModal()" aria-label="إغلاق"></button>
                    </div>
                        
                    <div class="modal-body">
                        @if ($viewEmployee)
                            @include('livewire.hr-management.employees.partials.employee-view')
                        @else
                            <div class="alert alert-danger">
                                <strong>Error:</strong> No employee data loaded
                            </div>
                        @endif
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-secondary btn-md" @click="closeViewEmployeeModal()">
                            {{ __('إغلاق') }}
                        </button>
                    </div>
                </div>
                </div>
                </div>
            </div>
        </template>
            </div>
        </div>

<!-- Alpine.js Component Definition -->
@push('scripts')
        <script>
document.addEventListener('alpine:init', () => {
    Alpine.data('employeeManager', (config) => ({
        // State synced with Livewire
        showModal: config.showModal,
        showViewModal: config.showViewModal,
        kpiIds: config.kpiIds,
        kpiWeights: config.kpiWeights,
        selectedKpiId: config.selectedKpiId,
        currentImageUrl: config.currentImageUrl,
        isEdit: config.isEdit,
        
        // Local state
        kpis: config.kpis,
        activeTab: 'personal',
        notifications: [],
        imagePreview: null,
        showPassword: false,
        
        // KPI Search state
        kpiSearch: '',
        kpiSearchOpen: false,
        kpiSearchIndex: -1,
        
        // Computed
        get totalKpiWeight() {
                        let total = 0;
            this.kpiIds.forEach(kpiId => {
                total += parseInt(this.kpiWeights[kpiId]) || 0;
            });
            return total;
        },
        
        get weightStatus() {
            if (this.totalKpiWeight === 100) return 'success';
            if (this.totalKpiWeight > 100) return 'danger';
            return 'warning';
        },
        
        get weightMessage() {
            if (this.totalKpiWeight === 100) {
                return 'ممتاز! تم اكتمال النسبة بنجاح. يمكنك الآن حفظ البيانات.';
            } else if (this.totalKpiWeight > 100) {
                return `المجموع الحالي ${this.totalKpiWeight}% أكبر من 100%. يرجى تقليل الأوزان.`;
                            } else {
                return `المجموع الحالي ${this.totalKpiWeight}% أقل من 100%. يرجى إكمال الأوزان.`;
            }
        },
        
                get availableKpis() {
                    return this.kpis.filter(kpi => !this.kpiIds.includes(kpi.id));
                },
                
        get filteredKpis() {
            if (!this.kpiSearch) return this.availableKpis;
            const search = this.kpiSearch.toLowerCase();
                    return this.availableKpis.filter(kpi =>
                kpi.name.toLowerCase().includes(search) || 
                (kpi.description && kpi.description.toLowerCase().includes(search))
                    );
                },
                
                // Methods
                init() {
            // Listen for Livewire notifications
            this.$wire.on('notify', (data) => {
                this.addNotification(data.type, data.message);
            });
            
            // Listen for KPI added event to clear selection
            this.$wire.on('kpiAdded', () => {
                this.clearKpiSelection();
                console.log('✅ KPI added, selection cleared');
            });
            
            // Watch for modal body overflow
            this.$watch('showModal', (value) => {
                document.body.classList.toggle('modal-open', value);
            });
            
            this.$watch('showViewModal', (value) => {
                document.body.classList.toggle('modal-open', value);
            });
        },
        
        addNotification(type, message) {
            const id = Date.now();
            this.notifications.push({ id, type, message });
            setTimeout(() => {
                this.notifications = this.notifications.filter(n => n.id !== id);
            }, 3000);
        },
        
        closeEmployeeModal() {
            this.showModal = false;
            this.$wire.closeModal();
            this.resetImagePreview();
        },
        
        closeViewEmployeeModal() {
            this.showViewModal = false;
            this.$wire.closeView();
        },
        
        switchTab(tab) {
            this.activeTab = tab;
        },
        
        // Image handling
        handleImageChange(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        
        resetImagePreview() {
            this.imagePreview = null;
        },
        
        togglePassword() {
            this.showPassword = !this.showPassword;
        },
        
        // KPI Management
                getKpiName(kpiId) {
                    const kpi = this.kpis.find(k => k.id == kpiId);
                    return kpi ? kpi.name : '';
                },

                getKpiDescription(kpiId) {
                    const kpi = this.kpis.find(k => k.id == kpiId);
                    return kpi && kpi.description ? kpi.description.substring(0, 50) + '...' : '';
                },

        selectKpi(kpi) {
            this.selectedKpiId = kpi.id;
            this.kpiSearchOpen = false;
            this.kpiSearch = '';
            this.kpiSearchIndex = -1;
        },
        
        clearKpiSelection() {
            this.selectedKpiId = '';
            this.kpiSearch = '';
        },
        
        navigateKpiDown() {
            if (this.kpiSearchIndex < this.filteredKpis.length - 1) {
                this.kpiSearchIndex++;
            }
        },
        
        navigateKpiUp() {
            if (this.kpiSearchIndex > 0) {
                this.kpiSearchIndex--;
            }
        },
        
        selectCurrentKpi() {
            if (this.kpiSearchIndex >= 0 && this.kpiSearchIndex < this.filteredKpis.length) {
                this.selectKpi(this.filteredKpis[this.kpiSearchIndex]);
                    }
                }
            }));
        });
        </script>
@endpush

