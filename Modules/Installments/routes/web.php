<?php

use Illuminate\Support\Facades\Route;
use Modules\Installments\Http\Controllers\InstallmentsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('installments', InstallmentsController::class)->names('installments');
});
