<?php

namespace Modules\Checks\Policies;

use App\Models\User;
use Modules\Checks\Models\Check;

class CheckPolicy
{
    /**
     * Determine if the user can view any checks.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view Checks');
    }

    /**
     * Determine if the user can view the check.
     */
    public function view(User $user, Check $check): bool
    {
        return $user->hasPermissionTo('view Checks');
    }

    /**
     * Determine if the user can create checks.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create Checks');
    }

    /**
     * Determine if the user can update the check.
     */
    public function update(User $user, Check $check): bool
    {
        return $user->hasPermissionTo('edit Checks');
    }

    /**
     * Determine if the user can delete the check.
     */
    public function delete(User $user, Check $check): bool
    {
        return $user->hasPermissionTo('delete Checks');
    }

    /**
     * Determine if the user can clear the check.
     */
    public function clear(User $user, Check $check): bool
    {
        return $user->hasPermissionTo('edit Checks');
    }

    /**
     * Determine if the user can bounce the check.
     */
    public function bounce(User $user, Check $check): bool
    {
        return $user->hasPermissionTo('mark Checks as bounced');
    }

    /**
     * Determine if the user can cancel the check.
     */
    public function cancel(User $user, Check $check): bool
    {
        return $user->hasPermissionTo('cancel Checks');
    }

    /**
     * Determine if the user can approve the check.
     */
    public function approve(User $user, Check $check): bool
    {
        return $user->hasPermissionTo('approve Checks');
    }

    /**
     * Determine if the user can export checks.
     */
    public function export(User $user): bool
    {
        return $user->hasPermissionTo('export Checks');
    }
}
