<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\purchaseReportController;

Route::middleware(['auth'])->group(function () {
    Route::get('/reports/general-purchases-total', [purchaseReportController::class, 'generalPurchasesTotalReport'])
        ->name('reports.general-purchases-total');

    Route::get('/reports/general-purchases-items-report', [purchaseReportController::class, 'generalPurchasesItemsReport'])->name('reports.general-purchases-items-report');
});
