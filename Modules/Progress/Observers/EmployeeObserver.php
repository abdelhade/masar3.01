<?php

namespace Modules\Progress\Observers;

use Modules\Progress\Models\Employee;
use Modules\Progress\Services\ActivityLogService;

class EmployeeObserver
{
    /**
     * Handle the Employee "created" event.
     */
    public function created(Employee $employee): void
    {
        ActivityLogService::created($employee, [
            'name' => $employee->name,
            'email' => $employee->email,
            'phone' => $employee->phone,
            'position' => $employee->position,
        ], 'employees');
    }

    /**
     * Handle the Employee "updated" event.
     */
    public function updated(Employee $employee): void
    {
        $changes = $employee->getChanges();
        $original = $employee->getOriginal();
        
        $properties = [];
        foreach ($changes as $field => $newValue) {
            if ($field !== 'updated_at') {
                $properties[$field] = [
                    'old' => $original[$field] ?? null,
                    'new' => $newValue
                ];
            }
        }
        
        if (!empty($properties)) {
            ActivityLogService::updated($employee, $properties, 'employees');
        }
    }

    /**
     * Handle the Employee "deleted" event.
     */
    public function deleted(Employee $employee): void
    {
        ActivityLogService::deleted($employee, [
            'name' => $employee->name,
            'email' => $employee->email,
            'position' => $employee->position,
        ], 'employees');
    }
}
