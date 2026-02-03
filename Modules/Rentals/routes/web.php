<?php

use Illuminate\Support\Facades\Route;
use Modules\Rentals\Http\Controllers\RentalsStatisticsController;
use Modules\Rentals\Http\Controllers\{RentalsUnitController, RentalsBuildingController, RentalsLeaseController, RentalsReportController};

Route::middleware(['auth'])->group(function () {

    Route::get('rentals-reports', [RentalsReportController::class, 'index'])
        ->name('rentals.reports');

    Route::get('rentals/statistics/overview', [RentalsStatisticsController::class, 'index'])
        ->name('rentals.statistics');

    Route::resource('buildings', RentalsBuildingController::class)->names('rentals.buildings');
    Route::get('rentals-units/create/{id?}', [RentalsUnitController::class, 'create'])
        ->name('rentals-units.create');

    Route::resource('rentals-units', RentalsUnitController::class)->names('rentals.units')->except(['create']);

    Route::resource('rentals-leases', RentalsLeaseController::class)->names('rentals.leases');

    Route::post('/dashboard/refresh', [RentalsStatisticsController::class, 'refreshCache'])
        ->name('dashboard.refresh');
});
