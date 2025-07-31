<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Livewire\AttendanceProcessingManager;

/*
|--------------------------------------------------------------------------
| Attendance Routes
|--------------------------------------------------------------------------
|
| Routes for attendance management system
|
*/

Route::middleware(['auth'])->group(function () {
    // Attendance Processing Routes
    Route::view('/attendance/processing', 'hr-management.attendances.processing.manage-processing')
        ->name('attendance.processing');
    
    // Regular Attendance Routes  
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');
    
    Route::get('/attendance/create', [AttendanceController::class, 'create'])
        ->name('attendance.create');
    
    Route::post('/attendance', [AttendanceController::class, 'store'])
        ->name('attendance.store');
    
    Route::get('/attendance/{attendance}', [AttendanceController::class, 'show'])
        ->name('attendance.show');
    
    Route::get('/attendance/{attendance}/edit', [AttendanceController::class, 'edit'])
        ->name('attendance.edit');
    
    Route::put('/attendance/{attendance}', [AttendanceController::class, 'update'])
        ->name('attendance.update');
    
    Route::delete('/attendance/{attendance}', [AttendanceController::class, 'destroy'])
        ->name('attendance.destroy');
});