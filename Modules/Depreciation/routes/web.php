
<?php

use Illuminate\Support\Facades\Route;
use Modules\Depreciation\Http\Controllers\DepreciationController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('depreciation')->name('depreciation.')->group(function () {
        Route::get('/', [DepreciationController::class, 'index'])->name('index')->middleware('can:view Depreciation Dashboard');
        // Specific routes must come before parameterized routes
        Route::get('/schedule', [DepreciationController::class, 'schedule'])->name('schedule')->middleware('can:view Depreciation Schedules');
        Route::get('/report', [DepreciationController::class, 'report'])->name('report')->middleware('can:view Depreciation Dashboard');
        Route::get('/{id}', [DepreciationController::class, 'show'])->name('show')->middleware('can:view Depreciation Items');

        // Depreciation calculation routes
        Route::post('/calculate-all', [DepreciationController::class, 'calculateAllDepreciation'])->name('calculate-all')->middleware('can:edit Depreciation Items');
        Route::get('/calculate-all', [DepreciationController::class, 'calculateAllDepreciation'])->name('calculate-all.get')->middleware('can:edit Depreciation Items');

        // Account synchronization routes
        Route::post('/sync-accounts', [DepreciationController::class, 'syncDepreciationAccounts'])->name('sync-accounts')->middleware('can:edit Accounts Assets');
        Route::get('/sync-accounts', [DepreciationController::class, 'syncDepreciationAccounts'])->name('sync-accounts.get')->middleware('can:edit Accounts Assets');

        // Schedule management routes
        Route::post('/schedule/generate', [DepreciationController::class, 'generateSchedule'])->name('schedule.generate')->middleware('can:create Depreciation Schedules');
        Route::get('/schedule/export/{assetId}', [DepreciationController::class, 'exportSchedule'])->name('schedule.export')->middleware('can:print Depreciation Schedules');
        Route::post('/schedule/bulk-process', [DepreciationController::class, 'bulkProcessSchedule'])->name('schedule.bulk-process')->middleware('can:edit Depreciation Schedules');
    });
});
