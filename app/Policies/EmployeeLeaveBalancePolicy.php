<?php

namespace App\Policies;

use App\Models\EmployeeLeaveBalance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeeLeaveBalancePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'hr-admin', 'manager']) || $user->hasPermissionTo('view-leave-balances');
    }

    public function view(User $user, EmployeeLeaveBalance $balance): bool
    {
        // الموظف يرى رصيده فقط
        if ($user->hasRole('employee')) {
            return $balance->employee_id === $user->employee_id;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        // المدير يرى رصيد مرؤوسيه
        if ($user->hasRole('manager')) {
            return $this->isSubordinate($user, $balance->employee_id);
        }

        // HR يرى الجميع
        return $user->hasRole('hr-admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'hr-admin']) || $user->hasPermissionTo('create-leave-balances');
    }

    public function update(User $user, EmployeeLeaveBalance $balance): bool
    {
        // المدير يعدل رصيد مرؤوسيه
        if ($user->hasRole('manager')) {
            return $this->isSubordinate($user, $balance->employee_id);
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        // HR يعدل الجميع
        return $user->hasRole('hr-admin');
    }

    public function delete(User $user, EmployeeLeaveBalance $balance): bool
    {
        // المدير يحذف رصيد مرؤوسيه
        if ($user->hasRole('manager')) {
            return $this->isSubordinate($user, $balance->employee_id);
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        // HR يحذف الجميع
        return $user->hasRole('hr-admin');
    }

    private function isSubordinate(User $manager, int $employeeId): bool
    {
        // TODO: تنفيذ منطق التحقق من المرؤوسين
        // يمكن استخدام جدول العلاقات التنظيمية
        return true; // مؤقتاً
    }
}
