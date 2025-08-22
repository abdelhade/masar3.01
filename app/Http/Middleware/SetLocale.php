<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App as AppFacade;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');

        if (! $locale) {
            $locale = config('app.locale');
            $publicSettings = config('public_settings');
            if (is_array($publicSettings) && ! empty($publicSettings['app_locale'])) {
                $locale = $publicSettings['app_locale'];
            }
        }

        if (! in_array($locale, ['ar', 'en', 'tr'], true)) {
            $locale = config('app.locale');
        }

        AppFacade::setLocale($locale);

        return $next($request);
    }
}
