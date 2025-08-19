<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'duration_days',
        'status',
        'approver_id',
        'approved_at',
        'reason',
        'overlaps_attendance',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'duration_days' => 'decimal:2',
        'approved_at' => 'datetime',
        'overlaps_attendance' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    // Scopes
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['submitted', 'draft']);
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->whereYear('start_date', $year);
    }

    public function scopeOverlapping(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->where(function (Builder $q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function (Builder $subQ) use ($startDate, $endDate) {
                    $subQ->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        });
    }

    public function scopeForEmployee(Builder $query, int $employeeId): Builder
    {
        return $query->where('employee_id', $employeeId);
    }

    // Helper methods
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canBeApproved(): bool
    {
        return $this->isSubmitted() && ! $this->overlaps_attendance;
    }

    public function canBeRejected(): bool
    {
        return $this->isSubmitted();
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'submitted']);
    }

    // Calculate duration days
    public function calculateDurationDays(): float
    {
        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);

        return $start->diffInDays($end) + 1; // Including both start and end dates
    }

    // Check for attendance overlap
    public function checkAttendanceOverlap(): bool
    {
        $overlap = $this->employee->attendances()
            ->whereBetween('date', [$this->start_date, $this->end_date])
            ->exists();

        $this->update(['overlaps_attendance' => $overlap]);

        return $overlap;
    }
}
