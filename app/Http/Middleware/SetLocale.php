<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App as AppFacade;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // الأولوية: query > session > cookie > config > public_settings
        $locale = $request->query('locale')
            ?? $request->session()->get('locale')
            ?? Cookie::get('app_locale')
            ?? config('app.locale');

        // لو عندك إعدادات عامة تضبطها هنا
        $publicSettings = config('public_settings');
        if (! $locale && is_array($publicSettings) && ! empty($publicSettings['app_locale'])) {
            $locale = $publicSettings['app_locale'];
        }

        // السماح بلغات معينة فقط
        if (! in_array($locale, ['ar', 'en', 'ur', 'hi', 'tr', 'fr'], true)) {
            $locale = config('app.locale');
        }

        // تطبيق اللغة
        AppFacade::setLocale($locale);

        // كمان نخزنها في السيشن للتأكد
        $request->session()->put('locale', $locale);

        return $next($request);
    }
}