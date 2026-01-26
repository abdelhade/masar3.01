<?php

declare(strict_types=1);

namespace Modules\HR\Livewire\Leaves\LeaveRequests;

use Modules\HR\Services\LeaveBalanceService;
use Modules\HR\Models\Employee;
use Modules\HR\Models\EmployeeLeaveBalance;
use Modules\HR\Models\LeaveRequest;
use Modules\HR\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Modules\HR\Models\HRSetting;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Add Leave Request')]
class Create extends Component
{
    /** @var Collection<int, Employee> */
    public Collection $employees;

    /** @var Collection<int, LeaveType> */
    public Collection $leaveTypes;

    public ?EmployeeLeaveBalance $selectedEmployeeBalance = null;

    #[Rule('required|exists:employees,id')]
    public string $employee_id = '';

    #[Rule('required|exists:leave_types,id')]
    public string $leave_type_id = '';

    #[Rule('required|date|after_or_equal:today')]
    public string $start_date = '';

    #[Rule('required|date|after_or_equal:start_date')]
    public string $end_date = '';

    #[Rule('nullable|string|max:1000')]
    public string $reason = '';

    public float $calculated_days = 0;

    public float $available_balance = 0;

    public bool $overlaps_attendance = false;

    public function mount(): void
    {
        $this->employees = Employee::orderBy('name')->get();
        $this->leaveTypes = LeaveType::orderBy('name')->get();
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->addDays(1)->format('Y-m-d');
        $this->calculateDays();
    }

    public function updatedEmployeeId(): void
    {
        $this->leave_type_id = '';
        $this->selectedEmployeeBalance = null;
        $this->available_balance = 0;

        // Clear related validation errors so the user isn't blocked while re-selecting
        $this->resetErrorBag(['employee_id', 'leave_type_id']);
    }

    public function updatedLeaveTypeId(): void
    {
        // Clear the field error when the user selects a valid type
        $this->resetErrorBag('leave_type_id');

        $this->loadEmployeeBalance();
    }

    public function updatedStartDate(): void
    {
        $this->calculateDays();
        $this->checkAttendanceOverlap();
    }

    public function updatedEndDate(): void
    {
        $this->calculateDays();
        $this->checkAttendanceOverlap();
    }

    public function loadEmployeeBalance(): void
    {
        if ($this->employee_id && $this->leave_type_id) {
            $service = new LeaveBalanceService;
            $year = now()->year;
            $balance = $service->getOrCreateBalance((int) $this->employee_id, (int) $this->leave_type_id, $year);
            $this->selectedEmployeeBalance = $balance;
            $this->available_balance = $balance->remaining_days;
        }
    }

    public function calculateDays(): void
    {
        if ($this->start_date && $this->end_date) {
            try {
                $start = Carbon::parse($this->start_date);
                $end = Carbon::parse($this->end_date);

                if ($end->gte($start)) {
                    $this->calculated_days = $start->diffInDays($end);
                } else {
                    $this->calculated_days = 0;
                }
            } catch (\Exception $e) {
                $this->calculated_days = 0;
            }
        } else {
            $this->calculated_days = 0;
        }
    }

    public function checkAttendanceOverlap(): void
    {
        if ($this->employee_id && $this->start_date && $this->end_date) {
            $employee = Employee::findOrFail($this->employee_id);
            $overlap = $employee->attendances()
                ->whereBetween('date', [$this->start_date, $this->end_date])
                ->exists();

            $this->overlaps_attendance = $overlap;
        }
    }

    public function save(): void
    {
        $this->validate($this->rules(), [], $this->getValidationAttributes());

        // Check for overlapping approved requests
        $service = new LeaveBalanceService;
        $hasOverlap = $service->hasOverlappingApprovedRequests(
            (int) $this->employee_id,
            $this->start_date,
            $this->end_date
        );

        if ($hasOverlap) {
            $this->addError('general', __('hr.overlapping_approved_request'));

            return;
        }

        // Check for sufficient balance
        if ($this->selectedEmployeeBalance && $this->selectedEmployeeBalance->leaveType->is_paid) {
            if (! $this->selectedEmployeeBalance->hasSufficientBalance($this->calculated_days)) {
                $this->addError('general', __('hr.insufficient_balance'));

                return;
            }
        }

        // Check for monthly limit
        if ($this->selectedEmployeeBalance) {
            $year = now()->year;
            $month = (int) Carbon::parse($this->start_date)->format('m');
            if (! $service->checkMonthlyLimit(
                (int) $this->employee_id,
                (int) $this->leave_type_id,
                $year,
                $month,
                $this->calculated_days
            )) {
                $this->addError('general', 'تجاوز الحد الأقصى الشهري للإجازات.');

                return;
            }
        }

        // Check for leave percentage limit
        $employee = Employee::findOrFail($this->employee_id);
        $departmentId = $employee->department_id ?? null;
        // Log::info('=== Create Leave Request - Checking Percentage Limit ===');
        // Log::info('Employee: ' . $employee->name . ' (ID: ' . $this->employee_id . ')');
        // Log::info('Department: ' . ($employee->department->name ?? 'N/A') . ' (ID: ' . ($departmentId ?? 'null') . ')');
        $hasPercentageLimit = $service->checkLeavePercentageLimit(
            (int) $this->employee_id,
            $this->start_date,
            $this->end_date,
            $departmentId
        );
        // Log::info('Percentage Limit Check Result: ' . ($hasPercentageLimit ? 'PASS' : 'FAIL'));

        if (! $hasPercentageLimit) {
            // التحقق من سبب الفشل (عدم وجود نسبة محددة أم تجاوز النسبة)
            $department = $employee->department;
            $hasDepartmentPercentage = $department && ! is_null($department->max_leave_percentage);
            $hasCompanyPercentage = ! is_null(HRSetting::getCompanyMaxLeavePercentage());

            if (! $hasDepartmentPercentage && ! $hasCompanyPercentage) {
                $this->addError('general', __('hr.no_leave_percentage_set'));
            } else {
                $this->addError('general', __('hr.leave_percentage_exceeded'));
            }

            return;
        }

        $data = [
            'employee_id' => $this->employee_id,
            'leave_type_id' => $this->leave_type_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'duration_days' => $this->calculated_days,
            'status' => 'draft',
            'reason' => $this->reason,
            'overlaps_attendance' => $this->overlaps_attendance,
        ];

        $request = LeaveRequest::create($data);

        session()->flash('message', __('hr.leave_request_created_successfully'));
        $this->redirect(route('leaves.requests.show', $request->id));
    }

    protected function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:1000',
        ];
    }

    protected function getValidationAttributes(): array
    {
        return [
            'employee_id' => __('hr.employee'),
            'leave_type_id' => __('hr.leave_type'),
            'start_date' => __('hr.start_date'),
            'end_date' => __('hr.end_date'),
            'reason' => __('hr.reason'),
        ];
    }

    public function render()
    {
        return view('hr::livewire.hr-management.leaves.leave-requests.create');
    }
}
