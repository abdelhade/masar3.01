<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Accounts\Models\AccHead;
use Modules\Branches\Models\Branch;
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
        'is_errand_allowed' => 'boolean',
    ];

    protected $guarded = ['id'];

    // عهد عمل
    public function covenants(): HasMany
    {
        return $this->hasMany(Covenant::class, 'employee_id');
    }

    // ماموريات العمل
    public function errands(): HasMany
    {
        return $this->hasMany(Errand::class, 'employee_id');
    }

    // أذونات العمل
    public function workPermissions(): HasMany
    {
        return $this->hasMany(WorkPermission::class, 'employee_id');
    }

    // الخصومات والمكافآت
    public function deductionsRewards(): HasMany
    {
        return $this->hasMany(EmployeeDeductionReward::class, 'employee_id');
    }

    // السلف
    public function advances(): HasMany
    {
        return $this->hasMany(EmployeeAdvance::class, 'employee_id');
    }

    // معالجات الراتب المرن
    public function flexibleSalaryProcessings(): HasMany
    {
        return $this->hasMany(FlexibleSalaryProcessing::class, 'employee_id');
    }

    // Mapping arrays for marital_status, education, and status
    // English to Arabic mapping (for form input -> database)
    private static $maritalStatusMap = [
        'single' => 'غير متزوج',
        'married' => 'متزوج',
        'divorced' => 'مطلق',
        'widowed' => 'أرمل',
    ];

    private static $educationMap = [
        'diploma' => 'دبلوم',
        'bachelor' => 'بكالوريوس',
        'master' => 'ماجستير',
        'doctorate' => 'دكتوراه',
    ];

    private static $statusMap = [
        'active' => 'مفعل',
        'inactive' => 'معطل',
    ];

    // Reverse mapping: Arabic to English (for database -> form)
    private static function getMaritalStatusReverseMap(): array
    {
        return array_flip(self::$maritalStatusMap);
    }

    private static function getEducationReverseMap(): array
    {
        return array_flip(self::$educationMap);
    }

    private static function getStatusReverseMap(): array
    {
        return array_flip(self::$statusMap);
    }

    // Mutators: Convert English to Arabic when saving (also accept Arabic directly)
    public function setMaritalStatusAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['marital_status'] = null;

            return;
        }

        // If value is already in Arabic (from database enum), keep it
        if (in_array($value, self::$maritalStatusMap)) {
            $this->attributes['marital_status'] = $value;

            return;
        }

        // Convert English to Arabic
        if (isset(self::$maritalStatusMap[$value])) {
            $this->attributes['marital_status'] = self::$maritalStatusMap[$value];
        } else {
            $this->attributes['marital_status'] = $value;
        }
    }

    public function setEducationAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['education'] = null;

            return;
        }

        // If value is already in Arabic (from database enum), keep it
        if (in_array($value, self::$educationMap)) {
            $this->attributes['education'] = $value;

            return;
        }

        // Convert English to Arabic
        if (isset(self::$educationMap[$value])) {
            $this->attributes['education'] = self::$educationMap[$value];
        } else {
            $this->attributes['education'] = $value;
        }
    }

    public function setStatusAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['status'] = 'مفعل'; // Default

            return;
        }

        // If value is already in Arabic (from database enum), keep it
        if (in_array($value, self::$statusMap)) {
            $this->attributes['status'] = $value;

            return;
        }

        // Convert English to Arabic
        if (isset(self::$statusMap[$value])) {
            $this->attributes['status'] = self::$statusMap[$value];
        } else {
            $this->attributes['status'] = $value;
        }
    }

    // Accessors: Convert Arabic (from DB) to English for form compatibility
    // Status form uses Arabic, so status accessor returns Arabic
    // Marital status and education forms use English, so convert to English
    public function getMaritalStatusAttribute($value)
    {
        if (! $value) {
            return null;
        }
        // Convert Arabic (from DB) to English (form expects English)
        $reverseMap = self::getMaritalStatusReverseMap();

        return $reverseMap[$value] ?? $value;
    }

    public function getEducationAttribute($value)
    {
        if (! $value) {
            return null;
        }
        // Convert Arabic (from DB) to English (form expects English)
        $reverseMap = self::getEducationReverseMap();

        return $reverseMap[$value] ?? $value;
    }

    public function getStatusAttribute($value)
    {
        // Return Arabic value directly (form expects Arabic)
        return $value ?: 'مفعل'; // Default to Arabic
    }

    // Helper methods to get English values when needed
    public function getMaritalStatusEnglishAttribute(): ?string
    {
        $value = $this->attributes['marital_status'] ?? null;
        if (! $value) {
            return null;
        }
        $reverseMap = self::getMaritalStatusReverseMap();

        return $reverseMap[$value] ?? $value;
    }

    public function getEducationEnglishAttribute(): ?string
    {
        $value = $this->attributes['education'] ?? null;
        if (! $value) {
            return null;
        }
        $reverseMap = self::getEducationReverseMap();

        return $reverseMap[$value] ?? $value;
    }

    public function getStatusEnglishAttribute(): string
    {
        $value = $this->attributes['status'] ?? 'مفعل';
        $reverseMap = self::getStatusReverseMap();

        return $reverseMap[$value] ?? 'active';
    }

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
        return $this->hasMany(\App\Models\PayrollEntry::class);
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
        // Check raw database value directly (bypass accessor)
        $rawStatus = $this->attributes['status'] ?? null;

        return $rawStatus === 'مفعل' || $rawStatus === 'active';
    }

    public function isInactive(): bool
    {
        // Check raw database value directly (bypass accessor)
        $rawStatus = $this->attributes['status'] ?? null;

        return $rawStatus === 'معطل' || $rawStatus === 'inactive';
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
        if (! $media) {
            return asset('assets/images/avatar-placeholder.svg');
        }

        // Get the standard URL from Spatie
        $url = $media->getUrl();

        // For production environments, check if symlink is working
        if (
            ! str_contains(config('app.url'), 'localhost') &&
            ! str_contains(config('app.url'), '127.0.0.1') &&
            ! str_contains(config('app.url'), 'http://massar1.02.test:81') &&
            ! str_contains(config('app.url'), 'https://massar1.02.test') &&
            ! str_contains(config('app.url'), 'https://massar1.02.test:8000')
        ) {
            $baseUrl = config('app.url');
            $url = $baseUrl.'/storage/app/public/'.$media->id.'/'.$media->file_name;
        }

        return $url;
    }

    public function lineManager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'line_manager_id');
    }

    public function terminations(): HasMany
    {
        return $this->hasMany(\Modules\Recruitment\Models\Termination::class);
    }

    public function latestContract(): BelongsTo
    {
        return $this->belongsTo(\Modules\Recruitment\Models\Contract::class, 'id', 'employee_id')
            ->latestOfMany();
    }

    public function dailyProgress(): HasMany
    {
        return $this->hasMany(\Modules\Progress\Models\DailyProgress::class, 'employee_id');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(\Modules\Progress\Models\ProjectProgress::class, 'employee_project', 'employee_id', 'project_id');
    }
}
