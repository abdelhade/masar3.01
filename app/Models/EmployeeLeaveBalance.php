<?php

declare(strict_types=1);

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
        'used_days',
        'pending_days',
        'max_monthly_days',
        'monthly_used_days',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'opening_balance_days' => 'decimal:2',
        'used_days' => 'decimal:2',
        'pending_days' => 'decimal:2',
        'max_monthly_days' => 'decimal:2',
        'monthly_used_days' => 'array',
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
        return $this->opening_balance_days -
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
        // التحقق من أن pending_days كافية
        if ($this->pending_days < $days) {
            throw new \RuntimeException(
                sprintf(
                    'Insufficient pending days. Required: %s, Available: %s',
                    $days,
                    $this->pending_days
                )
            );
        }

        // التحقق من أن الرصيد المتبقي كافي (بعد إزالة pending_days)
        $availableAfterRelease = $this->remaining_days + $this->pending_days;
        if ($availableAfterRelease < $days) {
            throw new \RuntimeException(
                sprintf(
                    'Insufficient balance to consume. Required: %s, Available: %s',
                    $days,
                    $availableAfterRelease
                )
            );
        }

        $this->decrement('pending_days', $days);
        $this->increment('used_days', $days);
    }

    public function releasePending(float $days): void
    {
        $this->decrement('pending_days', $days);
    }

    // Monthly limit methods
    public function hasMaxMonthlyLimit(): bool
    {
        return ! is_null($this->max_monthly_days) && $this->max_monthly_days > 0;
    }

    public function getMaxMonthlyDays(): ?float
    {
        return $this->max_monthly_days;
    }

    public function getMonthlyUsedDays(int $month): float
    {
        $monthlyData = $this->monthly_used_days ?? [];

        return (float) ($monthlyData[$month] ?? 0);
    }

    public function addMonthlyUsedDays(int $month, float $days): void
    {
        $monthlyData = $this->monthly_used_days ?? [];
        $currentDays = (float) ($monthlyData[$month] ?? 0);
        $monthlyData[$month] = $currentDays + $days;
        $this->monthly_used_days = $monthlyData;
    }

    public function hasExceededMonthlyLimit(int $month, float $days): bool
    {
        if (! $this->hasMaxMonthlyLimit()) {
            return false;
        }

        $currentUsed = $this->getMonthlyUsedDays($month);
        $totalAfterAdd = $currentUsed + $days;

        return $totalAfterAdd > $this->max_monthly_days;
    }
}
