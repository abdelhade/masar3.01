<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\AccountsReportController;
use Modules\Accounts\Http\Controllers\AccHeadController;

Route::middleware(['auth'])->group(function () {
    // شجرة الحسابات
    Route::get('reports/accounts-tree', [AccountsReportController::class, 'accountsTree'])->name('reports.accounts-tree');
    // الميزانية العمومية
    Route::get('reports/general-balance-sheet', [AccountsReportController::class, 'generalBalanceSheet'])->name('reports.general-balance-sheet');

    // تقرير الأرباح والخسائر
    Route::get('reports/general-profit-loss-report', [AccountsReportController::class, 'generalProfitLossReport'])->name('reports.general-profit-loss-report');

    // ميزان الحسابات
    Route::get('reports/general-account-balances', [AccountsReportController::class, 'generalAccountBalances'])->name('reports.general-account-balances');

    // Account Movement Report
    Route::get('account-movement/{accountId?}', [AccHeadController::class, 'accountMovementReport'])->name('account-movement');
    

});