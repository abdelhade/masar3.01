<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;

class EmployeesJob extends Model
{
    protected $table = 'employees_jobs';
    protected $guarded = ['id'];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
