<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Invoices\Http\Controllers\Api\InvoiceApiController;
use Modules\Invoices\Http\Controllers\Api\InvoiceDataApiController;
use Modules\Invoices\Http\Controllers\Api\ItemSearchApiController;
use Modules\Invoices\Http\Controllers\Api\AccountBalanceApiController;
use Modules\Invoices\Http\Controllers\InvoiceController;

/*
|--------------------------------------------------------------------------
| Invoice API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth'])->prefix('invoices')->group(function () {

    // Initial Data
    Route::get('/initial-data', [InvoiceDataApiController::class, 'getInitialData'])
        ->name('api.invoices.initial-data');

    Route::get('/{invoiceId}/edit-data', [InvoiceDataApiController::class, 'getInvoiceForEdit'])
        ->name('api.invoices.edit-data');

    // Item Search
    Route::get('/items/search', [ItemSearchApiController::class, 'searchItems'])
        ->name('api.invoices.items.search');

    Route::get('/items/{itemId}/details', [ItemSearchApiController::class, 'getItemDetails'])
        ->name('api.invoices.items.details');

    Route::get('/items/{itemId}/price', [ItemSearchApiController::class, 'getItemPrice'])
        ->name('api.invoices.items.price');

    Route::get('/customers/{customerId}/recommended-items', [ItemSearchApiController::class, 'getRecommendedItems'])
        ->name('api.invoices.customers.recommended-items');

    // Invoice CRUD
    Route::post('/', [InvoiceController::class, 'store'])
        ->name('invoices.store');

    Route::put('/{invoiceId}', [InvoiceController::class, 'update'])
        ->name('api.invoices.update');

    Route::delete('/{invoiceId}', [InvoiceApiController::class, 'destroy'])
        ->name('api.invoices.destroy');
});

// Items Lite endpoint (outside invoices prefix for simpler URL)
Route::middleware(['web', 'auth'])->get('/items/lite', [ItemSearchApiController::class, 'getLiteItems'])
    ->name('api.items.lite');

// Quick create item endpoint
Route::middleware(['web', 'auth'])->post('/items/quick-create', [ItemSearchApiController::class, 'quickCreateItem'])
    ->name('api.items.quick-create');

// Account balance endpoint
Route::middleware(['web', 'auth'])->get('/accounts/{accountId}/balance', [AccountBalanceApiController::class, 'getBalance'])
    ->name('api.accounts.balance');
