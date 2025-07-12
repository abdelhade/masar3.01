<?php

namespace App\Providers;

use App\Models\JournalDetail;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use App\Observers\JournalDetailObserver;

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
        // Use Bootstrap 5 for pagination styling
        Paginator::useBootstrapFive();
        JournalDetail::observe(JournalDetailObserver::class);

        // automatically egar load relations
        // Model::automaticallyEagerLoadRelationships();
    }
}
