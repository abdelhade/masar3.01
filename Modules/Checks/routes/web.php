<?php

use Illuminate\Support\Facades\Route;
use Modules\Checks\Http\Controllers\ChecksController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Main checks routes
    Route::prefix('checks')->name('checks.')->group(function () {
        Route::get('/', [ChecksController::class, 'index'])->name('index');

        // أوراق القبض (Incoming checks)
        Route::get('/incoming', [ChecksController::class, 'incoming'])->name('incoming');
        Route::get('/incoming/create', [ChecksController::class, 'createIncoming'])->name('incoming.create');

        // أوراق الدفع (Outgoing checks)
        Route::get('/outgoing', [ChecksController::class, 'outgoing'])->name('outgoing');
        Route::get('/outgoing/create', [ChecksController::class, 'createOutgoing'])->name('outgoing.create');

        Route::get('/dashboard', [ChecksController::class, 'dashboard'])->name('dashboard');
        Route::get('/management', [ChecksController::class, 'management'])->name('management');
        Route::get('/export', [ChecksController::class, 'export'])->name('export');
        Route::get('/export/pdf', [\Modules\Checks\Http\Controllers\CheckExportController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/export/excel', [\Modules\Checks\Http\Controllers\CheckExportController::class, 'exportExcel'])->name('export.excel');

        // CRUD operations
        Route::get('/{check}', [ChecksController::class, 'show'])->name('show');
        Route::get('/{check}/edit', [ChecksController::class, 'edit'])->name('edit');
        Route::post('/', [ChecksController::class, 'store'])->name('store');
        Route::put('/{check}', [ChecksController::class, 'update'])->name('update');
        Route::delete('/{check}', [ChecksController::class, 'destroy'])->name('destroy');

        // Check operations
        Route::post('/{check}/clear', [ChecksController::class, 'clear'])->name('clear');
        Route::post('/batch-collect', [ChecksController::class, 'batchCollect'])->name('batch-collect');
        Route::post('/batch-cancel-reversal', [ChecksController::class, 'batchCancelReversal'])->name('batch-cancel-reversal');

        // File download
        Route::get('/{check}/download/{attachmentIndex}', [ChecksController::class, 'downloadAttachment'])
            ->name('download.attachment');

        // API endpoints
        Route::get('/api/accounts', [ChecksController::class, 'getAccounts'])->name('api.accounts');
    });
});
