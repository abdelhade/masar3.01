<?php

use Illuminate\Support\Facades\Route;
use Modules\Invoices\Http\Controllers\Api\InvoiceActionsController;
use Modules\Invoices\Http\Controllers\Api\InvoiceSearchController;

Route::get('/search', InvoiceSearchController::class);
Route::post('/add-item', [InvoiceActionsController::class, 'addItem']);
Route::post('/create-item', [InvoiceActionsController::class, 'createItem']);