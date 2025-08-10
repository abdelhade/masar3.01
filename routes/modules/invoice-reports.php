<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Reports\InvoiceReportController;

Route::get('/billing/invoice-report', [InvoiceReportController::class, 'purchaseInvoices'])->name('billing.invoice-report');
Route::get('/sales/invoice-report', [InvoiceReportController::class, 'salesInvoices'])->name('sales.invoice-report');

Route::get('/sales/order-report', [InvoiceReportController::class, 'salesOrdersReport'])->name('sales-orders-report');
Route::get('/purchase/quotations-report', [InvoiceReportController::class, 'purchaseQuotationsReport'])->name('purchase-quotations-reports');
Route::get('/supplier/rfqs-report', [InvoiceReportController::class, 'supplierRfqsReport'])->name('supplier-rfqs-report');

Route::group(['prefix' => 'invoices', 'as' => 'invoices.'], function () {
    Route::get('/convert-to-purchase/{id}', [InvoiceReportController::class, 'convertToPurchaseInvoice'])
        ->name('convert-to-purchase');
    Route::get('/convert-to-sales/{id}', [InvoiceReportController::class, 'convertToSalesInvoice'])
        ->name('convert-to-sales');
});

Route::get('/manufacturing/invoice/report', [InvoiceReportController::class, 'manufacturingReport'])->name('manufacturing.invoice.report');
