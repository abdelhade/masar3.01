<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use Modules\Reports\Http\Controllers\salesReportController;
use Modules\Reports\Http\Controllers\purchaseReportController;

/**
 * التقارير العامة
 * Note: Most routes have been moved to Modules/Reports
 * These routes are kept for backward compatibility reference
 */

// محلل العمل اليومي - MOVED TO GeneralReportController
// Route::get('/reports/daily-activity-analyzer', [ReportController::class, 'dailyActivityAnalyzer'])->name('reports.daily-activity-analyzer');

// ميزان الحسابات
// Route::get('/reports/general-account-balances', [ReportController::class, 'generalAccountBalances'])->name('reports.general-account-balances');

// قائمة الاصناف مع الارصدة كل المخازن - MOVED TO InventoryReportController
// Route::get('/reports/general-inventory-balances', [ReportController::class, 'generalInventoryBalances'])->name('reports.general-inventory-balances');

// قائمة الاصناف مع الارصدة مخزن معين - MOVED TO InventoryReportController
// Route::get('/reports/general-inventory-balances-by-store', [ReportController::class, 'generalInventoryBalancesByStore'])->name('reports.general-inventory-balances-by-store');

// قائمة الحسابات مع الارصدة - MOVED TO GeneralReportController
// Route::get('/reports/general-account-balances-by-store', [ReportController::class, 'generalAccountBalancesByStore'])->name('reports.general-account-balances-by-store');

// حركة الصنف - MOVED TO InventoryReportController
// Route::get('/reports/general-inventory-movements', [ReportController::class, 'generalInventoryMovements'])->name('reports.general-inventory-movements');

// تقارير المبيعات - MOVED TO salesReportController
// Route::get('/reports/general-sales-report', [ReportController::class, 'generalSalesReport'])->name('reports.general-sales-report');

// تقرير المبيعات اليومية
// Route::get('/reports/general-sales-daily-report', [ReportController::class, 'generalSalesDailyReport'])->name('reports.general-sales-daily-report');

// تقريب المبيعات بالعنوان - MOVED TO salesReportController
// Route::get('/reports/general-sales-report-by-address', [ReportController::class, 'salesReportByAddress'])->name('reports.general-sales-report-by-address');

// تقارير المشتريات - MOVED TO purchaseReportController
// Route::get('/reports/general-purchases-report', [ReportController::class, 'generalPurchasesReport'])->name('reports.general-purchases-report');

// تقرير المشتريات اليومية - MOVED TO purchaseReportController
// Route::get('/reports/general-purchases-daily-report', [ReportController::class, 'generalPurchasesDailyReport'])->name('reports.general-purchases-daily-report');

// تقارير العملاء - MOVED TO CustomerReportController
// Route::get('/reports/general-customers-report', [ReportController::class, 'generalCustomersReport'])->name('reports.general-customers-report');

// تقرير العملاء اليومية
// Route::get('/reports/general-customers-daily-report', [ReportController::class, 'generalCustomersDailyReport'])->name('reports.general-customers-daily-report');

// تقرير العملاء اجماليات
// Route::get('/reports/general-customers-total-report', [ReportController::class, 'generalCustomersTotalReport'])->name('reports.general-customers-total-report');

// تقرير العملاء اصناف
// Route::get('/reports/general-customers-items-report', [ReportController::class, 'generalCustomersItemsReport'])->name('reports.general-customers-items-report');

// تقرير اعمار ديون العملاء
// Route::get('/reports/general-customers-debt-history-report', [ReportController::class, 'generalCustomersDebtHistoryReport'])->name('reports.general-customers-debt-history-report');

// تقارير الموردين - MOVED TO SupplierReportController
// Route::get('/reports/general-suppliers-report', [ReportController::class, 'generalSuppliersReport'])->name('reports.general-suppliers-report');

// تقرير الموردين اليومية
// Route::get('/reports/general-suppliers-daily-report', [ReportController::class, 'generalSuppliersDailyReport'])->name('reports.general-suppliers-daily-report');

//  تقرير الموردين اجماليات
// Route::get('/reports/general-suppliers-total-report', [ReportController::class, 'generalSuppliersTotalReport'])->name('reports.general-suppliers-total-report');

// تقرير الموردين اصناف
// Route::get('/reports/general-suppliers-items-report', [ReportController::class, 'generalSuppliersItemsReport'])->name('reports.general-suppliers-items-report');

// تقارير المصروفات - MOVED TO ExpenseReportController
// Route::get('/reports/general-expenses-report', [ReportController::class, 'generalExpensesReport'])->name('reports.general-expenses-report');

// Route::get('/reports/general-expenses-daily-report', [ReportController::class, 'generalExpensesDailyReport'])->name('reports.general-expenses-daily-report');

// تقرير ميزان المصروفات - MOVED TO ExpenseReportController
// Route::get('/reports/expenses-balance-report', [ReportController::class, 'expensesBalanceReport'])->name('reports.expenses-balance-report');

// تقارير مراكز التكلفة - MOVED TO CostCenterReportController
// Route::get('/reports/general-cost-centers-report', [ReportController::class, 'generalCostCentersReport'])->name('reports.general-cost-centers-report');

// قائمة مراكز التكلفة
// Route::get('/reports/general-cost-centers-list', [ReportController::class, 'generalCostCentersList'])->name('reports.general-cost-centers-list');

// كشف حساب مركز التكلفة
// Route::get('/reports/general-cost-center-account-statement', [ReportController::class, 'generalCostCenterAccountStatement'])->name('reports.general-cost-center-account-statement');

// كشف حساب عام مع مركز تكلفة
// Route::get('/reports/general-account-statement-with-cost-center', [ReportController::class, 'generalAccountStatementWithCostCenter'])->name('reports.general-account-statement-with-cost-center');
// تقارير المخزون - MOVED TO InventoryReportController
// Route::get('/reports/general-inventory-report', [ReportController::class, 'generalInventoryReport'])->name('reports.general-inventory-report');

// تقرير حركة المخزون اليومية - MOVED TO InventoryReportController
// Route::get('/reports/general-inventory-daily-movement-report', [ReportController::class, 'generalInventoryDailyMovementReport'])->name('reports.general-inventory-daily-movement-report');

// تقرير جرد المخزون - MOVED TO InventoryReportController
// Route::get('/reports/general-inventory-stocktaking-report', [ReportController::class, 'generalInventoryStocktakingReport'])->name('reports.general-inventory-stocktaking-report');

// تقارير الحسابات - MOVED TO GeneralReportController
// Route::get('/reports/general-accounts-report', [ReportController::class, 'generalAccountsReport'])->name('reports.general-accounts-report');

// تقرير كشف حساب عام - MOVED TO GeneralReportController
// Route::get('/reports/general-account-statement-report', [ReportController::class, 'generalAccountStatementReport'])->name('reports.general-account-statement-report');


// تقرير الأرباح والخسائر
// Route::get('/reports/general-profit-loss-report', [ReportController::class, 'generalProfitLossReport'])->name('reports.general-profit-loss-report');

// تقارير المبيعات - MOVED TO salesReportController in Modules/Reports/routes/reports/sales.php
// Route::get('/reports/general-sales-report', [ReportController::class, 'generalSalesReport'])->name('reports.general-sales-report');

// تقرير المبيعات اليومية
// Route::get('/reports/general-sales-daily-report', [ReportController::class, 'generalSalesDailyReport'])->name('reports.general-sales-daily-report');

// تقارير النقدية والبنوك
// Route::get('/reports/general-cash-bank-report', [ReportController::class, 'generalCashBankReport'])->name('reports.general-cash-bank-report');

// تقرير حركة الصندوق - MOVED TO GeneralReportController
// Route::get('/reports/general-cashbox-movement-report', [ReportController::class, 'generalCashboxMovementReport'])->name('reports.general-cashbox-movement-report');

// MOVED TO InventoryReportController
// Route::get('/reports/get-items-max&min-quntity', [ReportController::class, 'getItemsMaxMinQuantity'])->name('reports.get-items-max-min-quantity');

// MOVED TO InventoryReportController
// Route::get('/prices/compare-report', [ReportController::class, 'pricesCompareReport'])->name('prices.compare.report');

// تقرير جرد الأصناف - MOVED TO InventoryReportController
// Route::get('/discrepancy-report', [ReportController::class, 'inventoryDiscrepancyReport'])->name('reports.inventory-discrepancy-report');

// MOVED TO GeneralReportController
// Route::get('/oper-aging', [ReportController::class, 'agingReport'])->name('reports.oper-aging');


// MOVED TO Modules/Reports/routes/reports/sales.php and purchase.php
// Route::prefix('reports')->middleware(['auth'])->group(function () {
//
//     // تقرير المشتريات أصناف
//     Route::get('/purchases/items', [purchaseReportController::class, 'generalPurchasesItemsReport'])
//         ->name('reports.purchases.items');
//
//     // تقرير المبيعات أصناف
//     Route::get('/sales/items', [salesReportController::class, 'generalSalesItemsReport'])
//         ->name('reports.sales.items');
//
//     // تقرير المشتريات إجماليات
//     Route::get('/purchases/total', [purchaseReportController::class, 'generalPurchasesTotalReport'])
//         ->name('reports.purchases.total');
//
//     // تقرير المبيعات إجماليات
//     Route::get('/sales/total', [salesReportController::class, 'generalSalesTotalReport'])
//         ->name('reports.sales.total');
// });

// MOVED TO Modules/Reports/routes/reports/sales.php
// Route::get('/reports/sales/by-representative', [salesReportController::class, 'salesByRepresentativeReport'])
//     ->name('reports.sales.representative');
