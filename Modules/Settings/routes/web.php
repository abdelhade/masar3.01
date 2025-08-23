<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\{
    SettingsController,
    BarcodePrintSettingController,
    DataExportController
};

// Route::middleware(['auth', 'verified'])->prefix('crm')->group(function () {
    Route::get('mysettings', [SettingsController::class, 'index'])->name('mysettings.index')->middleware(['auth', 'can:عرض التحكم في الاعدادات']);
    Route::post('/mysettings/update', [SettingsController::class, 'update'])->name('mysettings.update')->middleware(['auth', 'can:عرض التحكم في الاعدادات']);

    Route::get('/test-setting', function () {
        return config('public_settings.campany_name');
    });

    Route::get('/barcode-print-settings/edit', [BarcodePrintSettingController::class, 'edit'])->name('barcode.print.settings.edit');
    Route::put('/barcode-print-settings', [BarcodePrintSettingController::class, 'update'])->name('barcode.print.settings.update');


    // في routes/web.php
    Route::get('/export-settings', function () {
        return view('settings::export-settings.index');
    })->middleware('auth')->name('export-settings');

    Route::get('/settings/export-data', [DataExportController::class, 'exportAllData'])
        ->name('export.data');
    Route::get('/settings/export-sql', [DataExportController::class, 'exportSqlDump'])
        ->name('export.sql');
// });
