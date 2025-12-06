<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlexibleSalaryProcessing extends Model
{
    protected $guarded = ['id'];

    protected $table = 'flexible_salary_processings';

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'fixed_salary' => 'decimal:2',
        'hours_worked' => 'decimal:2',
        'hourly_wage' => 'decimal:2',
        'total_salary' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function hasJournal(): bool
    {
        return ! is_null($this->journal_id);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'قيد المراجعة',
            'approved' => 'معتمد',
            'rejected' => 'مرفوض',
            default => 'غير محدد'
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => '<span class="badge bg-warning">قيد المراجعة</span>',
            'approved' => '<span class="badge bg-success">معتمد</span>',
            'rejected' => '<span class="badge bg-danger">مرفوض</span>',
            default => '<span class="badge bg-secondary">غير محدد</span>'
        };
    }
}
