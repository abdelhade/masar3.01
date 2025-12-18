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

    /**
     * الحصول على سعر الصرف في تاريخ محدد
     */
    public function rateOnDate($date)
    {
        return $this->hasOne(ExchangeRate::class)
            ->where('rate_date', '<=', $date)
            ->orderBy('rate_date', 'desc');
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
     * العملات التي تستخدم API
     */
    public function scopeAutomatic($query)
    {
        return $query->where('rate_mode', 'automatic');
    }

    /**
     * العملات اليدوية
     */
    public function scopeManual($query)
    {
        return $query->where('rate_mode', 'manual');
    }

    // ==================== Helper Methods ====================

    /**
     * التحقق من أن العملة هي الافتراضية
     */
    public function isDefault(): bool
    {
        return $this->is_default;
    }

    /**
     * التحقق من أن العملة نشطة
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * التحقق من أن العملة تستخدم API
     */
    public function isAutomatic(): bool
    {
        return $this->rate_mode === 'automatic';
    }

    /**
     * الحصول على سعر الصرف الحالي
     */
    public function getCurrentRate()
    {
        // لو العملة هي الافتراضية، السعر = 1
        if ($this->is_default) {
            return 1;
        }

        return $this->latestRate?->rate ?? null;
    }

    /**
     * تنسيق المبلغ حسب decimal_places
     */
    public function formatAmount($amount): string
    {
        return number_format($amount, $this->decimal_places, '.', ',');
    }

    /**
     * عرض المبلغ مع رمز العملة
     */
    public function displayAmount($amount): string
    {
        $formatted = $this->formatAmount($amount);
        return $this->symbol . ' ' . $formatted;
    }

    // ==================== Events ====================

    protected static function boot()
    {
        parent::boot();

        // عند تفعيل عملة كـ default، إلغاء باقي العملات
        static::saving(function ($currency) {
            if ($currency->is_default) {
                static::where('id', '!=', $currency->id)
                    ->update(['is_default' => false]);
            }
        });
    }
}
