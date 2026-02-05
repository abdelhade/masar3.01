<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\ItemReportController;
use Modules\Reports\Http\Controllers\GeneralReportController;
use Modules\Reports\Http\Controllers\InvoiceReportController;

Route::middleware(['auth'])->group(function () {
    Route::get('/reports', [GeneralReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/inactive-items', [ItemReportController::class, 'inactiveItemsReport'])->name('reports.inactive-items');
    
    // Invoice Reports
    Route::get('/billing/invoice-report', [InvoiceReportController::class, 'purchaseInvoices'])->name('billing.invoice-report');
    Route::get('/sales/invoice-report', [InvoiceReportController::class, 'salesInvoices'])->name('sales.invoice-report');
    Route::get('/sales/order-report', [InvoiceReportController::class, 'salesOrdersReport'])->name('sales-orders-report');
    Route::get('/purchase/quotations-report', [InvoiceReportController::class, 'purchaseQuotationsReport'])->name('purchase-quotations-reports');
    Route::get('/supplier/rfqs-report', [InvoiceReportController::class, 'supplierRfqsReport'])->name('supplier-rfqs-report');
    Route::get('/manufacturing/invoice/report', [InvoiceReportController::class, 'manufacturingReport'])->name('manufacturing.invoice.report');
    Route::get('/edit/purchase/price/invoice/report/{id}', [InvoiceReportController::class, 'editPurchasePriceInvoice'])->name('edit.purchase.price.invoice.report');
    Route::get('/invoices/barcode-report/{id}', [InvoiceReportController::class, 'invoicesBarcodeReport'])->name('invoices.barcode-report');
    Route::get('/admin/reports/supplier-rfqs/{id}/details', [InvoiceReportController::class, 'getSupplierRfqDetails'])->name('reports.supplier-rfqs.details');
    Route::get('/reports/customer-quotations-comparison', [InvoiceReportController::class, 'customerQuotationsComparisonReport'])->name('reports.customer-quotations-comparison');
    
    // Invoice Conversion Routes
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/convert-to-purchase/{id}', [InvoiceReportController::class, 'convertToPurchaseInvoice'])->name('convert-to-purchase');
        Route::get('/convert-to-sales/{id}', [InvoiceReportController::class, 'convertToSalesInvoice'])->name('convert-to-sales');
    });
});
