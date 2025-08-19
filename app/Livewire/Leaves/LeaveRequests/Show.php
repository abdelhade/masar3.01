<?php

namespace App\Livewire\Leaves\LeaveRequests;

use App\Events\LeaveRequestApproved;
use App\Events\LeaveRequestCancelled;
use App\Events\LeaveRequestRejected;
use App\Events\LeaveRequestSubmitted;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('تفاصيل طلب الإجازة')]
class Show extends Component
{
    public LeaveRequest $request;

    public function mount(int $requestId): void
    {
        $this->request = LeaveRequest::with(['employee', 'leaveType', 'approver'])
            ->findOrFail($requestId);

        $this->authorize('view', $this->request);
    }

    public function submitRequest(): void
    {
        $this->authorize('update', $this->request);

        if (! $this->request->isDraft()) {
            session()->flash('error', 'لا يمكن تقديم هذا الطلب.');

            return;
        }

        $this->request->update(['status' => 'submitted']);

        // إطلاق الحدث
        event(new LeaveRequestSubmitted($this->request));

        session()->flash('message', 'تم تقديم الطلب بنجاح.');
        $this->redirect(route('leaves.requests.index'));
    }

    public function approveRequest(): void
    {
        $this->authorize('approve', $this->request);

        if (! $this->request->canBeApproved()) {
            session()->flash('error', 'لا يمكن الموافقة على هذا الطلب.');

            return;
        }

        $this->request->update([
            'status' => 'approved',
            'approver_id' => Auth::id(),
            'approved_at' => now(),
        ]);

        // إطلاق الحدث
        event(new LeaveRequestApproved($this->request));

        session()->flash('message', 'تم الموافقة على الطلب بنجاح.');
        $this->redirect(route('leaves.requests.index'));
    }

    public function rejectRequest(): void
    {
        $this->authorize('reject', $this->request);

        if (! $this->request->canBeRejected()) {
            session()->flash('error', 'لا يمكن رفض هذا الطلب.');

            return;
        }

        $this->request->update([
            'status' => 'rejected',
            'approver_id' => Auth::id(),
        ]);

        // إطلاق الحدث
        event(new LeaveRequestRejected($this->request));

        session()->flash('message', 'تم رفض الطلب بنجاح.');
        $this->redirect(route('leaves.requests.index'));
    }

    public function cancelRequest(): void
    {
        $this->authorize('cancel', $this->request);

        if (! $this->request->canBeCancelled()) {
            session()->flash('error', 'لا يمكن إلغاء هذا الطلب.');

            return;
        }

        $this->request->update(['status' => 'cancelled']);

        // إطلاق الحدث
        event(new LeaveRequestCancelled($this->request));

        session()->flash('message', 'تم إلغاء الطلب بنجاح.');
        $this->redirect(route('leaves.requests.index'));
    }

    public function editRequest(): void
    {
        $this->authorize('update', $this->request);
        // redirect to edit page
        $this->redirect(route('leaves.requests.edit', $this->request->id));
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

    public function getStatusDescription($status): string
    {
        return match ($status) {
            'draft' => 'الطلب في حالة المسودة ويمكن تعديله أو تقديمه',
            'submitted' => 'تم تقديم الطلب وانتظار الموافقة',
            'approved' => 'تم الموافقة على الطلب',
            'rejected' => 'تم رفض الطلب',
            'cancelled' => 'تم إلغاء الطلب',
            default => 'حالة غير محددة'
        };
    }

    public function render()
    {
        return view('livewire.hr-management.leaves.leave-requests.show');
    }
}
