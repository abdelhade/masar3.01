<?php

use Illuminate\Support\Facades\Route;
use Modules\MyResources\Http\Controllers\ResourcesController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('myresources', ResourcesController::class)->names('myresources');
});
