<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\VaribalController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\PosShiftController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\CostCenterController;
use App\Http\Controllers\PosVouchersController;
use App\Http\Controllers\MultiJournalController;
use App\Http\Controllers\MultiVoucherController;
use App\Http\Controllers\VaribalValueController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductionOrderController;
use App\Http\Controllers\InventoryStartBalanceController;

Route::get('/locale/{locale}', function (string $locale) {
    if (! in_array($locale, ['ar', 'en', 'ur', 'hi'], true)) {
        abort(404);
    }
    session(['locale' => $locale]);

    return back();
})->name('locale.switch');

// Admin Dashboard
use App\Http\Controllers\DashboardController;
Route::get('/admin/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('admin.dashboard');

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


    // ############################################################################################################
    // 📁 Projects
    Route::get('projects/statistics', [ProjectController::class, 'statistics'])->name('projects.statistics');
    // Route::resource('projects', ProjectController::class)->names('projects')->only('index', 'show', 'create', 'edit');

    // 📁 Items & Units & Prices & Notes

    Route::resource('varibals', VaribalController::class)->names('varibals')->middleware('can:view varibals');
    Route::get('varibalValues/{varibalId?}', [VaribalValueController::class, 'index'])->name('varibalValues.index')->middleware('can:view varibalsValues');
    // Items statistics routes (must be BEFORE resource route to avoid conflicts)
    Route::get('items/statistics', [ItemController::class, 'getStatistics'])->name('items.statistics');
    Route::get('items/statistics/refresh', [ItemController::class, 'refresh'])->name('items.statistics.refresh');
    Route::get('items/print', [ItemController::class, 'printItems'])->name('items.print');
    Route::get('items/{id}/json', [ItemController::class, 'getItemJson'])->name('items.json');
    Route::resource('items', ItemController::class)->names('items')->only('index', 'show', 'create', 'edit');
    Route::get('item-movement/print', [ItemController::class, 'printItemMovement'])->name('item-movement.print');
    Route::resource('units', UnitController::class)->names('units')->only('index');
    Route::resource('prices', PriceController::class)->names('prices')->only('index');
    Route::resource('notes', NoteController::class)->names('notes')->only('index');
    Route::get('notes/{id}', [NoteController::class, 'noteDetails'])->name('notes.noteDetails');
    // 📁 Item Movement
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


    Route::get('/items/statistics', [ItemController::class, 'getStatistics'])->name('items.statistics');
    Route::get('/items/statistics/refresh', [ItemController::class, 'refresh'])->name('items.statistics.refresh');

    require __DIR__ . '/modules/magicals.php';
    require __DIR__ . '/modules/cheques.php';
    require __DIR__ . '/modules/invoice-reports.php';
    require __DIR__ . '/modules/reports.php';

});

require __DIR__ . '/auth.php';
