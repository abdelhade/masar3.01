<?php
namespace Modules\Progress\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // 1. تحديد اللغة (الأولوية: الكوكي > الافتراضي)
        $locale = $this->determineLocale();

        // 2. تعيين اللغة للتطبيق
        $this->setApplicationLocale($locale);

        // 3. مشاركة المتغيرات مع الـ views
        $this->shareViewVariables($locale);

        // 4. تسجيل معلومات للتصحيح
        $this->logDebugInfo($locale);

        return $next($request);
    }

    protected function determineLocale()
    {
        // جلب اللغة من الكوكي أو الافتراضي من config/app.php
        $locale = Cookie::get('locale', config('app.locale', 'ar'));

        $supportedLocales = [
            'en' => 'en',
            'ar' => 'ar',
            'ur' => 'ur',
            'hi' => 'hi'
        ];

        return $supportedLocales[$locale] ?? 'ar';
    }

    protected function setApplicationLocale($locale)
    {
        // تعيين اللغة في التطبيق
        App::setLocale($locale);

        // حفظ اللغة في الكوكي لمدة 30 يوم
        Cookie::queue('locale', $locale, 60 * 24 * 30);

        // تحديث إعدادات config
        config(['app.locale' => $locale]);

        // تحديث المترجم
        $translator = app('translator');
        $translator->setLocale($locale);

        if (method_exists($translator, 'load')) {
            $translator->load('*', '*', $locale);
        }
    }

    protected function shareViewVariables($locale)
    {
        view()->share([
            'currentLocale' => $locale,
            'isRTL' => in_array($locale, ['ar', 'ur']),
            'direction' => in_array($locale, ['ar', 'ur']) ? 'rtl' : 'ltr',
            'availableLocales' => [
                'en' => 'English',
                'ar' => 'العربية',
                'ur' => 'اردو',
                'hi' => 'हिन्दी'
            ]
        ]);
    }

    protected function logDebugInfo($locale)
    {
        \Log::debug('Locale Settings:', [
            'Selected Locale'   => $locale,
            'Cookie Locale'     => Cookie::get('locale'),
            'App Locale'        => App::getLocale(),
            'Config Locale'     => config('app.locale'),
            'Translator Locale' => app('translator')->getLocale(),
            'Validation Test'   => __('validation.required', ['attribute' => 'email'])
        ]);
    }
}
