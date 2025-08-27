<?php

use Illuminate\Support\Facades\Route;
use Modules\Rentals\Http\Controllers\{RentalsUnitController, RentalsBuildingController, RentalsLeaseController};

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('buildings', RentalsBuildingController::class)->names('rentals.buildings');
    Route::resource('rentals-units', RentalsUnitController::class)->names('rentals.units')->except(['create']);

    Route::get('rentals-units/create/{id}', [RentalsUnitController::class, 'create'])
        ->name('rentals-units.create');

    Route::resource('rentals-leases', RentalsLeaseController::class)->names('rentals.leases');
});
