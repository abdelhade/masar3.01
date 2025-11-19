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
use App\Models\LeaveType;
use App\Models\EmployeeLeaveBalance;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Models\AccHead;
use Modules\Accounts\Services\AccountService;


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
        $status = 'Ã™â€¦Ã™ÂÃ˜Â¹Ã™â€ž';
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
            'marital_status' => 'nullable|in:Ã˜ÂºÃ™Å Ã˜Â± Ã™â€¦Ã˜ÂªÃ˜Â²Ã™Ë†Ã˜Â¬,Ã™â€¦Ã˜ÂªÃ˜Â²Ã™Ë†Ã˜Â¬,Ã™â€¦Ã˜Â·Ã™â€žÃ™â€š,Ã˜Â£Ã˜Â±Ã™â€¦Ã™â€ž',
            'education' => 'nullable|in:Ã˜Â¯Ã˜Â¨Ã™â€žÃ™Ë†Ã™â€¦,Ã˜Â¨Ã™Æ’Ã˜Â§Ã™â€žÃ™Ë†Ã˜Â±Ã™Å Ã™Ë†Ã˜Â³,Ã™â€¦Ã˜Â§Ã˜Â¬Ã˜Â³Ã˜ÂªÃ™Å Ã˜Â±,Ã˜Â¯Ã™Æ’Ã˜ÂªÃ™Ë†Ã˜Â±Ã˜Â§Ã™â€¡',
            'information' => 'nullable|string',
            'status' => 'required|in:Ã™â€¦Ã™ÂÃ˜Â¹Ã™â€ž,Ã™â€¦Ã˜Â¹Ã˜Â·Ã™â€ž',
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
            'name.required' => 'Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â³Ã™â€¦ Ã™â€¦Ã˜Â·Ã™â€žÃ™Ë†Ã˜Â¨.',
            'email.required' => 'Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â±Ã™Å Ã˜Â¯ Ã˜Â§Ã™â€žÃ˜Â¥Ã™â€žÃ™Æ’Ã˜ÂªÃ˜Â±Ã™Ë†Ã™â€ Ã™Å  Ã™â€¦Ã˜Â·Ã™â€žÃ™Ë†Ã˜Â¨.',
            'phone.required' => 'Ã˜Â±Ã™â€šÃ™â€¦ Ã˜Â§Ã™â€žÃ™â€¡Ã˜Â§Ã˜ÂªÃ™Â Ã™â€¦Ã˜Â·Ã™â€žÃ™Ë†Ã˜Â¨.',
            'marital_status.in' => 'Ã˜Â§Ã™â€žÃ˜Â­Ã˜Â§Ã™â€žÃ˜Â© Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â¬Ã˜ÂªÃ™â€¦Ã˜Â§Ã˜Â¹Ã™Å Ã˜Â© Ã˜ÂºÃ™Å Ã˜Â± Ã™â€¦Ã™Ë†Ã˜Â¬Ã™Ë†Ã˜Â¯Ã˜Â©.',
            'education.in' => 'Ã™â€¦Ã˜Â³Ã˜ÂªÃ™Ë†Ã™â€° Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â¹Ã™â€žÃ™Å Ã™â€¦ Ã˜ÂºÃ™Å Ã˜Â± Ã™â€¦Ã™Ë†Ã˜Â¬Ã™Ë†Ã˜Â¯',
            'status.required' => 'Ã˜Â§Ã™â€žÃ˜Â­Ã˜Â§Ã™â€žÃ˜Â© Ã™â€¦Ã˜Â·Ã™â€žÃ™Ë†Ã˜Â¨Ã˜Â©.',
            'status.in' => 'Ã˜Â§Ã™â€žÃ˜Â­Ã˜Â§Ã™â€žÃ˜Â© Ã˜ÂºÃ™Å Ã˜Â± Ã™â€¦Ã™Ë†Ã˜Â¬Ã™Ë†Ã˜Â¯Ã˜Â©.',
            'name.unique' => 'Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â³Ã™â€¦ Ã™â€¦Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã™â€¦ Ã™â€¦Ã™â€  Ã™â€šÃ˜Â¨Ã™â€ž Ã˜Â¨Ã™Ë†Ã˜Â§Ã˜Â³Ã˜Â·Ã˜Â© Ã™â€¦Ã™Ë†Ã˜Â¸Ã™Â Ã˜Â¢Ã˜Â®Ã˜Â± Ã˜Â¨Ã˜Â§Ã™â€žÃ™ÂÃ˜Â¹Ã™â€ž Ã˜Â§Ã™Æ’Ã˜ÂªÃ˜Â¨ Ã˜Â§Ã˜Â³Ã™â€¦ Ã˜Â¢Ã˜Â®Ã˜Â±.',
            'email.unique' => 'Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â±Ã™Å Ã˜Â¯ Ã˜Â§Ã™â€žÃ˜Â¥Ã™â€žÃ™Æ’Ã˜ÂªÃ˜Â±Ã™Ë†Ã™â€ Ã™Å  Ã™â€¦Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã™â€¦ Ã™â€¦Ã™â€  Ã™â€šÃ˜Â¨Ã™â€ž Ã˜Â¨Ã™Ë†Ã˜Â§Ã˜Â³Ã˜Â·Ã˜Â© Ã™â€¦Ã™Ë†Ã˜Â¸Ã™Â Ã˜Â¢Ã˜Â®Ã˜Â± Ã˜Â¨Ã˜Â§Ã™â€žÃ™ÂÃ˜Â¹Ã™â€ž Ã˜Â§Ã™Æ’Ã˜ÂªÃ˜Â¨ Ã˜Â¨Ã˜Â±Ã™Å Ã˜Â¯ Ã˜Â¢Ã˜Â®Ã˜Â±.',
            'phone.unique' => 'Ã˜Â±Ã™â€šÃ™â€¦ Ã˜Â§Ã™â€žÃ™â€¡Ã˜Â§Ã˜ÂªÃ™Â Ã™â€¦Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã™â€¦ Ã™â€¦Ã™â€  Ã™â€šÃ˜Â¨Ã™â€ž Ã˜Â¨Ã™Ë†Ã˜Â§Ã˜Â³Ã˜Â·Ã˜Â© Ã™â€¦Ã™Ë†Ã˜Â¸Ã™Â Ã˜Â¢Ã˜Â®Ã˜Â± Ã˜Â¨Ã˜Â§Ã™â€žÃ™ÂÃ˜Â¹Ã™â€ž Ã˜Â§Ã™Æ’Ã˜ÂªÃ˜Â¨ Ã˜Â±Ã™â€šÃ™â€¦ Ã™â€¡Ã˜Â§Ã˜ÂªÃ™Â Ã˜Â¢Ã˜Â®Ã˜Â±.',
            'nationalId.unique' => 'Ã˜Â±Ã™â€šÃ™â€¦ Ã˜Â§Ã™â€žÃ™â€¡Ã™Ë†Ã™Å Ã˜Â© Ã™â€¦Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã™â€¦ Ã™â€¦Ã™â€  Ã™â€šÃ˜Â¨Ã™â€ž Ã˜Â¨Ã™Ë†Ã˜Â§Ã˜Â³Ã˜Â·Ã˜Â© Ã™â€¦Ã™Ë†Ã˜Â¸Ã™Â Ã˜Â¢Ã˜Â®Ã˜Â± Ã˜Â¨Ã˜Â§Ã™â€žÃ™ÂÃ˜Â¹Ã™â€ž Ã˜Â§Ã™Æ’Ã˜ÂªÃ˜Â¨ Ã˜Â±Ã™â€šÃ™â€¦ Ã™â€¡Ã™Ë†Ã™Å Ã˜Â© Ã˜Â¢Ã˜Â®Ã˜Â±.',
            'finger_print_id.integer' => 'Ã˜Â±Ã™â€šÃ™â€¦ Ã˜Â§Ã™â€žÃ˜Â¨Ã˜ÂµÃ™â€¦Ã˜Â© Ã™Å Ã˜Â¬Ã˜Â¨ Ã˜Â£Ã™â€  Ã™Å Ã™Æ’Ã™Ë†Ã™â€  Ã˜Â±Ã™â€šÃ™â€¦Ã˜Â§Ã™â€¹ Ã˜ÂµÃ˜Â­Ã™Å Ã˜Â­Ã˜Â§Ã™â€¹.',
            'finger_print_id.unique' => 'Ã˜Â±Ã™â€šÃ™â€¦ Ã˜Â§Ã™â€žÃ˜Â¨Ã˜ÂµÃ™â€¦Ã˜Â© Ã™â€¦Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã™â€¦ Ã™â€¦Ã™â€  Ã™â€šÃ˜Â¨Ã™â€ž Ã˜Â¨Ã™Ë†Ã˜Â§Ã˜Â³Ã˜Â·Ã˜Â© Ã™â€¦Ã™Ë†Ã˜Â¸Ã™Â Ã˜Â¢Ã˜Â®Ã˜Â± Ã˜Â¨Ã˜Â§Ã™â€žÃ™ÂÃ˜Â¹Ã™â€ž Ã˜Â§Ã™Æ’Ã˜ÂªÃ˜Â¨ Ã˜Â±Ã™â€šÃ™â€¦ Ã˜Â¨Ã˜ÂµÃ™â€¦Ã˜Â© Ã˜Â¢Ã˜Â®Ã˜Â±.',
            'finger_print_name.unique' => 'Ã˜Â§Ã˜Â³Ã™â€¦ Ã˜Â§Ã™â€žÃ˜Â¨Ã˜ÂµÃ™â€¦Ã˜Â© Ã™â€¦Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã™â€¦ Ã™â€¦Ã™â€  Ã™â€šÃ˜Â¨Ã™â€ž Ã˜Â¨Ã™Ë†Ã˜Â§Ã˜Â³Ã˜Â·Ã˜Â© Ã™â€¦Ã™Ë†Ã˜Â¸Ã™Â Ã˜Â¢Ã˜Â®Ã˜Â± Ã˜Â¨Ã˜Â§Ã™â€žÃ™ÂÃ˜Â¹Ã™â€ž Ã˜Â§Ã™Æ’Ã˜ÂªÃ˜Â¨ Ã˜Â§Ã˜Â³Ã™â€¦ Ã˜Â¨Ã˜ÂµÃ™â€¦Ã˜Â© Ã˜Â¢Ã˜Â®Ã˜Â±.',
            'finger_print_name.string' => 'Ã˜Â§Ã˜Â³Ã™â€¦ Ã˜Â§Ã™â€žÃ˜Â¨Ã˜ÂµÃ™â€¦Ã˜Â© Ã™Å Ã˜Â¬Ã˜Â¨ Ã˜Â£Ã™â€  Ã™Å Ã™Æ’Ã™Ë†Ã™â€  Ã™â€ Ã˜ÂµÃ˜Â§Ã™â€¹.',
            'finger_print_name.max' => 'Ã˜Â§Ã˜Â³Ã™â€¦ Ã˜Â§Ã™â€žÃ˜Â¨Ã˜ÂµÃ™â€¦Ã˜Â© Ã™Å Ã˜Â¬Ã˜Â¨ Ã˜Â£Ã™â€  Ã™Å Ã™Æ’Ã™Ë†Ã™â€  Ã˜Â£Ã™â€šÃ™â€ž Ã™â€¦Ã™â€  255 Ã˜Â­Ã˜Â±Ã™ÂÃ˜Â§Ã™â€¹.',
            'finger_print_name.min' => 'Ã˜Â§Ã˜Â³Ã™â€¦ Ã˜Â§Ã™â€žÃ˜Â¨Ã˜ÂµÃ™â€¦Ã˜Â© Ã™Å Ã˜Â¬Ã˜Â¨ Ã˜Â£Ã™â€  Ã™Å Ã™Æ’Ã™Ë†Ã™â€  Ã˜Â£Ã™Æ’Ã˜Â«Ã˜Â± Ã™â€¦Ã™â€  3 Ã˜Â­Ã˜Â±Ã™ÂÃ˜Â§Ã™â€¹.',
            'kpi_ids.array' => 'Ã™â€¦Ã˜Â¹Ã˜Â¯Ã™â€žÃ˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â¯Ã˜Â§Ã˜Â¡ Ã™Å Ã˜Â¬Ã˜Â¨ Ã˜Â£Ã™â€  Ã˜ÂªÃ™Æ’Ã™Ë†Ã™â€  Ã™â€šÃ˜Â§Ã˜Â¦Ã™â€¦Ã˜Â©.',
            'kpi_ids.*.exists' => 'Ã™â€¦Ã˜Â¹Ã˜Â¯Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â¯Ã˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â­Ã˜Â¯Ã˜Â¯ Ã˜ÂºÃ™Å Ã˜Â± Ã™â€¦Ã™Ë†Ã˜Â¬Ã™Ë†Ã˜Â¯.',
            'kpi_weights.array' => 'Ã˜Â£Ã™Ë†Ã˜Â²Ã˜Â§Ã™â€  Ã™â€¦Ã˜Â¹Ã˜Â¯Ã™â€žÃ˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â¯Ã˜Â§Ã˜Â¡ Ã™Å Ã˜Â¬Ã˜Â¨ Ã˜Â£Ã™â€  Ã˜ÂªÃ™Æ’Ã™Ë†Ã™â€  Ã™â€šÃ˜Â§Ã˜Â¦Ã™â€¦Ã˜Â©.',
            'kpi_weights.*.integer' => 'Ã˜Â§Ã™â€žÃ™Ë†Ã˜Â²Ã™â€  Ã˜Â§Ã™â€žÃ™â€ Ã˜Â³Ã˜Â¨Ã™Å  Ã™Å Ã˜Â¬Ã˜Â¨ Ã˜Â£Ã™â€  Ã™Å Ã™Æ’Ã™Ë†Ã™â€  Ã˜Â±Ã™â€šÃ™â€¦Ã˜Â§Ã™â€¹ Ã˜ÂµÃ˜Â­Ã™Å Ã˜Â­Ã˜Â§Ã™â€¹.',
            'kpi_weights.*.min' => 'Ã˜Â§Ã™â€žÃ™Ë†Ã˜Â²Ã™â€  Ã˜Â§Ã™â€žÃ™â€ Ã˜Â³Ã˜Â¨Ã™Å  Ã™Å Ã˜Â¬Ã˜Â¨ Ã˜Â£Ã™â€  Ã™Å Ã™Æ’Ã™Ë†Ã™â€  0 Ã˜Â£Ã™Ë† Ã˜Â£Ã™Æ’Ã˜Â«Ã˜Â±.',
            'kpi_weights.*.max' => 'Ã˜Â§Ã™â€žÃ™Ë†Ã˜Â²Ã™â€  Ã˜Â§Ã™â€žÃ™â€ Ã˜Â³Ã˜Â¨Ã™Å  Ã™Å Ã˜Â¬Ã˜Â¨ Ã˜Â£Ã™â€  Ã™Å Ã™Æ’Ã™Ë†Ã™â€  100 Ã˜Â£Ã™Ë† Ã˜Â£Ã™â€šÃ™â€ž.',
            'selected_kpi_id.exists' => 'Ã™â€¦Ã˜Â¹Ã˜Â¯Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â¯Ã˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â­Ã˜Â¯Ã˜Â¯ Ã˜ÂºÃ™Å Ã˜Â± Ã™â€¦Ã™Ë†Ã˜Â¬Ã™Ë†Ã˜Â¯.',
            'image.image' => 'Ã˜Â§Ã™â€žÃ™â€¦Ã™â€žÃ™Â Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â±Ã™ÂÃ™â€š Ã™Å Ã˜Â¬Ã˜Â¨ Ã˜Â£Ã™â€  Ã™Å Ã™Æ’Ã™Ë†Ã™â€  Ã˜ÂµÃ™Ë†Ã˜Â±Ã˜Â©.',
            'image.mimes' => 'Ã™â€ Ã™Ë†Ã˜Â¹ Ã˜Â§Ã™â€žÃ˜ÂµÃ™Ë†Ã˜Â±Ã˜Â© Ã™Å Ã˜Â¬Ã˜Â¨ Ã˜Â£Ã™â€  Ã™Å Ã™Æ’Ã™Ë†Ã™â€ : jpeg, png, jpg, gif.',
            'image.max' => 'Ã˜Â­Ã˜Â¬Ã™â€¦ Ã˜Â§Ã™â€žÃ˜ÂµÃ™Ë†Ã˜Â±Ã˜Â© Ã™Å Ã˜Â¬Ã˜Â¨ Ã˜Â£Ã™â€  Ã™Å Ã™Æ’Ã™Ë†Ã™â€  Ã˜Â£Ã™â€šÃ™â€ž Ã™â€¦Ã™â€  2 Ã™â€¦Ã™Å Ã˜Â¬Ã˜Â§Ã˜Â¨Ã˜Â§Ã™Å Ã˜Âª.',
            'password.required' => 'Ã™Æ’Ã™â€žÃ™â€¦Ã˜Â© Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â±Ã™Ë†Ã˜Â± Ã™â€¦Ã˜Â·Ã™â€žÃ™Ë†Ã˜Â¨Ã˜Â©.',
            'password.min' => 'Ã™Æ’Ã™â€žÃ™â€¦Ã˜Â© Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â±Ã™Ë†Ã˜Â± Ã™Å Ã˜Â¬Ã˜Â¨ Ã˜Â£Ã™â€  Ã˜ÂªÃ™Æ’Ã™Ë†Ã™â€  6 Ã˜Â£Ã˜Â­Ã˜Â±Ã™Â Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â§Ã™â€žÃ˜Â£Ã™â€šÃ™â€ž.',
            'salary_basic_account_id.required' => 'Ã˜Â§Ã™â€žÃ˜Â­Ã˜Â³Ã˜Â§Ã˜Â¨ Ã˜Â§Ã™â€žÃ˜Â±Ã˜Â¦Ã™Å Ã˜Â³Ã™Å  Ã™â€žÃ™â€žÃ™â€¦Ã˜Â±Ã˜ÂªÃ˜Â¨ Ã™â€¦Ã˜Â·Ã™â€žÃ™Ë†Ã˜Â¨.',
            'salary_basic_account_id.exists' => 'Ã˜Â§Ã™â€žÃ˜Â­Ã˜Â³Ã˜Â§Ã˜Â¨ Ã˜Â§Ã™â€žÃ˜Â±Ã˜Â¦Ã™Å Ã˜Â³Ã™Å  Ã™â€žÃ™â€žÃ™â€¦Ã˜Â±Ã˜ÂªÃ˜Â¨ Ã˜ÂºÃ™Å Ã˜Â± Ã™â€¦Ã™Ë†Ã˜Â¬Ã™Ë†Ã˜Â¯.',
            'opening_balance.numeric' => 'Ã˜Â§Ã™â€žÃ˜Â±Ã˜ÂµÃ™Å Ã˜Â¯ Ã˜Â§Ã™â€žÃ˜Â£Ã™ÂÃ˜ÂªÃ˜ÂªÃ˜Â§Ã˜Â­Ã™Å  Ã™Å Ã˜Â¬Ã˜Â¨ Ã˜Â£Ã™â€  Ã™Å Ã™Æ’Ã™Ë†Ã™â€  Ã˜Â±Ã™â€šÃ™â€¦Ã˜Â§Ã™â€¹.',
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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getEmployeesProperty()
    {
        return Employee::with('media')->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))->orderByDesc('id')->paginate(10);
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
        DB::transaction(function () use ($id) {
            $employee = Employee::with('media', 'account', 'kpis', 'leaveBalances.leaveType')->findOrFail($id);
            $this->employeeId = $employee->id;

            // Set current image URL using the accessor (works in both local and production)
            $this->currentImageUrl = $employee->image_url;

            foreach (['name', 'email', 'phone', 'image', 'gender', 'date_of_birth', 'nationalId', 'marital_status', 'education', 'information', 'status', 'country_id', 'city_id', 'state_id', 'town_id', 'job_id', 'department_id', 'date_of_hire', 'date_of_fire', 'job_level', 'salary', 'finger_print_id', 'finger_print_name', 'salary_type', 'shift_id', 'additional_hour_calculation', 'additional_day_calculation', 'late_hour_calculation', 'late_day_calculation', 'kpi_ids', 'kpi_weights', 'salary_basic_account_id', 'opening_balance'] as $field) {
                // use case to set the value of the field
                switch ($field) {
                    case ['date_of_birth', 'date_of_hire', 'date_of_fire']:
                        $this->$field = $employee->$field ? $employee->$field->format('Y-m-d') : null;
                        break;
                    case 'salary_basic_account_id':
                        $this->$field = $employee->account->parent_id ?? null;
                        break;
                    case 'opening_balance':
                        $this->$field = $employee->account->start_balance ?? null;
                        break;
                    default:
                        $this->$field = $employee->$field;
                        break;
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
                $this->addError('kpi_weights', 'Ã™â€¦Ã˜Â¬Ã™â€¦Ã™Ë†Ã˜Â¹ Ã˜Â§Ã™â€žÃ˜Â£Ã™Ë†Ã˜Â²Ã˜Â§Ã™â€  Ã˜Â§Ã™â€žÃ™â€ Ã˜Â³Ã˜Â¨Ã™Å Ã˜Â© Ã™â€žÃ™â€¦Ã˜Â¹Ã˜Â¯Ã™â€žÃ˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â¯Ã˜Â§Ã˜Â¡ Ã™Å Ã˜Â¬Ã˜Â¨ Ã˜Â£Ã™â€  Ã™Å Ã™Æ’Ã™Ë†Ã™â€  100% Ã˜Â¨Ã˜Â§Ã™â€žÃ˜Â¶Ã˜Â¨Ã˜Â·. Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â¬Ã™â€¦Ã™Ë†Ã˜Â¹ Ã˜Â§Ã™â€žÃ˜Â­Ã˜Â§Ã™â€žÃ™Å : ' . $totalWeight . '%');
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

                    // Sync leave balances
                    $this->syncLeaveBalances($employee);

                    // sync the employee Account 
                    $this->syncEmployeeAccount($employee);
                    session()->flash('success', __('Ã˜ÂªÃ™â€¦ Ã˜ÂªÃ˜Â­Ã˜Â¯Ã™Å Ã˜Â« Ã˜Â§Ã™â€žÃ™â€¦Ã™Ë†Ã˜Â¸Ã™Â Ã˜Â¨Ã™â€ Ã˜Â¬Ã˜Â§Ã˜Â­.'));
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

                    session()->flash('success', __('Ã˜ÂªÃ™â€¦ Ã˜Â¥Ã™â€ Ã˜Â´Ã˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ™â€¦Ã™Ë†Ã˜Â¸Ã™Â Ã˜Â¨Ã™â€ Ã˜Â¬Ã˜Â§Ã˜Â­.'));
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
            session()->flash('error', __('Ã˜Â­Ã˜Â¯Ã˜Â« Ã˜Â®Ã˜Â·Ã˜Â£ Ã™â€¦Ã˜Â§.'));
            Log::error($th);
        }
    }

    public function delete($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
        session()->flash('success', __('Ã˜ÂªÃ™â€¦ Ã˜Â­Ã˜Â°Ã™Â Ã˜Â§Ã™â€žÃ™â€¦Ã™Ë†Ã˜Â¸Ã™Â Ã˜Â¨Ã™â€ Ã˜Â¬Ã˜Â§Ã˜Â­.'));
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
        $this->status = 'Ã™â€¦Ã™ÂÃ˜Â¹Ã™â€ž';
        $this->image = null;
    }

    public function view($id)
    {
        $this->viewEmployee = Employee::with(['country', 'city', 'state', 'town', 'job', 'department', 'shift', 'kpis', 'leaveBalances.leaveType', 'media'])->find($id);
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
                    'message' => __('Ã˜ÂªÃ™â€¦ Ã˜Â¥Ã˜Â¶Ã˜Â§Ã™ÂÃ˜Â© Ã™â€¦Ã˜Â¹Ã˜Â¯Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â¯Ã˜Â§Ã˜Â¡ Ã˜Â¨Ã™â€ Ã˜Â¬Ã˜Â§Ã˜Â­.'),
                ]);
            } else {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => __('Ã™â€¡Ã˜Â°Ã˜Â§ Ã™â€¦Ã˜Â¹Ã˜Â¯Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â¯Ã˜Â§Ã˜Â¡ Ã™â€¦Ã˜Â¶Ã˜Â§Ã™Â Ã˜Â¨Ã˜Â§Ã™â€žÃ™ÂÃ˜Â¹Ã™â€ž.'),
                ]);
            }
        } else {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('Ã™Å Ã˜Â±Ã˜Â¬Ã™â€° Ã˜Â§Ã˜Â®Ã˜ÂªÃ™Å Ã˜Â§Ã˜Â± Ã™â€¦Ã˜Â¹Ã˜Â¯Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â¯Ã˜Â§Ã˜Â¡.'),
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
            'message' => __('Ã˜ÂªÃ™â€¦ Ã˜Â­Ã˜Â°Ã™Â Ã™â€¦Ã˜Â¹Ã˜Â¯Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â¯Ã˜Â§Ã˜Â¡ Ã˜Â¨Ã™â€ Ã˜Â¬Ã˜Â§Ã˜Â­.'),
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
                    'message' => __('Ã™â€¡Ã˜Â°Ã˜Â§ Ã˜Â§Ã™â€žÃ™â€ Ã™Ë†Ã˜Â¹ Ã™â€¦Ã™â€  Ã˜Â§Ã™â€žÃ˜Â¥Ã˜Â¬Ã˜Â§Ã˜Â²Ã˜Â© Ã™â€¦Ã˜Â³Ã˜Â¬Ã™â€ž Ã˜Â¨Ã˜Â§Ã™â€žÃ™ÂÃ˜Â¹Ã™â€ž Ã™â€žÃ™â€¡Ã˜Â°Ã˜Â§ Ã˜Â§Ã™â€žÃ™â€¦Ã™Ë†Ã˜Â¸Ã™Â Ã™â€žÃ™â€žÃ˜Â³Ã™â€ Ã˜Â© Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â­Ã˜Â¯Ã˜Â¯Ã˜Â©.'),
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
                        'message' => __('Ã™â€¡Ã˜Â°Ã˜Â§ Ã˜Â§Ã™â€žÃ™â€ Ã™Ë†Ã˜Â¹ Ã™â€¦Ã™â€  Ã˜Â§Ã™â€žÃ˜Â¥Ã˜Â¬Ã˜Â§Ã˜Â²Ã˜Â© Ã™â€¦Ã˜Â³Ã˜Â¬Ã™â€ž Ã˜Â¨Ã˜Â§Ã™â€žÃ™ÂÃ˜Â¹Ã™â€ž Ã™â€žÃ™â€¡Ã˜Â°Ã˜Â§ Ã˜Â§Ã™â€žÃ™â€¦Ã™Ë†Ã˜Â¸Ã™Â Ã™â€žÃ™â€žÃ˜Â³Ã™â€ Ã˜Â© Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â­Ã˜Â¯Ã˜Â¯Ã˜Â©.'),
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
                'message' => __('Ã˜ÂªÃ™â€¦ Ã˜Â¥Ã˜Â¶Ã˜Â§Ã™ÂÃ˜Â© Ã˜Â±Ã˜ÂµÃ™Å Ã˜Â¯ Ã˜Â§Ã™â€žÃ˜Â¥Ã˜Â¬Ã˜Â§Ã˜Â²Ã˜Â© Ã˜Â¨Ã™â€ Ã˜Â¬Ã˜Â§Ã˜Â­.'),
            ]);
        } else {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('Ã™Å Ã˜Â±Ã˜Â¬Ã™â€° Ã˜Â§Ã˜Â®Ã˜ÂªÃ™Å Ã˜Â§Ã˜Â± Ã™â€ Ã™Ë†Ã˜Â¹ Ã˜Â§Ã™â€žÃ˜Â¥Ã˜Â¬Ã˜Â§Ã˜Â²Ã˜Â©.'),
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
            'message' => __('Ã˜ÂªÃ™â€¦ Ã˜Â­Ã˜Â°Ã™Â Ã˜Â±Ã˜ÂµÃ™Å Ã˜Â¯ Ã˜Â§Ã™â€žÃ˜Â¥Ã˜Â¬Ã˜Â§Ã˜Â²Ã˜Â© Ã˜Â¨Ã™â€ Ã˜Â¬Ã˜Â§Ã˜Â­.'),
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
                    @can('Ã˜Â¥Ã˜Â¶Ã˜Â§Ã™ÂÃ˜Â© Ã˜Â§Ã™â€žÃ™â€¦Ã™Ë†Ã˜Â¸Ã™ÂÃ™Å Ã™Å Ã™â€ ')
                        <button wire:click="create" 
                            type="button"
                            wire:loading.attr="disabled"
                            wire:target="create"
                            class="btn btn-primary font-family-cairo fw-bold">
                            <span wire:loading wire:target="create" class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>
                            <span wire:loading.remove wire:target="create">
                                {{ __('Ã˜Â¥Ã˜Â¶Ã˜Â§Ã™ÂÃ˜Â© Ã™â€¦Ã™Ë†Ã˜Â¸Ã™Â') }}
                                <i class="fas fa-plus me-2"></i>
                            </span>
                        </button>
                    @endcan
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control w-auto"
                        style="min-width:200px" placeholder="{{ __('Ã˜Â¨Ã˜Â­Ã˜Â« Ã˜Â¨Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â³Ã™â€¦...') }}">
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive" style="overflow-x: auto;">
                            <x-table-export-actions table-id="employee-table" filename="employee-table"
                                excel-label="Ã˜ÂªÃ˜ÂµÃ˜Â¯Ã™Å Ã˜Â± Excel" pdf-label="Ã˜ÂªÃ˜ÂµÃ˜Â¯Ã™Å Ã˜Â± PDF" print-label="Ã˜Â·Ã˜Â¨Ã˜Â§Ã˜Â¹Ã˜Â©" />

                            <table id="employee-table" class="table table-striped text-center mb-0"
                                style="min-width: 1200px;">
                                <thead class="table-light align-middle">
                                    <tr>
                                        <th class="font-family-cairo fw-bold">#</th>
                                        <th class="font-family-cairo fw-bold">{{ __('Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â³Ã™â€¦') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â±Ã™Å Ã˜Â¯ Ã˜Â§Ã™â€žÃ˜Â¥Ã™â€žÃ™Æ’Ã˜ÂªÃ˜Â±Ã™Ë†Ã™â€ Ã™Å ') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('Ã˜Â±Ã™â€šÃ™â€¦ Ã˜Â§Ã™â€žÃ™â€¡Ã˜Â§Ã˜ÂªÃ™Â') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('Ã˜Â§Ã™â€žÃ™â€šÃ˜Â³Ã™â€¦') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('Ã˜Â§Ã™â€žÃ™Ë†Ã˜Â¸Ã™Å Ã™ÂÃ˜Â©') }}</th>
                                        <th class="font-family-cairo fw-bold">{{ __('Ã˜Â§Ã™â€žÃ˜Â­Ã˜Â§Ã™â€žÃ˜Â©') }}</th>
                                        @canany(['Ã˜ÂªÃ˜Â¹Ã˜Â¯Ã™Å Ã™â€ž Ã˜Â§Ã™â€žÃ™â€¦Ã™Ë†Ã˜Â¸Ã™ÂÃ™Å Ã™Å Ã™â€ ', 'Ã˜Â­Ã˜Â°Ã™Â Ã˜Â§Ã™â€žÃ™â€¦Ã™Ë†Ã˜Â¸Ã™ÂÃ™Å Ã™Å Ã™â€ '])
                                            <th class="font-family-cairo fw-bold">{{ __('Ã˜Â¥Ã˜Â¬Ã˜Â±Ã˜Â§Ã˜Â¡Ã˜Â§Ã˜Âª') }}</th>
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

                                            @canany(['Ã˜ÂªÃ˜Â¹Ã˜Â¯Ã™Å Ã™â€ž Ã˜Â§Ã™â€žÃ™â€¦Ã™Ë†Ã˜Â¸Ã™ÂÃ™Å Ã™Å Ã™â€ ', 'Ã˜Â­Ã˜Â°Ã™Â Ã˜Â§Ã™â€žÃ™â€¦Ã™Ë†Ã˜Â¸Ã™ÂÃ™Å Ã™Å Ã™â€ '])
                                                <td>
                                                    <button 
                                                        wire:click="view({{ $employee->id }})"
                                                        wire:loading.attr="disabled"
                                                        wire:target="view"
                                                        class="btn btn-info btn-sm me-1"
                                                        title="{{ __('Ã˜Â¹Ã˜Â±Ã˜Â¶') }}">
                                                        <span wire:loading wire:target="view({{ $employee->id }})" class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>
                                                        <i class="las la-eye fa-lg" wire:loading.remove wire:target="view({{ $employee->id }})"></i>
                                                    </button>
                                                    @can('Ã˜ÂªÃ˜Â¹Ã˜Â¯Ã™Å Ã™â€ž Ã˜Â§Ã™â€žÃ™â€¦Ã™Ë†Ã˜Â¸Ã™ÂÃ™Å Ã™Å Ã™â€ ')
                                                        <a 
                                                            wire:click="edit({{ $employee->id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="edit"
                                                            class="btn btn-success btn-sm me-1"
                                                            title="{{ __('Ã˜ÂªÃ˜Â¹Ã˜Â¯Ã™Å Ã™â€ž') }}">
                                                            <span wire:loading wire:target="edit({{ $employee->id }})" class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>
                                                            <i class="las la-edit fa-lg" wire:loading.remove wire:target="edit({{ $employee->id }})"></i>
                                                        </a>
                                                    @endcan
                                                    @can('Ã˜Â­Ã˜Â°Ã™Â Ã˜Â§Ã™â€žÃ™â€¦Ã™Ë†Ã˜Â¸Ã™ÂÃ™Å Ã™Å Ã™â€ ')
                                                        <button 
                                                            type="button"
                                                            class="btn btn-danger btn-sm"
                                                            wire:click="delete({{ $employee->id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="delete"
                                                            onclick="confirm('Ã™â€¡Ã™â€ž Ã˜Â£Ã™â€ Ã˜Âª Ã™â€¦Ã˜ÂªÃ˜Â£Ã™Æ’Ã˜Â¯ Ã™â€¦Ã™â€  Ã˜Â­Ã˜Â°Ã™Â Ã™â€¡Ã˜Â°Ã˜Â§ Ã˜Â§Ã™â€žÃ™â€¦Ã™Ë†Ã˜Â¸Ã™ÂÃ˜Å¸') || event.stopImmediatePropagation()"
                                                            title="{{ __('Ã˜Â­Ã˜Â°Ã™Â') }}">
                                                            <span wire:loading wire:target="delete({{ $employee->id }})" class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>
                                                            <i class="las la-trash fa-lg" wire:loading.remove wire:target="delete({{ $employee->id }})"></i>
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
                                                    Ã™â€žÃ˜Â§ Ã˜ÂªÃ™Ë†Ã˜Â¬Ã˜Â¯ Ã˜Â¨Ã™Å Ã˜Â§Ã™â€ Ã˜Â§Ã˜Âª
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
                                        x-text="isEdit ? '{{ __('Ã˜ÂªÃ˜Â¹Ã˜Â¯Ã™Å Ã™â€ž Ã™â€¦Ã™Ë†Ã˜Â¸Ã™Â') }}' : '{{ __('Ã˜Â¥Ã˜Â¶Ã˜Â§Ã™ÂÃ˜Â© Ã™â€¦Ã™Ë†Ã˜Â¸Ã™Â') }}'"></span>
                                </h5>
                                <button type="button" class="btn-close m-3" @click="closeEmployeeModal()"
                                    aria-label="Ã˜Â¥Ã˜ÂºÃ™â€žÃ˜Â§Ã™â€š"></button>
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
                                    {{ __('Ã˜Â¥Ã™â€žÃ˜ÂºÃ˜Â§Ã˜Â¡') }}
                                </button>
                                <button type="button" class="btn btn-primary btn-md" @click="$wire.save()" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-not-allowed">
                                    <span x-text="isEdit ? '{{ __('Ã˜ÂªÃ˜Â­Ã˜Â¯Ã™Å Ã˜Â«') }}' : '{{ __('Ã˜Â­Ã™ÂÃ˜Â¸') }}'"></span>
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
                                    {{ __('Ã˜Â¹Ã˜Â±Ã˜Â¶ Ã˜ÂªÃ™ÂÃ˜Â§Ã˜ÂµÃ™Å Ã™â€ž Ã˜Â§Ã™â€žÃ™â€¦Ã™Ë†Ã˜Â¸Ã™Â') }}
                                </h5>
                                <button type="button" class="btn-close m-3" @click="closeViewEmployeeModal()"
                                    aria-label="Ã˜Â¥Ã˜ÂºÃ™â€žÃ˜Â§Ã™â€š"></button>
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
                                <button type="button" class="btn btn-secondary btn-md"
                                    @click="closeViewEmployeeModal()">
                                    {{ __('Ã˜Â¥Ã˜ÂºÃ™â€žÃ˜Â§Ã™â€š') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
    @foreach ($this->employees as $employee)
        {{ $employee->id }} - {{ $employee->image_url }} <br>
    @endforeach
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
                        return 'Ã™â€¦Ã™â€¦Ã˜ÂªÃ˜Â§Ã˜Â²! Ã˜ÂªÃ™â€¦ Ã˜Â§Ã™Æ’Ã˜ÂªÃ™â€¦Ã˜Â§Ã™â€ž Ã˜Â§Ã™â€žÃ™â€ Ã˜Â³Ã˜Â¨Ã˜Â© Ã˜Â¨Ã™â€ Ã˜Â¬Ã˜Â§Ã˜Â­. Ã™Å Ã™â€¦Ã™Æ’Ã™â€ Ã™Æ’ Ã˜Â§Ã™â€žÃ˜Â¢Ã™â€  Ã˜Â­Ã™ÂÃ˜Â¸ Ã˜Â§Ã™â€žÃ˜Â¨Ã™Å Ã˜Â§Ã™â€ Ã˜Â§Ã˜Âª.';
                    } else if (this.totalKpiWeight > 100) {
                        return `Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â¬Ã™â€¦Ã™Ë†Ã˜Â¹ Ã˜Â§Ã™â€žÃ˜Â­Ã˜Â§Ã™â€žÃ™Å  ${this.totalKpiWeight}% Ã˜Â£Ã™Æ’Ã˜Â¨Ã˜Â± Ã™â€¦Ã™â€  100%. Ã™Å Ã˜Â±Ã˜Â¬Ã™â€° Ã˜ÂªÃ™â€šÃ™â€žÃ™Å Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â£Ã™Ë†Ã˜Â²Ã˜Â§Ã™â€ .`;
                    } else {
                        return `Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â¬Ã™â€¦Ã™Ë†Ã˜Â¹ Ã˜Â§Ã™â€žÃ˜Â­Ã˜Â§Ã™â€žÃ™Å  ${this.totalKpiWeight}% Ã˜Â£Ã™â€šÃ™â€ž Ã™â€¦Ã™â€  100%. Ã™Å Ã˜Â±Ã˜Â¬Ã™â€° Ã˜Â¥Ã™Æ’Ã™â€¦Ã˜Â§Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â£Ã™Ë†Ã˜Â²Ã˜Â§Ã™â€ .`;
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
                                'Ã™â€ Ã™Ë†Ã˜Â¹ Ã˜Â§Ã™â€žÃ™â€¦Ã™â€žÃ™Â Ã˜ÂºÃ™Å Ã˜Â± Ã™â€¦Ã˜Â¯Ã˜Â¹Ã™Ë†Ã™â€¦. Ã™Å Ã˜Â±Ã˜Â¬Ã™â€° Ã˜Â§Ã˜Â®Ã˜ÂªÃ™Å Ã˜Â§Ã˜Â± Ã˜ÂµÃ™Ë†Ã˜Â±Ã˜Â© (JPG, PNG, GIF)');
                            event.target.value = '';
                            return;
                        }

                        // Validate file size (2MB = 2 * 1024 * 1024 bytes)
                        const maxSize = 2 * 1024 * 1024;
                        if (file.size > maxSize) {
                            this.addNotification('error',
                                'Ã˜Â­Ã˜Â¬Ã™â€¦ Ã˜Â§Ã™â€žÃ˜ÂµÃ™Ë†Ã˜Â±Ã˜Â© Ã™Æ’Ã˜Â¨Ã™Å Ã˜Â± Ã˜Â¬Ã˜Â¯Ã˜Â§Ã™â€¹. Ã˜Â§Ã™â€žÃ˜Â­Ã˜Â¯ Ã˜Â§Ã™â€žÃ˜Â£Ã™â€šÃ˜ÂµÃ™â€° 2 Ã™â€¦Ã™Å Ã˜Â¬Ã˜Â§Ã˜Â¨Ã˜Â§Ã™Å Ã˜Âª');
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
                                'Ã™â€ Ã™Ë†Ã˜Â¹ Ã˜Â§Ã™â€žÃ™â€¦Ã™â€žÃ™Â Ã˜ÂºÃ™Å Ã˜Â± Ã™â€¦Ã˜Â¯Ã˜Â¹Ã™Ë†Ã™â€¦. Ã™Å Ã˜Â±Ã˜Â¬Ã™â€° Ã˜Â§Ã˜Â®Ã˜ÂªÃ™Å Ã˜Â§Ã˜Â± Ã˜ÂµÃ™Ë†Ã˜Â±Ã˜Â© (JPG, PNG, GIF)');
                            return;
                        }

                        // Validate file size
                        const maxSize = 2 * 1024 * 1024;
                        if (file.size > maxSize) {
                            this.addNotification('error',
                                'Ã˜Â­Ã˜Â¬Ã™â€¦ Ã˜Â§Ã™â€žÃ˜ÂµÃ™Ë†Ã˜Â±Ã˜Â© Ã™Æ’Ã˜Â¨Ã™Å Ã˜Â± Ã˜Â¬Ã˜Â¯Ã˜Â§Ã™â€¹. Ã˜Â§Ã™â€žÃ˜Â­Ã˜Â¯ Ã˜Â§Ã™â€žÃ˜Â£Ã™â€šÃ˜ÂµÃ™â€° 2 Ã™â€¦Ã™Å Ã˜Â¬Ã˜Â§Ã˜Â¨Ã˜Â§Ã™Å Ã˜Âª');
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

                        this.addNotification('success', 'Ã˜ÂªÃ™â€¦ Ã˜Â§Ã˜Â®Ã˜ÂªÃ™Å Ã˜Â§Ã˜Â± Ã˜Â§Ã™â€žÃ˜ÂµÃ™Ë†Ã˜Â±Ã˜Â© Ã˜Â¨Ã™â€ Ã˜Â¬Ã˜Â§Ã˜Â­');
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

                    this.addNotification('info', 'Ã˜ÂªÃ™â€¦ Ã˜Â­Ã˜Â°Ã™Â Ã˜Â§Ã™â€žÃ˜ÂµÃ™Ë†Ã˜Â±Ã˜Â©');
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

