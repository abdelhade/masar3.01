<?php

declare(strict_types=1);

namespace App\Livewire\HrManagement\Employees\Concerns;

use App\Http\Requests\StoreEmployeeRequest;
use App\Models\City;
use App\Models\Country;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\EmployeesJob;
use App\Models\Kpi;
use App\Models\LeaveType;
use App\Models\Shift;
use App\Models\State;
use App\Models\Town;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
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
            return Department::select('id', 'title')->orderBy('title')->get();
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
        ])->findOrFail($id);

        $this->employeeId = $employee->id;

        // Set current image URL using the accessor
        $this->currentImageUrl = $employee->image_url;

        // Load basic employee fields
        foreach (['name', 'email', 'phone', 'image', 'gender', 'nationalId', 'marital_status', 'education', 'information', 'status', 'country_id', 'city_id', 'state_id', 'town_id', 'job_id', 'department_id', 'job_level', 'salary', 'finger_print_id', 'finger_print_name', 'salary_type', 'shift_id', 'additional_hour_calculation', 'additional_day_calculation', 'late_hour_calculation', 'late_day_calculation'] as $field) {
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
                'accrued_days' => $balance->accrued_days,
                'used_days' => $balance->used_days,
                'pending_days' => $balance->pending_days,
                'carried_over_days' => $balance->carried_over_days,
                'notes' => $balance->notes,
            ];
        }

        $this->isEdit = true;
    }

    public function save()
    {
        // Authorization check
        $permission = $this->isEdit ? 'edit Hr-Employees' : 'create Hr-Employees';
        abort_unless(auth()->user()->can($permission), 403, __('hr.unauthorized_action'));

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

        // Sanitize input data before validation
        $this->sanitizeInputs();

        // Validate the data
        $validated = $this->validate();

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

                    // Sync the employee Account
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
        } catch (\Throwable $th) {
            session()->flash('error', __('hr.error_occurred'));
            Log::error('Employee save error', [
                'user_id' => auth()->id(),
                'employee_id' => $this->employeeId,
                'is_edit' => $this->isEdit,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
        }
    }

    public function resetEmployeeFields(): void
    {
        foreach (['employeeId', 'name', 'email', 'phone', 'image', 'gender', 'date_of_birth', 'nationalId', 'marital_status', 'education', 'information', 'status', 'country_id', 'city_id', 'state_id', 'town_id', 'job_id', 'department_id', 'date_of_hire', 'date_of_fire', 'job_level', 'salary', 'finger_print_id', 'finger_print_name', 'salary_type', 'shift_id', 'password', 'additional_hour_calculation', 'additional_day_calculation', 'late_hour_calculation', 'late_day_calculation', 'selected_kpi_id', 'salary_basic_account_id', 'opening_balance'] as $field) {
            $this->$field = null;
        }

        $this->kpi_ids = [];
        $this->kpi_weights = [];
        $this->leave_balances = [];
        $this->selected_leave_type_id = '';
        $this->status = 'مفعل';
        $this->image = null;
    }

    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName);
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

    public function addKpi(): void
    {
        // Authorization check
        abort_unless(auth()->user()->can('edit Hr-Employees'), 403, __('hr.unauthorized_action'));

        if ($this->selected_kpi_id) {
            if (! is_array($this->kpi_ids)) {
                $this->kpi_ids = [];
            }
            if (! is_array($this->kpi_weights)) {
                $this->kpi_weights = [];
            }

            if (! in_array($this->selected_kpi_id, $this->kpi_ids)) {
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

    public function removeKpi($kpiId): void
    {
        // Authorization check
        abort_unless(auth()->user()->can('edit Hr-Employees'), 403, __('hr.unauthorized_action'));

        if (! is_array($this->kpi_ids)) {
            $this->kpi_ids = [];
        }
        if (! is_array($this->kpi_weights)) {
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

    public function addLeaveBalance(): void
    {
        // Authorization check
        abort_unless(auth()->user()->can('edit Hr-Employees'), 403, __('hr.unauthorized_action'));

        if ($this->selected_leave_type_id) {
            if (! is_array($this->leave_balances)) {
                $this->leave_balances = [];
            }

            // Check if this leave type already exists for the current year
            $currentYear = date('Y');
            $key = $this->selected_leave_type_id.'_'.$currentYear;

            if (isset($this->leave_balances[$key])) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => __('hr.leave_balance_already_exists'),
                ]);

                return;
            }

            // Get existing leave types for this employee to filter them out
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

    public function removeLeaveBalance($balanceKey): void
    {
        // Authorization check
        abort_unless(auth()->user()->can('edit Hr-Employees'), 403, __('hr.unauthorized_action'));

        if (! is_array($this->leave_balances)) {
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

        foreach ($this->leave_balances as $balanceData) {
            if (! isset($balanceData['leave_type_id']) || ! isset($balanceData['year'])) {
                continue;
            }

            $key = $balanceData['leave_type_id'].'_'.$balanceData['year'];
            $data = [
                'opening_balance_days' => $balanceData['opening_balance_days'] ?? 0,
                'accrued_days' => $balanceData['accrued_days'] ?? 0,
                'used_days' => $balanceData['used_days'] ?? 0,
                'pending_days' => $balanceData['pending_days'] ?? 0,
                'carried_over_days' => $balanceData['carried_over_days'] ?? 0,
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

        // Bulk update existing records (optimized)
        if (! empty($toUpdate)) {
            // Use bulk update with case statement for better performance
            $ids = array_keys($toUpdate);
            $cases = [];
            $bindings = [];

            foreach ($toUpdate as $id => $data) {
                foreach ($data as $field => $value) {
                    if (! isset($cases[$field])) {
                        $cases[$field] = [];
                    }
                    $cases[$field][] = "WHEN {$id} THEN ?";
                    $bindings[] = $value;
                }
            }

            // If we have many updates, use bulk update, otherwise use individual updates
            if (count($toUpdate) > 10) {
                // For large datasets, use individual updates (more reliable)
                foreach ($toUpdate as $id => $data) {
                    EmployeeLeaveBalance::where('id', $id)->update($data);
                }
            } else {
                // For small datasets, use individual updates
                foreach ($toUpdate as $id => $data) {
                    EmployeeLeaveBalance::where('id', $id)->update($data);
                }
            }
        }

        // Bulk insert new records
        if (! empty($toCreate)) {
            EmployeeLeaveBalance::insert($toCreate);
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
        return Str::transliterate(Str::lower(auth()->id().'|'.$action.'|'.request()->ip()));
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

        if (! $employee->account) {
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
}
