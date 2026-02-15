<?php

use Illuminate\Support\Facades\Route;
use Modules\Manufacturing\Http\Controllers\{ManufacturingController, ManufacturingStageController, ManufacturingOrderController};

Route::middleware(['auth', 'verified'])->group(function () {

    // Specific routes must come BEFORE resource routes to avoid conflicts
    Route::get('/manufacturing/statistics', [ManufacturingController::class, 'manufacturingStatistics'])->name('manufacturing.statistics');
    
    Route::get(
        'manufacturing/stage-invoices-report',
        [ManufacturingController::class, 'stageInvoicesReport']
    )->name('manufacturing.stage-invoices-report');

    // Manufacturing Templates
    Route::get('manufacturing/templates', [\Modules\Manufacturing\Http\Controllers\ManufacturingTemplateController::class, 'index'])
        ->name('manufacturing.templates.index');
    Route::patch('manufacturing/templates/{templateId}/toggle-active', [\Modules\Manufacturing\Http\Controllers\ManufacturingTemplateController::class, 'toggleActive'])
        ->name('manufacturing.templates.toggle-active');
    Route::delete('manufacturing/templates/{templateId}', [\Modules\Manufacturing\Http\Controllers\ManufacturingTemplateController::class, 'destroy'])
        ->name('manufacturing.templates.destroy');

    // Resource routes
    Route::resource('manufacturing', ManufacturingController::class)->names('manufacturing');
    Route::resource('manufacturing-stages', ManufacturingStageController::class)->names('manufacturing.stages');
    Route::resource('manufacturing-orders', ManufacturingOrderController::class)->names('manufacturing.orders');

    Route::patch(
        'manufacturing-stages/{manufacturingStage}/toggle-status',
        [ManufacturingStageController::class, 'toggleStatus']
    )->name('manufacturing-stages.toggle-status');
});
