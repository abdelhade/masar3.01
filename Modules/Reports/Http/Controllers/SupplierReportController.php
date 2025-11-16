<?php

declare(strict_types=1);

namespace Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Accounts\Models\AccHead;
use App\Models\JournalDetail;
use App\Models\OperationItems;

class SupplierReportController extends Controller
{
    public function generalSuppliersReport()
    {
        $suppliers = AccHead::where('code', 'like', '2101%')->where('isdeleted', 0)->get();

        $supplierTransactions = JournalDetail::whereHas('accHead', function ($q) {
            $q->where('code', 'like', '211%'); // Supplier accounts
        })->with(['accHead', 'head'])
            ->when(request('from_date'), function ($q) {
                $q->whereDate('crtime', '>=', request('from_date'));
            })
            ->when(request('to_date'), function ($q) {
                $q->whereDate('crtime', '<=', request('to_date'));
            })
            ->when(request('supplier_id'), function ($q) {
                $q->where('account_id', request('supplier_id'));
            })
            ->orderBy('crtime', 'desc')
            ->paginate(50);

        $totalAmount = $supplierTransactions->sum('debit') + $supplierTransactions->sum('credit');
        $totalPurchases = $supplierTransactions->sum('credit');
        $totalPayments = $supplierTransactions->sum('debit');
        $finalBalance = $totalPurchases - $totalPayments;
        $totalTransactions = $supplierTransactions->count();

        return view('reports::suppliers.general-suppliers-report', compact(
            'suppliers',
            'supplierTransactions',
            'totalAmount',
            'totalPurchases',
            'totalPayments',
            'finalBalance',
            'totalTransactions'
        ));
    }

    public function generalSuppliersDailyReport()
    {
        $suppliers = AccHead::where('code', 'like', '2101%')->where('isdeleted', 0)->get();

        $query = JournalDetail::whereHas('accHead', function ($q) {
            $q->where('code', 'like', '2101%'); // Supplier accounts
        })->with(['accHead', 'head']);

        if (request('from_date')) {
            $query->whereDate('crtime', '>=', request('from_date'));
        }
        if (request('to_date')) {
            $query->whereDate('crtime', '<=', request('to_date'));
        }
        if (request('supplier_id')) {
            $query->where('account_id', request('supplier_id'));
        }

        $supplierTransactions = $query->orderBy('crtime', 'desc')->paginate(50);

        $totalAmount = $supplierTransactions->sum('debit') + $supplierTransactions->sum('credit');
        $totalPurchases = $supplierTransactions->sum('credit');
        $totalPayments = $supplierTransactions->sum('debit');
        $finalBalance = $totalPurchases - $totalPayments;
        $totalTransactions = $supplierTransactions->count();

        return view('reports::suppliers.general-suppliers-daily-report', compact(
            'suppliers',
            'supplierTransactions',
            'totalAmount',
            'totalPurchases',
            'totalPayments',
            'finalBalance',
            'totalTransactions'
        ));
    }

    public function generalSuppliersItemsReport()
    {
        $suppliers = AccHead::where('code', 'like', '2101%')->where('isdeleted', 0)->get();

        $query = OperationItems::whereHas('operhead', function ($q) {
            $q->where('pro_type', 11); // Purchase invoices
        })->with(['item', 'operhead']);

        if (request('from_date')) {
            $query->whereHas('operhead', function ($q) {
                $q->whereDate('pro_date', '>=', request('from_date'));
            });
        }
        if (request('to_date')) {
            $query->whereHas('operhead', function ($q) {
                $q->whereDate('pro_date', '<=', request('to_date'));
            });
        }
        if (request('supplier_id')) {
            $query->whereHas('operhead', function ($q) {
                $q->where('acc1', request('supplier_id'));
            });
        }

        $supplierItems = $query->selectRaw('item_id, SUM(qty_in) as total_quantity, SUM(qty_in * item_price) as total_purchases, COUNT(DISTINCT pro_id) as invoices_count')
            ->groupBy('item_id')
            ->with('item')
            ->orderBy('total_quantity', 'desc')
            ->paginate(50);

        $totalQuantity = $supplierItems->sum('total_quantity');
        $totalPurchases = $supplierItems->sum('total_purchases');
        $averagePrice = $totalQuantity > 0 ? $totalPurchases / $totalQuantity : 0;
        $totalInvoices = $supplierItems->sum('invoices_count');
        $totalItems = $supplierItems->count();
        $topPurchasedItem = $supplierItems->first() ? $supplierItems->first()->item->name : '---';
        $averageQuantityPerItem = $totalItems > 0 ? $totalQuantity / $totalItems : 0;
        $averagePurchasesPerItem = $totalItems > 0 ? $totalPurchases / $totalItems : 0;

        return view('reports::suppliers.general-suppliers-items-report', compact(
            'suppliers',
            'supplierItems',
            'totalQuantity',
            'totalPurchases',
            'averagePrice',
            'totalInvoices',
            'totalItems',
            'topPurchasedItem',
            'averageQuantityPerItem',
            'averagePurchasesPerItem'
        ));
    }

    public function generalSuppliersTotalReport()
    {
        $groupBy = request('group_by', 'supplier');
        $fromDate = request('from_date');
        $toDate = request('to_date');

        $query = JournalDetail::whereHas('accHead', function ($q) {
            $q->where('code', 'like', '2101%'); // Supplier accounts
        })->with('accHead');

        if ($fromDate) {
            $query->whereDate('crtime', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('crtime', '<=', $toDate);
        }

        if ($groupBy === 'supplier') {
            $supplierTotals = $query->selectRaw('account_id, SUM(credit) as total_purchases, SUM(debit) as total_payments, COUNT(*) as transactions_count')
                ->groupBy('account_id')
                ->with('accHead')
                ->orderBy('total_purchases', 'desc')
                ->paginate(50);
        } else {
            $supplierTotals = $query->selectRaw('DATE(crtime) as date, SUM(credit) as total_purchases, SUM(debit) as total_payments, COUNT(*) as transactions_count')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->paginate(50);
        }

        $grandTotalTransactions = $supplierTotals->sum('transactions_count');
        $grandTotalPurchases = $supplierTotals->sum('total_purchases');
        $grandTotalPayments = $supplierTotals->sum('total_payments');
        $grandTotalBalance = $grandTotalPurchases - $grandTotalPayments;
        $grandAverageTransaction = $grandTotalTransactions > 0 ? ($grandTotalPurchases + $grandTotalPayments) / $grandTotalTransactions : 0;

        $totalSuppliers = $supplierTotals->count();
        $topSupplier = $supplierTotals->first() ? ($supplierTotals->first()->accHead->aname ?? '---') : '---';
        $averagePurchasesPerSupplier = $totalSuppliers > 0 ? $grandTotalPurchases / $totalSuppliers : 0;
        $averageBalancePerSupplier = $totalSuppliers > 0 ? $grandTotalBalance / $totalSuppliers : 0;

        return view('reports::suppliers.general-suppliers-total-report', compact(
            'supplierTotals',
            'groupBy',
            'grandTotalTransactions',
            'grandTotalPurchases',
            'grandTotalPayments',
            'grandTotalBalance',
            'grandAverageTransaction',
            'totalSuppliers',
            'topSupplier',
            'averagePurchasesPerSupplier',
            'averageBalancePerSupplier'
        ));
    }
}

