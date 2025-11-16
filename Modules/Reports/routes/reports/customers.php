<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\CustomerReportController;

Route::middleware(['auth'])->group(function () {
    Route::get('/reports/general-customers-report', [CustomerReportController::class, 'generalCustomersReport'])
        ->name('reports.general-customers-report');

    Route::get('/reports/general-customers-daily-report', [CustomerReportController::class, 'generalCustomersDailyReport'])
        ->name('reports.general-customers-daily-report');

    Route::get('/reports/general-customers-items-report', [CustomerReportController::class, 'generalCustomersItemsReport'])
        ->name('reports.general-customers-items-report');

    Route::get('/reports/general-customers-total-report', [CustomerReportController::class, 'generalCustomersTotalReport'])
        ->name('reports.general-customers-total-report');

    Route::get('/reports/customer-debt-history', [CustomerReportController::class, 'customerDebtHistory'])
        ->name('reports.customer-debt-history');
});

