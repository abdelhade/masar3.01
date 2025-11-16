<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\GeneralReportController;

Route::middleware(['auth'])->group(function () {
    // محلل العمل اليومي
    Route::get('reports/overall', [GeneralReportController::class, 'overall'])->name('reports.overall');
        // اليومية العامة
    Route::get('reports/journal-summery', [GeneralReportController::class, 'journalSummery'])->name('reports.journal-summery');
    // كشف حساب عام - تفاصيل اليومية
    Route::get('reports/general-journal-details', [GeneralReportController::class, 'generalJournalDetails'])->name('reports.general-journal-details');
    
    // محلل العمل اليومي
    Route::get('/reports/daily-activity-analyzer', [GeneralReportController::class, 'dailyActivityAnalyzer'])->name('reports.daily-activity-analyzer');
    
    // كشف حساب حساب
    Route::get('/reports/general-account-statement', [GeneralReportController::class, 'generalAccountStatement'])->name('reports.general-account-statement');
    
    // قائمة الحسابات مع الارصدة
    Route::get('/reports/general-account-balances-by-store', [GeneralReportController::class, 'generalAccountBalancesByStore'])->name('reports.general-account-balances-by-store');
    
    // تقرير الحسابات العام
    Route::get('/reports/general-accounts-report', [GeneralReportController::class, 'generalAccountsReport'])->name('reports.general-accounts-report');
    
    // تقرير كشف حساب عام
    Route::get('/reports/general-account-statement-report', [GeneralReportController::class, 'generalAccountStatementReport'])->name('reports.general-account-statement-report');
    
    // تقرير حركة الصندوق
    Route::get('/reports/general-cashbox-movement-report', [GeneralReportController::class, 'generalCashboxMovementReport'])->name('reports.general-cashbox-movement-report');
    
    // تقرير الأعمار
    Route::get('/oper-aging', [GeneralReportController::class, 'agingReport'])->name('reports.oper-aging');
});