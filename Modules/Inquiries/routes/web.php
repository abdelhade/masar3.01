<?php

use Illuminate\Support\Facades\Route;
use Modules\Inquiries\Http\Controllers\{
    WorkTypeController,
    ProjectSizeController,
    InquiriesController,
    InquirySourceController,
    DifficultyMatrixController,
    QuotationInfoController,
    InquiryDocumentController,
    InquiryStatisticsController
};

Route::middleware(['auth', 'verified'])->group(function () {

    Route::resource('inquiries', InquiriesController::class)->names('inquiries');
    Route::resource('inquiry-documents', InquiryDocumentController::class)->names([
        'index'   => 'inquiry.documents.index',
        'create'  => 'inquiry.documents.create',
        'store'   => 'inquiry.documents.store',
        'show'    => 'inquiry.documents.show',
        'edit'    => 'inquiry.documents.edit',
        'update'  => 'inquiry.documents.update',
        'destroy' => 'inquiry.documents.destroy',
    ]);

    Route::resource('inquiry-sources', InquirySourceController::class)->names('inquiry.sources')->except(['show']);

    Route::prefix('inquiry-sources')->name('inquiry.sources.')->group(function () {
        Route::post('/{id}/toggle-status', [InquirySourceController::class, 'toggleStatus'])->name('toggleStatus');
        Route::get('/tree', [InquirySourceController::class, 'getTreeData'])->name('tree');
    });

    Route::resource('work-types', WorkTypeController::class)->names('work.types')->except(['show']);
    Route::resource('project-size', ProjectSizeController::class)->names('project-size');

    Route::get('quotation-info/create', [QuotationInfoController::class, 'create'])->name('quotation-info.create');

    Route::prefix('work-types')->name('work.types.')->group(function () {
        Route::post('/{id}/toggle-status', [WorkTypeController::class, 'toggleStatus'])->name('toggleStatus');
        Route::get('/tree', [WorkTypeController::class, 'getTreeData'])->name('tree');
        Route::get('/active', [WorkTypeController::class, 'getActiveWorkTypes'])->name('active');
    });

    Route::get('/difficulty-matrix/create', [DifficultyMatrixController::class, 'create'])->name('difficulty-matrix.create');
    Route::get('dashboard/statistics/workout', [InquiryStatisticsController::class, 'index'])
        ->name('inquiries.dashboard.statistics');

    Route::post('preferences/save', [InquiriesController::class, 'savePreferences'])->name('inquiries.preferences.save');
    Route::post('preferences/reset', [InquiriesController::class, 'resetPreferences'])->name('inquiries.preferences.reset');

});
