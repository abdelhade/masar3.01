<?php

declare(strict_types=1);

namespace App\Livewire\Leaves\LeaveRequests;

use App\Events\LeaveRequestApproved;
use App\Events\LeaveRequestCancelled;
use App\Events\LeaveRequestRejected;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('طلبات الإجازة')]
class Index extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public string $search = '';

    public string $selectedEmployee = '';

    public string $selectedLeaveType = '';

    public string $selectedStatus = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    /** @var Collection<int, Employee> */
    public Collection $employees;

    /** @var Collection<int, LeaveType> */
    public Collection $leaveTypes;

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

    public function approveRequest(LeaveRequest $request): void
    {
        $this->authorize('approve', $request);

        if (! $request->canBeApproved()) {
            session()->flash('error', 'لا يمكن الموافقة على هذا الطلب.');
            $this->dispatch('show-message', message: 'لا يمكن الموافقة على هذا الطلب.', type: 'error');

            return;
        }

        $request->update([
            'status' => 'approved',
            'approver_id' => Auth::id(),
            'approved_at' => now(),
        ]);

        // إطلاق الحدث
        event(new LeaveRequestApproved($request));

        session()->flash('message', 'تم الموافقة على الطلب بنجاح.');
        $this->dispatch('show-message', message: 'تم الموافقة على الطلب بنجاح.', type: 'success');
    }

    public function rejectRequest(LeaveRequest $request): void
    {
        $this->authorize('reject', $request);

        if (! $request->canBeRejected()) {
            session()->flash('error', 'لا يمكن رفض هذا الطلب.');
            $this->dispatch('show-message', message: 'لا يمكن رفض هذا الطلب.', type: 'error');

            return;
        }

        $request->update([
            'status' => 'rejected',
            'approver_id' => Auth::id(),
        ]);

        // إطلاق الحدث
        event(new LeaveRequestRejected($request));

        session()->flash('message', 'تم رفض الطلب بنجاح.');
        $this->dispatch('show-message', message: 'تم رفض الطلب بنجاح.', type: 'success');
    }

    public function cancelRequest(LeaveRequest $request): void
    {
        $this->authorize('cancel', $request);

        if (! $request->canBeCancelled()) {
            session()->flash('error', 'لا يمكن إلغاء هذا الطلب.');
            $this->dispatch('show-message', message: 'لا يمكن إلغاء هذا الطلب.', type: 'error');

            return;
        }

        $request->update(['status' => 'cancelled']);

        // إطلاق الحدث
        event(new LeaveRequestCancelled($request));

        session()->flash('message', 'تم إلغاء الطلب بنجاح.');
        $this->dispatch('show-message', message: 'تم إلغاء الطلب بنجاح.', type: 'success');
    }

    public function deleteRequest(LeaveRequest $request): void
    {
        $this->authorize('delete', $request);

        $request->delete();

        session()->flash('message', 'تم حذف طلب الإجازة بنجاح.');
        $this->dispatch('show-message', message: 'تم حذف طلب الإجازة بنجاح.', type: 'success');
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
