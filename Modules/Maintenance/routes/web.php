<?php

use Illuminate\Support\Facades\Route;
use Modules\Maintenance\Http\Controllers\PeriodicMaintenanceController;
use Modules\Maintenance\Http\Controllers\MaintenanceStatisticsController;
use Modules\Maintenance\Http\Controllers\{MaintenanceController, ServiceTypeController};

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('service-types', ServiceTypeController::class)->names('service.types');
    Route::resource('maintenances', MaintenanceController::class)->names('maintenances');

    Route::prefix('maintenance')->name('maintenance.')->group(function () {

        Route::get('/dashboard', [MaintenanceStatisticsController::class, 'index'])
            ->name('dashboard.index');
    });

    Route::prefix('periodic-maintenances')->name('periodic.maintenances.')->group(function () {
        Route::get('/', [PeriodicMaintenanceController::class, 'index'])->name('index');
        Route::get('/create', [PeriodicMaintenanceController::class, 'create'])->name('create');
        Route::post('/', [PeriodicMaintenanceController::class, 'store'])->name('store');
        Route::get('/{periodicMaintenance}/edit', [PeriodicMaintenanceController::class, 'edit'])->name('edit');
        Route::put('/{periodicMaintenance}', [PeriodicMaintenanceController::class, 'update'])->name('update');
        Route::delete('/{periodicMaintenance}', [PeriodicMaintenanceController::class, 'destroy'])->name('destroy');
        Route::patch('/{periodicMaintenance}/toggle', [PeriodicMaintenanceController::class, 'toggleActive'])->name('toggle');
        Route::get('/{schedule}/create-maintenance', [PeriodicMaintenanceController::class, 'createMaintenanceFromSchedule'])->name('create-maintenance');
    });

    Route::middleware(['auth', 'permission:edit Periodic Maintenance'])->group(function () {
        Route::post('periodic-maintenances/{periodicMaintenance}/toggle-active', [\Modules\Maintenance\Http\Controllers\PeriodicMaintenanceController::class, 'toggleActive'])
            ->name('periodic.maintenances.toggleActive');
    });
});
