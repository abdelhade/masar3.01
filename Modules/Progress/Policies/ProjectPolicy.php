<?php

namespace Modules\Progress\Policies;

use App\Models\User;
use Modules\Progress\Models\ProjectProgress as Project;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any projects.
     */
    public function viewAny(User $user): bool
    {
        // كل المستخدمين المصادقين يمكنهم رؤية قائمة المشاريع
        return true;
    }

    /**
     * Determine whether the user can view the project.
     */
    public function view(User $user, Project $project): bool
    {
        // Admin يمكنه رؤية كل المشاريع
        if ($user->hasRole('Admin')) {
            return true;
        }

// التحقق من الموظفين المرتبطين بالمشروع
        // User -> Employee -> Project
        $userEmployee = $user->employee; // من علاقة hasOne في User model
        
        if ($userEmployee) {
            // التحقق إذا كان هذا الموظف مرتبط بالمشروع
            if ($project->employees()->where('employee_id', $userEmployee->id)->exists()) {
                return true;
            }
        }

        // ⚠️ مؤقتاً: السماح لكل المستخدمين المصادقين برؤية كل المشاريع
        // TODO: إضافة عمود created_by للجدول projects ثم تفعيل التحقق الصارم
        return true;
    }

    /**
     * Determine whether the user can view the project progress report.
     */
    public function viewProgress(User $user, Project $project): bool
    {
        // نفس صلاحيات view - يمكن تخصيصها لاحقاً
        return $this->view($user, $project);
    }

    /**
     * Determine whether the user can create projects.
     */
    public function create(User $user): bool
    {
        // Admin و Project Managers يمكنهم إنشاء مشاريع
        return $user->hasRole(['Admin', 'Manager']);
    }

    /**
     * Determine whether the user can update the project.
     */
    public function update(User $user, Project $project): bool
    {
        // Admin يمكنه تعديل كل المشاريع
        if ($user->hasRole('Admin')) {
            return true;
        }

        // صاحب المشروع
        if ($project->created_by && $project->created_by == $user->id) {
            return true;
        }

        // المدير المسؤول عن المشروع (إذا كان موجود حقل manager_id)
        if (isset($project->manager_id) && $project->manager_id == $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        // فقط Admin يمكنه الحذف
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can restore the project.
     */
    public function restore(User $user, Project $project): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can permanently delete the project.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can export the project progress.
     */
    public function export(User $user, Project $project): bool
    {
        // نفس صلاحيات viewProgress
        return $this->viewProgress($user, $project);
    }
}

