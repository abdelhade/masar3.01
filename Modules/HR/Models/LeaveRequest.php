<?php

declare(strict_types=1);

namespace Modules\HR\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use App\Models\User;

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

    public ?string $approval_error = null;

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
        $this->approval_error = null;

        if (! $this->isSubmitted() || $this->overlaps_attendance) {
            $this->approval_error = 'لا يمكن الموافقة على هذا الطلب: يجب أن يكون الطلب مقدماً ولا يتداخل مع الحضور.';

            return false;
        }

        $service = app(\Modules\HR\Services\LeaveBalanceService::class);
        $year = $this->start_date->year;
        $month = $this->start_date->month;
        $departmentId = $this->employee->department_id;

        // 1. التحقق من النسبة المئوية (الشرط الأول والأهم)
        // استثناء الطلب الحالي من الحساب (مفيد عند التعديل لطلب معتمد)
        // Log::info('=== LeaveRequest canBeApproved - Checking Percentage Limit ===');
        // Log::info('Leave Request ID: ' . $this->id);
        // Log::info('Employee: ' . $this->employee->name . ' (ID: ' . $this->employee_id . ')');
        // Log::info('Department: ' . ($this->employee->department->name ?? 'N/A') . ' (ID: ' . ($departmentId ?? 'null') . ')');
        $hasPercentageLimit = $service->checkLeavePercentageLimit(
            $this->employee_id,
            $this->start_date->format('Y-m-d'),
            $this->end_date->format('Y-m-d'),
            $departmentId,
            $this->id // استثناء الطلب الحالي من الحساب
        );
        // Log::info('Percentage Limit Check Result: ' . ($hasPercentageLimit ? 'PASS' : 'FAIL'));

        if (! $hasPercentageLimit) {
            // التحقق من سبب الفشل (عدم وجود نسبة محددة أم تجاوز النسبة)
            $department = $this->employee->department;
            $hasDepartmentPercentage = $department && ! is_null($department->max_leave_percentage);
            $hasCompanyPercentage = ! is_null(\Modules\HR\Models\HRSetting::getCompanyMaxLeavePercentage());

            if (! $hasDepartmentPercentage && ! $hasCompanyPercentage) {
                $this->approval_error = 'لا يمكن الموافقة على هذا الطلب: لا توجد نسبة مئوية محددة للقسم في جدول الأقسام أو للشركة في إعدادات الموارد البشرية.';
            } else {
                $this->approval_error = 'لا يمكن الموافقة على هذا الطلب: تجاوز الحد الأقصى لنسبة الموظفين في الإجازة للشركة/القسم.';
            }

            return false;
        }

        // 2. التحقق من الرصيد لجميع أنواع الإجازات (مدفوعة وغير مدفوعة)
        if (! $service->hasSufficientBalance(
            $this->employee_id,
            $this->leave_type_id,
            $year,
            $this->duration_days
        )) {
            $this->approval_error = 'لا يمكن الموافقة على هذا الطلب: الرصيد غير كافٍ.';

            return false;
        }

        // 3. التحقق من الحد الشهري لجميع أنواع الإجازات (مدفوعة وغير مدفوعة)
        if (! $service->checkMonthlyLimit(
            $this->employee_id,
            $this->leave_type_id,
            $year,
            $month,
            $this->duration_days
        )) {
            $this->approval_error = 'لا يمكن الموافقة على هذا الطلب: تجاوز الحد الأقصى الشهري لهذا النوع من الإجازات.';

            return false;
        }

        return true;
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
