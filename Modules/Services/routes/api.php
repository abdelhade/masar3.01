<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Services\Http\Controllers\ServiceController;
use Modules\Services\Http\Controllers\ServiceBookingController;

/*
|--------------------------------------------------------------------------
| Services API Routes
|--------------------------------------------------------------------------
|
| مسارات API لوحدة إدارة الخدمات
| جميع المسارات محمية بـ middleware للمصادقة
|
*/

Route::middleware(['auth:sanctum'])->prefix('services')->group(function () {
    
    // API للخدمات
    Route::apiResource('services', ServiceController::class);
    Route::patch('services/{service}/toggle-status', [ServiceController::class, 'toggleStatus']);
    
    // API لحجوزات الخدمات
    Route::apiResource('bookings', ServiceBookingController::class);
    Route::patch('bookings/{booking}/complete', [ServiceBookingController::class, 'complete']);
    Route::patch('bookings/{booking}/cancel', [ServiceBookingController::class, 'cancel']);
    Route::get('bookings/available-slots', [ServiceBookingController::class, 'getAvailableSlots']);

});
