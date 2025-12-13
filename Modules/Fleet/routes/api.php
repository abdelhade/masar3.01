<?php

use Illuminate\Support\Facades\Route;
use Modules\Fleet\Http\Controllers\FleetController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('fleets', FleetController::class)->names('fleet');
});
