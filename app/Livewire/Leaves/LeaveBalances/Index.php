<?php

declare(strict_types=1);

namespace App\Livewire\Leaves\LeaveBalances;

use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('رصيد الإجازات')]
class Index extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';
    public string $search = '';

    public string $selectedEmployee = '';

    public string $selectedLeaveType = '';

    public int $selectedYear;

    /** @var Collection<int, Employee> */
    public Collection $employees;

    /** @var Collection<int, LeaveType> */
    public Collection $leaveTypes;

    public function mount(): void
    {
        $this->selectedYear = now()->year;
        $this->employees = Employee::orderBy('name')->get();
        $this->leaveTypes = LeaveType::orderBy('name')->get();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedEmployee(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedLeaveType(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedYear(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = EmployeeLeaveBalance::with(['employee', 'leaveType'])
            ->when($this->search, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->selectedEmployee, function ($query) {
                $query->where('employee_id', $this->selectedEmployee);
            })
            ->when($this->selectedLeaveType, function ($query) {
                $query->where('leave_type_id', $this->selectedLeaveType);
            })
            ->when($this->selectedYear, function ($query) {
                $query->where('year', $this->selectedYear);
            })
            ->orderBy('year', 'desc')
            ->orderBy('employee_id');

        $balances = $query->paginate(15);

        return view('livewire.hr-management.leaves.leave-balances.index', [
            'balances' => $balances,
            'employees' => $this->employees,
            'leaveTypes' => $this->leaveTypes,
        ]);
    }

    public function deleteBalance(EmployeeLeaveBalance $balance): void
    {
        $this->authorize('delete', $balance);

        $balance->delete();

        session()->flash('message', 'تم حذف رصيد الإجازة بنجاح.');
        $this->dispatch('show-message', message: 'تم حذف رصيد الإجازة بنجاح.', type: 'success');
    }
}
