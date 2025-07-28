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
}
