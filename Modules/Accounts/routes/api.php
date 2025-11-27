<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// API routes for Accounts module
// Note: API endpoints can be added here when needed
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // API routes will be added here as needed
});
