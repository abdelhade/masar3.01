<?php

namespace App\Http\Controllers\Reports;

use App\Models\OperHead;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InvoiceReportController extends Controller
{
    public function purchaseInvoices(Request $request)
    {
        $invoices = OperHead::whereIn('pro_type', [11, 13])
            ->with(['acc1Head', 'acc2Head', 'employee', 'user'])
            ->orderByDesc('pro_date')
            ->get();
        return view('reports.invoices.purchase-invoice', compact('invoices'));
    }

    public function salesInvoices(Request $request)
    {
        $invoices = OperHead::whereIn('pro_type', [10, 12])
            ->with(['acc1Head', 'acc2Head', 'employee', 'user'])
            ->orderByDesc('pro_date')
            ->get();
        return view('reports.invoices.sales-invoice', compact('invoices'));
    }

    public function salesOrdersReport()
    {
        $invoices = OperHead::whereIn('pro_type', [14])
            ->with(['acc1Head', 'acc2Head', 'employee', 'user'])
            ->orderByDesc('pro_date')
            ->get();
        return view('reports.invoices.sales-orders', compact('invoices'));
    }

    public function purchaseQuotationsReport()
    {
        $invoices = OperHead::whereIn('pro_type', [16])
            ->with(['acc1Head', 'acc2Head', 'employee', 'user'])
            ->orderByDesc('pro_date')
            ->get();
        return view('reports.invoices.purchase-quotations', compact('invoices'));
    }

    public function supplierRfqsReport()
    {
        $invoices = OperHead::whereIn('pro_type', [17])
            ->with(['acc1Head', 'acc2Head', 'employee', 'user'])
            ->orderByDesc('pro_date')
            ->get();
        return view('reports.invoices.supplier-rfqs', compact('invoices'));
    }
}
