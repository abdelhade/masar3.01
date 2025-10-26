<?php

namespace App\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Employee extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $table = 'employees';

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_hire' => 'date',
        'date_of_fire' => 'date',
        'password' => 'hashed',
    ];

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('employee_images')
            ->singleFile() // Only one image per employee
            ->useFallbackUrl(asset('assets/images/avatar-placeholder.svg'))
            ->useFallbackPath(public_path('assets/images/avatar-placeholder.svg'));
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(EmployeesJob::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }

    public function payrollEntries(): HasMany
    {
        return $this->hasMany(PayrollEntry::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function town(): BelongsTo
    {
        return $this->belongsTo(Town::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Employee_Evaluation::class);
    }

    public function employeeProductions(): HasMany
    {
        return $this->hasMany(EmployeeProduction::class);
    }

    // New relationships for attendance and salary system
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendanceProcessings(): HasMany
    {
        return $this->hasMany(AttendanceProcessing::class);
    }

    // Helper methods for salary calculation
    public function getExpectedHoursAttribute(): float
    {
        if (! $this->shift) {
            return 8.0; // Default 8 hours
        }

        $startTime = \Carbon\Carbon::parse($this->shift->start_time);
        $endTime = \Carbon\Carbon::parse($this->shift->end_time);

        return $startTime->diffInHours($endTime, false);
    }

    public function getHourlyRateAttribute(): float
    {
        if (! $this->salary) {
            return 0.0;
        }

        return $this->salary / 30 / 8; // Assuming 8 hours per day, 30 days per month
    }

    public function getDailyRateAttribute(): float
    {
        if (! $this->salary) {
            return 0.0;
        }

        return $this->salary / 30; // Assuming 30 days per month
    }

    // Helper methods for attendance
    public function getAttendanceForDate(string $date): \Illuminate\Database\Eloquent\Collection
    {
        return $this->attendances()
            ->where('date', $date)
            ->orderBy('time')
            ->get();
    }

    public function getAttendanceForPeriod(string $startDate, string $endDate): \Illuminate\Database\Eloquent\Collection
    {
        return $this->attendances()
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('time')
            ->get();
    }

    // Helper methods for salary types
    public function isHoursOnlyType(): bool
    {
        return $this->salary_type === 'ساعات عمل فقط';
    }

    public function isHoursWithDailyOvertimeType(): bool
    {
        return $this->salary_type === 'ساعات عمل و إضافي يومى';
    }

    public function isHoursWithPeriodOvertimeType(): bool
    {
        return $this->salary_type === 'ساعات عمل و إضافي للمده';
    }

    public function isAttendanceOnlyType(): bool
    {
        return $this->salary_type === 'حضور فقط';
    }

    public function isProductionOnlyType(): bool
    {
        return $this->salary_type === 'إنتاج فقط';
    }

    // Helper methods for status
    public function isActive(): bool
    {
        return $this->status === 'مفعل';
    }

    public function isInactive(): bool
    {
        return $this->status === 'معطل';
    }

    // Helper methods for employment
    public function isCurrentlyEmployed(): bool
    {
        return $this->date_of_hire && ! $this->date_of_fire;
    }

    public function getEmploymentDuration(): int
    {
        if (! $this->date_of_hire) {
            return 0;
        }

        $startDate = \Carbon\Carbon::parse($this->date_of_hire);
        $endDate = $this->date_of_fire ? \Carbon\Carbon::parse($this->date_of_fire) : now();

        return $startDate->diffInDays($endDate);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // polymorphic relationship with acchead model
    public function account()
    {
        return $this->morphOne(AccHead::class, 'accountable');
    }
    
    // Accessors for finger print data
    public function getFingerPrintIdAttribute($value)
    {
        return $value ?: $this->id;
    }
    
    public function getFingerPrintNameAttribute($value)
    {
        return $value ?: $this->name;
    }

    public function kpis(): BelongsToMany
    {
        return $this->belongsToMany(Kpi::class, 'employee_kpis', 'employee_id', 'kpi_id')->withPivot('weight_percentage');
    }

    /**
     * Get the employee's image URL or fallback to placeholder
     * Works correctly in both local (Laragon) and production environments
     * Automatically detects and handles production symlink issues
     */
    public function getImageUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('employee_images');
        
        // If no media exists, return the fallback URL
        if (!$media) {
            return asset('assets/images/avatar-placeholder.svg');
        }
        
        // Get the standard URL from Spatie
        $url = $media->getUrl();
        
        // For production environments, check if symlink is working
        if (
            !str_contains(config('app.url'), 'localhost') &&
            !str_contains(config('app.url'), 'massar1.02.test:81')
        ) {
            $baseUrl = config('app.url');
            $url = $baseUrl . '/storage/app/public/' . $media->id . '/' . $media->file_name;
        }
        
        return $url;
    }
}
