<?php

declare(strict_types=1);

namespace Modules\HR\Livewire\HrManagement\Employees\Concerns;

use Modules\HR\Http\Requests\StoreEmployeeRequest;
use Modules\HR\Models\City;
use Modules\HR\Models\Country;
use Modules\HR\Models\Department;
use Modules\HR\Models\Employee;
use Modules\HR\Models\EmployeeLeaveBalance;
use Modules\HR\Models\EmployeesJob;
use Modules\HR\Models\Kpi;
use Modules\HR\Models\LeaveType;
use Modules\HR\Models\Shift;
use Modules\HR\Models\State;
use Modules\HR\Models\Town;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Modules\Accounts\Models\AccHead;
use Modules\Accounts\Services\AccountService;

trait HandlesEmployeeForm
{
    public $salary_basic_accounts = [];

    public $salary_basic_account_id = null;

    public $opening_balance = null;

    public $isEdit = false;

    public $employeeId = null;

    public $countries = [];

    public $cities = [];

    public $states = [];

    public $towns = [];

    public $departments = [];

    public $jobs = [];

    public $shifts = [];

    public $kpis = [];

    public $leaveTypes = [];

    // Employee fields
    public $name;

    public $email;

    public $phone;

    public $image;

    public $gender;

    public $date_of_birth;

    public $nationalId;

    public $marital_status;

    public $education;

    public $information;

    public $status = 'مفعل';

    public $country_id;

    public $city_id;

    public $state_id;

    public $town_id;

    public $job_id;

    public $department_id;

    public $line_manager_id;

    public $date_of_hire;

    public $date_of_fire;

    public $job_level;

    public $salary;

    public $finger_print_id;

    public $finger_print_name;

    public $salary_type;

    public $shift_id;

    public $password;

    public $additional_hour_calculation;

    public $additional_day_calculation;

    public $late_hour_calculation;

    public $late_day_calculation;

    public $flexible_hourly_wage;

    public $allowed_permission_days;

    public $allowed_late_days;

    public $allowed_absent_days;

    public $allowed_errand_days;

    public $is_errand_allowed;

    // KPI fields
    public $kpi_ids = [];

    public $kpi_weights = [];

    public $selected_kpi_id = '';

    // Leave Balance fields
    public $leave_balances = [];

    public $selected_leave_type_id = '';

    // Image URL for current employee
    public $currentImageUrl = null;

    /**
     * Get validation rules from StoreEmployeeRequest
     */
    protected function rules(): array
    {
        return StoreEmployeeRequest::getRules($this->employeeId, $this->isEdit);
    }

    /**
     * Get validation messages from StoreEmployeeRequest
     */
    protected function messages(): array
    {
        return StoreEmployeeRequest::getMessages();
    }

    /**
     * Load common data for form
     * Optimized with improved cache durations
     * Uses cache tags if supported, otherwise falls back to regular cache
     */
    protected function loadFormData(): void
    {
        // Get cache instance - use tags if supported, otherwise use regular cache
        $cache = $this->getCacheInstance();

        // Cache for 1 hour (frequently updated)
        $this->salary_basic_accounts = $cache->remember('salary_basic_accounts', 3600, function () {
            return AccHead::where([
                'acc_type' => 5,
                'is_basic' => 1,
            ])->select('id', 'aname', 'code')->orderBy('aname')->get()->toArray();
        });

        // Cache for 4 hours (rarely changed - location data)
        $this->countries = $cache->remember('countries_list', 14400, function () {
            return Country::select('id', 'title')->orderBy('title')->get();
        });

        $this->cities = $cache->remember('cities_list', 14400, function () {
            return City::select('id', 'title')->orderBy('title')->get();
        });

        $this->states = $cache->remember('states_list', 14400, function () {
            return State::select('id', 'title')->orderBy('title')->get();
        });

        $this->towns = $cache->remember('towns_list', 14400, function () {
            return Town::select('id', 'title')->orderBy('title')->get();
        });

        // Cache for 2 hours (moderately updated - HR data)
        $this->departments = $cache->remember('departments_list', 7200, function () {
            return Department::with('director:id,name')
                ->select('id', 'title', 'director_id')
                ->orderBy('title')
                ->get();
        });

        $this->jobs = $cache->remember('jobs_list', 7200, function () {
            return EmployeesJob::select('id', 'title')->orderBy('title')->get();
        });

        $this->shifts = $cache->remember('shifts_list', 7200, function () {
            return Shift::select('id', 'name', 'shift_type', 'start_time', 'end_time')
                ->orderBy('name')
                ->get();
        });

        $this->kpis = $cache->remember('kpis_list', 7200, function () {
            return Kpi::select('id', 'name', 'description')->orderBy('name')->get();
        });

        $this->leaveTypes = $cache->remember('leave_types_list', 7200, function () {
            return LeaveType::select('id', 'name', 'code')->orderBy('name')->get();
        });

        $this->currentImageUrl = null;
    }

    /**
     * Line managers computed property
     * Note: This is now handled client-side by Alpine.js for better performance
     * Keeping this for backward compatibility if needed
     */
    #[Computed]
    public function lineManagers()
    {
        if (!$this->department_id) {
            return collect([]);
        }
        return Employee::where('department_id', $this->department_id)
            ->where('id', '!=', $this->employeeId)
            ->select('id', 'name')
            ->get();
    }

    /**
     * Get cache instance with tags if supported, otherwise regular cache
     */
    protected function getCacheInstance()
    {
        $store = Cache::getStore();

        // Check if cache store supports tags
        if (method_exists($store, 'tags')) {
            try {
                return Cache::tags(['employee_form_data']);
            } catch (\BadMethodCallException $e) {
                // Tags not supported, fall back to regular cache
                return Cache::store();
            }
        }

        return Cache::store();
    }

    /**
     * Load employee data for editing
     * Removed unnecessary transaction (read-only operation)
     */
    protected function loadEmployee(int $id): void
    {
        // Eager load all relationships to avoid N+1 queries
        $employee = Employee::with([
            'media',
            'account.haveParent',
            'kpis',
            'leaveBalances.leaveType',
            'department',
            'job',
            'shift',
            'country',
            'city',
            'state',
            'town',
            'lineManager',
        ])->findOrFail($id);

        $this->employeeId = $employee->id;

        // Set current image URL using the accessor
        $this->currentImageUrl = $employee->image_url;

        // Load basic employee fields
        foreach (['name', 'email', 'phone', 'image', 'gender', 'nationalId', 'marital_status', 'education', 'information', 'status', 'country_id', 'city_id', 'state_id', 'town_id', 'job_id', 'department_id', 'line_manager_id', 'job_level', 'salary', 'finger_print_id', 'finger_print_name', 'salary_type', 'shift_id', 'additional_hour_calculation', 'additional_day_calculation', 'late_hour_calculation', 'late_day_calculation', 'flexible_hourly_wage', 'allowed_permission_days', 'allowed_late_days', 'allowed_absent_days', 'allowed_errand_days', 'is_errand_allowed'] as $field) {
            $this->$field = $employee->$field;
        }

        // Handle date fields
        $this->date_of_birth = ($employee->date_of_birth instanceof \Carbon\Carbon) ? $employee->date_of_birth->format('Y-m-d') : null;
        $this->date_of_hire = ($employee->date_of_hire instanceof \Carbon\Carbon) ? $employee->date_of_hire->format('Y-m-d') : null;
        $this->date_of_fire = ($employee->date_of_fire instanceof \Carbon\Carbon) ? $employee->date_of_fire->format('Y-m-d') : null;

        // Handle account fields
        $this->salary_basic_account_id = $employee->account?->parent_id ?? null;
        $this->opening_balance = $employee->account?->start_balance ?? null;

        // Clear password for security
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
            $key = $balance->leave_type_id.'_'.$balance->year;
            $this->leave_balances[$key] = [
                'leave_type_id' => $balance->leave_type_id,
                'year' => $balance->year,
                'opening_balance_days' => $balance->opening_balance_days,
                'used_days' => $balance->used_days,
                'pending_days' => $balance->pending_days,
                'max_monthly_days' => $balance->max_monthly_days ?? 0,
                'notes' => $balance->notes,
            ];
        }

        $this->isEdit = true;
    }

    public function save()
    {
        // Authorization check
        $permission = $this->isEdit ? 'edit Employees' : 'create Employees';
        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user?->can($permission) ?? false, 403, __('hr.unauthorized_action'));

        // Rate limiting check
        $this->ensureIsNotRateLimited('save');

        // Convert finger_print_id to integer before validation
        if ($this->finger_print_id !== null && $this->finger_print_id !== '') {
            $this->finger_print_id = (int) $this->finger_print_id;
        }

        // Fix image validation - handle Livewire's empty array behavior
        if (is_array($this->image) && empty($this->image)) {
            $this->image = null;
        }

        // Ensure status has a value before validation
        if (empty($this->status)) {
            $this->status = 'مفعل';
        }

        // Ensure salary_basic_account_id is set (convert empty string to null)
        if ($this->salary_basic_account_id === '') {
            $this->salary_basic_account_id = null;
        }

        // Ensure salary_type has a default value (good practice, even though nullable)
        if (empty($this->salary_type)) {
            $this->salary_type = 'ساعات عمل فقط'; // Default value
        }

        // Ensure flexible_hourly_wage has a default value (required by database)
        if ($this->flexible_hourly_wage === null || $this->flexible_hourly_wage === '') {
            $this->flexible_hourly_wage = 0;
        }

        // Ensure numeric fields have default values
        if ($this->allowed_permission_days === null || $this->allowed_permission_days === '') {
            $this->allowed_permission_days = 0;
        }
        if ($this->allowed_late_days === null || $this->allowed_late_days === '') {
            $this->allowed_late_days = 0;
        }
        if ($this->allowed_absent_days === null || $this->allowed_absent_days === '') {
            $this->allowed_absent_days = 0;
        }
        if ($this->allowed_errand_days === null || $this->allowed_errand_days === '') {
            $this->allowed_errand_days = 0;
        }
        if ($this->is_errand_allowed === null || $this->is_errand_allowed === '') {
            $this->is_errand_allowed = false;
        }

        // Sanitize input data before validation
        $this->sanitizeInputs();

        // Log data before validation for debugging
        Log::info('Employee form data before validation', [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'salary_basic_account_id' => $this->salary_basic_account_id,
            'is_edit' => $this->isEdit,
            'employee_id' => $this->employeeId,
        ]);

        // Validate the data
        try {
            $validated = $this->validate();
            Log::info('Employee form validation passed', [
                'validated_fields' => array_keys($validated),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation errors for debugging
            Log::warning('Employee form validation failed', [
                'errors' => $e->errors(),
                'data' => [
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'status' => $this->status,
                    'salary_basic_account_id' => $this->salary_basic_account_id,
                ],
            ]);
            
            // Re-throw to let Livewire handle it
            throw $e;
        }

        // Check if validation added any errors to error bag
        if ($this->getErrorBag()->any()) {
            Log::warning('Employee form has validation errors after validate()', [
                'errors' => $this->getErrorBag()->all(),
            ]);
            return;
        }

        // Custom validation for KPI weights - must equal exactly 100%
        if (! empty($this->kpi_ids) && ! empty($this->kpi_weights)) {
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

        // Custom validation for leave balances - max_monthly_days must not exceed opening_balance_days
        if (is_array($this->leave_balances) && ! empty($this->leave_balances)) {
            foreach ($this->leave_balances as $index => $balanceData) {
                if (! isset($balanceData['leave_type_id']) || ! isset($balanceData['year'])) {
                    continue;
                }

                $openingBalance = (float) ($balanceData['opening_balance_days'] ?? 0);
                $maxMonthly = (float) ($balanceData['max_monthly_days'] ?? 0);

                if ($openingBalance > 0 && $maxMonthly > $openingBalance) {
                    $this->addError("leave_balances.{$index}.max_monthly_days", __('hr.max_monthly_days_exceeds_opening_balance', [
                        'max_monthly' => number_format($maxMonthly, 1),
                        'opening_balance' => number_format($openingBalance, 1),
                    ]));
                }
            }

            // If there are validation errors, stop here
            if ($this->getErrorBag()->hasAny(['leave_balances.*.max_monthly_days'])) {
                return;
            }
        }

        // Check if there are any validation errors before proceeding
        if ($this->getErrorBag()->any()) {
            Log::warning('Employee form has validation errors before save', [
                'errors' => $this->getErrorBag()->all(),
            ]);
            return;
        }

        try {
            $imageFile = null;
            $employee = null;

            DB::transaction(function () use (&$validated, &$imageFile, &$employee) {
                // Hash password if it exists and is not empty
                if (! empty($validated['password'])) {
                    $validated['password'] = Hash::make($validated['password']);
                } else {
                    // In edit mode, if password is empty, don't update it
                    if ($this->isEdit) {
                        unset($validated['password']);
                    } else {
                        unset($validated['password']);
                    }
                }

                // Pull out image file reference
                if (isset($validated['image']) && $validated['image']) {
                    $imageFile = $validated['image'];
                    unset($validated['image']);
                }

                // Remove KPI fields and leave balances from validated data
                unset($validated['kpi_ids'], $validated['kpi_weights'], $validated['leave_balances']);

                // Ensure status has a default value if not provided
                if (empty($validated['status'])) {
                    $validated['status'] = $this->status ?? 'مفعل';
                }

                // Ensure salary_type has a default value if not provided
                if (empty($validated['salary_type'])) {
                    $validated['salary_type'] = $this->salary_type ?? 'ساعات عمل فقط';
                }

                // Ensure numeric fields have default values (required by database)
                if (!isset($validated['flexible_hourly_wage']) || $validated['flexible_hourly_wage'] === null || $validated['flexible_hourly_wage'] === '') {
                    $validated['flexible_hourly_wage'] = 0;
                }
                if (!isset($validated['allowed_permission_days']) || $validated['allowed_permission_days'] === null || $validated['allowed_permission_days'] === '') {
                    $validated['allowed_permission_days'] = 0;
                }
                if (!isset($validated['allowed_late_days']) || $validated['allowed_late_days'] === null || $validated['allowed_late_days'] === '') {
                    $validated['allowed_late_days'] = 0;
                }
                if (!isset($validated['allowed_absent_days']) || $validated['allowed_absent_days'] === null || $validated['allowed_absent_days'] === '') {
                    $validated['allowed_absent_days'] = 0;
                }
                if (!isset($validated['allowed_errand_days']) || $validated['allowed_errand_days'] === null || $validated['allowed_errand_days'] === '') {
                    $validated['allowed_errand_days'] = 0;
                }
                if (!isset($validated['is_errand_allowed'])) {
                    $validated['is_errand_allowed'] = false;
                }

                if ($this->isEdit && $this->employeeId) {
                    $employee = Employee::findOrFail($this->employeeId);
                    $employee->update($validated);
                    session()->flash('success', __('hr.employee_updated_successfully'));
                } else {
                    $employee = Employee::create($validated);
                    session()->flash('success', __('hr.employee_created_successfully'));
                }

                // Sync KPIs with weights (shared logic)
                $this->syncEmployeeKpis($employee);

                // Sync leave balances
                $this->syncLeaveBalances($employee);

                // Sync the employee Account
                $this->syncEmployeeAccount($employee);

                $this->dispatch('employee-saved');
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

            // Clear rate limiter on success
            RateLimiter::clear($this->throttleKey('save'));

            // Invalidate cache after successful save
            $this->invalidateCache();

            // Redirect to index
            return redirect()->route('employees.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors are already handled by Livewire
            Log::info('Employee form validation exception', [
                'errors' => $e->errors(),
            ]);
            // Don't flash error, Livewire will show validation errors
        } catch (\Throwable $th) {
            session()->flash('error', __('hr.error_occurred') . ': ' . $th->getMessage());
            Log::error('Employee save error', [
                'user_id' => Auth::id(),
                'employee_id' => $this->employeeId,
                'is_edit' => $this->isEdit,
                'error' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ]);
            
            // Add error to Livewire error bag so it shows in the form
            $this->addError('general', __('hr.error_occurred') . ': ' . $th->getMessage());
        }
    }

    public function resetEmployeeFields(): void
    {
        foreach (['employeeId', 'name', 'email', 'phone', 'image', 'gender', 'date_of_birth', 'nationalId', 'marital_status', 'education', 'information', 'status', 'country_id', 'city_id', 'state_id', 'town_id', 'job_id', 'department_id', 'line_manager_id', 'date_of_hire', 'date_of_fire', 'job_level', 'salary', 'finger_print_id', 'finger_print_name', 'salary_type', 'shift_id', 'password', 'additional_hour_calculation', 'additional_day_calculation', 'late_hour_calculation', 'late_day_calculation', 'flexible_hourly_wage', 'allowed_permission_days', 'allowed_late_days', 'allowed_absent_days', 'allowed_errand_days', 'is_errand_allowed', 'selected_kpi_id', 'salary_basic_account_id', 'opening_balance'] as $field) {
            $this->$field = null;
        }

        $this->kpi_ids = [];
        $this->kpi_weights = [];
        $this->leave_balances = [];
        $this->selected_leave_type_id = '';
        $this->status = 'مفعل';
        $this->image = null;
    }

    /**
     * Handle property updates
     * Note: We removed real-time validation to improve performance
     * Validation happens only on save() to reduce server requests
     */
    public function updated($propertyName): void
    {
        // Only validate specific fields that need immediate feedback
        // Most validation happens on save() to avoid unnecessary server requests
        
        // Email uniqueness check (important for user feedback)
        if ($propertyName === 'email' && !empty($this->email)) {
            $this->validateOnly('email');
        }
        
        // Don't validate on every change - let save() handle it
    }

    /**
     * Sanitize input data to prevent XSS and ensure data integrity
     */
    private function sanitizeInputs(): void
    {
        // Sanitize string fields
        if (isset($this->name)) {
            $this->name = trim(strip_tags($this->name));
        }

        if (isset($this->email)) {
            $this->email = trim(strtolower(filter_var($this->email, FILTER_SANITIZE_EMAIL)));
        }

        if (isset($this->phone)) {
            $this->phone = trim(preg_replace('/[^0-9+\-() ]/', '', $this->phone));
        }

        if (isset($this->nationalId)) {
            $this->nationalId = trim(preg_replace('/[^0-9]/', '', $this->nationalId));
        }

        if (isset($this->finger_print_name)) {
            $this->finger_print_name = trim(strip_tags($this->finger_print_name));
        }

        if (isset($this->information)) {
            $this->information = trim(strip_tags($this->information));
        }

        // Sanitize numeric fields
        if (isset($this->salary) && $this->salary !== null) {
            $this->salary = filter_var($this->salary, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        }

        if (isset($this->opening_balance) && $this->opening_balance !== null) {
            $this->opening_balance = filter_var($this->opening_balance, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        }

        if (isset($this->finger_print_id) && $this->finger_print_id !== null && $this->finger_print_id !== '') {
            $this->finger_print_id = filter_var($this->finger_print_id, FILTER_SANITIZE_NUMBER_INT);
        }

        // Sanitize leave balances notes
        if (is_array($this->leave_balances)) {
            foreach ($this->leave_balances as $key => $balance) {
                if (isset($balance['notes'])) {
                    $this->leave_balances[$key]['notes'] = trim(strip_tags($balance['notes']));
                }
            }
        }
    }

    /**
     * Add KPI - DEPRECATED: Now handled client-side by Alpine.js
     * Keeping for backward compatibility and server-side fallback
     * KPIs are now managed locally and synced via $wire.set() on save
     */
    public function addKpi(): void
    {
        // Note: This method is no longer called from frontend
        // KPI management is now done client-side for better performance
        // Data is synced to Livewire via $wire.set() before save()
    }

    /**
     * Remove KPI - DEPRECATED: Now handled client-side by Alpine.js
     */
    public function removeKpi($kpiId): void
    {
        // Note: This method is no longer called from frontend
        // KPI management is now done client-side for better performance
    }

    /**
     * Add Leave Balance - DEPRECATED: Now handled client-side by Alpine.js
     */
    public function addLeaveBalance(): void
    {
        // Note: This method is no longer called from frontend
        // Leave balance management is now done client-side for better performance
    }

    /**
     * Remove Leave Balance - DEPRECATED: Now handled client-side by Alpine.js
     */
    public function removeLeaveBalance($balanceKey): void
    {
        // Note: This method is no longer called from frontend
        // Leave balance management is now done client-side for better performance
    }

    /**
     * Sync leave balances - create or update (optimized with bulk operations)
     */
    private function syncLeaveBalances($employee): void
    {
        if (! is_array($this->leave_balances) || empty($this->leave_balances)) {
            return;
        }

        // Get existing balances for this employee to minimize queries
        $existingBalances = EmployeeLeaveBalance::where('employee_id', $employee->id)
            ->get()
            ->keyBy(function ($balance) {
                return $balance->leave_type_id.'_'.$balance->year;
            });

        $toUpdate = [];
        $toCreate = [];

        foreach ($this->leave_balances as $index => $balanceData) {
            if (! isset($balanceData['leave_type_id']) || ! isset($balanceData['year'])) {
                continue;
            }

            $key = $balanceData['leave_type_id'].'_'.$balanceData['year'];

            // Note: Validation is done in save() method before calling this function
            $data = [
                'opening_balance_days' => (float) ($balanceData['opening_balance_days'] ?? 0),
                'used_days' => (float) ($balanceData['used_days'] ?? 0),
                'pending_days' => (float) ($balanceData['pending_days'] ?? 0),
                'max_monthly_days' => (float) ($balanceData['max_monthly_days'] ?? 0),
                'notes' => $balanceData['notes'] ?? null,
            ];

            if ($existingBalances->has($key)) {
                $toUpdate[$existingBalances->get($key)->id] = $data;
            } else {
                $toCreate[] = array_merge([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $balanceData['leave_type_id'],
                    'year' => $balanceData['year'],
                ], $data);
            }
        }

        // Bulk update existing records
        if (! empty($toUpdate)) {
            // Use chunking for large updates to avoid memory issues
            if (count($toUpdate) > 50) {
                foreach (array_chunk($toUpdate, 50, true) as $chunk) {
                    foreach ($chunk as $id => $data) {
                        EmployeeLeaveBalance::where('id', $id)->update($data);
                    }
                }
            } else {
                foreach ($toUpdate as $id => $data) {
                    EmployeeLeaveBalance::where('id', $id)->update($data);
                }
            }
        }

        // Bulk insert new records
        if (! empty($toCreate)) {
            // Use chunking for large inserts
            if (count($toCreate) > 100) {
                foreach (array_chunk($toCreate, 100) as $chunk) {
                    EmployeeLeaveBalance::insert($chunk);
                }
            } else {
                EmployeeLeaveBalance::insert($toCreate);
            }
        }

        // Delete balances that are no longer in the form
        $currentKeys = array_map(function ($balance) {
            return $balance['leave_type_id'].'_'.$balance['year'];
        }, array_filter($this->leave_balances, function ($balance) {
            return isset($balance['leave_type_id']) && isset($balance['year']);
        }));

        EmployeeLeaveBalance::where('employee_id', $employee->id)
            ->whereNotIn(DB::raw("CONCAT(leave_type_id, '_', year)"), $currentKeys)
            ->delete();
    }

    /**
     * Ensure request is not rate limited
     */
    protected function ensureIsNotRateLimited(string $action): void
    {
        $key = $this->throttleKey($action);
        $maxAttempts = $action === 'delete' ? 5 : 10;
        $decayMinutes = 1;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            throw new \Illuminate\Http\Exceptions\ThrottleRequestsException(
                __('hr.rate_limit_exceeded', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)])
            );
        }

        RateLimiter::hit($key, $decayMinutes * 60);
    }

    /**
     * Get throttle key for rate limiting
     */
    protected function throttleKey(string $action): string
    {
        return Str::transliterate(Str::lower((Auth::id() ?? 0).'|'.$action.'|'.request()->ip()));
    }

    /**
     * Invalidate cache when data changes
     * Uses cache tags if supported, otherwise invalidates individual keys
     */
    protected function invalidateCache(): void
    {
        $store = Cache::getStore();

        // Try to use cache tags if supported
        if (method_exists($store, 'tags')) {
            try {
                Cache::tags(['employee_form_data'])->flush();

                return;
            } catch (\BadMethodCallException $e) {
                // Tags not supported, fall back to individual key invalidation
            }
        }

        // Invalidate individual cache keys
        $cacheKeys = [
            'salary_basic_accounts',
            'countries_list',
            'cities_list',
            'states_list',
            'towns_list',
            'departments_list',
            'jobs_list',
            'shifts_list',
            'kpis_list',
            'leave_types_list',
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Sync employee account - create or update
     */
    private function syncEmployeeAccount($employee): void
    {
        $employee->load('account');

        // Get the parent account and its children once
        $parentAccount = AccHead::where('id', $this->salary_basic_account_id)->first();

        if (! $parentAccount) {
            throw new \Exception('Parent account not found');
        }

        // Generate new account code
        $newCode = $this->generateAccountCode($parentAccount);

        $accountData = [
            'code' => $newCode,
            'aname' => $employee->name,
            'parent_id' => $this->salary_basic_account_id,
            'acc_type' => 5,
            'accountable_type' => Employee::class,
            'accountable_id' => $employee->id,
        ];

        if (! $employee->account) {
            $employee->account()->create($accountData);
            $employee->load('account');
        } else {
            unset($accountData['accountable_type'], $accountData['accountable_id']);
            $employee->account->update($accountData);
        }

        // Update account balances and recalculate (single call)
        $this->updateEmployeeAccountBalances($employee);

        // Create sub-accounts (advance, deductions, rewards)
        $this->createEmployeeSubAccounts($employee);
    }

    /**
     * Create sub-accounts for employee (advance, deductions, rewards)
     */
    private function createEmployeeSubAccounts(Employee $employee): void
    {
        // 1. حساب السلف (تحت 110601)
        $this->createOrUpdateSubAccount(
            $employee,
            '110601',
            'سلف '.$employee->name,
            'advance'
        );

        // 2. حساب الجزاءات والخصومات (تحت 210402 - جزاءات وخصومات الموظفين)
        $this->createOrUpdateSubAccount(
            $employee,
            '4202',
            'جزاءات وخصومات '.$employee->name,
            'deductions'
        );

        // 3. حساب المكافآت والحوافز (تحت 5303 - المكافآت والحوافز)
        $this->createOrUpdateSubAccount(
            $employee,
            '5303',
            'مكافآت وحوافز '.$employee->name,
            'rewards'
        );

        // 4. حساب إضافى حضور (تحت 5304 - إضافى حضور)
        $this->createOrUpdateSubAccount(
            $employee,
            '5304',
            'إضافى حضور '.$employee->name,
            'attendance'
        );

        // 5. حساب خصم حضور (تحت 4203 - خصم حضور)
        $this->createOrUpdateSubAccount(
            $employee,
            '4203',
            'خصم حضور '.$employee->name,
            'absence'
        );

        // 6. حساب الجزاءات والخصومات المستحقه (تحت 210402 - جزاءات وخصومات الموظفين)
        $this->createOrUpdateSubAccount(
            $employee,
            '110602',
            'جزاءات وخصومات مستحقه '.$employee->name,
            'deductions'
        );

        // 7. حساب المكافآت والحوافز (تحت 5303 - المكافآت والحوافز)
        $this->createOrUpdateSubAccount(
            $employee,
            '2109',
            'مكافآت وحوافز مستحقه '.$employee->name,
            'rewards'
        );
    }

    /**
     * Create or update a sub-account for employee
     */
    private function createOrUpdateSubAccount(Employee $employee, string $parentCode, string $accountName, string $accountType): void
    {
        $parentAccount = AccHead::where('code', $parentCode)->first();

        if (! $parentAccount) {
            Log::warning("Parent account with code {$parentCode} not found for employee {$employee->id}");

            return;
        }

        // Check if account already exists for this employee
        $existingAccount = AccHead::where('accountable_type', Employee::class)
            ->where('accountable_id', $employee->id)
            ->where('aname', $accountName)
            ->where('parent_id', $parentAccount->id)
            ->first();

        if ($existingAccount) {
            // Update existing account
            $existingAccount->update([
                'aname' => $accountName,
                'mdtime' => now(),
            ]);

            return;
        }

        // Generate code for new account
        $newCode = $this->generateAccountCode($parentAccount);

        // Create new account
        AccHead::create([
            'code' => (string) $newCode,
            'aname' => $accountName,
            'parent_id' => $parentAccount->id,
            'acc_type' => 5,
            'accountable_type' => Employee::class,
            'accountable_id' => $employee->id,
            'is_basic' => 0,
            'deletable' => 0,
            'editable' => 1,
            'is_stock' => 0,
            'is_fund' => 0,
            'start_balance' => 0,
            'credit' => 0,
            'debit' => 0,
            'balance' => 0,
            'crtime' => now(),
            'mdtime' => now(),
        ]);
    }

    /**
     * Sync employee KPIs with weights
     * Extracted to avoid code duplication
     */
    private function syncEmployeeKpis(Employee $employee): void
    {
        $kpiData = [];
        foreach ($this->kpi_ids as $kpiId) {
            if (isset($this->kpi_weights[$kpiId]) && $this->kpi_weights[$kpiId] > 0) {
                $kpiData[$kpiId] = ['weight_percentage' => $this->kpi_weights[$kpiId]];
            }
        }
        $employee->kpis()->sync($kpiData);
    }

    /**
     * Update employee account balances and recalculate
     * Optimized to call AccountService once instead of twice
     */
    private function updateEmployeeAccountBalances(Employee $employee): void
    {
        if (! $employee->account) {
            return;
        }

        $accountService = app(AccountService::class);
        $accountService->setStartBalances([$employee->account->id => $this->opening_balance]);
        $accountService->recalculateOpeningCapitalAndSyncJournal();
    }

    /**
     * Generate account code for new account
     * Extracted to avoid code duplication
     */
    private function generateAccountCode(AccHead $parentAccount): string
    {
        // Get all direct children and find the maximum code value
        $children = $parentAccount->haveChildrens()->get();
        
        $maxChildCode = null;
        foreach ($children as $child) {
            if (is_numeric($child->code)) {
                $childCodeValue = (int) $child->code;
                if ($maxChildCode === null || $childCodeValue > $maxChildCode) {
                    $maxChildCode = $childCodeValue;
                }
            }
        }
        
        // Generate new code: max child code + 1, or find next available if no children
        if ($maxChildCode !== null) {
            return (string) ($maxChildCode + 1);
        }
        
        // No children exist - find next available code starting from parent + 1
        $parentCodeValue = (int) $parentAccount->code;
        $newCodeValue = $parentCodeValue + 1;
        
        // Ensure the code doesn't already exist as a non-child account
        while (AccHead::where('code', (string) $newCodeValue)->exists()) {
            $newCodeValue++;
        }
        
        return (string) $newCodeValue;
    }
}