<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\ExpenseReportController;

Route::middleware(['auth'])->group(function () {
    Route::get('/reports/general-expenses-report', [ExpenseReportController::class, 'generalExpensesReport'])
        ->name('reports.general-expenses-report');

    Route::get('/reports/general-expenses-daily-report', [ExpenseReportController::class, 'generalExpensesDailyReport'])
        ->name('reports.general-expenses-daily-report');

    Route::get('/reports/expenses-balance-report', [ExpenseReportController::class, 'expensesBalanceReport'])
        ->name('reports.expenses-balance-report');
});



