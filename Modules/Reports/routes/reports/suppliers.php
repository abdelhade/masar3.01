<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\SupplierReportController;

Route::middleware(['auth'])->group(function () {
    Route::get('/reports/general-suppliers-report', [SupplierReportController::class, 'generalSuppliersReport'])
        ->name('reports.general-suppliers-report');

    Route::get('/reports/general-suppliers-daily-report', [SupplierReportController::class, 'generalSuppliersDailyReport'])
        ->name('reports.general-suppliers-daily-report');

    Route::get('/reports/general-suppliers-items-report', [SupplierReportController::class, 'generalSuppliersItemsReport'])
        ->name('reports.general-suppliers-items-report');

    Route::get('/reports/general-suppliers-total-report', [SupplierReportController::class, 'generalSuppliersTotalReport'])
        ->name('reports.general-suppliers-total-report');
});

