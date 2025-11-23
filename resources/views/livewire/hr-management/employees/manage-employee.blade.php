<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use App\Models\Employee;
use App\Models\Country;
use App\Models\City;
use App\Models\State;
use App\Models\Town;
use App\Models\Department;
use App\Models\EmployeesJob;
use App\Models\Shift;
use App\Models\Kpi;
use App\Models\LeaveType;
use App\Models\EmployeeLeaveBalance;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Models\AccHead;
use Modules\Accounts\Services\AccountService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


new class extends Component {
    use WithPagination, WithFileUploads;

    public $salary_basic_accounts = [];
    public $salary_basic_account_id = null;
    public $opening_balance = null;

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
        $kpis = [],
        $leaveTypes = [];

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
    public $job_id, $department_id, $date_of_hire, $date_of_fire, $job_level, $salary, $finger_print_id, $finger_print_name, $salary_type, $shift_id, $password, $additional_hour_calculation, $additional_day_calculation, $late_hour_calculation, $late_day_calculation;

    // KPI fields
    public $kpi_ids = [],
        $kpi_weights = [];
    public $selected_kpi_id = '';

    // Leave Balance fields
    public $leave_balances = [];
    public $selected_leave_type_id = '';

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
            'marital_status' => 'nullable|in:single,married,divorced,widowed,غير متزوج,متزوج,مطلق,أرمل',
            'education' => 'nullable|in:diploma,bachelor,master,doctorate,دبلوم,بكالوريوس,ماجستير,دكتوراه',
            'information' => 'nullable|string',
            'status' => 'required|in:مفعل,معطل,active,inactive',
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
            'late_hour_calculation' => 'nullable|numeric',
            'late_day_calculation' => 'nullable|numeric',
            'kpi_ids' => 'nullable|array',
            'kpi_ids.*' => 'exists:kpis,id',
            'kpi_weights' => 'nullable|array',
            'kpi_weights.*' => 'nullable|integer|min:0|max:100',
            'selected_kpi_id' => 'nullable|exists:kpis,id',
            'salary_basic_account_id' => 'required|exists:acc_head,id',
            'opening_balance' => 'nullable|numeric',
            'leave_balances' => 'nullable|array',
            'leave_balances.*.leave_type_id' => 'required|exists:leave_types,id',
            'leave_balances.*.year' => 'required|integer|min:2020|max:2030',
            'leave_balances.*.opening_balance_days' => 'nullable|numeric|min:0',
            'leave_balances.*.accrued_days' => 'nullable|numeric|min:0',
            'leave_balances.*.used_days' => 'nullable|numeric|min:0',
            'leave_balances.*.pending_days' => 'nullable|numeric|min:0',
            'leave_balances.*.carried_over_days' => 'nullable|numeric|min:0',
            'leave_balances.*.notes' => 'nullable|string',
            'selected_leave_type_id' => 'nullable|exists:leave_types,id',
        ];
    }

    protected function messages()
    {
        return [
            // Name validation
            'name.required' => __('hr.name_required'),
            'name.string' => __('hr.name_string'),
            'name.unique' => __('hr.name_unique'),
            
            // Email validation
            'email.required' => __('hr.email_required'),
            'email.email' => __('hr.email_email'),
            'email.unique' => __('hr.email_unique'),
            
            // Phone validation
            'phone.required' => __('hr.phone_required'),
            'phone.string' => __('hr.phone_string'),
            'phone.unique' => __('hr.phone_unique'),
            
            // Image validation
            'image.image' => __('hr.image_image'),
            'image.mimes' => __('hr.image_mimes'),
            'image.max' => __('hr.image_max'),
            
            // Gender validation
            'gender.in' => __('hr.gender_invalid'),
            
            // Date of birth validation
            'date_of_birth.date' => __('hr.date_of_birth_date'),
            
            // National ID validation
            'nationalId.string' => __('hr.nationalId_string'),
            'nationalId.unique' => __('hr.nationalId_unique'),
            
            // Marital status validation
            'marital_status.in' => __('hr.marital_status_invalid'),
            
            // Education validation
            'education.in' => __('hr.education_invalid'),
            
            // Information validation
            'information.string' => __('hr.information_string'),
            
            // Status validation
            'status.required' => __('hr.status_required'),
            'status.in' => __('hr.status_invalid'),
            
            // Location validation
            'country_id.exists' => __('hr.country_id_exists'),
            'city_id.exists' => __('hr.city_id_exists'),
            'state_id.exists' => __('hr.state_id_exists'),
            'town_id.exists' => __('hr.town_id_exists'),
            
            // Job and department validation
            'job_id.exists' => __('hr.job_id_exists'),
            'department_id.exists' => __('hr.department_id_exists'),
            
            // Date validation
            'date_of_hire.date' => __('hr.date_of_hire_date'),
            'date_of_fire.date' => __('hr.date_of_fire_date'),
            
            // Salary validation
            'salary.numeric' => __('hr.salary_numeric'),
            
            // Fingerprint validation
            'finger_print_id.integer' => __('hr.finger_print_id_integer'),
            'finger_print_id.unique' => __('hr.finger_print_id_unique'),
            'finger_print_name.string' => __('hr.finger_print_name_string'),
            'finger_print_name.unique' => __('hr.finger_print_name_unique'),
            'finger_print_name.max' => __('hr.finger_print_name_max'),
            'finger_print_name.min' => __('hr.finger_print_name_min'),
            
            // Shift validation
            'shift_id.exists' => __('hr.shift_id_exists'),
            
            // Password validation
            'password.required' => __('hr.password_required'),
            'password.string' => __('hr.password_string'),
            'password.min' => __('hr.password_min'),
            
            // Calculation validation
            'additional_hour_calculation.numeric' => __('hr.additional_hour_calculation_numeric'),
            'additional_day_calculation.numeric' => __('hr.additional_day_calculation_numeric'),
            'late_hour_calculation.numeric' => __('hr.late_hour_calculation_numeric'),
            'late_day_calculation.numeric' => __('hr.late_day_calculation_numeric'),
            
            // KPI validation
            'kpi_ids.array' => __('hr.kpi_ids_array'),
            'kpi_ids.*.exists' => __('hr.kpi_ids_exists'),
            'kpi_weights.array' => __('hr.kpi_weights_array'),
            'kpi_weights.*.integer' => __('hr.kpi_weights_integer'),
            'kpi_weights.*.min' => __('hr.kpi_weights_min'),
            'kpi_weights.*.max' => __('hr.kpi_weights_max'),
            'selected_kpi_id.exists' => __('hr.selected_kpi_id_exists'),
            
            // Account validation
            'salary_basic_account_id.required' => __('hr.salary_basic_account_id_required'),
            'salary_basic_account_id.exists' => __('hr.salary_basic_account_id_exists'),
            'opening_balance.numeric' => __('hr.opening_balance_numeric'),
            
            // Leave balance validation
            'leave_balances.*.leave_type_id.required' => __('hr.leave_balances_leave_type_id_required'),
            'leave_balances.*.leave_type_id.exists' => __('hr.leave_balances_leave_type_id_exists'),
            'leave_balances.*.year.required' => __('hr.leave_balances_year_required'),
            'leave_balances.*.year.integer' => __('hr.leave_balances_year_integer'),
            'leave_balances.*.year.min' => __('hr.leave_balances_year_min'),
            'leave_balances.*.year.max' => __('hr.leave_balances_year_max'),
            'leave_balances.*.opening_balance_days.numeric' => __('hr.leave_balances_opening_balance_days_numeric'),
            'leave_balances.*.opening_balance_days.min' => __('hr.leave_balances_opening_balance_days_min'),
            'leave_balances.*.accrued_days.numeric' => __('hr.leave_balances_accrued_days_numeric'),
            'leave_balances.*.accrued_days.min' => __('hr.leave_balances_accrued_days_min'),
            'leave_balances.*.used_days.numeric' => __('hr.leave_balances_used_days_numeric'),
            'leave_balances.*.used_days.min' => __('hr.leave_balances_used_days_min'),
            'leave_balances.*.pending_days.numeric' => __('hr.leave_balances_pending_days_numeric'),
            'leave_balances.*.pending_days.min' => __('hr.leave_balances_pending_days_min'),
            'leave_balances.*.carried_over_days.numeric' => __('hr.leave_balances_carried_over_days_numeric'),
            'leave_balances.*.carried_over_days.min' => __('hr.leave_balances_carried_over_days_min'),
            'leave_balances.*.notes.string' => __('hr.leave_balances_notes_string'),
            'selected_leave_type_id.exists' => __('hr.selected_leave_type_id_exists'),
        ];
    }

    public function mount()
    {
        $this->salary_basic_accounts = AccHead::where([
            'acc_type' => 5,
            'is_basic' => 1,
        ])->get()->select('id','aname','code')->toArray();
        $this->countries = Country::all();
        $this->cities = City::all();
        $this->states = State::all();
        $this->towns = Town::all();
        $this->departments = Department::all();
        $this->jobs = EmployeesJob::all();
        $this->shifts = Shift::all();
        $this->kpis = Kpi::all();
        $this->leaveTypes = LeaveType::orderBy('name')->get();
        $this->currentImageUrl = null;
        $this->selectedFileName = '';
        $this->selectedFileSize = '';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function employees(): LengthAwarePaginator
    {
        return Employee::with(['media', 'department', 'job', 'shift'])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function create(): void
    {
        $this->resetValidation();
        $this->resetEmployeeFields();
        $this->image = null;
        $this->isEdit = false;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $this->resetValidation();
        DB::transaction(function () use ($id) {
            // Eager load all relationships to avoid N+1 queries
            $employee = Employee::with([
                'media',
                'account.haveParent', // Load parent account relationship
                'kpis',
                'leaveBalances.leaveType',
                'department',
                'job',
                'shift',
                'country',
                'city',
                'state',
                'town'
            ])->findOrFail($id);
            $this->employeeId = $employee->id;

            // Set current image URL using the accessor (works in both local and production)
            $this->currentImageUrl = $employee->image_url;

            // Load basic employee fields
            foreach (['name', 'email', 'phone', 'image', 'gender', 'nationalId', 'marital_status', 'education', 'information', 'status', 'country_id', 'city_id', 'state_id', 'town_id', 'job_id', 'department_id', 'job_level', 'salary', 'finger_print_id', 'finger_print_name', 'salary_type', 'shift_id', 'additional_hour_calculation', 'additional_day_calculation', 'late_hour_calculation', 'late_day_calculation'] as $field) {
                $this->$field = $employee->$field;
            }

            // Handle date fields
            $this->date_of_birth = $employee->date_of_birth ? $employee->date_of_birth->format('Y-m-d') : null;
            $this->date_of_hire = $employee->date_of_hire ? $employee->date_of_hire->format('Y-m-d') : null;
            $this->date_of_fire = $employee->date_of_fire ? $employee->date_of_fire->format('Y-m-d') : null;

            // Handle account fields (with eager loading to avoid N+1)
            $this->salary_basic_account_id = $employee->account?->parent_id ?? null;
            $this->opening_balance = $employee->account?->start_balance ?? null;

            // Clear any previous upload and don't load password in edit mode - leave it empty for security
            $this->password = null;

            // Load employee KPIs
            $this->kpi_ids = $employee->kpis->pluck('id')->toArray();
            $this->kpi_weights = [];
            foreach ($employee->kpis as $kpi) {
                $this->kpi_weights[$kpi->id] = $kpi->pivot->weight_percentage;
            }

            // Load employee leave balances
            $this->leave_balances = [];
            foreach ($employee->leaveBalances as $balance) {
                $key = $balance->leave_type_id . '_' . $balance->year;
                $this->leave_balances[$key] = [
                    'leave_type_id' => $balance->leave_type_id,
                    'year' => $balance->year,
                    'opening_balance_days' => $balance->opening_balance_days,
                    'accrued_days' => $balance->accrued_days,
                    'used_days' => $balance->used_days,
                    'pending_days' => $balance->pending_days,
                    'carried_over_days' => $balance->carried_over_days,
                    'notes' => $balance->notes,
                ];
            }

            $this->isEdit = true;
            $this->showModal = true;
        });
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
                $this->addError('kpi_weights', __('hr.kpi_weights_error', ['total' => $totalWeight]));
                return;
            }
        }

        try {
            $imageFile = null;
            $employee = null;

            DB::transaction(function () use (&$validated, &$imageFile, &$employee) {
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

                // Pull out image file reference; don't process media inside the transaction
                if (isset($validated['image']) && $validated['image']) {
                    $imageFile = $validated['image'];
                    unset($validated['image']); // Remove from validated data
                }

                // Remove KPI fields and leave balances from validated data
                unset($validated['kpi_ids'], $validated['kpi_weights'], $validated['leave_balances']);

                if ($this->isEdit && $this->employeeId) {
                    $employee = Employee::findOrFail($this->employeeId);
                    $employee->update($validated);

                    // Sync KPIs with weights
                    $kpiData = [];
                    foreach ($this->kpi_ids as $kpiId) {
                        if (isset($this->kpi_weights[$kpiId]) && $this->kpi_weights[$kpiId] > 0) {
                            $kpiData[$kpiId] = ['weight_percentage' => $this->kpi_weights[$kpiId]];
                        }
                    }
                    $employee->kpis()->sync($kpiData);

                    // Sync leave balances
                    $this->syncLeaveBalances($employee);

                    // sync the employee Account 
                    $this->syncEmployeeAccount($employee);
                    session()->flash('success', __('hr.employee_updated'));
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

                    // Sync leave balances
                    $this->syncLeaveBalances($employee);

                    // Create employee account for new employee
                    $this->syncEmployeeAccount($employee);

                    session()->flash('success', __('hr.employee_created'));
                }
            });

            // Run media operations after the transaction commits successfully
            DB::afterCommit(function () use ($imageFile, $employee) {
                if ($imageFile && $employee) {
                    if ($this->isEdit) {
                        $employee->clearMediaCollection('employee_images');
                    }
                    $employee->addMedia($imageFile->getRealPath())
                        ->usingName($imageFile->getClientOriginalName())
                        ->toMediaCollection('employee_images');
                }
            });

            // Reset image-related properties after successful save
            $this->image = null;
            $this->currentImageUrl = null;

            $this->showModal = false;
        } catch (\Throwable $th) {
            session()->flash('error', __('hr.error_occurred'));
            Log::error($th);
        }
    }

    public function delete(int $id): void
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
        session()->flash('success', __('hr.employee_deleted'));
    }

    public function resetEmployeeFields()
    {
        foreach (['employeeId', 'name', 'email', 'phone', 'image', 'gender', 'date_of_birth', 'nationalId', 'marital_status', 'education', 'information', 'status', 'country_id', 'city_id', 'state_id', 'town_id', 'job_id', 'department_id', 'date_of_hire', 'date_of_fire', 'job_level', 'salary', 'finger_print_id', 'finger_print_name', 'salary_type', 'shift_id', 'password', 'additional_hour_calculation', 'additional_day_calculation', 'late_hour_calculation', 'late_day_calculation', 'selected_kpi_id', 'selectedFileName', 'selectedFileSize', 'salary_basic_account_id', 'opening_balance'] as $field) {
            $this->$field = null;
        }

        $this->kpi_ids = [];
        $this->kpi_weights = [];
        $this->leave_balances = [];
        $this->selected_leave_type_id = '';
        $this->status = 'مفعل';
        $this->image = null;
    }

    public function view($id)
    {
        // Eager load all relationships to avoid N+1 queries
        $this->viewEmployee = Employee::with([
            'country',
            'city',
            'state',
            'town',
            'job',
            'department',
            'shift',
            'kpis',
            'leaveBalances.leaveType',
            'media',
            'account.haveParent' // Load account with parent to avoid N+1
        ])->findOrFail($id);
        $this->showViewModal = true;
    }

    public function closeView()
    {
        $this->showViewModal = false;
        $this->viewEmployee = null;
        $this->currentImageUrl = null;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->resetEmployeeFields();
        $this->image = null;
        $this->currentImageUrl = null;
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
                    'message' => __('hr.kpi_added'),
                ]);
            } else {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => __('hr.kpi_already_added'),
                ]);
            }
        } else {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('hr.kpi_required'),
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
            'message' => __('hr.kpi_removed'),
        ]);
    }

    public function addLeaveBalance()
    {
        if ($this->selected_leave_type_id) {
            if (!is_array($this->leave_balances)) {
                $this->leave_balances = [];
            }

            // Check if this leave type already exists for the current year (default to current year)
            $currentYear = date('Y');
            $key = $this->selected_leave_type_id . '_' . $currentYear;
            
            if (isset($this->leave_balances[$key])) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => __('hr.leave_balance_already_exists'),
                ]);
                return;
            }

            // Get existing leave types for this employee to filter them out
            $existingLeaveTypeIds = [];
            if ($this->isEdit && $this->employeeId) {
                $existingBalances = EmployeeLeaveBalance::where('employee_id', $this->employeeId)
                    ->where('leave_type_id', $this->selected_leave_type_id)
                    ->where('year', $currentYear)
                    ->exists();
                
                if ($existingBalances) {
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'message' => __('hr.leave_balance_already_exists'),
                    ]);
                    return;
                }
            }

            // Add new leave balance with default values
            $this->leave_balances[$key] = [
                'leave_type_id' => $this->selected_leave_type_id,
                'year' => $currentYear,
                'opening_balance_days' => 0,
                'accrued_days' => 0,
                'used_days' => 0,
                'pending_days' => 0,
                'carried_over_days' => 0,
                'notes' => '',
            ];

            $this->selected_leave_type_id = '';

            // Dispatch event to clear Alpine.js selection
            $this->dispatch('leaveBalanceAdded');

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => __('hr.leave_balance_added'),
            ]);
        } else {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('hr.leave_type_required'),
            ]);
        }
    }

    public function removeLeaveBalance($balanceKey)
    {
        if (!is_array($this->leave_balances)) {
            $this->leave_balances = [];
        }

        if (isset($this->leave_balances[$balanceKey])) {
            unset($this->leave_balances[$balanceKey]);
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('hr.leave_balance_removed'),
        ]);
    }

    /**
     * Sync leave balances - create or update
     */
    private function syncLeaveBalances($employee)
    {
        if (!is_array($this->leave_balances) || empty($this->leave_balances)) {
            return;
        }

        foreach ($this->leave_balances as $balanceData) {
            if (!isset($balanceData['leave_type_id']) || !isset($balanceData['year'])) {
                continue;
            }

            EmployeeLeaveBalance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'leave_type_id' => $balanceData['leave_type_id'],
                    'year' => $balanceData['year'],
                ],
                [
                    'opening_balance_days' => $balanceData['opening_balance_days'] ?? 0,
                    'accrued_days' => $balanceData['accrued_days'] ?? 0,
                    'used_days' => $balanceData['used_days'] ?? 0,
                    'pending_days' => $balanceData['pending_days'] ?? 0,
                    'carried_over_days' => $balanceData['carried_over_days'] ?? 0,
                    'notes' => $balanceData['notes'] ?? null,
                ]
            );
        }
    }

    /**
     * Sync employee account - create or update
     */
    private function syncEmployeeAccount($employee)
    {
        $employee->load('account');
        
        // Get the parent account and its children once
        $parentAccount = AccHead::where('id', $this->salary_basic_account_id)->first();
        
        if (!$parentAccount) {
            throw new \Exception('Parent account not found');
        }
        
        $lastChild = $parentAccount->haveChildrens()->orderByDesc('code')->first();
        $lastChildCode = $lastChild ? $lastChild->code : $parentAccount->code;
        
        $accountData = [
            'code' => $lastChildCode + 1,
            'aname' => $employee->name,
            'parent_id' => $this->salary_basic_account_id,
            'acc_type' => 5,
            'accountable_type' => Employee::class,
            'accountable_id' => $employee->id,
        ];
        

        if (!$employee->account) {
            $employee->account()->create($accountData);
            $employee->load('account');
            app(AccountService::class)->setStartBalances([$employee->account->id => $this->opening_balance]);
            app(AccountService::class)->recalculateOpeningCapitalAndSyncJournal();
        } else {
            unset($accountData['accountable_type'], $accountData['accountable_id']);
            $employee->account->update($accountData);
            app(AccountService::class)->setStartBalances([$employee->account->id => $this->opening_balance]);
            app(AccountService::class)->recalculateOpeningCapitalAndSyncJournal();
        }
    }
}; ?>

<div style="font-family: 'Cairo', sans-serif; direction: rtl;" x-data="employeeManager({
    showModal: $wire.entangle('showModal'),
    showViewModal: $wire.entangle('showViewModal'),
    kpiIds: $wire.entangle('kpi_ids'),
    kpiWeights: $wire.entangle('kpi_weights'),
    selectedKpiId: $wire.entangle('selected_kpi_id'),
    leaveBalances: $wire.entangle('leave_balances'),
    selectedLeaveTypeId: $wire.entangle('selected_leave_type_id'),
    currentImageUrl: $wire.entangle('currentImageUrl'),
    kpis: @js($kpis),
    leaveTypes: @js($leaveTypes),
    isEdit: $wire.entangle('isEdit')
})" x-init="init()">

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
                x-transition:leave-end="opacity-0 transform translate-x-full" role="alert">
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
                    @can('create Hr-Employees')
                        <button wire:click="create" 
                            type="button"
                            wire:loading.attr="disabled"
                            wire:target="create"
                            class="btn btn-primary font-family-cairo fw-bold">
                            <span wire:loading wire:target="create" class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>
                            <span wire:loading.remove wire:target="create">
                                {{ __('hr.add_employee') }}
                                <i class="fas fa-plus me-2"></i>
                            </span>
                        </button>
                    @endcan
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control w-auto"
                        style="min-width:200px" placeholder="{{ __('hr.search_by_name') }}">
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive" style="overflow-x: auto;">
                            <x-table-export-actions table-id="employee-table" filename="employee-table"
                                excel-label="{{ __('hr.export_excel') }}" pdf-label="{{ __('hr.export_pdf') }}" print-label="{{ __('hr.print') }}" />

                            <table id="employee-table" class="table table-striped text-center mb-0"
                                style="min-width: 1200px;">
                                <thead class="table-light align-middle">
                                    <tr>
                                        <th class="font-family-cairo fw-bold">#</th>
                                        <th class="font-family-cairo fw-bold">{{ __('hr.name') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('hr.email') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('hr.phone') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('hr.department') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('hr.job') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('hr.status') }}</th>
                                        @canany(['edit Hr-Employees', 'delete Hr-Employees'])
                                            <th class="font-family-cairo fw-bold">{{ __('hr.actions') }}</th>
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

                                            @canany(['edit Hr-Employees', 'delete Hr-Employees'])
                                                <td>
                                                    <button 
                                                        wire:click="view({{ $employee->id }})"
                                                        wire:loading.attr="disabled"
                                                        wire:target="view"
                                                        class="btn btn-info btn-sm me-1"
                                                        title="{{ __('hr.view') }}">
                                                        <span wire:loading wire:target="view({{ $employee->id }})" class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>
                                                        <i class="las la-eye fa-lg" wire:loading.remove wire:target="view({{ $employee->id }})"></i>
                                                    </button>
                                                    @can('edit Hr-Employees')
                                                        <a 
                                                            wire:click="edit({{ $employee->id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="edit"
                                                            class="btn btn-success btn-sm me-1"
                                                            title="{{ __('hr.edit') }}">
                                                            <span wire:loading wire:target="edit({{ $employee->id }})" class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>
                                                            <i class="las la-edit fa-lg" wire:loading.remove wire:target="edit({{ $employee->id }})"></i>
                                                        </a>
                                                    @endcan
                                                    @can('delete Hr-Employees')
                                                        <button 
                                                            type="button"
                                                            class="btn btn-danger btn-sm"
                                                            wire:click="delete({{ $employee->id }})"
                                                            wire:confirm="{{ __('hr.confirm_delete_employee') }}"
                                                            wire:loading.attr="disabled"
                                                            wire:target="delete"
                                                            title="{{ __('hr.delete') }}">
                                                            <span wire:loading wire:target="delete({{ $employee->id }})" class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>
                                                            <i class="las la-trash fa-lg" wire:loading.remove wire:target="delete({{ $employee->id }})"></i>
                                                        </button>
                                                    @endcan
                                                </td>
                                            @endcanany
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ auth()->user()->canany(['edit Hr-Employees', 'delete Hr-Employees']) ? '8' : '7' }}" 
                                                class="text-center font-family-cairo fw-bold py-4">
                                                <div class="alert alert-info mb-0">
                                                    <i class="las la-info-circle me-2"></i>
                                                    {{ __('hr.no_employees_found') }}
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
                <div class="modal fade show" style="display: block; z-index: 1056;" tabindex="-1" role="dialog"
                    @click.self="closeEmployeeModal()">
                    <div class="modal-dialog modal-fullscreen" role="document">
                        <div class="modal-content">
                            <!-- Modal Header -->
                            <div class="modal-header">
                                <h5 class="modal-title font-family-cairo fw-bold">
                                    <span
                                            x-text="isEdit ? '{{ __('hr.edit_employee') }}' : '{{ __('hr.add_employee') }}'"></span>
                                </h5>
                                <button type="button" class="btn-close m-3" @click="closeEmployeeModal()"
                                    aria-label="{{ __('hr.close') }}"></button>
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
                                <button type="button" class="btn btn-secondary btn-md"
                                    @click="closeEmployeeModal()">
                                    {{ __('hr.cancel') }}
                                </button>
                                <button type="button" class="btn btn-primary btn-md" @click="$wire.save()" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-not-allowed">
                                    <span x-text="isEdit ? '{{ __('hr.update') }}' : '{{ __('hr.save') }}'"></span>
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
                <div class="modal fade show" style="display: block; z-index: 1056;" tabindex="-1" role="dialog"
                    @click.self="closeViewEmployeeModal()">
                    <div class="modal-dialog modal-fullscreen" role="document">
                        <div class="modal-content">
                            <!-- Modal Header -->
                            <div class="modal-header">
                                <h5 class="modal-title font-family-cairo fw-bold">
                                    {{ __('hr.view_employee') }}
                                </h5>
                                <button type="button" class="btn-close m-3" @click="closeViewEmployeeModal()"
                                    aria-label="{{ __('hr.close') }}"></button>
                            </div>

                            <div class="modal-body">
                                @if ($viewEmployee)
                                    @include('livewire.hr-management.employees.partials.employee-view')
                                @else
                                    <div class="alert alert-danger">
                                        <strong>{{ __('hr.error') }}:</strong> {{ __('hr.no_employee_data_loaded') }}
                                    </div>
                                @endif
                            </div>

                            <!-- Modal Footer -->
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-secondary btn-md"
                                    @click="closeViewEmployeeModal()">
                                    {{ __('hr.close') }}
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
                leaveTypes: config.leaveTypes,
                leaveBalances: config.leaveBalances || {},
                selectedLeaveTypeId: config.selectedLeaveTypeId || '',
                activeTab: 'personal',
                notifications: [],
                imagePreview: null,
                showPassword: false,
                selectedFileName: '',
                selectedFileSize: '',
                isDragging: false,
                imageLoading: false,

                // KPI Search state
                kpiSearch: '',
                kpiSearchOpen: false,
                kpiSearchIndex: -1,

                // Leave Type Search state
                leaveTypeSearch: '',
                leaveTypeSearchOpen: false,
                leaveTypeSearchIndex: -1,

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
                        return __('hr.total_weight_100');
                    } else if (this.totalKpiWeight > 100) {
                        return `{{ __('hr.total_weight_greater_100', ['total' => '']) }}`.replace(':total', this.totalKpiWeight);
                    } else {
                        return `{{ __('hr.total_weight_less_100', ['total' => '']) }}`.replace(':total', this.totalKpiWeight);
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

                get leaveBalanceIds() {
                    return Object.keys(this.leaveBalances || {});
                },

                get availableLeaveTypes() {
                    // Get leave types that are not already added for the current year
                    const currentYear = new Date().getFullYear();
                    const addedLeaveTypeIds = Object.values(this.leaveBalances || {})
                        .map(b => b.leave_type_id);
                    return this.leaveTypes.filter(lt => !addedLeaveTypeIds.includes(lt.id));
                },

                get filteredLeaveTypes() {
                    if (!this.leaveTypeSearch) return this.availableLeaveTypes;
                    const search = this.leaveTypeSearch.toLowerCase();
                    return this.availableLeaveTypes.filter(lt =>
                        lt.name.toLowerCase().includes(search) ||
                        (lt.code && lt.code.toLowerCase().includes(search))
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
                        console.log('Ã¢Å“â€¦ KPI added, selection cleared');
                    });

                    // Listen for leave balance added event to clear selection
                    this.$wire.on('leaveBalanceAdded', () => {
                        this.clearLeaveTypeSelection();
                        console.log('Ã¢Å“â€¦ Leave balance added, selection cleared');
                    });

                    // Watch for modal body overflow
                    this.$watch('showModal', (value) => {
                        document.body.classList.toggle('modal-open', value);
                        // Reset active tab to 'personal' when modal opens
                        if (value) {
                            this.activeTab = 'personal';
                        }
                    });

                    this.$watch('showViewModal', (value) => {
                        document.body.classList.toggle('modal-open', value);
                    });
                },

                addNotification(type, message) {
                    const id = Date.now();
                    this.notifications.push({
                        id,
                        type,
                        message
                    });
                    setTimeout(() => {
                        this.notifications = this.notifications.filter(n => n.id !== id);
                    }, 3000);
                },

                closeEmployeeModal() {
                    this.showModal = false;
                    this.resetImagePreview();
                    this.$wire.closeModal();
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
                        // Validate file type
                        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                        if (!validTypes.includes(file.type)) {
                            this.addNotification('error',
                                __('hr.image_invalid_type'));
                            event.target.value = '';
                            return;
                        }

                        // Validate file size (2MB = 2 * 1024 * 1024 bytes)
                        const maxSize = 2 * 1024 * 1024;
                        if (file.size > maxSize) {
                            this.addNotification('error',
                                __('hr.image_size_error'));
                            event.target.value = '';
                            return;
                        }

                        // Set file info
                        this.selectedFileName = file.name;
                        this.selectedFileSize = this.formatFileSize(file.size);

                        // Create preview
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.imagePreview = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                handleImageDrop(event) {
                    const file = event.dataTransfer.files[0];
                    if (file) {
                        // Validate file type
                        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                        if (!validTypes.includes(file.type)) {
                            this.addNotification('error',
                                __('hr.image_invalid_type'));
                            return;
                        }

                        // Validate file size
                        const maxSize = 2 * 1024 * 1024;
                        if (file.size > maxSize) {
                            this.addNotification('error',
                                __('hr.image_size_error'));
                            return;
                        }

                        // Set file to input and trigger Livewire upload
                        const input = document.getElementById('employee-image-input');
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        input.files = dataTransfer.files;

                        // Trigger change event for Livewire
                        input.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));

                        // Set file info
                        this.selectedFileName = file.name;
                        this.selectedFileSize = this.formatFileSize(file.size);

                        // Create preview
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.imagePreview = e.target.result;
                        };
                        reader.readAsDataURL(file);

                        this.addNotification('success', __('hr.image_selected_successfully'));
                    }
                },

                removeImage() {
                    // Clear preview and file info
                    this.imagePreview = null;
                    this.selectedFileName = '';
                    this.selectedFileSize = '';
                    this.currentImageUrl = null;

                    // Clear file input
                    const input = document.getElementById('employee-image-input');
                    if (input) {
                        input.value = '';
                    }

                    // Clear Livewire model
                    this.$wire.set('image', null);

                    this.addNotification('info', __('hr.image_removed'));
                },

                formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
                },

                resetImagePreview() {
                    this.imagePreview = null;
                    this.selectedFileName = '';
                    this.selectedFileSize = '';
                    this.isDragging = false;
                    this.imageLoading = false;
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
                },

                // Leave Type Management
                getLeaveTypeName(leaveTypeId) {
                    const leaveType = this.leaveTypes.find(lt => lt.id == leaveTypeId);
                    return leaveType ? leaveType.name : '';
                },

                selectLeaveType(leaveType) {
                    this.selectedLeaveTypeId = leaveType.id;
                    this.leaveTypeSearchOpen = false;
                    this.leaveTypeSearch = '';
                    this.leaveTypeSearchIndex = -1;
                },

                clearLeaveTypeSelection() {
                    this.selectedLeaveTypeId = '';
                    this.leaveTypeSearch = '';
                },

                navigateLeaveTypeDown() {
                    if (this.leaveTypeSearchIndex < this.filteredLeaveTypes.length - 1) {
                        this.leaveTypeSearchIndex++;
                    }
                },

                navigateLeaveTypeUp() {
                    if (this.leaveTypeSearchIndex > 0) {
                        this.leaveTypeSearchIndex--;
                    }
                },

                selectCurrentLeaveType() {
                    if (this.leaveTypeSearchIndex >= 0 && this.leaveTypeSearchIndex < this.filteredLeaveTypes.length) {
                        this.selectLeaveType(this.filteredLeaveTypes[this.leaveTypeSearchIndex]);
                    }
                },

                calculateRemainingDays(balance) {
                    const opening = parseFloat(balance.opening_balance_days) || 0;
                    const accrued = parseFloat(balance.accrued_days) || 0;
                    const carried = parseFloat(balance.carried_over_days) || 0;
                    const used = parseFloat(balance.used_days) || 0;
                    const pending = parseFloat(balance.pending_days) || 0;
                    const remaining = opening + accrued + carried - used - pending;
                    return remaining.toFixed(1);
                }
            }));
        });
    </script>
@endpush

