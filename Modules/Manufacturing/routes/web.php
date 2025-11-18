<?php

use Illuminate\Support\Facades\Route;
use Modules\Manufacturing\Http\Controllers\{ManufacturingController, ManufacturingStageController, ManufacturingOrderController};

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/manufacturing/statistics', [ManufacturingController::class, 'manufacturingStatistics'])->name('manufacturing.statistics');

    Route::resource('manufacturing', ManufacturingController::class)->names('manufacturing');
    Route::resource('manufacturing-stages', ManufacturingStageController::class)->names('manufacturing.stages');
    Route::resource('manufacturing-orders', ManufacturingOrderController::class)->names('manufacturing.orders');

    Route::patch(
        'manufacturing-stages/{manufacturingStage}/toggle-status',
        [ManufacturingStageController::class, 'toggleStatus']
    )->name('manufacturing-stages.toggle-status');
});
