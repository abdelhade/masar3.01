<?php

use Illuminate\Support\Facades\Route;
use Modules\Progress\Http\Controllers\WorkItemController;
use Modules\Progress\Http\Controllers\ProjectTypeController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('project-types', ProjectTypeController::class)->names('project.types');
    Route::resource('work-items', WorkItemController::class)->names('work.items');
});
