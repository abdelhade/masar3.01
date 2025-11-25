<?php

namespace App\Models;

use Modules\Branches\Models\Branch;
use App\Services\SalaryCalculationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Attendance extends Model
{
    protected $guarded = ['id'];
    protected $table = 'attendances';
    protected $casts = [
        'date' => 'date',
        'time' => 'string', // Cast as string since it's a time field
        'location' => 'array', // تغيير من string إلى array للـ JSON
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);

        // Invalidate salary calculation cache when attendance is created, updated, or deleted
        static::created(function ($attendance) {
            static::invalidateSalaryCache($attendance);
        });

        static::updated(function ($attendance) {
            static::invalidateSalaryCache($attendance);
        });

        static::deleted(function ($attendance) {
            static::invalidateSalaryCache($attendance);
        });
    }

    /**
     * Invalidate salary calculation cache for the employee
     */
    protected static function invalidateSalaryCache(Attendance $attendance): void
    {
        if (!$attendance->employee_id) {
            return;
        }

        $employee = \App\Models\Employee::find($attendance->employee_id);
        if (!$employee) {
            return;
        }

        // Invalidate cache for the month containing this attendance date
        $date = \Carbon\Carbon::parse($attendance->date);
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        $salaryService = app(SalaryCalculationService::class);
        $salaryService->invalidateCache($employee, $startDate, $endDate);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendanceProcessingDetails()
    {
        return $this->hasMany(AttendanceProcessingDetail::class);
    }

    // Accessor to format time for display
    public function getFormattedTimeAttribute()
    {
        return $this->time ? $this->time : '';
    }

    // Accessor to get location address
    public function getLocationAddressAttribute()
    {
        if (!$this->location || !is_array($this->location)) {
            return null;
        }
        
        return $this->location['address'] ?? null;
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
