<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ItemSearchController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/items/search', [ItemSearchController::class, 'search'])->name('api.items.search');
    Route::get('/items/{id}/details', [ItemSearchController::class, 'getItemDetails'])->name('api.items.details');
});
