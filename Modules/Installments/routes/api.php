<?php

use Illuminate\Support\Facades\Route;
use Modules\Installments\Http\Controllers\InstallmentsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('installments', InstallmentsController::class)->names('installments');
});
