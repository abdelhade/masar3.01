<?php

namespace App\Livewire\Leaves\LeaveRequests;

use App\LeaveBalanceService;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('تعديل طلب الإجازة')]
class Edit extends Component
{
    public $employees = [];

    public $leaveTypes = [];

    public $selectedEmployeeBalance = null;

    public LeaveRequest $request;

    protected array $validationAttributes = [
        'employee_id' => 'الموظف',
        'leave_type_id' => 'نوع الإجازة',
        'start_date' => 'تاريخ البداية',
        'end_date' => 'تاريخ النهاية',
        'reason' => 'السبب',
    ];

    #[Rule('required|exists:employees,id')]
    public $employee_id = '';

    #[Rule('required|exists:leave_types,id')]
    public $leave_type_id = '';

    #[Rule('required|date|after_or_equal:today')]
    public $start_date = '';

    #[Rule('required|date|after_or_equal:start_date')]
    public $end_date = '';

    #[Rule('nullable|string|max:1000')]
    public $reason = '';

    public $calculated_days = 0;

    public $available_balance = 0;

    public $overlaps_attendance = false;

    public function mount(int $requestId): void
    {
        $this->request = LeaveRequest::with(['employee', 'leaveType'])
            ->findOrFail($requestId);

        $this->authorize('update', $this->request);

        // Load data
        $this->employees = Employee::orderBy('name')->get();
        $this->leaveTypes = LeaveType::orderBy('name')->get();

        // Set form data from existing request
        $this->employee_id = $this->request->employee_id;
        $this->leave_type_id = $this->request->leave_type_id;
        $this->start_date = $this->request->start_date->format('Y-m-d');
        $this->end_date = $this->request->end_date->format('Y-m-d');
        $this->reason = $this->request->reason;
        $this->calculated_days = $this->request->duration_days;

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
            $balance = $service->getOrCreateBalance($this->employee_id, $this->leave_type_id, $year);
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
            $employee = Employee::find($this->employee_id);
            if ($employee) {
                $overlap = $employee->attendances()
                    ->whereBetween('date', [$this->start_date, $this->end_date])
                    ->exists();

                $this->overlaps_attendance = $overlap;
            }
        }
    }

    public function save(): void
    {
        $this->validate();

        // التحقق من تداخل الطلبات المعتمدة (استثناء الطلب الحالي)
        $service = new LeaveBalanceService;
        $hasOverlap = $service->hasOverlappingApprovedRequests(
            $this->employee_id,
            $this->start_date,
            $this->end_date,
            $this->request->id // استثناء الطلب الحالي
        );

        if ($hasOverlap) {
            $this->addError('general', 'يوجد تداخل مع طلب إجازة معتمد آخر في نفس الفترة.');

            return;
        }

        // التحقق من كفاية الرصيد (إذا كان نوع الإجازة مدفوع)
        if ($this->selectedEmployeeBalance && $this->selectedEmployeeBalance->leaveType->is_paid) {
            // حساب الرصيد المطلوب (الفرق بين المدة الجديدة والقديمة)
            $originalDays = $this->request->duration_days;
            $newDays = $this->calculated_days;
            $additionalDaysNeeded = $newDays - $originalDays;

            if ($additionalDaysNeeded > 0 && ! $this->selectedEmployeeBalance->hasSufficientBalance($additionalDaysNeeded)) {
                $this->addError('general', 'الرصيد المتاح غير كافي لهذا التعديل.');

                return;
            }
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

        session()->flash('message', 'تم تحديث طلب الإجازة بنجاح.');
        $this->redirect(route('leaves.requests.show', $this->request->id));
    }

    public function render()
    {
        return view('livewire.hr-management.leaves.leave-requests.edit');
    }
}
