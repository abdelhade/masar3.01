<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\ReportsController;
use Modules\Reports\Http\Controllers\GeneralReportController;

Route::middleware(['auth'])->group(function () {
    Route::get('/reports', [GeneralReportController::class, 'index'])->name('reports.index');

});
