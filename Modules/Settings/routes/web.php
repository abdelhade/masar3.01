<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\{
    SettingsController,
    BarcodePrintSettingController,
    CurrencyController,
    DataExportController
};

// ==========================================
// Currency API Endpoints
// ==========================================
Route::middleware(['auth'])->group(function () {
    Route::get('currencies/available', [CurrencyController::class, 'getAvailableCurrencies'])
        ->name('currencies.available');

    Route::get('currencies/active', [CurrencyController::class, 'getActiveCurrencies'])
        ->name('currencies.active');

    Route::get('currencies/convert', [CurrencyController::class, 'getConversionRate'])
        ->name('currencies.convert');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('mysettings', [SettingsController::class, 'index'])
        ->name('mysettings.index');
    // ->middleware('permission:view General Settings');

    Route::post('/mysettings/update', [SettingsController::class, 'update'])
        ->name('mysettings.update');
        //->middleware('permission:edit General Settings');

    Route::get('/barcode-print-settings/edit', [BarcodePrintSettingController::class, 'edit'])
        ->name('barcode.print.settings.edit')
        ->middleware('permission:view Barcode Settings');

    Route::put('/barcode-print-settings', [BarcodePrintSettingController::class, 'update'])
        ->name('barcode.print.settings.update')
        ->middleware('permission:edit Barcode Settings');

    Route::get('/export-settings', function () {
        return view('settings::export-settings.index');
    })->name('export-settings')
        ->middleware('permission:view Export Data');

    Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {
        Route::get('/export-data', [DataExportController::class, 'exportAllData'])
            ->name('export-data')
            ->middleware('permission:edit Export Data');

        Route::get('/export-sql', [DataExportController::class, 'exportSqlDump'])
            ->name('export-sql')
            ->middleware('permission:edit Export Data');

        Route::get('/export-stats', [DataExportController::class, 'getExportStats'])
            ->name('export-stats')
            ->middleware('permission:view Export Data');
    });

    Route::get('currencies/available', [CurrencyController::class, 'getAvailableCurrencies'])
        ->name('currencies.available');

    // Exchange Rate Management (no permission required - only auth)
    Route::post('currencies/{currency}/update-rate', [CurrencyController::class, 'updateRate'])
        ->name('currencies.update-rate');

    Route::post('currencies/{currency}/fetch-live-rate', [CurrencyController::class, 'fetchLiveRate'])
        ->name('currencies.fetch-live-rate');

    Route::post('currencies/{currency}/update-mode', [CurrencyController::class, 'updateMode'])
        ->name('currencies.update-mode');

    // CRUD Routes (لازم تيجي بعد الـ Specific Routes)
    Route::resource('currencies', CurrencyController::class)
        ->names('currencies');
});
