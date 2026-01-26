<?php

declare(strict_types=1);

namespace Modules\HR\Livewire\Leaves\LeaveRequests;

use Modules\HR\Services\LeaveBalanceService;
use Modules\HR\Models\Employee;
use Modules\HR\Models\EmployeeLeaveBalance;
use Modules\HR\Models\LeaveRequest;
use Modules\HR\Models\LeaveType;
use Modules\HR\Models\HRSetting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Leave Request')]
class Edit extends Component
{
    /** @var Collection<int, Employee> */
    public Collection $employees;

    /** @var Collection<int, LeaveType> */
    public Collection $leaveTypes;

    public ?EmployeeLeaveBalance $selectedEmployeeBalance = null;

    public LeaveRequest $request;

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

    public function mount(int $requestId): void
    {
        $this->request = LeaveRequest::with(['employee', 'leaveType'])
            ->findOrFail($requestId);

        $this->authorize('update', $this->request);

        // Load data
        $this->employees = Employee::orderBy('name')->get();
        $this->leaveTypes = LeaveType::orderBy('name')->get();

        // Set form data from existing request
        $this->employee_id = (string) $this->request->employee_id;
        $this->leave_type_id = (string) $this->request->leave_type_id;
        $this->start_date = $this->request->start_date->format('Y-m-d');
        $this->end_date = $this->request->end_date->format('Y-m-d');
        $this->reason = $this->request->reason ?? '';
        $this->calculated_days = (float) $this->request->duration_days;

        // Load employee balance
        $this->loadEmployeeBalance();

        // Check attendance overlap
        $this->checkAttendanceOverlap();
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

    public function save(): void
    {
        $this->validate($this->rules(), [], $this->getValidationAttributes());

        // Check for overlapping approved requests (excluding current request)
        $service = new LeaveBalanceService;
        $hasOverlap = $service->hasOverlappingApprovedRequests(
            (int) $this->employee_id,
            $this->start_date,
            $this->end_date,
            $this->request->id // Exclude current request
        );

        if ($hasOverlap) {
            $this->addError('general', __('hr.overlapping_approved_request'));

            return;
        }

        // Check for sufficient balance (if leave type is paid)
        if ($this->selectedEmployeeBalance && $this->selectedEmployeeBalance->leaveType->is_paid) {
            $originalDays = $this->request->duration_days;
            $newDays = $this->calculated_days;
            $additionalDaysNeeded = $newDays - $originalDays;

            // إذا كان الطلب معتمد، يجب التحقق من الرصيد الكامل
            if ($this->request->isApproved()) {
                // للطلبات المعتمدة، نحتاج للتحقق من الرصيد الكامل (بعد إزالة pending_days)
                $availableBalance = $this->selectedEmployeeBalance->remaining_days + $this->selectedEmployeeBalance->pending_days;
                if ($newDays > $availableBalance) {
                    $this->addError('general', __('hr.insufficient_balance_for_edit'));

                    return;
                }
            } elseif ($this->request->isSubmitted()) {
                // للطلبات المقدمة، pending_days محجوزة بالفعل
                // نحتاج فقط للتحقق من الأيام الإضافية
                if ($additionalDaysNeeded > 0) {
                    // الرصيد المتبقي بعد إزالة pending_days الحالية
                    $availableAfterCurrentPending = $this->selectedEmployeeBalance->remaining_days + $this->selectedEmployeeBalance->pending_days;
                    // الرصيد المطلوب بعد إزالة pending_days القديمة وإضافة الجديدة
                    $requiredBalance = $availableAfterCurrentPending - $originalDays + $newDays;

                    if ($newDays > $availableAfterCurrentPending) {
                        $this->addError('general', __('hr.insufficient_balance_for_edit'));

                        return;
                    }
                }
            } else {
                // للطلبات في حالة draft، نحتاج للتحقق من الرصيد الكامل
                if (! $this->selectedEmployeeBalance->hasSufficientBalance($newDays)) {
                    $this->addError('general', __('hr.insufficient_balance_for_edit'));

                    return;
                }
            }
        }

        // Check for monthly limit
        if ($this->selectedEmployeeBalance) {
            $year = (int) Carbon::parse($this->start_date)->format('Y');
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
        Log::info('=== Edit Leave Request - Checking Percentage Limit ===');
        Log::info('Leave Request ID: ' . $this->request->id);
        Log::info('Employee: ' . $employee->name . ' (ID: ' . $this->employee_id . ')');
        Log::info('Department: ' . ($employee->department->name ?? 'N/A') . ' (ID: ' . ($departmentId ?? 'null') . ')');
        $hasPercentageLimit = $service->checkLeavePercentageLimit(
            (int) $this->employee_id,
            $this->start_date,
            $this->end_date,
            $departmentId,
            $this->request->id // استثناء الطلب الحالي من الحساب
        );
        Log::info('Percentage Limit Check Result: ' . ($hasPercentageLimit ? 'PASS' : 'FAIL'));

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
            'reason' => $this->reason,
            'overlaps_attendance' => $this->overlaps_attendance,
        ];

        $this->request->update($data);

        session()->flash('message', __('hr.leave_request_updated_successfully'));
        $this->redirect(route('leaves.requests.show', $this->request->id));
    }

    public function render()
    {
        return view('hr::livewire.hr-management.leaves.leave-requests.edit');
    }
}
