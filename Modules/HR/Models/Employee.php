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
use App\Models\Scopes\BranchScope;

class Employee extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'employees';

    protected $guarded = ['id'];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_hire' => 'date',
        'date_of_fire' => 'date',
        'password' => 'hashed',
        'is_errand_allowed' => 'boolean',
    ];

    /**
     * تشغيل الـ Global Scope عند استدعاء الموديل
     * يجب أن تكون الدالة داخل الكلاس لتعمل على السيرفر
     */
    protected static function booted()
    {
        // تأكد من المسار الصحيح
        if (class_exists(BranchScope::class)) {
            static::addGlobalScope(new BranchScope);
        }
    }

    // --- العلاقات (Relationships) ---

    public function covenants(): HasMany
    {
        return $this->hasMany(Covenant::class, 'employee_id');
    }

    public function errands(): HasMany
    {
        return $this->hasMany(Errand::class, 'employee_id');
    }

    public function workPermissions(): HasMany
    {
        return $this->hasMany(WorkPermission::class, 'employee_id');
    }

    public function deductionsRewards(): HasMany
    {
        return $this->hasMany(EmployeeDeductionReward::class, 'employee_id');
    }

    public function advances(): HasMany
    {
        return $this->hasMany(EmployeeAdvance::class, 'employee_id');
    }

    public function flexibleSalaryProcessings(): HasMany
    {
        return $this->hasMany(FlexibleSalaryProcessing::class, 'employee_id');
    }

    // --- التحويلات (Mutators & Accessors) ---

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

    private static function getMaritalStatusReverseMap(): array { return array_flip(self::$maritalStatusMap); }
    private static function getEducationReverseMap(): array { return array_flip(self::$educationMap); }
    private static function getStatusReverseMap(): array { return array_flip(self::$statusMap); }

    public function setMaritalStatusAttribute($value)
    {
        if (empty($value)) { $this->attributes['marital_status'] = null; return; }
        if (in_array($value, self::$maritalStatusMap)) { $this->attributes['marital_status'] = $value; return; }
        $this->attributes['marital_status'] = self::$maritalStatusMap[$value] ?? $value;
    }

    public function setEducationAttribute($value)
    {
        if (empty($value)) { $this->attributes['education'] = null; return; }
        if (in_array($value, self::$educationMap)) { $this->attributes['education'] = $value; return; }
        $this->attributes['education'] = self::$educationMap[$value] ?? $value;
    }

    public function setStatusAttribute($value)
    {
        if (empty($value)) { $this->attributes['status'] = 'مفعل'; return; }
        if (in_array($value, self::$statusMap)) { $this->attributes['status'] = $value; return; }
        $this->attributes['status'] = self::$statusMap[$value] ?? $value;
    }

    public function getMaritalStatusAttribute($value)
    {
        if (!$value) return null;
        return self::getMaritalStatusReverseMap()[$value] ?? $value;
    }

    public function getEducationAttribute($value)
    {
        if (!$value) return null;
        return self::getEducationReverseMap()[$value] ?? $value;
    }

    public function getStatusAttribute($value) { return $value ?: 'مفعل'; }

    // --- الميديا (Spatie Media Library) ---

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('employee_images')
            ->singleFile()
            ->useFallbackUrl(asset('assets/images/avatar-placeholder.svg'))
            ->useFallbackPath(public_path('assets/images/avatar-placeholder.svg'));
    }

    public function getImageUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('employee_images');
        if (!$media) return asset('assets/images/avatar-placeholder.svg');

        $url = $media->getUrl();
        // حل مشكلة روابط التخزين على Hostinger (Shared Hosting)
        if (!app()->isLocal()) {
            $baseUrl = config('app.url');
            $url = $baseUrl.'/storage/app/public/'.$media->id.'/'.$media->file_name;
        }
        return $url;
    }

    // --- باقي العلاقات الإضافية ---

    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function shift(): BelongsTo { return $this->belongsTo(Shift::class); }
    public function job(): BelongsTo { return $this->belongsTo(EmployeesJob::class); }
    public function leaveRequests(): HasMany { return $this->hasMany(LeaveRequest::class); }
    public function country(): BelongsTo { return $this->belongsTo(Country::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function account() { return $this->morphOne(AccHead::class, 'accountable'); }

    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }

    public function isActive(): bool
    {
        $rawStatus = $this->attributes['status'] ?? null;
        return $rawStatus === 'مفعل' || $rawStatus === 'active';
    }

    protected $hidden = ['password'];
}