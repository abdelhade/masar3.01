<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\ItemReportController;

Route::middleware(['auth'])->group(function () {
    Route::get('reports/items/inactive', [ItemReportController::class, 'itemInactiveReport'])
        ->name('reports.items.inactive');

    Route::get('reports/items/idle', [ItemReportController::class, 'idleItemsReport'])
        ->name('reports.items.idle');

    Route::get('reports/items/most-expensive', [ItemReportController::class, 'mostExpensiveItemsReport'])
        ->name('reports.items.most-expensive');

    Route::get('reports/items/with/stores', [ItemReportController::class, 'itemsWithStoresReport'])
        ->name('reports.items.with-stores');
});
