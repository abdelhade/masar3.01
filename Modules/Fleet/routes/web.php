<?php

use Illuminate\Support\Facades\Route;
use Modules\Fleet\Http\Controllers\FleetDashboardController;
use Modules\Fleet\Http\Controllers\VehicleTypeController;
use Modules\Fleet\Http\Controllers\VehicleController;
use Modules\Fleet\Http\Controllers\TripController;
use Modules\Fleet\Http\Controllers\FuelRecordController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::prefix('fleet')->name('fleet.')->group(function () {
        Route::get('/dashboard', [FleetDashboardController::class, 'index'])
            ->name('dashboard.index');
    });

    // Vehicle Types
    Route::resource('vehicle-types', VehicleTypeController::class)
        ->names('fleet.vehicle-types');

    // Vehicles
    Route::resource('vehicles', VehicleController::class)
        ->names('fleet.vehicles');

    // Trips
    Route::resource('trips', TripController::class)
        ->names('fleet.trips');

    // Fuel Records
    Route::resource('fuel-records', FuelRecordController::class)
        ->names('fleet.fuel-records');
});
