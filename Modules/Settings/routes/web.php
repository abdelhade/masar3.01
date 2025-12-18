<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\{
    SettingsController,
    BarcodePrintSettingController,
    CurrencyController,
    DataExportController
};

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('mysettings', [SettingsController::class, 'index'])->name('mysettings.index')->middleware('can:view settings');
    Route::post('/mysettings/update', [SettingsController::class, 'update'])->name('mysettings.update')->middleware('can:edit settings');

    Route::get('/barcode-print-settings/edit', [BarcodePrintSettingController::class, 'edit'])->name('barcode.print.settings.edit');
    Route::put('/barcode-print-settings', [BarcodePrintSettingController::class, 'update'])->name('barcode.print.settings.update');

    Route::get('/export-settings', function () {
        return view('settings::export-settings.index');
    })->name('export-settings');

    Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {
        Route::get('/export-data', [DataExportController::class, 'exportAllData'])->name('export-data');
        Route::get('/export-sql', [DataExportController::class, 'exportSqlDump'])->name('export-sql');
        Route::get('/export-stats', [DataExportController::class, 'getExportStats'])->name('export-stats');
    });

    Route::get('currencies/available', [CurrencyController::class, 'getAvailableCurrencies'])
        ->name('currencies.available');

    Route::post('currencies/{currency}/update-rate', [CurrencyController::class, 'updateRate'])
        ->name('currencies.update-rate');

    Route::post('currencies/{currency}/fetch-live-rate', [CurrencyController::class, 'fetchLiveRate'])
        ->name('currencies.fetch-live-rate');

    Route::post('currencies/{currency}/update-mode', [CurrencyController::class, 'updateMode'])
        ->name('currencies.update-mode');

    Route::resource('currencies', CurrencyController::class)->names('currencies');
});
