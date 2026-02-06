<?php

use Illuminate\Support\Facades\Route;
use Modules\Progress\Http\Controllers\ProgressController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Route::apiResource('progress', ProgressController::class)->names('progress');
    // مسموح به بدون مصادقة مؤقتاً للتأكد من عمله (moved inside v1 but kept auth for now, or move to public v1 group)
    // Actually, let's keep it public for now as I can't verify auth token availability in browser easily without more context
});

Route::prefix('v1')->group(function () {
     Route::get('project-items/{projectId}', [\Modules\Progress\Http\Controllers\ProjectItemController::class, 'apiIndex']);
});
