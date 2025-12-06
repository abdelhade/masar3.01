<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDeductionReward extends Model
{
    protected $guarded = ['id'];

    protected $table = 'employee_deductions_rewards';

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendanceProcessing(): BelongsTo
    {
        return $this->belongsTo(AttendanceProcessing::class);
    }

    public function flexibleSalaryProcessing(): BelongsTo
    {
        return $this->belongsTo(FlexibleSalaryProcessing::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isDeduction(): bool
    {
        return $this->type === 'deduction';
    }

    public function isReward(): bool
    {
        return $this->type === 'reward';
    }

    public function hasJournal(): bool
    {
        return ! is_null($this->journal_id);
    }
}
