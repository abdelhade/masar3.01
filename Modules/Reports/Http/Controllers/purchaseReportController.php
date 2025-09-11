<?php

namespace Modules\Reports\Http\Controllers;

use App\Models\OperationItems;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class purchaseReportController extends Controller
{
    // تقرير المشتريات إجماليات
    public function generalPurchasesTotalReport()
    {
        $groupBy = request('group_by', 'day');
        $fromDate = request('from_date');
        $toDate = request('to_date');

        // Base Query
        $query = DB::table('operhead')
            ->join('operation_items', 'operhead.id', '=', 'operation_items.pro_id')
            ->where('operhead.pro_type', 11);

        if ($fromDate) {
            $query->whereDate('operhead.pro_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('operhead.pro_date', '<=', $toDate);
        }

        if ($groupBy === 'day') {
            $purchasesTotals = $query->selectRaw('
                    DATE(operhead.pro_date) as period_name,
                    COUNT(DISTINCT operhead.id) as invoices_count,
                    SUM(operation_items.qty_in) as total_quantity,
                    SUM(operation_items.qty_in * operation_items.item_price) as total_purchases,
                    SUM(operhead.fat_disc) as total_discount,
                    SUM(operhead.fat_net) as net_purchases,
                    (SUM(operhead.fat_net) / COUNT(DISTINCT operhead.id)) as average_invoice
                ')
                ->groupBy('period_name')
                ->orderBy('period_name', 'desc')
                ->paginate(50);
        } elseif ($groupBy === 'month') {
            $purchasesTotals = $query->selectRaw('
                YEAR(operhead.pro_date) as year,
                MONTH(operhead.pro_date) as month,
                COUNT(DISTINCT operhead.id) as invoices_count,
                SUM(operation_items.qty_in) as total_quantity,
                SUM(operation_items.qty_in * operation_items.item_price) as total_purchases,
                SUM(operhead.fat_disc) as total_discount,
                SUM(operhead.fat_net) as net_purchases
            ')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->paginate(50);
        } else {
            $purchasesTotals = $query->selectRaw('
                COUNT(DISTINCT operhead.id) as invoices_count,
                SUM(operation_items.qty_in) as total_quantity,
                SUM(operation_items.qty_in * operation_items.item_price) as total_purchases,
                SUM(operhead.fat_disc) as total_discount,
                SUM(operhead.fat_net) as net_purchases
            ')
                ->paginate(50);
        }

        // إجماليات
        $grandTotalInvoices = $purchasesTotals->sum('invoices_count');
        $grandTotalQuantity = $purchasesTotals->sum('total_quantity');
        $grandTotalPurchases = $purchasesTotals->sum('total_purchases');
        $grandTotalDiscount = $purchasesTotals->sum('total_discount');
        $grandTotalNetPurchases = $purchasesTotals->sum('net_purchases');
        $grandAverageInvoice = $grandTotalInvoices > 0 ? $grandTotalNetPurchases / $grandTotalInvoices : 0;

        $totalPeriods = $purchasesTotals->count();
        $highestPurchases = $purchasesTotals->max('net_purchases') ?? 0;
        $lowestPurchases = $purchasesTotals->min('net_purchases') ?? 0;
        $averagePurchases = $totalPeriods > 0 ? $grandTotalNetPurchases / $totalPeriods : 0;

        return view('reports::purchases.general-purchases-total', compact(
            'purchasesTotals',
            'groupBy',
            'grandTotalInvoices',
            'grandTotalQuantity',
            'grandTotalPurchases',
            'grandTotalDiscount',
            'grandTotalNetPurchases',
            'grandAverageInvoice',
            'totalPeriods',
            'highestPurchases',
            'lowestPurchases',
            'averagePurchases'
        ));
    }

    public function generalPurchasesItemsReport()
    {
        $query = OperationItems::whereHas('operhead', function ($q) {
            $q->where('pro_type', 11);
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

        $purchasesItems = $query->selectRaw('item_id, SUM(qty_in) as total_quantity, SUM(qty_in * item_price) as total_purchases,
         COUNT(DISTINCT pro_id) as invoices_count')
            ->groupBy('item_id')
            ->with('item')
            ->orderBy('total_quantity', 'desc')
            ->paginate(50);

        $totalQuantity = $purchasesItems->sum('total_quantity');
        $totalPurchases = $purchasesItems->sum('total_purchases');
        $averagePrice = $totalQuantity > 0 ? $totalPurchases / $totalQuantity : 0;
        $totalInvoices = $purchasesItems->sum('invoices_count');
        $totalItems = $purchasesItems->count();
        $topPurchasedItem = $purchasesItems->first() ? $purchasesItems->first()->item->name : '---';
        $averageQuantityPerItem = $totalItems > 0 ? $totalQuantity / $totalItems : 0;
        $averagePurchasesPerItem = $totalItems > 0 ? $totalPurchases / $totalItems : 0;

        return view('reports::purchases.general-purchases-items', compact(
            'purchasesItems',
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
}
