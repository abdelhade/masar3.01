<?php

use Illuminate\Support\Facades\Route;
use Modules\Services\Http\Controllers\ServiceController;
use Modules\Services\Http\Controllers\ServiceBookingController;
use Modules\Services\Http\Controllers\ServiceTypeController;
use Modules\Services\Http\Controllers\ServiceUnitController;

/*
|--------------------------------------------------------------------------
| Services Module Routes
|--------------------------------------------------------------------------
|
| نظام إدارة الخدمات - المسارات الخاصة بوحدة Services
| جميع المسارات محمية بـ middleware للمصادقة والصلاحيات
|
*/

Route::middleware(['auth', 'verified'])->prefix('services')->name('services.')->group(function () {
    
    // إدارة الخدمات
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', [ServiceController::class, 'index'])->name('index')->middleware('can:view Services');
        Route::get('/create', [ServiceController::class, 'create'])->name('create')->middleware('can:create Services');
        Route::post('/', [ServiceController::class, 'store'])->name('store')->middleware('can:create Services');
        Route::get('/{service}', [ServiceController::class, 'show'])->name('show')->middleware('can:view Services');
        Route::get('/{service}/edit', [ServiceController::class, 'edit'])->name('edit')->middleware('can:edit Services');
        Route::put('/{service}', [ServiceController::class, 'update'])->name('update')->middleware('can:edit Services');
        Route::delete('/{service}', [ServiceController::class, 'destroy'])->name('destroy')->middleware('can:delete Services');
        Route::patch('/{service}/toggle-status', [ServiceController::class, 'toggleStatus'])->name('toggle-status')->middleware('can:toggle Services');
    });

    // إدارة حجوزات الخدمات
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [ServiceBookingController::class, 'index'])->name('index')->middleware('can:view Service Bookings');
        Route::get('/create', [ServiceBookingController::class, 'create'])->name('create')->middleware('can:create Service Bookings');
        Route::post('/', [ServiceBookingController::class, 'store'])->name('store')->middleware('can:create Service Bookings');
        Route::get('/{booking}', [ServiceBookingController::class, 'show'])->name('show')->middleware('can:view Service Bookings');
        Route::get('/{booking}/edit', [ServiceBookingController::class, 'edit'])->name('edit')->middleware('can:edit Service Bookings');
        Route::put('/{booking}', [ServiceBookingController::class, 'update'])->name('update')->middleware('can:edit Service Bookings');
        Route::delete('/{booking}', [ServiceBookingController::class, 'destroy'])->name('destroy')->middleware('can:delete Service Bookings');
        Route::patch('/{booking}/complete', [ServiceBookingController::class, 'complete'])->name('complete')->middleware('can:complete Service Bookings');
        Route::patch('/{booking}/cancel', [ServiceBookingController::class, 'cancel'])->name('cancel')->middleware('can:cancel Service Bookings');
        Route::get('/available-slots', [ServiceBookingController::class, 'getAvailableSlots'])->name('available-slots')->middleware('can:view Service Available Slots');
    });

    // إدارة أنواع الخدمات
    Route::prefix('service-types')->name('service-types.')->group(function () {
        Route::get('/', [ServiceTypeController::class, 'index'])->name('index')->middleware('can:view Service Types');
        Route::get('/create', [ServiceTypeController::class, 'create'])->name('create')->middleware('can:create Service Types');
        Route::post('/', [ServiceTypeController::class, 'store'])->name('store')->middleware('can:create Service Types');
        Route::get('/{service_type}', [ServiceTypeController::class, 'show'])->name('show')->middleware('can:view Service Types');
        Route::get('/{service_type}/edit', [ServiceTypeController::class, 'edit'])->name('edit')->middleware('can:edit Service Types');
        Route::put('/{service_type}', [ServiceTypeController::class, 'update'])->name('update')->middleware('can:edit Service Types');
        Route::delete('/{service_type}', [ServiceTypeController::class, 'destroy'])->name('destroy')->middleware('can:delete Service Types');
    });

    // إدارة وحدات الخدمات
    Route::prefix('service-units')->name('service-units.')->group(function () {
        Route::get('/', [ServiceUnitController::class, 'index'])->name('index')->middleware('can:view Service Units');
        Route::get('/create', [ServiceUnitController::class, 'create'])->name('create')->middleware('can:create Service Units');
        Route::post('/', [ServiceUnitController::class, 'store'])->name('store')->middleware('can:create Service Units');
        Route::get('/{service_unit}', [ServiceUnitController::class, 'show'])->name('show')->middleware('can:view Service Units');
        Route::get('/{service_unit}/edit', [ServiceUnitController::class, 'edit'])->name('edit')->middleware('can:edit Service Units');
        Route::put('/{service_unit}', [ServiceUnitController::class, 'update'])->name('update')->middleware('can:edit Service Units');
        Route::delete('/{service_unit}', [ServiceUnitController::class, 'destroy'])->name('destroy')->middleware('can:delete Service Units');
    });

    // إدارة فواتير الخدمات
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', function () {
            return view('services::invoices.index');
        })->name('index')->middleware('can:view Service Invoices');
        Route::get('/create', function () {
            return view('services::invoices.create');
        })->name('create')->middleware('can:create Service Invoices');
        Route::get('/{invoice}', function ($invoice) {
            return view('services::invoices.show', compact('invoice'));
        })->name('show');
        Route::get('/{invoice}/edit', function ($invoice) {
            return view('services::invoices.edit', compact('invoice'));
        })->name('edit');
    });

    // تكامل مع نظام نقاط البيع
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/services', [\Modules\Services\Http\Controllers\ServicePOSController::class, 'getServicesForPOS'])->name('services');
        Route::get('/services/{id}', [\Modules\Services\Http\Controllers\ServicePOSController::class, 'getServiceForPOS'])->name('service');
        Route::post('/bookings', [\Modules\Services\Http\Controllers\ServicePOSController::class, 'createBookingFromPOS'])->name('create-booking');
        Route::get('/available-slots', [\Modules\Services\Http\Controllers\ServicePOSController::class, 'getAvailableSlots'])->name('available-slots');
    });

});
