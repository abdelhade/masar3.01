<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\ItemReportController;

Route::middleware(['auth'])->group(function () {
    Route::get('reports/items/inactive', [ItemReportController::class, 'itemInactiveReport'])
        ->name('reports.items.inactive');

    Route::get('reports/items/with/stores', [ItemReportController::class, 'itemsWithStoresReport'])
        ->name('reports.items.with-stores');
});
