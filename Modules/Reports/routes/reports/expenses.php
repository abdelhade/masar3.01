<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\ExpenseManagementController;
use Modules\Reports\Http\Controllers\ExpenseReportController;

Route::middleware(['auth'])->group(function () {
    // إدارة المصروفات
    Route::get('/expenses/dashboard', [ExpenseManagementController::class, 'dashboard'])
        ->name('expenses.dashboard');

    Route::get('/expenses/create', [ExpenseManagementController::class, 'create'])
        ->name('expenses.create');

    Route::post('/expenses/store', [ExpenseManagementController::class, 'store'])
        ->name('expenses.store');

    // تقارير المصروفات
    Route::get('/reports/general-expenses-report', [ExpenseReportController::class, 'generalExpensesReport'])
        ->name('reports.general-expenses-report');

    Route::get('/reports/general-expenses-daily-report', [ExpenseReportController::class, 'generalExpensesDailyReport'])
        ->name('reports.general-expenses-daily-report');

    Route::get('/reports/expenses-balance-report', [ExpenseReportController::class, 'expensesBalanceReport'])
        ->name('reports.expenses-balance-report');
});
