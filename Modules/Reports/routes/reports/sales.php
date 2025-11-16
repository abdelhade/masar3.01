<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\salesReportController;

Route::middleware(['auth'])->group(function () {
    Route::get('reports/general-sales-items', [salesReportController::class, 'generalSalesItemsReport'])
        ->name('reports.general.sales.items');

    Route::get('/reports/general-sales-total', [salesReportController::class, 'generalSalesTotalReport'])
        ->name('reports.general-sales-total-report');

    Route::get('/reports/general-sales-report', [salesReportController::class, 'generalSalesReport'])
        ->name('reports.general-sales-report');

    Route::get('/reports/general-sales-report-by-address', [salesReportController::class, 'salesReportByAddress'])
        ->name('reports.general-sales-report-by-address');

    Route::get('/reports/sales/by-representative', [salesReportController::class, 'salesByRepresentativeReport'])
        ->name('reports.sales.representative');

    // تقرير المبيعات أصناف
    Route::get('/reports/sales/items', [salesReportController::class, 'generalSalesItemsReport'])
        ->name('reports.sales.items');

    // تقرير المبيعات إجماليات
    Route::get('/reports/sales/total', [salesReportController::class, 'generalSalesTotalReport'])
        ->name('reports.sales.total');

    // تقرير مبيعات صنف
    Route::get('/reports/item-sales', [salesReportController::class, 'manageItemSales'])
        ->name('reports.item-sales');

    // تقرير المبيعات اليومية العام
    Route::get('/reports/general-sales-daily-report', [salesReportController::class, 'generalSalesDailyReport'])
        ->name('reports.general-sales-daily-report');
});
