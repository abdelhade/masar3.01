<?php
namespace Modules\Progress\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocalizationController extends Controller
{
    public function switch($locale)
    {
        // 1. التحقق من اللغة المدعومة
        if (!in_array($locale, ['en', 'ar', 'ur', 'hi'])) {
            return redirect()->back();
        }

        // 2. تحديث اللغة في ثلاث أماكن:
        Session::put('locale', $locale);       // الجلسة
        App::setLocale($locale);               // التطبيق الحالي
        config(['app.locale' => $locale]);     // الكونفج العام

        // 3. إعادة تحميل المترجم
        $translator = app('translator');
        $translator->setLocale($locale);

        return redirect()->back();
    }
}
