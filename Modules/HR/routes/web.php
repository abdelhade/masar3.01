<?php

use Illuminate\Support\Facades\Route;
use Modules\HR\Http\Controllers\HRController;
use Modules\HR\Http\Controllers\KpiController;
use Modules\HR\Http\Controllers\CityController;
use Modules\HR\Http\Controllers\ErrandController;
use Modules\HR\Http\Controllers\CountryController;
use Modules\HR\Http\Controllers\CovenantController;
use Modules\HR\Http\Controllers\EmployeeController;
use Modules\HR\Http\Controllers\AttendanceController;
use Modules\HR\Http\Controllers\DepartmentController;
use Modules\HR\Http\Controllers\EmployeeAuthController;
use Modules\HR\Http\Controllers\EmployeesJobController;
use Modules\HR\Http\Controllers\StateController;
use Modules\HR\Http\Controllers\ShiftController;
use Modules\HR\Http\Controllers\WorkPermissionController;
use Modules\HR\Http\Controllers\MobileAttendanceController;
use Modules\HR\Http\Controllers\TownController;


    // 📁 HR Management
    // 📁 Departments
    Route::resource('departments', DepartmentController::class)->names('departments')->only('index');
    // 📁 Jobs
    Route::resource('jobs', EmployeesJobController::class)->names('jobs')->only('index');
    // 📁 Addresses
    Route::resource('countries', CountryController::class)->names('countries')->only('index');
    Route::resource('states', StateController::class)->names('states')->only('index');
    Route::resource('cities', CityController::class)->names('cities')->only('index');
    Route::resource('towns', TownController::class)->names('towns')->only('index');
    // 📁 Shifts
    Route::resource('shifts', ShiftController::class)->names('shifts')->only('index');
    // 📁 Employees
    Route::resource('employees', EmployeeController::class)->names('employees');
    // 📁 KPIs
    Route::resource('kpis', KpiController::class)->names('kpis')->only('index');
    Route::get('kpis/employee-evaluation', [KpiController::class, 'employeeEvaluation'])->name('kpis.employeeEvaluation');
    // Note: Contract Types moved to Recruitment module
    // 📁 Attendances
    Route::resource('attendances', AttendanceController::class)->names('attendances')->only('index');
    // Note: Contracts and CVs routes moved to Recruitment module
    // 📁 Leave Management
    Route::prefix('hr/leaves')->middleware(['auth'])->group(function () {
        // Leave Balances
        Route::get('/balances', function () {
            return view('hr::hr-management.leaves.leave-balances.index');
        })->name('hr.leaves.balances.index')->middleware('can:view Leave Balances');
        Route::get('/balances/create', function () {
            return view('hr::hr-management.leaves.leave-balances.create-edit');
        })->name('hr.leaves.balances.create')->middleware('can:create Leave Balances');
        Route::get('/balances/{balanceId}/edit', function () {
            return view('hr::hr-management.leaves.leave-balances.create-edit');
        })->name('hr.leaves.balances.edit')->middleware('can:edit Leave Balances');

        // Leave Requests
        Route::get('/requests', function () {
            return view('hr::hr-management.leaves.leave-requests.index');
        })->name('hr.leaves.requests.index')->middleware('can:view Leave Requests');
        Route::get('/requests/create', function () {
            return view('hr::hr-management.leaves.leave-requests.create');
        })->name('hr.leaves.requests.create')->middleware('can:create Leave Requests');
        Route::get('/requests/{requestId}', function ($requestId) {
            return view('hr::hr-management.leaves.leave-requests.show', ['requestId' => $requestId]);
        })->name('hr.leaves.requests.show')->middleware('can:view Leave Requests');
        Route::get('/requests/{requestId}/edit', function ($requestId) {
            return view('hr::hr-management.leaves.leave-requests.edit', ['requestId' => $requestId]);
        })->name('hr.leaves.requests.edit')->middleware('can:edit Leave Requests');
        // Leave Types
        Route::get('/leave-types', function () {
            return view('hr::hr-management.leaves.leave-types.manage-leave-types');
        })->name('hr.leaves.types.manage')->middleware('can:view Leave Types');
    });

    // 📁 HR Settings
    Route::prefix('hr/settings')->middleware(['auth'])->group(function () {
        Route::get('/', function () {
            return view('hr::hr-management.hr-settings.index');
        })->name('hr.settings.index')->middleware('can:view HR Settings');
        Route::get('/edit', function () {
            $setting = \Modules\HR\Models\HRSetting::getCompanyDefault();
            if (! $setting) {
                $setting = \Modules\HR\Models\HRSetting::create([
                    'company_max_leave_percentage' => 7.00,
                ]);
            }

            return view('hr::hr-management.hr-settings.create-edit', ['settingId' => $setting->id]);
        })->name('hr.settings.edit')->middleware('can:edit HR Settings');
    });

    // 📁 Covenants
    Route::resource('covenants', CovenantController::class)->names('covenants')->only('index');
    // 📁 Errands
    Route::resource('errands', ErrandController::class)->names('errands')->only('index');
    // 📁 Work Permissions
    Route::resource('work-permissions', WorkPermissionController::class)->names('work-permissions')->only('index');


    require __DIR__ . '/attendance.php';


    
// ===== Employee Mobile Routes (خارج auth middleware) =====
// Employee Login Routes
Route::get('/mobile/employee-login', function () {
    return view('hr::mobile.employee-login');
})->name('mobile.employee-login')->middleware(['auth', 'can:view Mobile-fingerprint']);

// Employee Auth API Routes
Route::post('/api/employee/login', [EmployeeAuthController::class, 'login'])->name('api.employee.login');
Route::post('/api/employee/logout', [EmployeeAuthController::class, 'logout'])->name('api.employee.logout');
Route::get('/api/employee/check-auth', [EmployeeAuthController::class, 'checkAuth'])->name('api.employee.check-auth');
Route::get('/api/employee/current', [EmployeeAuthController::class, 'getCurrentEmployee'])->name('api.employee.current');

// Mobile Attendance Routes
Route::get('/mobile/attendance', function () {
    return view('hr::mobile.attendance');
})->middleware(['employee.auth'])->name('mobile.attendance');

// Mobile Attendance API Routes
Route::post('/api/attendance/record', [MobileAttendanceController::class, 'recordAttendance'])->middleware(['employee.auth'])->name('api.attendance.record');
Route::get('/api/attendance/last', [MobileAttendanceController::class, 'getLastAttendance'])->middleware(['employee.auth'])->name('api.attendance.last');
Route::get('/api/attendance/history', [MobileAttendanceController::class, 'getAttendanceHistory'])->middleware(['employee.auth'])->name('api.attendance.history');
Route::get('/api/attendance/stats', [MobileAttendanceController::class, 'getAttendanceStats'])->middleware(['employee.auth'])->name('api.attendance.stats');
Route::get('/api/attendance/can-record', [MobileAttendanceController::class, 'canRecordAttendance'])->middleware(['employee.auth'])->name('api.attendance.can-record');

