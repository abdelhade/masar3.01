<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeaveRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'hr-admin', 'manager']) || $user->hasPermissionTo('view Leave Requests');
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        // التحقق من الصلاحية أولاً
        if ($user->hasPermissionTo('view Leave Requests')) {
            return true;
        }

        // الموظف يرى طلباته فقط
        if ($user->hasRole('employee')) {
            return $leaveRequest->employee_id === $user->employee_id;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        // المدير يرى مرؤوسيه
        if ($user->hasRole('manager')) {
            return $this->isSubordinate($user, $leaveRequest->employee_id);
        }

        // HR يرى الجميع
        return $user->hasRole('hr-admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'hr-admin', 'manager', 'employee']) || $user->hasPermissionTo('create Leave Requests');
    }

    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        // التحقق من الصلاحية أولاً
        if ($user->hasPermissionTo('edit Leave Requests')) {
            return true;
        }

        // الموظف يعدل طلباته فقط إذا كانت في حالة draft
        if ($user->hasRole('employee')) {
            return $leaveRequest->employee_id === $user->employee_id && $leaveRequest->isDraft();
        }

        // المدير يعدل طلبات مرؤوسيه
        if ($user->hasRole('manager')) {
            return $this->isSubordinate($user, $leaveRequest->employee_id);
        }
        if ($user->hasRole('admin')) {
            return true;
        }

        // HR يعدل الجميع
        return $user->hasRole('hr-admin');
    }

    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        // التحقق من الصلاحية أولاً
        if ($user->hasPermissionTo('delete Leave Requests')) {
            return true;
        }

        // الموظف يحذف طلباته فقط إذا كانت في حالة draft
        if ($user->hasRole('employee')) {
            return $leaveRequest->employee_id === $user->employee_id && $leaveRequest->isDraft();
        }

        // المدير يحذف طلبات مرؤوسيه
        if ($user->hasRole('manager')) {
            return $this->isSubordinate($user, $leaveRequest->employee_id);
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        // HR يحذف الجميع
        return $user->hasRole('hr-admin');
    }

    public function approve(User $user, LeaveRequest $leaveRequest): bool
    {
        if (! $leaveRequest->canBeApproved()) {
            return false;
        }

        // التحقق من الصلاحية أولاً
        if ($user->hasPermissionTo('edit Leave Requests')) {
            return true;
        }

        // المدير يوافق على طلبات مرؤوسيه
        if ($user->hasRole('manager')) {
            return $this->isSubordinate($user, $leaveRequest->employee_id);
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        // HR يوافق على الجميع
        return $user->hasRole('hr-admin');
    }

    public function reject(User $user, LeaveRequest $leaveRequest): bool
    {
        if (! $leaveRequest->canBeRejected()) {
            return false;
        }

        // التحقق من الصلاحية أولاً
        if ($user->hasPermissionTo('view Leave Rejections') || $user->hasPermissionTo('edit Leave Requests')) {
            return true;
        }

        // المدير يرفض طلبات مرؤوسيه
        if ($user->hasRole('manager')) {
            return $this->isSubordinate($user, $leaveRequest->employee_id);
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        // HR يرفض الجميع
        return $user->hasRole('hr-admin');
    }

    public function cancel(User $user, LeaveRequest $leaveRequest): bool
    {
        if (! $leaveRequest->canBeCancelled()) {
            return false;
        }

        // التحقق من الصلاحية أولاً
        if ($user->hasPermissionTo('edit Leave Requests')) {
            return true;
        }

        // الموظف يلغي طلباته فقط
        if ($user->hasRole('employee')) {
            return $leaveRequest->employee_id === $user->employee_id;
        }

        // المدير يلغي طلباته مرؤوسيه
        if ($user->hasRole('manager')) {
            return $this->isSubordinate($user, $leaveRequest->employee_id);
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        // HR يلغي الجميع
        return $user->hasRole('hr-admin');
    }

    private function isSubordinate(User $manager, int $employeeId): bool
    {
        // TODO: تنفيذ منطق التحقق من المرؤوسين
        // يمكن استخدام جدول العلاقات التنظيمية
        return true; // مؤقتاً
    }
}
