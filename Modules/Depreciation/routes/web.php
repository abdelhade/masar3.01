
<?php

use Illuminate\Support\Facades\Route;
use Modules\Depreciation\Http\Controllers\DepreciationController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('depreciation')->name('depreciation.')->group(function () {
        Route::get('/', [DepreciationController::class, 'index'])->name('index');
        // Specific routes must come before parameterized routes
        Route::get('/schedule', [DepreciationController::class, 'schedule'])->name('schedule');
        Route::get('/report', [DepreciationController::class, 'report'])->name('report');
        Route::get('/{id}', [DepreciationController::class, 'show'])->name('show');

        // Depreciation calculation routes
        Route::post('/calculate-all', [DepreciationController::class, 'calculateAllDepreciation'])->name('calculate-all');
        Route::get('/calculate-all', [DepreciationController::class, 'calculateAllDepreciation'])->name('calculate-all.get');

        // Account synchronization routes
        Route::post('/sync-accounts', [DepreciationController::class, 'syncDepreciationAccounts'])->name('sync-accounts');
        Route::get('/sync-accounts', [DepreciationController::class, 'syncDepreciationAccounts'])->name('sync-accounts.get');

        // Schedule management routes
        Route::post('/schedule/generate', [DepreciationController::class, 'generateSchedule'])->name('schedule.generate');
        Route::get('/schedule/export/{assetId}', [DepreciationController::class, 'exportSchedule'])->name('schedule.export');
        Route::post('/schedule/bulk-process', [DepreciationController::class, 'bulkProcessSchedule'])->name('schedule.bulk-process');
    });
});
