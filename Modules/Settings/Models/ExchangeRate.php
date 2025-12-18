<?php

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRate extends Model
{
    protected $fillable = [
        'currency_id',
        'rate',
        'rate_date',
    ];

    protected $casts = [
        'rate' => 'decimal:8',
        'rate_date' => 'date',
    ];

    // ==================== Relationships ====================

    /**
     * علاقة السعر بالعملة
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    // ==================== Scopes ====================

    /**
     * الأسعار في تاريخ محدد
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('rate_date', $date);
    }

    /**
     * الأسعار في نطاق تاريخي
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('rate_date', [$startDate, $endDate]);
    }

    /**
     * ترتيب حسب الأحدث
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('rate_date', 'desc');
    }

    // ==================== Helper Methods ====================

    /**
     * تحويل مبلغ باستخدام هذا السعر
     */
    public function convert($amount): float
    {
        return $amount * $this->rate;
    }
}
