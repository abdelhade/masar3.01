<?php

namespace Modules\Progress\Repositories;

use Modules\Progress\Models\Employee;
use Illuminate\Support\Collection;

class EmployeeRepository
{
    public function getAll(): Collection
    {
        return Employee::select('id', 'name', 'position')
            ->orderBy('name')
            ->get();
    }

    public function findById(int $id): ?Employee
    {
        return Employee::find($id);
    }
}
