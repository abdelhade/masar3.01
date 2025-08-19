<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'leave_days_paid',
        'leave_days_unpaid',
        'notes',
    ];

    protected $casts = [
        'leave_days_paid' => 'decimal:2',
        'leave_days_unpaid' => 'decimal:2',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // Helper methods
    public function getTotalLeaveDaysAttribute(): float
    {
        return $this->leave_days_paid + $this->leave_days_unpaid;
    }

    public function addPaidLeaveDays(float $days): void
    {
        $this->increment('leave_days_paid', $days);
    }

    public function addUnpaidLeaveDays(float $days): void
    {
        $this->increment('leave_days_unpaid', $days);
    }
}
