<?php

use Illuminate\Support\Facades\Route;
use Modules\Shipping\Http\Controllers\{
    ShippingCompanyController,
    ShipmentController,
    DriverController,
    OrderController,
    ShippingStatisticsController,
    ShippingZoneController,
    DriverRatingController
};

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('companies', ShippingCompanyController::class)->names('companies');
    Route::resource('shipments', ShipmentController::class)->names('shipments');
    Route::resource('drivers', DriverController::class)->names('drivers');
    Route::resource('orders', OrderController::class)->names('orders');
    Route::resource('shipping/zones', ShippingZoneController::class)->names('shipping.zones');

    Route::get('dashboard/statistics', [ShippingStatisticsController::class, 'index'])
        ->name('shipping.dashboard.statistics');
    
    Route::get('orders/{order}/rate-driver', [DriverRatingController::class, 'create'])
        ->name('orders.rate-driver');
    Route::post('orders/{order}/rate-driver', [DriverRatingController::class, 'store'])
        ->name('orders.rate-driver.store');
});

