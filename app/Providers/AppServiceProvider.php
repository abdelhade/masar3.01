<?php

namespace App\Providers;

use App\Models\JournalDetail;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use App\Observers\JournalDetailObserver;
use Illuminate\Support\Facades\Cache;
use Modules\Settings\Models\PublicSetting;
use Illuminate\Support\Facades\Schema;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (!Schema::hasTable('public_settings'))
                return;
            $settings = Cache::rememberForever('public_settings', function () {
                return PublicSetting::pluck('value', 'key')->toArray();
            });
            config(['public_settings' => $settings]);
        } catch (\Exception $e) {
            // ممكن تكتب لوج هنا لو حابب، بس الأهم ما توقفش الـ boot ولا تطلع error
        }

        Paginator::useBootstrapFive();
        JournalDetail::observe(JournalDetailObserver::class);
        // Model::automaticallyEagerLoadRelationships();
    }
    
}
