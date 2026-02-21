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
| Note: These routes use 'api' middleware from RouteServiceProvider
| which adds 'api' prefix automatically
*/

Route::middleware(['web', 'auth'])->group(function () {
    
    // Items endpoints (will be /api/items/*)
    Route::get('/items/lite', [ItemSearchApiController::class, 'getLiteItems'])
        ->name('items.lite')
        ->withoutMiddleware('auth:sanctum')
        ->middleware('auth');

    Route::post('/items/quick-create', [ItemSearchApiController::class, 'quickCreateItem'])
        ->name('items.quick-create')
        ->withoutMiddleware('auth:sanctum')
        ->middleware('auth');

    // Account balance endpoint
    Route::get('/accounts/{accountId}/balance', [AccountBalanceApiController::class, 'getBalance'])
        ->name('accounts.balance')
        ->withoutMiddleware('auth:sanctum')
        ->middleware('auth');

    // Invoice routes (will be /api/invoices/*)
    Route::prefix('invoices')->group(function () {

        // Initial Data
        Route::get('/initial-data', [InvoiceDataApiController::class, 'getInitialData'])
            ->name('invoices.initial-data')
            ->withoutMiddleware('auth:sanctum')
            ->middleware('auth');

        Route::get('/{invoiceId}/edit-data', [InvoiceDataApiController::class, 'getInvoiceForEdit'])
            ->name('invoices.edit-data')
            ->withoutMiddleware('auth:sanctum')
            ->middleware('auth');

        // Item Search
        Route::get('/items/search', [ItemSearchApiController::class, 'searchItems'])
            ->name('invoices.items.search')
            ->withoutMiddleware('auth:sanctum')
            ->middleware('auth');

        Route::get('/items/{itemId}/details', [ItemSearchApiController::class, 'getItemDetails'])
            ->name('invoices.items.details')
            ->withoutMiddleware('auth:sanctum')
            ->middleware('auth');

        Route::get('/items/{itemId}/price', [ItemSearchApiController::class, 'getItemPrice'])
            ->name('invoices.items.price')
            ->withoutMiddleware('auth:sanctum')
            ->middleware('auth');

        Route::get('/customers/{customerId}/recommended-items', [ItemSearchApiController::class, 'getRecommendedItems'])
            ->name('invoices.customers.recommended-items')
            ->withoutMiddleware('auth:sanctum')
            ->middleware('auth');

        // Invoice CRUD
        Route::post('/', [InvoiceController::class, 'store'])
            ->name('invoices.store')
            ->withoutMiddleware('auth:sanctum')
            ->middleware('auth');

        Route::put('/{invoiceId}', [InvoiceController::class, 'update'])
            ->name('invoices.update')
            ->withoutMiddleware('auth:sanctum')
            ->middleware('auth');

        Route::delete('/{invoiceId}', [InvoiceApiController::class, 'destroy'])
            ->name('invoices.destroy')
            ->withoutMiddleware('auth:sanctum')
            ->middleware('auth');
    });
});
