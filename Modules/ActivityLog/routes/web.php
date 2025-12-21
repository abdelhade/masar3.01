<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\ActivityLog\Http\Controllers\ActivityLogController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activitylog.index');
    Route::get('activity-logs/{id}', [ActivityLogController::class, 'show'])->name('activitylog.show');
});
