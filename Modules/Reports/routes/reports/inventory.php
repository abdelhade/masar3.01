<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\InventoryReportController;

Route::middleware(['auth'])->group(function () {
    Route::get('/reports/general-inventory-balances', [InventoryReportController::class, 'generalInventoryBalances'])
        ->name('reports.general-inventory-balances');

    Route::get('/reports/general-inventory-balances-by-store', [InventoryReportController::class, 'generalInventoryBalancesByStore'])
        ->name('reports.general-inventory-balances-by-store');

    Route::get('/reports/general-inventory-movements', [InventoryReportController::class, 'generalInventoryMovements'])
        ->name('reports.general-inventory-movements');

    Route::get('/reports/general-inventory-daily-movement-report', [InventoryReportController::class, 'generalInventoryDailyMovementReport'])
        ->name('reports.general-inventory-daily-movement-report');

    Route::get('/reports/general-inventory-stocktaking-report', [InventoryReportController::class, 'generalInventoryStocktakingReport'])
        ->name('reports.general-inventory-stocktaking-report');

    Route::get('/reports/get-items-max&min-quntity', [InventoryReportController::class, 'getItemsMaxMinQuantity'])
        ->name('reports.get-items-max-min-quantity');

    Route::get('/prices/compare-report', [InventoryReportController::class, 'pricesCompareReport'])
        ->name('prices.compare.report');

    Route::get('/discrepancy-report', [InventoryReportController::class, 'inventoryDiscrepancyReport'])
        ->name('reports.inventory-discrepancy-report');

    // Route::get('/reports/items/check-all-quantity-limits', [InventoryReportController::class, 'checkAllItemsQuantityLimits'])
    //     ->name('reports.items.check-all-quantity-limits');

    Route::get('/reports/items/with-quantity-issues', [InventoryReportController::class, 'getItemsWithQuantityIssues'])
        ->name('reports.items.with-quantity-issues');

    Route::get('/reports/items/clear-all-notifications', [InventoryReportController::class, 'clearAllQuantityNotifications'])
        ->name('reports.items.clear-all-notifications');

    Route::get('/reports/items/{itemId}/notification-status', [InventoryReportController::class, 'getItemNotificationStatus'])
        ->name('reports.items.notification-status');
});



