<?php

use Illuminate\Support\Facades\Route;
use Modules\Manufacturing\Http\Controllers\{ManufacturingController, ManufacturingStageController};

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('manufacturing', ManufacturingController::class)->names('manufacturing');

    Route::resource('manufacturing-stages', ManufacturingStageController::class)->names('manufacturing.stages');

    Route::patch(
        'manufacturing-stages/{manufacturingStage}/toggle-status',
        [ManufacturingStageController::class, 'toggleStatus']
    )->name('manufacturing-stages.toggle-status');
});
