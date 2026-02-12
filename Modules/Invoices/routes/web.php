<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Invoices\Http\Controllers\InvoiceController;
use Modules\Invoices\Http\Controllers\InvoiceFormController;
use Modules\Invoices\Http\Controllers\InvoiceTemplateController;
use Modules\Invoices\Http\Controllers\InvoiceWorkflowController;

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Invoice Form Routes (New - Alpine.js based)
    Route::get('/invoices/form/create', [InvoiceFormController::class, 'create'])->name('invoices.form.create');
    Route::get('/invoices/form/{invoiceId}/edit', [InvoiceFormController::class, 'edit'])->name('invoices.form.edit');
    
    // Invoice Templates
    Route::resource('invoice-templates', InvoiceTemplateController::class)->parameters([
        'invoice-templates' => 'template'
    ]);

    // Invoice Resource (includes old create route that redirects to new form)
    Route::resource('invoices', InvoiceController::class)->names('invoices');

    Route::post(
        'invoice-templates/{template}/toggle-active',
        [InvoiceTemplateController::class, 'toggleActive']
    )->name('invoice-templates.toggle-active');

    // Invoice Statistics Routes
    Route::get('/sales/statistics', [InvoiceController::class, 'salesStatistics'])->name('sales.statistics');
    Route::get('/purchases/statistics', [InvoiceController::class, 'purchasesStatistics'])->name('purchases.statistics');
    Route::get('/inventory/statistics', [InvoiceController::class, 'inventoryStatistics'])->name('inventory.statistics');

    // Invoice Print Route
    Route::get('/invoice/print/{operation_id}', [InvoiceController::class, 'print'])->name('invoice.print');
    
    // Invoice View Route
    Route::get('invoice/view/{operationId}', [InvoiceController::class, 'view'])->name('invoice.view');

    // Invoice Workflow Routes
    Route::get('/invoices/requests', [InvoiceWorkflowController::class, 'index'])->name('invoices.requests.index');
    Route::get('/invoices/track/search', [InvoiceWorkflowController::class, 'index'])->name('invoices.track.search');
    Route::get('/invoices/track/{id}', [InvoiceWorkflowController::class, 'show'])->name('invoices.track');
    Route::post('/invoices/confirm/{id}', [InvoiceWorkflowController::class, 'confirm'])->name('invoices.confirm');
});
