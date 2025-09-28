<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceProcessingDetail extends Model
{
    protected $guarded = ['id'];
    protected $table = 'attendance_processing_details';
    protected $casts = [
        'attendance_date' => 'date',
        'shift_start_time' => 'datetime:H:i',
        'shift_end_time' => 'datetime:H:i',
        'check_in_time' => 'datetime:H:i',
        'check_out_time' => 'datetime:H:i',
        'working_hours_in_shift' => 'decimal:2',
        'attendance_basic_hours_count' => 'decimal:2',
        'attendance_actual_hours_count' => 'decimal:2',
        'attendance_overtime_hours_count' => 'decimal:2',
        'attendance_late_hours_count' => 'decimal:2',
        'early_hours' => 'decimal:2',
        'attendance_total_hours_count' => 'decimal:2',
        'total_due_hourly_salary' => 'decimal:2',
        'day_type' => 'string',
    ];

    public function attendanceProcessing(): BelongsTo
    {
        return $this->belongsTo(AttendanceProcessing::class);
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
    public function getStatusBadgeAttribute(): string
    {
        $attendanceStatus = $this->getRawOriginal('attendance_status');
        return match($attendanceStatus) {
            'present' => '<span class="badge bg-success">حضور</span>',
            'absent' => '<span class="badge bg-danger">غياب</span>',
            'leave' => '<span class="badge bg-info">خروج مبكر</span>',
            'permission' => '<span class="badge bg-warning">إذن</span>',
            'late' => '<span class="badge bg-warning">متأخر</span>',
            'holiday' => '<span class="badge bg-secondary">إجازة أسبوعية</span>',
            default => '<span class="badge bg-secondary">غير محدد</span>'
        };
    }

    public function getWorkingDayBadgeAttribute(): string
    {
        $dayType = $this->getRawOriginal('day_type');
        if ($dayType == 'working_day') {
            return '<span class="badge bg-primary">يوم عمل</span>';
        } elseif ($dayType == 'overtime_day') {
            return '<span class="badge bg-warning">يوم إضافي</span>';
        } elseif ($dayType == 'holiday') {
            return '<span class="badge bg-secondary">إجازة أسبوعية</span>';
        }
        return '<span class="badge bg-secondary">غير محدد</span>';
    }

    public function getOvertimePercentageAttribute(): float
    {
        if ($this->attendance_basic_hours_count == 0) {
            return 0;
        }
        
        return ($this->attendance_overtime_hours_count / $this->attendance_basic_hours_count) * 100;
    }

    public function getLatePercentageAttribute(): float
    {
        if ($this->attendance_basic_hours_count == 0) {
            return 0;
        }
        
        return ($this->attendance_late_hours_count / $this->attendance_basic_hours_count) * 100;
    }

    public function getAttendanceEfficiencyAttribute(): float
    {
        if ($this->attendance_basic_hours_count == 0) {
            return 0;
        }
        
        return ($this->attendance_actual_hours_count / $this->attendance_basic_hours_count) * 100;
    }

    public function getFormattedCheckInTimeAttribute(): string
    {
        return $this->check_in_time ? $this->check_in_time->format('H:i') : '--';
    }

    public function getFormattedCheckOutTimeAttribute(): string
    {
        return $this->check_out_time ? $this->check_out_time->format('H:i') : '--';
    }

    public function getFormattedShiftTimeAttribute(): string
    {
        if ($this->shift_start_time && $this->shift_end_time) {
            return $this->shift_start_time . ' - ' . $this->shift_end_time;
        }
        return '--';
    }
}
