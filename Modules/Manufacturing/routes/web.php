<?php

use Illuminate\Support\Facades\Route;
use Modules\Manufacturing\Http\Controllers\ManufacturingController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('manufacturing', ManufacturingController::class)->names('manufacturing');
});
