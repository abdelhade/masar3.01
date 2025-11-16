<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\CostCenterReportController;

Route::middleware(['auth'])->group(function () {
    Route::get('/reports/general-cost-centers-report', [CostCenterReportController::class, 'generalCostCentersReport'])
        ->name('reports.general-cost-centers-report');

    Route::get('/reports/general-cost-center-account-statement', [CostCenterReportController::class, 'generalCostCenterAccountStatement'])
        ->name('reports.general-cost-center-account-statement');

    Route::get('/reports/general-cost-centers-list', [CostCenterReportController::class, 'generalCostCentersList'])
        ->name('reports.general-cost-centers-list');
});

