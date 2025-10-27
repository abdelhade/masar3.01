<?php

use Modules\Settings\Models\PublicSetting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('setting')) {
    /**
     * Get a setting value by key.
     */
    function setting(string $key, $default = null)
    {
        // نستخدم الكاش عشان الأداء
        $settings = Cache::rememberForever('public_settings', function () {
            return PublicSetting::pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }
}
