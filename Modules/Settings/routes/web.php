<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\SettingsController;
use Modules\Settings\Http\Controllers\BarcodePrintSettingController;

Route::get('mysettings', [SettingsController::class, 'index'])->name('mysettings.index')->middleware(['auth', 'can:عرض التحكم في الاعدادات']);
Route::post('/mysettings/update', [SettingsController::class, 'update'])->name('mysettings.update')->middleware(['auth', 'can:عرض التحكم في الاعدادات']);

Route::get('/test-setting', function () {
    return config('public_settings.campany_name');
});

Route::get('/barcode-print-settings/edit', [BarcodePrintSettingController::class, 'edit'])->name('barcode.print.settings.edit');
Route::put('/barcode-print-settings', [BarcodePrintSettingController::class, 'update'])->name('barcode.print.settings.update');
