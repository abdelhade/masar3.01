<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\CashBoxBankReportController;

Route::middleware(['auth'])->group(function () {
    // تقرير حركة الصندوق
    Route::get('reports/general-cashbox-movement-report', [CashBoxBankReportController::class, 'generalCashboxMovementReport'])->name('reports.general-cashbox-movement-report');
    // تقرير حركة البنك
    Route::get('reports/general-cash-bank-report', [CashBoxBankReportController::class, 'generalCashBankReport'])->name('reports.general-cash-bank-report');
});