<?php

use Illuminate\Support\Facades\Route;
use Modules\Accounts\Http\Controllers\AccHeadController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Start Balance
    Route::get('accounts/start-balance', [AccHeadController::class, 'startBalance'])->name('accounts.startBalance');
    
    // Balance Sheet
    Route::get('accounts/balance-sheet', [AccHeadController::class, 'balanceSheet'])->name('accounts.balanceSheet');
    
    // Basic Data Statistics
    Route::get('accounts/basic-data-statistics', [AccHeadController::class, 'basicDataStatistics'])->name('accounts.basic-data-statistics');
    
    // Direct edit route
    Route::get('accounts/{id}/edit-direct', [AccHeadController::class, 'edit'])->name('accounts.edit-direct');
    
    Route::resource('accounts', AccHeadController::class)->names('accounts');
});
