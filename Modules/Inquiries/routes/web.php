<?php

use Illuminate\Support\Facades\Route;
use Modules\Inquiries\Http\Controllers\InquiriesController;
use Modules\Inquiries\Http\Controllers\WorkTypeController;
use Modules\Inquiries\Http\Controllers\InquirySourceController;

Route::middleware(['auth', 'verified'])->group(function () {

    Route::resource('inquiries', InquiriesController::class)->names('inquiries');
    Route::resource('inquiry-sources', InquirySourceController::class)->names('inquiry.sources')->except(['show']);;

    Route::prefix('inquiry-sources')->name('inquiry.sources.')->group(function () {
        Route::post('/{id}/toggle-status', [InquirySourceController::class, 'toggleStatus'])->name('toggleStatus');
        Route::get('/tree', [InquirySourceController::class, 'getTreeData'])->name('tree');
    });

    Route::resource('work-types', WorkTypeController::class)->names('work.types')->except(['show']);

    Route::prefix('work-types')->name('work.types.')->group(function () {
        Route::post('/{id}/toggle-status', [WorkTypeController::class, 'toggleStatus'])->name('toggleStatus');
        Route::get('/tree', [WorkTypeController::class, 'getTreeData'])->name('tree');
        Route::get('/active', [WorkTypeController::class, 'getActiveWorkTypes'])->name('active');
    });
});
