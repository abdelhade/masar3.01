<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\purchaseReportController;
use Modules\Reports\Http\Controllers\PurchasingDashboardController;

Route::middleware(['auth'])->group(function () {
    Route::get('/reports/purchasing-dashboard', [PurchasingDashboardController::class, 'index'])->name('reports.purchasing.dashboard');
    Route::get('/reports/purchasing-delayed-orders', [PurchasingDashboardController::class, 'delayedOrders'])->name('reports.purchasing.delayed-orders');

    Route::get('/reports/general-purchases-total', [purchaseReportController::class, 'generalPurchasesTotalReport'])
        ->name('reports.general-purchases-total');

    Route::get('/reports/general-purchases-items-report', [purchaseReportController::class, 'generalPurchasesItemsReport'])
        ->name('reports.general-purchases-items-report');

    Route::get('/reports/general-purchases-report', [purchaseReportController::class, 'generalPurchasesReport'])
        ->name('reports.general-purchases-report');

    Route::get('/reports/general-purchases-daily-report', [purchaseReportController::class, 'generalPurchasesDailyReport'])
        ->name('reports.general-purchases-daily-report');

    // تقرير المشتريات أصناف
    Route::get('/reports/purchases/items', [purchaseReportController::class, 'generalPurchasesItemsReport'])
        ->name('reports.purchases.items');

    // تقرير المشتريات إجماليات
    Route::get('/reports/purchases/total', [purchaseReportController::class, 'generalPurchasesTotalReport'])
        ->name('reports.purchases.total');

    // تقرير مشتريات صنف
    Route::get('/reports/item-purchase', [purchaseReportController::class, 'manageItemPurchaseReport'])
        ->name('reports.item-purchase');
});
