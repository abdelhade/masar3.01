<?php

use Illuminate\Support\Facades\Route;
use Modules\Depreciation\Http\Controllers\DepreciationController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('depreciation')->name('depreciation.')->group(function () {
        Route::get('/', [DepreciationController::class, 'index'])->name('index');
        Route::get('/report', [DepreciationController::class, 'report'])->name('report');
        Route::post('/calculate-all', [DepreciationController::class, 'calculateAllDepreciation'])->name('calculate-all');
        Route::post('/sync-accounts', [DepreciationController::class, 'syncDepreciationAccounts'])->name('sync-accounts');
    });
});
