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
    
    // محلل النشاط اليومي
    Route::get('/reports/daily-activity-analyzer', [GeneralReportController::class, 'dailyActivityAnalyzer'])->name('reports.daily-activity-analyzer');

   
});