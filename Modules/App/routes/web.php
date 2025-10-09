<?php

use Illuminate\Support\Facades\Route;
use Modules\App\Http\Controllers\ExcelImportController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Route::resource('apps', AppController::class)->names('app');
    Route::prefix('excel-import')->group(function () {
        Route::post('{model}/preview', [ExcelImportController::class, 'preview'])->name('excel-import.preview');
        Route::post('{model}/import', [ExcelImportController::class, 'import'])->name('excel-import.import');
        Route::get('template', [ExcelImportController::class, 'template'])->name('excel-import.template');
    });
});
