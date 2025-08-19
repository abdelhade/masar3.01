<?php

namespace App\Providers;

use App\Models\Item;
use App\Models\JournalDetail;
use App\Observers\ItemObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use App\Observers\JournalDetailObserver;
use Modules\Settings\Models\PublicSetting;


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
        Item::observe(ItemObserver::class);
        // Model::automaticallyEagerLoadRelationships();
    }
}
