<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'opening_balance_days',
        'accrued_days',
        'used_days',
        'pending_days',
        'carried_over_days',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'opening_balance_days' => 'decimal:2',
        'accrued_days' => 'decimal:2',
        'used_days' => 'decimal:2',
        'pending_days' => 'decimal:2',
        'carried_over_days' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    // Accessor for remaining days
    public function getRemainingDaysAttribute(): float
    {
        return $this->opening_balance_days +
               $this->accrued_days +
               $this->carried_over_days -
               $this->used_days -
               $this->pending_days;
    }

    // Helper methods
    public function hasSufficientBalance(float $days): bool
    {
        return $this->remaining_days >= $days;
    }

    public function reservePending(float $days): void
    {
        $this->increment('pending_days', $days);
    }

    public function consumeApproved(float $days): void
    {
        $this->decrement('pending_days', $days);
        $this->increment('used_days', $days);
    }

    public function releasePending(float $days): void
    {
        $this->decrement('pending_days', $days);
    }

    public function addAccruedDays(float $days): void
    {
        $this->increment('accrued_days', $days);
    }

    public function carryOverToNextYear(): float
    {
        $remaining = $this->remaining_days;
        $limit = $this->leaveType->carry_over_limit_days ?? 0;

        return min($remaining, $limit);
    }
}
