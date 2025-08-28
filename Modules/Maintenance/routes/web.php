<?php

use Illuminate\Support\Facades\Route;
use Modules\Maintenance\Http\Controllers\{MaintenanceController, ServiceTypeController};

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('service-types', ServiceTypeController::class)->names('service.types');
    Route::resource('maintenances', MaintenanceController::class)->names('maintenances');
});
