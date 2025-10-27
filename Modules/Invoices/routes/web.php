<?php

use Illuminate\Support\Facades\Route;
use Modules\Invoices\Http\Controllers\InvoiceTemplateController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('invoice-templates', InvoiceTemplateController::class)->parameters([
        'invoice-templates' => 'template' // غيّر {invoice_template} إلى {template}
    ]);
    Route::post(
        'invoice-templates/{template}/toggle-active',
        [InvoiceTemplateController::class, 'toggleActive']
    )->name('invoice-templates.toggle-active');
});
