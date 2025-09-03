<?php

use Illuminate\Support\Facades\Route;
use Modules\Rentals\Http\Controllers\RentalsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Route::apiResource('rentals', RentalsController::class)->names('rentals');
});
