<?php

namespace App\Livewire\Leaves\LeaveBalances;

use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveType;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('إدارة رصيد الإجازات')]
class CreateEdit extends Component
{
    public ?EmployeeLeaveBalance $balance = null;

    public $employees = [];

    public $leaveTypes = [];

    #[Rule('required|exists:employees,id')]
    public $employee_id = '';

    #[Rule('required|exists:leave_types,id')]
    public $leave_type_id = '';

    #[Rule('required|integer|min:2020|max:2030')]
    public $year = '';

    #[Rule('required|numeric|min:0')]
    public $opening_balance_days = 0;

    #[Rule('required|numeric|min:0')]
    public $accrued_days = 0;

    #[Rule('required|numeric|min:0')]
    public $used_days = 0;

    #[Rule('required|numeric|min:0')]
    public $pending_days = 0;

    #[Rule('required|numeric|min:0')]
    public $carried_over_days = 0;

    #[Rule('nullable|string')]
    public $notes = '';

    public function mount($balanceId = null): void
    {
        $this->employees = Employee::orderBy('name')->get();
        $this->leaveTypes = LeaveType::orderBy('name')->get();

        if ($balanceId) {
            $this->balance = EmployeeLeaveBalance::findOrFail($balanceId);
            $this->loadBalanceData();
        } else {
            $this->year = now()->year;
        }
    }

    public function loadBalanceData(): void
    {
        if ($this->balance) {
            $this->employee_id = $this->balance->employee_id;
            $this->leave_type_id = $this->balance->leave_type_id;
            $this->year = $this->balance->year;
            $this->opening_balance_days = $this->balance->opening_balance_days;
            $this->accrued_days = $this->balance->accrued_days;
            $this->used_days = $this->balance->used_days;
            $this->pending_days = $this->balance->pending_days;
            $this->carried_over_days = $this->balance->carried_over_days;
            $this->notes = $this->balance->notes;
        }
    }

    public function getRemainingDaysProperty(): float
    {
        return $this->opening_balance_days +
               $this->accrued_days +
               $this->carried_over_days -
               $this->used_days -
               $this->pending_days;
    }

    public function save(): void
    {
        $this->validate();

        // التحقق من عدم وجود رصيد مكرر
        $existingBalance = EmployeeLeaveBalance::where([
            'employee_id' => $this->employee_id,
            'leave_type_id' => $this->leave_type_id,
            'year' => $this->year,
        ])->where('id', '!=', $this->balance?->id)->first();

        if ($existingBalance) {
            $this->addError('general', 'يوجد رصيد مسبق لهذا الموظف ونوع الإجازة في هذه السنة.');

            return;
        }

        $data = [
            'employee_id' => $this->employee_id,
            'leave_type_id' => $this->leave_type_id,
            'year' => $this->year,
            'opening_balance_days' => $this->opening_balance_days,
            'accrued_days' => $this->accrued_days,
            'used_days' => $this->used_days,
            'pending_days' => $this->pending_days,
            'carried_over_days' => $this->carried_over_days,
            'notes' => $this->notes,
        ];

        if ($this->balance) {
            $this->balance->update($data);
            session()->flash('message', 'تم تحديث رصيد الإجازة بنجاح.');
        } else {
            EmployeeLeaveBalance::create($data);
            session()->flash('message', 'تم إنشاء رصيد الإجازة بنجاح.');
        }

        $this->redirect(route('leaves.balances.index'));
    }

    public function render()
    {
        return view('livewire.hr-management.leaves.leave-balances.create-edit');
    }
}
