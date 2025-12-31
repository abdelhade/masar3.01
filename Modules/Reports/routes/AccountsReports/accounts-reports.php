<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\AccountsReportController;
use Modules\Accounts\Http\Controllers\AccHeadController;

Route::middleware(['auth'])->group(function () {
    // شجرة الحسابات (Accounts Tree)
    Route::get('reports/accounts-tree', [AccountsReportController::class, 'accountsTree'])
        ->name('reports.accounts-tree')
        ->middleware('permission:view Accounts Tree');

    // الميزانية العمومية (Balance Sheet)
    Route::get('reports/general-balance-sheet', [AccountsReportController::class, 'generalBalanceSheet'])
        ->name('reports.general-balance-sheet')
        ->middleware('permission:view Balance Sheet');

    // تقرير الأرباح والخسائر (Profit Loss Report)
    Route::get('reports/general-profit-loss-report', [AccountsReportController::class, 'generalProfitLossReport'])
        ->name('reports.general-profit-loss-report')
        ->middleware('permission:view Profit Loss Report');

    // تقرير الأرباح والخسائر لإجمالي الفترة (Income Statement Total)
    Route::get('reports/general-profit-loss-report-total', [AccountsReportController::class, 'generalProfitLossReportTotal'])
        ->name('reports.general-profit-loss-report-total')
        ->middleware('permission:view Income Statement Total');

    // ميزان الحسابات (Accounts Balance)
    Route::get('reports/general-account-balances', [AccountsReportController::class, 'generalAccountBalances'])
        ->name('reports.general-account-balances')
        ->middleware('permission:view Accounts Balance');

    // مقارنة أرصدة الحسابات مع القيود اليومية
    // (ملاحظة: هذا المسار لم يذكر له صلاحية محددة في السيدر، يمكنك استخدام صلاحية عامة أو إضافتها لاحقاً)
    Route::get('reports/compare-account-balances', [AccountsReportController::class, 'compareAccountBalances'])
        ->name('reports.compare-account-balances')
        ->middleware('permission:view Accounts Balance'); // تم وضع أقرب صلاحية منطقية، أو يمكنك إزالتها إذا لم ترغب

    // تقرير حركة الحساب (Account Movement Report)
    Route::get('account-movement/{accountId?}', [AccHeadController::class, 'accountMovementReport'])
        ->name('account-movement')
        ->middleware('permission:view Account Movement Report');
});
