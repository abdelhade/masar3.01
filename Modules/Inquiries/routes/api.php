<?php

use Illuminate\Support\Facades\Route;
use Modules\Inquiries\Http\Controllers\InquiriesController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Route::apiResource('inquiries', InquiriesController::class)->names('inquiries');
});
