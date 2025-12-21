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
        //
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
            // ممكن تكتب لوج هنا لو حابب، بس الأهم ما توقفش الـ boot ولا تطلع error
        }

        Paginator::useBootstrapFive();
        // JournalDetail::observe(classes: JournalDetailObserver::class);
        Item::observe(ItemObserver::class);
        NoteDetails::observe(NoteDetailsObserver::class);
        // Model::automaticallyEagerLoadRelationships();
    }
}
