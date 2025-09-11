<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\salesReportController;

Route::middleware(['auth'])->group(function () {
    Route::get('reports/general-sales-items', [salesReportController::class, 'generalSalesItemsReport'])
        ->name('reports.general.sales.items');

    Route::get('/reports/general-sales-total', [salesReportController::class, 'generalSalesTotalReport'])
        ->name('reports.general-sales-total-report');
});
