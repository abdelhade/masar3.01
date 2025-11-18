<?php

use Illuminate\Support\Facades\Route;
use Modules\Inquiries\Http\Controllers\{
    WorkTypeController,
    ProjectSizeController,
    InquiriesController,
    InquirySourceController,
    DifficultyMatrixController,
    InquiriesRoleController,
    QuotationInfoController,
    InquiryDocumentController,
    InquiryStatisticsController
};

Route::middleware(['auth', 'verified'])->group(function () {

    // الروتات العامة (بدون middleware المهندسين)
    Route::get('inquiries', [InquiriesController::class, 'index'])->name('inquiries.index');
    Route::get('inquiries/create', [InquiriesController::class, 'create'])->name('inquiries.create');
    Route::post('inquiries', [InquiriesController::class, 'store'])->name('inquiries.store');
    Route::get('/drafts/list', [InquiriesController::class, 'drafts'])->name('inquiries.drafts');

    // الروتات المحمية (بس المهندسين المكلفين)
    Route::middleware('engineer.access')->group(function () {
        Route::get('inquiries/{inquiry}', [InquiriesController::class, 'show'])->name('inquiries.show');
        Route::get('inquiries/{inquiry}/edit', [InquiriesController::class, 'edit'])->name('inquiries.edit');
        Route::put('inquiries/{inquiry}', [InquiriesController::class, 'update'])->name('inquiries.update');
        Route::delete('inquiries/{inquiry}', [InquiriesController::class, 'destroy'])->name('inquiries.destroy');
    });

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

    Route::resource('inquiries-roles', InquiriesRoleController::class)->names('inquiries-roles');

    Route::get('quotation-info/create', [QuotationInfoController::class, 'create'])->name('quotation-info.create')
        ->middleware('permission:create Quotation Info');

    Route::prefix('work-types')->name('work.types.')->group(function () {
        Route::post('/{id}/toggle-status', [WorkTypeController::class, 'toggleStatus'])->name('toggleStatus');
        Route::get('/tree', [WorkTypeController::class, 'getTreeData'])->name('tree');
        Route::get('/active', [WorkTypeController::class, 'getActiveWorkTypes'])->name('active');
    });

    Route::get('/difficulty-matrix/create', [DifficultyMatrixController::class, 'create'])->name('difficulty-matrix.create')
        ->middleware('permission:create Difficulty Matrix');

    Route::get('dashboard/statistics/workout', [InquiryStatisticsController::class, 'index'])
        ->name('inquiries.dashboard.statistics')->middleware('permission:view Inquiries Statistics');

    Route::post('preferences/save', [InquiriesController::class, 'savePreferences'])->name('inquiries.preferences.save');

    Route::post('preferences/reset', [InquiriesController::class, 'resetPreferences'])->name('inquiries.preferences.reset');
});
