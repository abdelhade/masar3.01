<?php

use Illuminate\Support\Facades\Route;
use Modules\Zatca\Http\Controllers\ZatcaController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('zatcas', ZatcaController::class)->names('zatca');
});

Route::get('/zatca-test', function () {
    return view('zatca::zatca-test');
});
