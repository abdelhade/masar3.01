<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ErrandController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\VaribalController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\CovenantController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PosShiftController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CostCenterController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PosVouchersController;
use App\Http\Controllers\EmployeeAuthController;
use App\Http\Controllers\EmployeesJobController;
use App\Http\Controllers\MultiJournalController;
use App\Http\Controllers\MultiVoucherController;
use App\Http\Controllers\VaribalValueController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\TownController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HomeController;
// ✅ تم حذف ItemSearchController - تم استبداله بـ Livewire method
use App\Http\Controllers\WorkPermissionController;
use App\Http\Controllers\InvoiceWorkflowController;
use App\Http\Controllers\ProductionOrderController;
use App\Http\Controllers\MobileAttendanceController;
use App\Http\Controllers\InventoryStartBalanceController;

Route::get('/locale/{locale}', function (string $locale) {
    if (! in_array($locale, ['ar', 'en'], true)) {
        abort(404);
    }
    session(['locale' => $locale]);

    return back();
})->name('locale.switch');

// Admin Dashboard
Route::get('/admin/dashboard', function () {
    return view('admin.main-dashboard');
})->middleware(['auth', 'verified'])->name('admin.dashboard');

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {

    Route::redirect('my-settings', 'my-settings/profile');
    Volt::route('my-settings/profile', 'my-settings.profile')->name('my-settings.profile');
    Volt::route('my-settings/password', 'my-settings.password')->name('my-settings.password');
    Volt::route('my-settings/appearance', 'my-settings.appearance')->name('my-settings.appearance');

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
    Route::resource('clients', ClientController::class)->names('clients');
    Route::post('/clients/toggle-active/{id}', [ClientController::class, 'toggleActive'])
        ->name('clients.toggle-active');

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
            return view('hr-management.leaves.leave-balances.index');
        })->name('leaves.balances.index')->middleware('can:view Leave Balances');
        Route::get('/balances/create', function () {
            return view('hr-management.leaves.leave-balances.create-edit');
        })->name('leaves.balances.create')->middleware('can:create Leave Balances');
        Route::get('/balances/{balanceId}/edit', function () {
            return view('hr-management.leaves.leave-balances.create-edit');
        })->name('leaves.balances.edit')->middleware('can:edit Leave Balances');

        // Leave Requests
        Route::get('/requests', function () {
            return view('hr-management.leaves.leave-requests.index');
        })->name('leaves.requests.index')->middleware('can:view Leave Requests');
        Route::get('/requests/create', function () {
            return view('hr-management.leaves.leave-requests.create');
        })->name('leaves.requests.create')->middleware('can:create Leave Requests');
        Route::get('/requests/{requestId}', function ($requestId) {
            return view('hr-management.leaves.leave-requests.show', ['requestId' => $requestId]);
        })->name('leaves.requests.show')->middleware('can:view Leave Requests');
        Route::get('/requests/{requestId}/edit', function ($requestId) {
            return view('hr-management.leaves.leave-requests.edit', ['requestId' => $requestId]);
        })->name('leaves.requests.edit')->middleware('can:edit Leave Requests');
        // Leave Types
        Route::get('/leave-types', function () {
            return view('hr-management.leaves.leave-types.manage-leave-types');
        })->name('leaves.types.manage')->middleware('can:view Leave Types');
    });

    // 📁 HR Settings
    Route::prefix('hr/settings')->middleware(['auth'])->group(function () {
        Route::get('/', function () {
            return view('hr-management.hr-settings.index');
        })->name('hr.settings.index')->middleware('can:view HR Settings');
        Route::get('/edit', function () {
            $setting = \App\Models\HRSetting::getCompanyDefault();
            if (! $setting) {
                $setting = \App\Models\HRSetting::create([
                    'company_max_leave_percentage' => 7.00,
                ]);
            }

            return view('hr-management.hr-settings.create-edit', ['settingId' => $setting->id]);
        })->name('hr.settings.edit')->middleware('can:edit HR Settings');
    });

    // 📁 Covenants
    Route::resource('covenants', CovenantController::class)->names('covenants')->only('index');
    // 📁 Errands
    Route::resource('errands', ErrandController::class)->names('errands')->only('index');
    // 📁 Work Permissions
    Route::resource('work-permissions', WorkPermissionController::class)->names('work-permissions')->only('index');
    // ############################################################################################################
    // 📁 Projects
    Route::get('projects/statistics', [ProjectController::class, 'statistics'])->name('projects.statistics');
    Route::resource('projects', ProjectController::class)->names('projects')->only('index', 'show', 'create', 'edit');

    // 📁 Items & Units & Prices & Notes

    Route::resource('varibals', VaribalController::class)->names('varibals')->middleware('can:view varibals');
    Route::get('varibalValues/{varibalId?}', [VaribalValueController::class, 'index'])->name('varibalValues.index')->middleware('can:view varibalsValues');
    // Items statistics routes (must be BEFORE resource route to avoid conflicts)
    Route::get('items/statistics', [ItemController::class, 'getStatistics'])->name('items.statistics');
    Route::get('items/statistics/refresh', [ItemController::class, 'refresh'])->name('items.statistics.refresh');
    Route::resource('items', ItemController::class)->names('items')->only('index', 'show', 'create', 'edit');
    Route::get('items/{id}/json', [ItemController::class, 'getItemJson'])->name('items.json');
    Route::get('items/print', [ItemController::class, 'printItems'])->name('items.print');
    Route::get('item-movement/print', [ItemController::class, 'printItemMovement'])->name('item-movement.print');
    Route::resource('units', UnitController::class)->names('units')->only('index');
    Route::resource('prices', PriceController::class)->names('prices')->only('index');
    Route::resource('notes', NoteController::class)->names('notes')->only('index');
    Route::get('notes/{id}', [NoteController::class, 'noteDetails'])->name('notes.noteDetails');
    // 📁 Item Movement
    Route::get('item-movement/{itemId?}/{warehouseId?}', [ItemController::class, 'itemMovementReport'])->name('item-movement');
    // 📁 Item Sales Report
    Route::get('item-sales', [ItemController::class, 'itemSalesReport'])->name('item-sales');
    // 📁 Item Purchase Report
    Route::get('item-purchase', [ItemController::class, 'itemPurchaseReport'])->name('item-purchase');

    // 📁 Account Movement

    Route::get('journals/statistics', [JournalController::class, 'statistics'])->name('journal.statistics');

    Route::resource('journals', JournalController::class)->names('journals');

    Route::resource('cost_centers', CostCenterController::class)->names('cost_centers');

    // 📊 User Monitoring Routes (Must be BEFORE resource route to avoid conflicts)
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/login-history', [App\Http\Controllers\UserMonitoringController::class, 'loginHistory'])->name('login-history');
        Route::get('/active-sessions', [App\Http\Controllers\UserMonitoringController::class, 'activeSessions'])->name('active-sessions');
        Route::post('/terminate-session/{sessionId}', [App\Http\Controllers\UserMonitoringController::class, 'terminateSession'])->name('terminate-session');
        Route::get('/activity-log', [App\Http\Controllers\UserMonitoringController::class, 'activityLog'])->name('activity-log');
    });

    Route::resource('users', UserController::class)->names('users');

    // 📁 Invoice Route
    Route::resource('invoices', InvoiceController::class)->names('invoices');

    // list request orders (طلب احتياج)
    Route::get('/invoices/requests', [InvoiceWorkflowController::class, 'index'])->name('invoices.requests.index');
    Route::get('/invoices/track/search', [InvoiceWorkflowController::class, 'index'])->name('invoices.track.search');
    Route::get('/invoices/track/{id}', [InvoiceWorkflowController::class, 'show'])->name('invoices.track');
    Route::post('/invoices/confirm/{id}', [InvoiceWorkflowController::class, 'confirm'])->name('invoices.confirm');

    // 📁 Invoice Print Route
    Route::get('/invoice/print/{operation_id}', [InvoiceController::class, 'print'])->name('invoice.print');
    // 📁 Invoice View Route
    Route::get('invoice/view/{operationId}', [InvoiceController::class, 'view'])->name('invoice.view');
    // 📁 Transfer Route

    Route::get('/discounts/general-statistics', [DiscountController::class, 'generalStatistics'])->name('discounts.general-statistics');
    Route::resource('discounts', DiscountController::class)->names('discounts');

    Route::get('/vouchers/statistics', [VoucherController::class, 'statistics'])->name('vouchers.statistics');
    Route::resource('vouchers', VoucherController::class)->names('vouchers');

    Route::get('transfers/statistics', [TransferController::class, 'statistics'])->name('transfers.statistics');
    Route::resource('transfers', TransferController::class)->names('transfers');

    Route::get('multi-vouchers/statistics', [MultiVoucherController::class, 'statistics'])->name('multi-vouchers.statistics');
    Route::get('multi-vouchers/{multivoucher}/duplicate', [MultiVoucherController::class, 'duplicate'])->name('multi-vouchers.duplicate');
    Route::resource('multi-vouchers', MultiVoucherController::class)->names('multi-vouchers');

    Route::resource('multi-journals', MultiJournalController::class)->names('multi-journals');

    Route::resource('production-orders', ProductionOrderController::class)->names('production-orders');
    Route::resource('rentals', RentalController::class)->names('rentals');
    Route::resource('inventory-balance', InventoryStartBalanceController::class)->names('inventory-balance');
    Route::get('/create', [InventoryStartBalanceController::class, 'create'])->name('inventory-start-balance.create');
    Route::post('/store', [InventoryStartBalanceController::class, 'store'])->name('inventory-start-balance.store');
    // Redirect GET requests to the create page
    Route::get('/update-opening-balance', function () {
        return redirect()->route('inventory-balance.index');
    });
    Route::post('/update-opening-balance', [InventoryStartBalanceController::class, 'updateOpeningBalance'])->name('inventory-start-balance.update-opening-balance');

    Route::get('home', [HomeController::class, 'index'])->name('home.index');

    Route::resource('pos-shifts', PosShiftController::class)->names('pos-shifts');
    Route::resource('pos-vouchers', PosVouchersController::class)->names('pos-vouchers');
    Route::get('pos-vouchers/get-items-by-note-detail', [PosVouchersController::class, 'getItemsByNoteDetail'])->name('pos-vouchers.get-items-by-note-detail');
    Route::get('pos-shifts/{shift}/close', [PosShiftController::class, 'close'])->name('pos-shifts.close');
    Route::post('pos-shifts/{shift}/close', [PosShiftController::class, 'closeConfirm'])->name('pos-shifts.close.confirm');

    // invoice Statistics Routes
    Route::get('/sales/statistics', [InvoiceController::class, 'salesStatistics'])->name('sales.statistics');
    Route::get('/purchases/statistics', [InvoiceController::class, 'purchasesStatistics'])->name('purchases.statistics');
    Route::get('/inventory/statistics', [InvoiceController::class, 'inventoryStatistics'])->name('inventory.statistics');

    Route::get('/items/statistics', [ItemController::class, 'getStatistics'])->name('items.statistics');
    Route::get('/items/statistics/refresh', [ItemController::class, 'refresh'])->name('items.statistics.refresh');

    require __DIR__ . '/modules/magicals.php';
    require __DIR__ . '/modules/cheques.php';
    require __DIR__ . '/modules/invoice-reports.php';
    require __DIR__ . '/modules/attendance.php';
    require __DIR__ . '/modules/reports.php';

});

// ===== Employee Mobile Routes (خارج auth middleware) =====
// Employee Login Routes
Route::get('/mobile/employee-login', function () {
    return view('mobile.employee-login');
})->name('mobile.employee-login');

// Employee Auth API Routes
Route::post('/api/employee/login', [EmployeeAuthController::class, 'login'])->name('api.employee.login');
Route::post('/api/employee/logout', [EmployeeAuthController::class, 'logout'])->name('api.employee.logout');
Route::get('/api/employee/check-auth', [EmployeeAuthController::class, 'checkAuth'])->name('api.employee.check-auth');
Route::get('/api/employee/current', [EmployeeAuthController::class, 'getCurrentEmployee'])->name('api.employee.current');

// Mobile Attendance Routes
Route::get('/mobile/attendance', function () {
    return view('mobile.attendance');
})->middleware(['employee.auth'])->name('mobile.attendance');

// Mobile Attendance API Routes
Route::post('/api/attendance/record', [MobileAttendanceController::class, 'recordAttendance'])->middleware(['employee.auth'])->name('api.attendance.record');
Route::get('/api/attendance/last', [MobileAttendanceController::class, 'getLastAttendance'])->middleware(['employee.auth'])->name('api.attendance.last');
Route::get('/api/attendance/history', [MobileAttendanceController::class, 'getAttendanceHistory'])->middleware(['employee.auth'])->name('api.attendance.history');
Route::get('/api/attendance/stats', [MobileAttendanceController::class, 'getAttendanceStats'])->middleware(['employee.auth'])->name('api.attendance.stats');
Route::get('/api/attendance/can-record', [MobileAttendanceController::class, 'canRecordAttendance'])->middleware(['employee.auth'])->name('api.attendance.can-record');

// ✅ تم نقل البحث إلى Livewire method (searchItems) - أسرع وأبسط

require __DIR__ . '/auth.php';
