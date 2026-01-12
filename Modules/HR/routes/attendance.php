<?php

use Modules\HR\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

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
    Route::view('/attendance/processing', 'hr::hr-management.attendances.processing.manage-processing')
        ->name('hr.attendance.processing');
    
    // Flexible Salary Processing Routes
    Route::prefix('attendances/flexible-salary')->name('hr.flexible-salary.processing.')->group(function () {
        Route::view('/', 'hr::hr-management.attendances.flexible-salary-processing.index')
            ->name('index');
        Route::view('/create', 'hr::hr-management.attendances.flexible-salary-processing.create')
            ->name('create');
        Route::get('/{processing}/edit', function ($processing) {
            return view('hr::hr-management.attendances.flexible-salary-processing.edit', [
                'processing' => \Modules\HR\Models\FlexibleSalaryProcessing::with('employee')->findOrFail($processing),
            ]);
        })->name('edit');
    });

    // Employee Advances Routes
    Route::view('/employee-advances', 'hr::hr-management.employee-advances.index')
        ->name('hr.employee-advances.index');

    // Employee Deductions & Rewards Routes
    Route::view('/employee-deductions-rewards', 'hr::hr-management.employee-deductions-rewards.index')
        ->name('hr.employee-deductions-rewards.index');
    
    // Regular Attendance Routes  
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('hr.attendance.index');

    // Attendance Reports For Projects
    Route::view('/attendance/reports/project-attendance', 'hr::hr-management.attendances.reports.project-attendance-report-container')
        ->name('hr.attendance.reports.project');
    
    Route::get('/attendance/create', [AttendanceController::class, 'create'])
        ->name('hr.attendance.create');
    
    Route::post('/attendance', [AttendanceController::class, 'store'])
        ->name('hr.attendance.store');
    
    Route::get('/attendance/{attendance}', [AttendanceController::class, 'show'])
        ->name('hr.attendance.show');
    
    Route::get('/attendance/{attendance}/edit', [AttendanceController::class, 'edit'])
        ->name('hr.attendance.edit');
    
    Route::put('/attendance/{attendance}', [AttendanceController::class, 'update'])
        ->name('hr.attendance.update');
    
    Route::delete('/attendance/{attendance}', [AttendanceController::class, 'destroy'])
        ->name('hr.attendance.destroy');
});
