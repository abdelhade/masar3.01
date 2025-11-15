<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\AccountsReportController;

Route::middleware(['auth'])->group(function () {
    // شجرة الحسابات
    Route::get('reports/accounts-tree', [AccountsReportController::class, 'accountsTree'])->name('reports.accounts-tree');

});