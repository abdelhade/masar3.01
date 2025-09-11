<?php

namespace Modules\Reports\Http\Controllers;

use App\Models\OperHead;
use App\Models\OperationItems;
use App\Http\Controllers\Controller;

class salesReportController extends Controller
{
    public function generalSalesItemsReport()
    {
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

        $salesItems = $query->selectRaw('item_id, SUM(qty_out) as total_quantity, SUM(qty_out * item_price) as total_sales,
        COUNT(DISTINCT pro_id) as invoices_count')
            ->groupBy('item_id')
            ->with('item')
            ->orderBy('total_quantity', 'desc')
            ->paginate(50);

        $totalQuantity = $salesItems->sum('total_quantity');
        $totalSales = $salesItems->sum('total_sales');
        $averagePrice = $totalQuantity > 0 ? $totalSales / $totalQuantity : 0;
        $totalInvoices = $salesItems->sum('invoices_count');
        $totalItems = $salesItems->count();
        $topSellingItem = $salesItems->first() ? $salesItems->first()->item->name : '---';
        $averageQuantityPerItem = $totalItems > 0 ? $totalQuantity / $totalItems : 0;
        $averageSalesPerItem = $totalItems > 0 ? $totalSales / $totalItems : 0;

        return view('reports::sales.general-sales-items', compact(
            'salesItems',
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

    public function generalSalesTotalReport()
    {
        $groupBy = request('group_by', 'day');
        $fromDate = request('from_date');
        $toDate = request('to_date');

        $query = OperHead::where('pro_type', 10)
            ->join('operation_items', 'operhead.id', '=', 'operation_items.pro_id');

        if ($fromDate) {
            $query->whereDate('operhead.pro_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('operhead.pro_date', '<=', $toDate);
        }

        if ($groupBy === 'day') {
            $salesTotals = $query->selectRaw('
                DATE(operhead.pro_date) as period_name,
                COUNT(DISTINCT operhead.id) as invoices_count,
                SUM(operation_items.qty_out) as total_quantity,
                SUM(operation_items.qty_out * operation_items.item_price) as total_sales,
                SUM(operhead.fat_disc) as total_discount,
                SUM(operhead.fat_net) as net_sales
            ')
                ->groupBy('period_name')
                ->orderBy('period_name', 'desc')
                ->paginate(50);
        } elseif ($groupBy === 'month') {
            $salesTotals = $query->selectRaw('
                YEAR(operhead.pro_date) as year,
                MONTH(operhead.pro_date) as month,
                CONCAT(YEAR(operhead.pro_date), "-", LPAD(MONTH(operhead.pro_date), 2, "0")) as period_name,
                COUNT(DISTINCT operhead.id) as invoices_count,
                SUM(operationitems.qty_out) as total_quantity,
                SUM(operationitems.qty_out * operationitems.item_price) as total_sales,
                SUM(operhead.discount) as total_discount,
                SUM(operhead.net_sales) as net_sales
            ')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->paginate(50);
        } else {
            $salesTotals = $query->selectRaw('
                "الإجمالي" as period_name,
                COUNT(DISTINCT operhead.id) as invoices_count,
                SUM(operationitems.qty_out) as total_quantity,
                SUM(operationitems.qty_out * operationitems.item_price) as total_sales,
                SUM(operhead.discount) as total_discount,
                SUM(operhead.net_sales) as net_sales
            ')
                ->paginate(50);
        }

        // أضف متوسط الفاتورة لكل صف
        foreach ($salesTotals as $row) {
            $row->average_invoice = $row->invoices_count > 0
                ? $row->net_sales / $row->invoices_count
                : 0;
        }

        // إجماليات عامة
        $grandTotalInvoices = $salesTotals->sum('invoices_count');
        $grandTotalQuantity = $salesTotals->sum('total_quantity');
        $grandTotalSales = $salesTotals->sum('total_sales');
        $grandTotalDiscount = $salesTotals->sum('total_discount');
        $grandTotalNetSales = $salesTotals->sum('net_sales');
        $grandAverageInvoice = $grandTotalInvoices > 0 ? $grandTotalNetSales / $grandTotalInvoices : 0;

        $totalPeriods = $salesTotals->count();
        $highestSales = $salesTotals->max('net_sales') ?? 0;
        $lowestSales = $salesTotals->min('net_sales') ?? 0;
        $averageSales = $totalPeriods > 0 ? $grandTotalNetSales / $totalPeriods : 0;

        return view('reports::sales.general-sales-total', compact(
            'salesTotals',
            'groupBy',
            'grandTotalInvoices',
            'grandTotalQuantity',
            'grandTotalSales',
            'grandTotalDiscount',
            'grandTotalNetSales',
            'grandAverageInvoice',
            'totalPeriods',
            'highestSales',
            'lowestSales',
            'averageSales'
        ));
    }
}
