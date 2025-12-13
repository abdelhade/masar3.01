<?php

use Illuminate\Support\Facades\Route;
use Modules\Authorization\Http\Controllers\RoleController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('roles', RoleController::class);
});
