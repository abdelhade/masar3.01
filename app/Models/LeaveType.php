<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_paid',
        'requires_approval',
        'max_per_request_days',
        'accrual_rate_per_month',
        'carry_over_limit_days',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'requires_approval' => 'boolean',
        'max_per_request_days' => 'integer',
        'accrual_rate_per_month' => 'decimal:2',
        'carry_over_limit_days' => 'integer',
    ];

    public function employeeLeaveBalances(): HasMany
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    // Helper methods
    public function isPaid(): bool
    {
        return $this->is_paid;
    }

    public function requiresApproval(): bool
    {
        return $this->requires_approval;
    }

    public function hasAccrualPolicy(): bool
    {
        return ! is_null($this->accrual_rate_per_month);
    }

    public function hasCarryOverLimit(): bool
    {
        return ! is_null($this->carry_over_limit_days);
    }
}
