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

        // Batch operations
        Route::post('/batch-collect', [ChecksController::class, 'batchCollect'])->name('batch-collect');
        Route::post('/batch-cancel-reversal', [ChecksController::class, 'batchCancelReversal'])->name('batch-cancel-reversal');

        // Check operations (يجب أن تكون قبل CRUD operations لأنها تحتوي على parameters إضافية)
        Route::get('/{check}/collect', [ChecksController::class, 'collect'])->name('collect');
        Route::post('/{check}/collect', [ChecksController::class, 'storeCollect'])->name('store-collect');
        Route::get('/{check}/clear', [ChecksController::class, 'showClear'])->name('show-clear');
        Route::post('/{check}/clear', [ChecksController::class, 'clear'])->name('clear');
        Route::get('/{check}/cancel-reversal', [ChecksController::class, 'showCancelReversal'])->name('show-cancel-reversal');
        Route::post('/{check}/cancel-reversal', [ChecksController::class, 'cancelReversal'])->name('cancel-reversal');
        Route::get('/{check}/download/{attachmentIndex}', [ChecksController::class, 'downloadAttachment'])
            ->name('download.attachment');
        Route::get('/{check}/edit', [ChecksController::class, 'edit'])->name('edit');

        // CRUD operations
        Route::get('/{check}', [ChecksController::class, 'show'])->name('show');
        Route::post('/', [ChecksController::class, 'store'])->name('store');
        Route::put('/{check}', [ChecksController::class, 'update'])->name('update');
        Route::delete('/{check}', [ChecksController::class, 'destroy'])->name('destroy');

        // API endpoints
        Route::get('/api/accounts', [ChecksController::class, 'getAccounts'])->name('api.accounts');
    });
});
