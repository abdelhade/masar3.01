<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceProcessing extends Model
{
    protected $guarded = ['id'];
    protected $table = 'attendance_processings';
    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_days' => 'integer',
        'working_days' => 'integer',
        'actual_work_days' => 'integer',
        'overtime_work_days' => 'integer',
        'absent_days' => 'integer',
        'total_hours' => 'decimal:2',
        'calculated_salary_for_day' => 'decimal:2',
        'calculated_salary_for_hour' => 'decimal:2',
        'actual_work_hours' => 'decimal:2',
        'overtime_work_hours' => 'decimal:2',
        'employee_productivity_salary' => 'decimal:2',
        'salary_due' => 'decimal:2',
        'total_salary' => 'decimal:2',
    ];
    
    public function attendanceProcessingDetails(): HasMany
    {
        return $this->hasMany(AttendanceProcessingDetail::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // Helper methods
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'single' => 'موظف واحد',
            'multiple' => 'عدة موظفين',
            'department' => 'قسم',
            default => 'غير محدد'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'قيد المراجعة',
            'approved' => 'معتمد',
            'rejected' => 'مرفوض',
            default => 'غير محدد'
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => '<span class="badge bg-warning">قيد المراجعة</span>',
            'approved' => '<span class="badge bg-success">معتمد</span>',
            'rejected' => '<span class="badge bg-danger">مرفوض</span>',
            default => '<span class="badge bg-secondary">غير محدد</span>'
        };
    }

    public function getDurationAttribute(): int
    {
        return $this->period_start->diffInDays($this->period_end) + 1;
    }

    public function getTotalEmployeesAttribute(): int
    {
        return $this->attendanceProcessingDetails()
            ->distinct('employee_id')
            ->count('employee_id');
    }

    public function getTotalActualHoursAttribute(): float
    {
        return $this->attendanceProcessingDetails()
            ->sum('attendance_actual_hours_count');
    }

    public function getTotalOvertimeHoursAttribute(): float
    {
        return $this->attendanceProcessingDetails()
            ->sum('attendance_overtime_hours_count');
    }

    public function getTotalLateHoursAttribute(): float
    {
        return $this->attendanceProcessingDetails()
            ->sum('attendance_late_hours_count');
    }

    public function getTotalSalaryCalculatedAttribute(): float
    {
        return $this->attendanceProcessingDetails()
            ->sum('total_due_hourly_salary');
    }

    public function getPresentDaysAttribute(): int
    {
        return $this->attendanceProcessingDetails()
            ->where('attendance_status', 'حضور')
            ->count();
    }

    public function getAbsentDaysAttribute(): int
    {
        return $this->attendanceProcessingDetails()
            ->where('attendance_status', 'غياب')
            ->count();
    }

    public function getVacationDaysAttribute(): int
    {
        return $this->attendanceProcessingDetails()
            ->whereIn('attendance_status', ['إجازة', 'إذن'])
            ->count();
    }
}