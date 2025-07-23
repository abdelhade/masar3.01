<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\SettingsController;

// Route::resource('settings', SettingsController::class)->names('settings');
Route::get('settings', [SettingsController::class, 'index'])->name('settings.index')->middleware(['auth', 'can:عرض التحكم في الاعدادات']);;
Route::post('/settings/update', [SettingsController::class, 'update'])->name('settings.update')->middleware(['auth', 'can:عرض التحكم في الاعدادات']);;

Route::get('/test-setting', function () {
    return config('public_settings.campany_name');
});