<?php

namespace App\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    protected $guarded = ['id'];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contract_type()
    {
        return $this->belongsTo(ContractType::class, 'contract_type_id');
    }

    public function job()
    {
        return $this->belongsTo(EmployeesJob::class, 'job_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contract_points(): HasMany
    {
        return $this->hasMany(ContractPoint::class);
    }

    public function salary_points(): HasMany
    {
        return $this->hasMany(SalaryPoint::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
