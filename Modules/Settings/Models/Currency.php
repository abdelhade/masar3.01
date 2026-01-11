<?php

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    protected $fillable = [
        'name',
        'code',
        'symbol',
        'decimal_places',
        'is_default',
        'is_active',
        'rate_mode',
    ];

    protected $casts = [
        'decimal_places' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ==================== Relationships ====================

    /**
     * علاقة العملة مع أسعار الصرف التاريخية
     */
    public function exchangeRates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class);
    }

    /**
     * الحصول على آخر سعر صرف للعملة
     */
    public function latestRate()
    {
        return $this->hasOne(ExchangeRate::class)->latestOfMany('rate_date');
    }

    // ==================== Scopes ====================

    /**
     * العملات النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * العملة الافتراضية
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * العملات اليدوية
     */
    public function scopeManual($query)
    {
        return $query->where('rate_mode', 'manual');
    }

    // ==================== Events ====================

    protected static function boot()
    {
        parent::boot();

        // عتد الحذف: منع حذف العملة الافتراضية
        static::deleting(function ($currency) {
            if ($currency->is_default || $currency->id == 1) {
                throw new \Exception("لا يمكن حذف العملة الافتراضية للنظام.");
            }
        });

        // عند التحديث: منع بعض التعديلات على العملة الافتراضية
        static::updating(function ($currency) {
            // منع إلغاء الـ default عن العملة الافتراضية إذا كانت هي الوحيدة
            if ($currency->getOriginal('is_default') && !$currency->is_default) {
                throw new \Exception("يجب أن يكون للنظام عملة افتراضية واحدة على الأقل. قم بتعيين عملة أخرى كافتراضية أولاً.");
            }

            // منع تعديل الـ ID أو الـ Rate Mode للعملة الأساسية
            if ($currency->id == 1 && $currency->isDirty('id')) {
                throw new \Exception("لا يمكن تعديل المعرف الفريد للعملة الأساسية.");
            }
        });

        // عند تفعيل عملة كـ default، إلغاء باقي العملات
        static::saving(function ($currency) {
            if ($currency->is_default) {
                static::where('id', '!=', $currency->id)
                    ->update(['is_default' => false]);
            }
        });
    }
}
