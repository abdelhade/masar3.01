<?php

use App\Http\Controllers\AccHeadController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ContractTypeController;
use App\Http\Controllers\CostCenterController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeesJobController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventoryStartBalanceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\JournalSummeryController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\ManufacturingController;
use App\Http\Controllers\MultiJournalController;
use App\Http\Controllers\MultiVoucherController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PosShiftController;
use App\Http\Controllers\PosVouchersController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\TownController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\ProductionOrderController;

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/locale/{locale}', function (string $locale) {
    if (! in_array($locale, ['ar', 'en'], true)) {
        abort(404);
    }
    session(['locale' => $locale]);

    return back();
})->name('locale.switch');

// test for dashboard
Route::get('/admin/dashboard', function () {
    return view('admin.index');
})->middleware(['auth', 'verified'])->name('admin.dashboard');

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::view('dashboard', 'admin.index')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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
    Route::resource('employees', EmployeeController::class)->names('employees')->only('index');
    // 📁 KPIs
    Route::resource('kpis', KpiController::class)->names('kpis')->only('index');
    Route::get('kpis/employee-evaluation', [KpiController::class, 'employeeEvaluation'])->name('kpis.employeeEvaluation');
    // 📁 Contracts
    // 📁 Contract Types
    Route::resource('contract-types', ContractTypeController::class)->names('contract-types')->only('index');
    // 📁 Contracts
    Route::resource('contracts', ContractController::class)->names('contracts')->only('index');
    // 📁 Attendances
    Route::resource('attendances', AttendanceController::class)->names('attendances')->only('index');
    // 📁 CVs
    Route::resource('cvs', CvController::class)->names('cvs')->only('index');
    // 📁 Leave Management
    Route::prefix('hr/leaves')->middleware(['auth'])->group(function () {
        // Leave Balances
        Route::get('/balances', function () {
            return view('hr-management.leaves.leave-balances.index');
        })->name('leaves.balances.index');
        Route::get('/balances/create', function () {
            return view('hr-management.leaves.leave-balances.create-edit');
        })->name('leaves.balances.create');
        Route::get('/balances/{balanceId}/edit', function () {
            return view('hr-management.leaves.leave-balances.create-edit');
        })->name('leaves.balances.edit');

        // Leave Requests
        Route::get('/requests', function () {
            return view('hr-management.leaves.leave-requests.index');
        })->name('leaves.requests.index');
        Route::get('/requests/create', function () {
            return view('hr-management.leaves.leave-requests.create');
        })->name('leaves.requests.create');
        Route::get('/requests/{requestId}', function ($requestId) {
            return view('hr-management.leaves.leave-requests.show', ['requestId' => $requestId]);
        })->name('leaves.requests.show');
        Route::get('/requests/{requestId}/edit', function ($requestId) {
            return view('hr-management.leaves.leave-requests.edit', ['requestId' => $requestId]);
        })->name('leaves.requests.edit');
    });
    // ############################################################################################################
    // 📁 Projects
    Route::resource('projects', ProjectController::class)->names('projects')->only('index', 'show', 'create', 'edit');

    // 📁 Items & Units & Prices & Notes
    Route::resource('items', ItemController::class)->names('items')->only('index', 'create', 'edit');
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
    Route::get('account-movement/{accountId?}', [AccHeadController::class, 'accountMovementReport'])->name('account-movement');

    Route::resource('journals', JournalController::class)->names('journals');

    Route::resource('cost_centers', CostCenterController::class)->names('cost_centers');
    Route::resource('users', UserController::class)->names('users');
    // 📁 Invoice Route
    Route::resource('invoices', InvoiceController::class)->names('invoices');
    // 📁 Invoice Print Route
    Route::get('/invoice/print/{operation_id}', [InvoiceController::class, 'print'])->name('invoice.print');
    // 📁 Invoice View Route
    Route::get('invoice/view/{operationId}', [InvoiceController::class, 'view'])->name('invoice.view');
    // 📁 Transfer Route
    Route::resource('transfers', TransferController::class)->names('transfers');
    Route::resource('discounts', DiscountController::class)->names('discounts');

    // abdelhade
    Route::get('journal-summery', [JournalSummeryController::class, 'index'])->name('journal-summery');
    Route::resource('cost_centers', CostCenterController::class);
    Route::resource('vouchers', VoucherController::class)->names('vouchers');
    Route::resource('transfers', TransferController::class)->names('transfers');
    Route::resource('accounts', AccHeadController::class)->except(['show'])->names('accounts');
    // 📁 Account Movement
    Route::get('account-movement/{accountId?}', [AccHeadController::class, 'accountMovementReport'])->name('account-movement');
    // 📁 Start Balance
    Route::get('accounts/start-balance', [AccHeadController::class, 'startBalance'])->name('accounts.startBalance');
    // 📁 Balance Sheet
    Route::get('accounts/balance-sheet', [AccHeadController::class, 'balanceSheet'])->name('accounts.balanceSheet');
    // 📁 Start Balance
    Route::get('accounts/start-balance', [AccHeadController::class, 'startBalance'])->name('accounts.startBalance');
    Route::resource('multi-vouchers', MultiVoucherController::class)->names('multi-vouchers');
    Route::resource('multi-journals', MultiJournalController::class)->names('multi-journals');

    Route::resource('manufacturing', ManufacturingController::class)->names('manufacturing');
    Route::resource('production-orders', ProductionOrderController::class)->names('production-orders');
    Route::resource('rentals', RentalController::class)->names('rentals');
    Route::resource('inventory-balance', InventoryStartBalanceController::class)->names('inventory-balance');
    Route::get('/create', [InventoryStartBalanceController::class, 'create'])->name('inventory-start-balance.create');
    Route::post('/store', [InventoryStartBalanceController::class, 'store'])->name('inventory-start-balance.store');
    Route::post('/update-opening-balance', [InventoryStartBalanceController::class, 'updateOpeningBalance'])->name('inventory-start-balance.update-opening-balance');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/overall', [ReportController::class, 'overall'])->name('reports.overall');
    Route::get('home', [HomeController::class, 'index'])->name('home.index');
    Route::resource('pos-shifts', PosShiftController::class)->names('pos-shifts');
    Route::resource('pos-vouchers', PosVouchersController::class)->names('pos-vouchers');
    Route::get('pos-vouchers/get-items-by-note-detail', [PosVouchersController::class, 'getItemsByNoteDetail'])->name('pos-vouchers.get-items-by-note-detail');
    Route::get('pos-shifts/{shift}/close', [PosShiftController::class, 'close'])->name('pos-shifts.close');
    Route::post('pos-shifts/{shift}/close', [PosShiftController::class, 'closeConfirm'])->name('pos-shifts.close.confirm');
    require __DIR__.'/modules/magicals.php';
    require __DIR__.'/modules/cheques.php';
    require __DIR__.'/modules/invoice-reports.php';
    require __DIR__.'/modules/attendance.php';
    require __DIR__.'/modules/reports.php';
});
require __DIR__.'/auth.php';
