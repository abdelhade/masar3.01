<?php
// Modules/Maintenance/Models/PeriodicMaintenanceSchedule.php

namespace Modules\Maintenance\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Branches\Models\Branch;
use Carbon\Carbon;

class PeriodicMaintenanceSchedule extends Model
{
    protected $fillable = [
        'item_name',
        'item_number',
        'client_name',
        'client_phone',
        'service_type_id',
        'frequency_type',
        'frequency_value',
        'start_date',
        'next_maintenance_date',
        'last_maintenance_date',
        'notification_days_before',
        'is_active',
        'notes',
        'branch_id',
        'maintainable_id',
        'maintainable_type',
    ];


    protected $casts = [
        'start_date' => 'date',
        'next_maintenance_date' => 'date',
        'last_maintenance_date' => 'date',
        'is_active' => 'boolean',
        'notification_days_before' => 'integer',
        'frequency_value' => 'integer',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);

        // حساب تاريخ الصيانة القادمة تلقائياً عند الإنشاء
        static::creating(function ($schedule) {
            if (!$schedule->next_maintenance_date) {
                $schedule->next_maintenance_date = $schedule->calculateNextMaintenanceDate($schedule->start_date);
            }
        });
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class, 'periodic_schedule_id');
    }

    public function notifications()
    {
        return $this->hasMany(MaintenanceNotification::class, 'periodic_schedule_id');
    }

    public function maintainable()
    {
        return $this->morphTo();
    }

    /**
     * حساب تاريخ الصيانة القادمة بناءً على التكرار
     */
    public function calculateNextMaintenanceDate($fromDate = null)
    {
        $date = $fromDate ? Carbon::parse($fromDate) : Carbon::parse($this->next_maintenance_date);

        return match ($this->frequency_type) {
            'daily' => $date->addDay(),
            'weekly' => $date->addWeek(),
            'monthly' => $date->addMonth(),
            'quarterly' => $date->addMonths(3),
            'semi_annual' => $date->addMonths(6),
            'annual' => $date->addYear(),
            'custom_days' => $date->addDays($this->frequency_value),
            default => $date,
        };
    }

    /**
     * تحديث الجدول بعد إتمام الصيانة
     */
    public function markMaintenanceCompleted()
    {
        $this->update([
            'last_maintenance_date' => now(),
            'next_maintenance_date' => $this->calculateNextMaintenanceDate(now()),
        ]);
    }

    /**
     * التحقق من اقتراب موعد الصيانة
     */
    public function isMaintenanceDueSoon()
    {
        $notificationDate = $this->next_maintenance_date->subDays($this->notification_days_before);
        return now()->greaterThanOrEqualTo($notificationDate) && now()->lessThan($this->next_maintenance_date);
    }

    /**
     * التحقق من تأخر الصيانة
     */
    public function isOverdue()
    {
        return now()->greaterThan($this->next_maintenance_date);
    }

    /**
     * الحصول على الاسم المقروء للتكرار
     */
    public function getFrequencyLabel()
    {
        return match ($this->frequency_type) {
            'daily' => 'يومي',
            'weekly' => 'أسبوعي',
            'monthly' => 'شهري',
            'quarterly' => 'ربع سنوي',
            'semi_annual' => 'نصف سنوي',
            'annual' => 'سنوي',
            'custom_days' => $this->frequency_value . ' يوم',
            default => 'غير محدد',
        };
    }
}
