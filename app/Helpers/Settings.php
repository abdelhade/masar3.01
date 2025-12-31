<?php

use Illuminate\Support\Facades\Cache;
use Modules\Settings\Models\PublicSetting;

if (! function_exists('setting')) {
    /**
     * Get a setting value by key.
     */
    function setting(string $key, $default = null)
    {
        // نستخدم الكاش عشان الأداء
        $settings = Cache::rememberForever('public_settings', function () {
            return PublicSetting::select('key', 'value', 'input_type')
                ->get()
                ->mapWithKeys(function ($setting) {
                    $value = $setting->value;

                    // تحويل القيم النصية للـ boolean إلى boolean حقيقي
                    if ($setting->input_type === 'boolean') {
                        $originalValue = $value;
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        if ($value === null) {
                            $value = in_array(strtolower($originalValue), ['1', 'true', 'yes', 'on'], true);
                        }
                    }

                    return [$setting->key => $value];
                })
                ->toArray();
        });

        return $settings[$key] ?? $default;
    }
}

if (! function_exists('isVatEnabled')) {
    /**
     * Check if VAT fields are enabled.
     * Requires BOTH enable_vat_fields=1 AND vat_level != 'disabled'
     * 
     * @return bool
     */
    function isVatEnabled(): bool
    {
        // Must have enable_vat_fields = 1 (master switch)
        $masterSwitch = setting('enable_vat_fields', false) == '1';
        
        // AND vat_level must not be 'disabled'
        $vatLevel = setting('vat_level', 'disabled');
        
        return $masterSwitch && ($vatLevel !== 'disabled');
    }
}

if (! function_exists('getVatLevel')) {
    /**
     * Get the VAT level setting (invoice_level, item_level, both, or disabled).
     * Returns 'disabled' if enable_vat_fields is not enabled.
     * 
     * @return string
     */
    function getVatLevel(): string
    {
        // Check master switch first
        if (setting('enable_vat_fields', false) != '1') {
            return 'disabled';
        }
        
        return setting('vat_level', 'disabled');
    }
}

if (! function_exists('isWithholdingTaxEnabled')) {
    /**
     * Check if Withholding Tax fields are enabled.
     * Requires BOTH enable_vat_fields=1 AND withholding_tax_level != 'disabled'
     * 
     * @return bool
     */
    function isWithholdingTaxEnabled(): bool
    {
        // Must have enable_vat_fields = 1 (master switch)
        $masterSwitch = setting('enable_vat_fields', false) == '1';
        
        // AND withholding_tax_level must not be 'disabled'
        $withholdingTaxLevel = setting('withholding_tax_level', 'disabled');
        
        return $masterSwitch && ($withholdingTaxLevel !== 'disabled');
    }
}

if (! function_exists('getWithholdingTaxLevel')) {
    /**
     * Get the Withholding Tax level setting (invoice_level, item_level, both, or disabled).
     * Returns 'disabled' if enable_vat_fields is not enabled.
     * 
     * @return string
     */
    function getWithholdingTaxLevel(): string
    {
        // Check master switch first
        if (setting('enable_vat_fields', false) != '1') {
            return 'disabled';
        }
        
        return setting('withholding_tax_level', 'disabled');
    }
}
