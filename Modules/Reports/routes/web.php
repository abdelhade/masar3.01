<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\ItemReportController;
use Modules\Reports\Http\Controllers\GeneralReportController;

Route::middleware(['auth'])->group(function () {
    Route::get('/reports', [GeneralReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/inactive-items', [ItemReportController::class, 'inactiveItemsReport'])->name('reports.inactive-items');
});
