<?php

namespace App\Providers;

use App\Models\Item;
use App\Models\JournalDetail;
use App\Models\NoteDetails;
use App\Observers\ItemObserver;
use App\Observers\JournalDetailObserver;
use App\Observers\NoteDetailsObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Modules\Settings\Models\PublicSetting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // حل مشكلة المسارات المفقودة على Hostinger (Shared Hosting)
        // هذا الجزء يوجه أي نداء قديم للمسار الجديد داخل الموديولات
        $aliases = [
            'App\Models\Employee'       => \Modules\HR\Models\Employee::class,
            'App\Models\WorkPermission' => \Modules\HR\Models\WorkPermission::class,
            // أضف أي موديلات أخرى قمت بنقلها هنا بنفس الطريقة
        ];

        foreach ($aliases as $oldPath => $newClass) {
            if (!class_exists($oldPath) && class_exists($newClass)) {
                class_alias($newClass, $oldPath);
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (! Schema::hasTable('public_settings')) {
                return;
            }
            $settings = Cache::rememberForever('public_settings', function () {
                return PublicSetting::pluck('value', 'key')->toArray();
            });
            config(['public_settings' => $settings]);
        } catch (\Exception $e) {
            // Log if needed
        }

        Paginator::useBootstrapFive();
        Item::observe(ItemObserver::class);
        NoteDetails::observe(NoteDetailsObserver::class);
    }
}