<?php

namespace Modules\Reports\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Reports';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')->group(module_path($this->name, '/routes/web.php'));
        Route::middleware('web')->group(module_path($this->name, '/routes/reports/items.php'));
        Route::middleware('web')->group(module_path($this->name, '/routes/reports/sales.php'));
        Route::middleware('web')->group(module_path($this->name, '/routes/reports/purchase.php'));
        Route::middleware('web')->group(module_path($this->name, '/routes/reports/inventory.php'));
        Route::middleware('web')->group(module_path($this->name, '/routes/reports/customers.php'));
        Route::middleware('web')->group(module_path($this->name, '/routes/reports/suppliers.php'));
        Route::middleware('web')->group(module_path($this->name, '/routes/reports/expenses.php'));
        Route::middleware('web')->group(module_path($this->name, '/routes/reports/cost-centers.php'));
        Route::middleware('web')->group(module_path($this->name, '/routes/GeneralReports/general-reports.php'));
        Route::middleware('web')->group(module_path($this->name, '/routes/AccountsReports/accounts-reports.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::middleware('api')->prefix('api')->name('api.')->group(module_path($this->name, '/routes/api.php'));
    }
}
