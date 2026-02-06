<?php

namespace App\Helpers;

class LocaleHelper
{
    public static function getCurrentLocale()
    {
        return session('locale', config('app.locale', 'ar'));
    }
    
    public static function isRTL()
    {
        return self::getCurrentLocale() === 'ar';
    }
    
    public static function getDirection()
    {
        return self::isRTL() ? 'rtl' : 'ltr';
    }
}
