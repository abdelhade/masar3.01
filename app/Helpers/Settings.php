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
