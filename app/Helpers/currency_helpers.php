<?php

use Modules\Settings\Models\{PublicSetting, Currency};
use Illuminate\Support\Facades\Cache;

/**
 * التحقق من تفعيل نظام تعدد العملات
 */
if (!function_exists('isMultiCurrencyEnabled')) {

    function isMultiCurrencyEnabled()
    {
        $setting = PublicSetting::where('key', 'multi_currency_enabled')->first();
        if (!$setting) return false;
        return in_array($setting->value, ['1', 'true', 1, true], true);
    }
}
/**
 * الحصول على العملة الافتراضية للنظام
 */
if (!function_exists('getDefaultCurrency')) {

    function getDefaultCurrency()
    {
        // return Cache::remember('default_currency', 3600, function () {
        return Currency::where('is_default', true)->first();
        // });
    }
}

if (!function_exists('getExchangeRate')) {

    function getExchangeRate($currencyId)
    {
        if (!$currencyId) return 1;

        // لو معاك العملة الافتراضية في الكاش، قارن الـ ID الأول عشان توفر كويري
        $defaultCurrency = getDefaultCurrency();
        if ($defaultCurrency && $defaultCurrency->id == $currencyId) {
            return 1;
        }

        $currency = Currency::with('latestRate')->find($currencyId);

        // لو العملة مش موجودة أو غير مفعلة أو هي الافتراضية
        if (!$currency || !$currency->is_active || $currency->is_default) {
            return 1;
        }

        return (float) ($currency->latestRate->rate ?? 1);
    }
}
/**
 * دالة مساعدة سريعة للتحويل
 */
if (!function_exists('convertCurrency')) {

    function convertCurrency($amount, $fromCurrencyId, $toCurrencyId)
    {
        if ($fromCurrencyId == $toCurrencyId) {
            return $amount;
        }

        $fromRate = getExchangeRate($fromCurrencyId); // سعر العملة الأصلية مقابل الافتراضية
        $toRate = getExchangeRate($toCurrencyId);     // سعر العملة الهدف مقابل الافتراضية

        // معادلة التحويل: (المبلغ * سعر الأصل) / سعر الهدف
        // مثال: 100 دولار (سعرها 50) عايز احولها ليورو (سعره 55)
        // (100 * 50) / 55 = 90.90 يورو

        if ($toRate == 0) return 0; // تجنب القسمة على صفر

        return ($amount * $fromRate) / $toRate;
    }
}
