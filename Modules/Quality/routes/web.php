<?php

use Illuminate\Support\Facades\Route;
use Modules\Quality\Http\Controllers\QualityDashboardController;
use Modules\Quality\Http\Controllers\QualityInspectionController;
use Modules\Quality\Http\Controllers\NonConformanceReportController;
use Modules\Quality\Http\Controllers\CorrectiveActionController;
use Modules\Quality\Http\Controllers\QualityStandardController;
use Modules\Quality\Http\Controllers\BatchTrackingController;
use Modules\Quality\Http\Controllers\SupplierRatingController;
use Modules\Quality\Http\Controllers\QualityCertificateController;
use Modules\Quality\Http\Controllers\QualityAuditController;

Route::middleware(['auth', 'verified'])->prefix('quality')->name('quality.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [QualityDashboardController::class, 'index'])->name('dashboard');
    
    // Quality Standards
    Route::resource('standards', QualityStandardController::class);
    
    // Quality Inspections
    Route::resource('inspections', QualityInspectionController::class);
    Route::post('inspections/{inspection}/approve', [QualityInspectionController::class, 'approve'])->name('inspections.approve');
    Route::post('inspections/{inspection}/reject', [QualityInspectionController::class, 'reject'])->name('inspections.reject');
    
    // Non-Conformance Reports (NCR)
    Route::resource('ncr', NonConformanceReportController::class);
    Route::post('ncr/{ncr}/close', [NonConformanceReportController::class, 'close'])->name('ncr.close');
    
    // Corrective Actions (CAPA)
    Route::resource('capa', CorrectiveActionController::class);
    Route::post('capa/{capa}/verify', [CorrectiveActionController::class, 'verify'])->name('capa.verify');
    
    // Batch Tracking
    Route::resource('batches', BatchTrackingController::class);
    
    // Supplier Ratings
    Route::resource('suppliers', SupplierRatingController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    
    // Quality Certificates
    Route::resource('certificates', QualityCertificateController::class);
    
    // Quality Audits
    Route::resource('audits', QualityAuditController::class);
    
    // Reports
    Route::get('/reports', function () {
        return view('reports::general-reports.index');
    })->name('reports');
});

