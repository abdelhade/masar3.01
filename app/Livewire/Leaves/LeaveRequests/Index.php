<?php

namespace App\Livewire\Leaves\LeaveRequests;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('طلبات الإجازة')]
class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $selectedEmployee = '';

    public $selectedLeaveType = '';

    public $selectedStatus = '';

    public $dateFrom = '';

    public $dateTo = '';

    public $employees = [];

    public $leaveTypes = [];

    public function mount(): void
    {
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

    public function updatedSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = LeaveRequest::with(['employee', 'leaveType', 'approver'])
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
            ->when($this->selectedStatus, function ($query) {
                $query->where('status', $this->selectedStatus);
            })
            ->when($this->dateFrom, function ($query) {
                $query->where('start_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->where('end_date', '<=', $this->dateTo);
            })
            ->orderBy('created_at', 'desc');

        $requests = $query->paginate(15);

        return view('livewire.hr-management.leaves.leave-requests.index', [
            'requests' => $requests,
            'employees' => $this->employees,
            'leaveTypes' => $this->leaveTypes,
        ]);
    }

    public function deleteRequest(LeaveRequest $request): void
    {
        $this->authorize('delete', $request);

        $request->delete();

        session()->flash('message', 'تم حذف طلب الإجازة بنجاح.');
    }

    public function getStatusBadgeClass($status): string
    {
        return match ($status) {
            'draft' => 'bg-secondary',
            'submitted' => 'bg-warning',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'cancelled' => 'bg-dark',
            default => 'bg-secondary'
        };
    }

    public function getStatusText($status): string
    {
        return match ($status) {
            'draft' => 'مسودة',
            'submitted' => 'مقدم',
            'approved' => 'معتمد',
            'rejected' => 'مرفوض',
            'cancelled' => 'ملغي',
            default => 'غير محدد'
        };
    }
}
