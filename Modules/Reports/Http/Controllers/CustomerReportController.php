<?php

declare(strict_types=1);

namespace Modules\Reports\Http\Controllers;

use App\Models\JournalDetail;
use App\Models\OperationItems;
use Illuminate\Routing\Controller;
use Modules\Accounts\Models\AccHead;

class CustomerReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Clients');
    }

    public function generalCustomersReport()
    {
        $customers = AccHead::where('code', 'like', '1103%')->where('isdeleted', 0)->get();

        $customerTransactions = JournalDetail::whereHas('accHead', function ($q) {
            $q->where('code', 'like', '122%'); // Customer accounts
        })->with(['accHead', 'head'])
            ->when(request('from_date'), function ($q) {
                $q->whereDate('crtime', '>=', request('from_date'));
            })
            ->when(request('to_date'), function ($q) {
                $q->whereDate('crtime', '<=', request('to_date'));
            })
            ->when(request('customer_id'), function ($q) {
                $q->where('account_id', request('customer_id'));
            })
            ->orderBy('crtime', 'desc')
            ->paginate(50);

        $totalAmount = $customerTransactions->sum('debit') + $customerTransactions->sum('credit');
        $totalSales = $customerTransactions->sum('debit');
        $totalPayments = $customerTransactions->sum('credit');
        $finalBalance = $totalSales - $totalPayments;
        $totalTransactions = $customerTransactions->count();

        return view('reports::customers.general-customers-report', compact(
            'customers',
            'customerTransactions',
            'totalAmount',
            'totalSales',
            'totalPayments',
            'finalBalance',
            'totalTransactions'
        ));
    }

    public function generalCustomersDailyReport()
    {
        $customers = AccHead::where('code', 'like', '1103%')->where('isdeleted', 0)->get();

        $query = JournalDetail::whereHas('accHead', function ($q) {
            $q->where('code', 'like', '1103%'); // Customer accounts
        })->with(['accHead', 'head']);

        if (request('from_date')) {
            $query->whereDate('crtime', '>=', request('from_date'));
        }
        if (request('to_date')) {
            $query->whereDate('crtime', '<=', request('to_date'));
        }
        if (request('customer_id')) {
            $query->where('account_id', request('customer_id'));
        }

        $customerTransactions = $query->orderBy('crtime', 'desc')->paginate(50);

        $totalAmount = $customerTransactions->sum('debit') + $customerTransactions->sum('credit');
        $totalSales = $customerTransactions->sum('debit');
        $totalPayments = $customerTransactions->sum('credit');
        $finalBalance = $totalSales - $totalPayments;
        $totalTransactions = $customerTransactions->count();

        return view('reports::customers.general-customers-daily-report', compact(
            'customers',
            'customerTransactions',
            'totalAmount',
            'totalSales',
            'totalPayments',
            'finalBalance',
            'totalTransactions'
        ));
    }

    public function generalCustomersItemsReport()
    {
        $customers = AccHead::where('code', 'like', '1103%')->where('isdeleted', 0)->get();

        $query = OperationItems::whereHas('operhead', function ($q) {
            $q->where('pro_type', 10); // Sales invoices
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
        if (request('customer_id')) {
            $query->whereHas('operhead', function ($q) {
                $q->where('acc1', request('customer_id'));
            });
        }

        $customerItems = $query->selectRaw('item_id, SUM(qty_out) as total_quantity, SUM(qty_out * item_price) as total_sales, COUNT(DISTINCT pro_id) as invoices_count')
            ->groupBy('item_id')
            ->with('item')
            ->orderBy('total_quantity', 'desc')
            ->paginate(50);

        $totalQuantity = $customerItems->sum('total_quantity');
        $totalSales = $customerItems->sum('total_sales');
        $averagePrice = $totalQuantity > 0 ? $totalSales / $totalQuantity : 0;
        $totalInvoices = $customerItems->sum('invoices_count');
        $totalItems = $customerItems->count();
        $topSellingItem = $customerItems->first() ? $customerItems->first()->item->name : '---';
        $averageQuantityPerItem = $totalItems > 0 ? $totalQuantity / $totalItems : 0;
        $averageSalesPerItem = $totalItems > 0 ? $totalSales / $totalItems : 0;

        return view('reports::customers.general-customers-items-report', compact(
            'customers',
            'customerItems',
            'totalQuantity',
            'totalSales',
            'averagePrice',
            'totalInvoices',
            'totalItems',
            'topSellingItem',
            'averageQuantityPerItem',
            'averageSalesPerItem'
        ));
    }

    public function generalCustomersTotalReport()
    {
        $groupBy = request('group_by', 'customer');
        $fromDate = request('from_date');
        $toDate = request('to_date');

        $query = JournalDetail::whereHas('accHead', function ($q) {
            $q->where('code', 'like', '1103%'); // Customer accounts
        })->with('accHead');

        if ($fromDate) {
            $query->whereDate('crtime', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('crtime', '<=', $toDate);
        }

        if ($groupBy === 'customer') {
            $customerTotals = $query->selectRaw('account_id, SUM(debit) as total_sales, SUM(credit) as total_payments, COUNT(*) as transactions_count')
                ->groupBy('account_id')
                ->with('accHead')
                ->orderBy('total_sales', 'desc')
                ->paginate(50);
        } else {
            $customerTotals = $query->selectRaw('DATE(crtime) as date, SUM(debit) as total_sales, SUM(credit) as total_payments, COUNT(*) as transactions_count')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->paginate(50);
        }

        $grandTotalTransactions = $customerTotals->sum('transactions_count');
        $grandTotalSales = $customerTotals->sum('total_sales');
        $grandTotalPayments = $customerTotals->sum('total_payments');
        $grandTotalBalance = $grandTotalSales - $grandTotalPayments;
        $grandAverageTransaction = $grandTotalTransactions > 0 ? ($grandTotalSales + $grandTotalPayments) / $grandTotalTransactions : 0;

        $totalCustomers = $customerTotals->count();
        $topCustomer = $customerTotals->first() ? ($customerTotals->first()->accHead->aname ?? '---') : '---';
        $averageSalesPerCustomer = $totalCustomers > 0 ? $grandTotalSales / $totalCustomers : 0;
        $averageBalancePerCustomer = $totalCustomers > 0 ? $grandTotalBalance / $totalCustomers : 0;

        return view('reports::customers.general-customers-total-report', compact(
            'customerTotals',
            'groupBy',
            'grandTotalTransactions',
            'grandTotalSales',
            'grandTotalPayments',
            'grandTotalBalance',
            'grandAverageTransaction',
            'totalCustomers',
            'topCustomer',
            'averageSalesPerCustomer',
            'averageBalancePerCustomer'
        ));
    }

    public function customerDebtHistory()
    {
        return view('reports::customers.customer-debt-history');
    }
}
