<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Livewire\Reports\BarcodePrintingReport;
use App\Http\Controllers\Reports\InvoiceReportController;
use Modules\Reports\Http\Controllers\salesReportController;

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
Route::get('/edit/purchase/price/invoice/report/{id}', [InvoiceReportController::class, 'editPurchasePriceInvoice'])->name('edit.purchase.price.invoice.report');

Route::get('/invoices/barcode-report/{id}', [InvoiceReportController::class, 'invoicesBarcodeReport'])
    ->name('invoices.barcode-report');

Route::get('/reports/items-max-min-quantity', [ReportController::class, 'getItemsMaxMinQuantity'])->name('reports.items.max-min-quantity');
Route::get('/reports/check-all-items-quantity', [ReportController::class, 'checkAllItemsQuantityLimits'])->name('reports.items.check-all-quantity');
Route::get('/reports/items-with-quantity-issues', [ReportController::class, 'getItemsWithQuantityIssues'])->name('reports.items.quantity-issues');
Route::get('/reports/check-item-quantity/{itemId}', [ReportController::class, 'checkItemQuantityAfterOperation'])->name('reports.items.check-item-quantity');

Route::get('/admin/reports/supplier-rfqs/{id}/details', [InvoiceReportController::class, 'getSupplierRfqDetails'])
    ->name('reports.supplier-rfqs.details');

Route::get('/invoices/convert-to-sales/{id}', [InvoiceReportController::class, 'convertToSalesInvoice'])
    ->name('invoices.convert-to-sales');

Route::get(
    '/reports/customer-quotations-comparison',
    [InvoiceReportController::class, 'customerQuotationsComparisonReport']
)->name('reports.customer-quotations-comparison');
