<?php

use Illuminate\Support\Facades\Route;
use Modules\MyResources\Http\Controllers\ResourceController;
use Modules\MyResources\Http\Controllers\ResourceCategoryController;
use Modules\MyResources\Http\Controllers\ResourceTypeController;
use Modules\MyResources\Http\Controllers\ResourceStatusController;
use Modules\MyResources\Http\Controllers\ResourceAssignmentController;
use Modules\MyResources\Http\Controllers\ResourceDashboardController;

Route::middleware(['auth', 'verified'])->prefix('myresources')->name('myresources.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [ResourceDashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('can:view MyResources Dashboard');

    // API route for getting types by category (must come before resource routes)
    Route::get('/api/types-by-category', [ResourceController::class, 'getTypesByCategory'])->name('api.types-by-category');

    // Categories (must come before resource routes)
    Route::resource('/categories', ResourceCategoryController::class)
        ->except(['show'])
        ->middleware('can:view Resource Categories');

    // Types (must come before resource routes)
    Route::resource('/types', ResourceTypeController::class)
        ->except(['show'])
        ->middleware('can:view Resource Types');

    // Statuses (must come before resource routes)
    Route::resource('/statuses', ResourceStatusController::class)
        ->except(['show'])
        ->middleware('can:view Resource Statuses');

    // Assignments (must come before resource routes)
    Route::resource('/assignments', ResourceAssignmentController::class)
        ->middleware('can:view Resource Assignments');

    // Resources (must come last to avoid conflicts)
    Route::resource('/', ResourceController::class)->parameters(['' => 'resource'])->except(['index', 'show']);
    Route::get('/', [ResourceController::class, 'index'])->name('index')->middleware('can:view MyResources');
    Route::get('/{resource}', [ResourceController::class, 'show'])->name('show');
});
