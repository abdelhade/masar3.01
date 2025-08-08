<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\SettingsController;

// Route::resource('settings', SettingsController::class)->names('settings');
Route::get('mysettings', [SettingsController::class, 'index'])->name('mysettings.index')->middleware(['auth', 'can:عرض التحكم في الاعدادات']);
Route::post('/mysettings/update', [SettingsController::class, 'update'])->name('mysettings.update')->middleware(['auth', 'can:عرض التحكم في الاعدادات']);

Route::get('/test-setting', function () {
    return config('public_settings.campany_name');
});
