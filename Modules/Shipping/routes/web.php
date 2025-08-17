<?php

use Illuminate\Support\Facades\Route;
use Modules\Shipping\Http\Controllers\{
    ShippingCompanyController,
    ShipmentController,
    DriverController,
    OrderController
};

Route::middleware(['auth', 'verified'])->group(function () {
    // Route::prefix('shipping')->name('shipping.')->group(function () {
    Route::resource('companies', ShippingCompanyController::class)->names('companies');
    Route::resource('shipments', ShipmentController::class)->names('shipments');
    // });
    // Route::prefix('delivery')->group(function () {
    Route::resource('drivers', DriverController::class)->names('drivers');
    Route::resource('orders', OrderController::class)->names('orders');
    // });
});
