<?php

namespace Modules\Reports\Http\Controllers;

use Modules\Accounts\Models\AccHead;
use App\Models\OperHead;
use Illuminate\Http\Request;
use App\Models\OperationItems;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class salesReportController extends Controller
{
    public function generalSalesItemsReport()
    {
        $query = OperationItems::whereHas('operhead', function ($q) {
            $q->where('pro_type', 10); // فواتير المبيعات
        })->with(['item', 'operhead']);

        // الفلترة حسب التاريخ
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

        // جلب البيانات مع التجميع
        $salesItems = $query->selectRaw('
                item_id,
                SUM(qty_out) as total_quantity,
                SUM(qty_out * item_price) as total_sales,
                COUNT(DISTINCT pro_id) as invoices_count
            ')
            ->groupBy('item_id')
            ->with('item')
            ->orderBy('total_sales', 'desc')
            ->paginate(50);

        // حساب الإجماليات
        $allData = OperationItems::whereHas('operhead', function ($q) {
            $q->where('pro_type', 10);

            if (request('from_date')) {
                $q->whereDate('pro_date', '>=', request('from_date'));
            }
            if (request('to_date')) {
                $q->whereDate('pro_date', '<=', request('to_date'));
            }
        })
            ->selectRaw('
            SUM(qty_out) as total_quantity,
            SUM(qty_out * item_price) as total_sales,
            COUNT(DISTINCT item_id) as total_items
        ')
            ->first();

        $totalQuantity = $allData->total_quantity ?? 0;
        $totalSales = $allData->total_sales ?? 0;
        $averagePrice = $totalQuantity > 0 ? $totalSales / $totalQuantity : 0;
        $totalItems = $allData->total_items ?? 0;

        // أعلى صنف
        $topSellingItem = $salesItems->first()
            ? $salesItems->first()->item->name
            : '---';

        $averageQuantityPerItem = $totalItems > 0 ? $totalQuantity / $totalItems : 0;
        $averageSalesPerItem = $totalItems > 0 ? $totalSales / $totalItems : 0;

        return view('reports::sales.general-sales-items', compact(
            'salesItems',
            'totalQuantity',
            'totalSales',
            'averagePrice',
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

        $query = DB::table('operhead')
            ->join('operation_items', 'operhead.id', '=', 'operation_items.pro_id')
            ->where('operhead.pro_type', 10);

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
                SUM(operation_items.qty_out) as total_quantity,
                SUM(operation_items.qty_out * operation_items.item_price) as total_sales,
                SUM(operhead.fat_disc) as total_discount,
                SUM(operhead.fat_net) as net_sales
            ')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->paginate(50);
        } else {
            $salesTotals = $query->selectRaw('
                "الإجمالي" as period_name,
                COUNT(DISTINCT operhead.id) as invoices_count,
                SUM(operation_items.qty_out) as total_quantity,
                SUM(operation_items.qty_out * operation_items.item_price) as total_sales,
                SUM(operhead.fat_disc) as total_discount,
                SUM(operhead.fat_net) as net_sales
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

    // أضف هذه الدالة في salesReportController

    public function salesByRepresentativeReport()
    {
        $fromDate = request('from_date');
        $toDate = request('to_date');
        $representativeId = request('representative_id');

        // Query أساسي
        $query = OperHead::where('pro_type', 10) // فواتير المبيعات
            ->with(['employee', 'operationItems']);

        // فلتر التاريخ
        if ($fromDate) {
            $query->whereDate('pro_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('pro_date', '<=', $toDate);
        }

        // فلتر المندوب
        if ($representativeId) {
            $query->where('emp_id', $representativeId);
        }

        // جلب البيانات مع التجميع
        $salesByRep = $query->selectRaw('
            emp_id,
            COUNT(DISTINCT id) as invoices_count,
            SUM(fat_total) as total_sales,
            SUM(fat_disc) as total_discount,
            SUM(fat_net) as net_sales
        ')
            ->groupBy('emp_id')
            ->orderBy('net_sales', 'desc')
            ->paginate(50);

        // حساب الإجماليات الكلية
        $grandTotals = OperHead::where('pro_type', 10);

        if ($fromDate) {
            $grandTotals->whereDate('pro_date', '>=', $fromDate);
        }
        if ($toDate) {
            $grandTotals->whereDate('pro_date', '<=', $toDate);
        }
        if ($representativeId) {
            $grandTotals->where('emp_id', $representativeId);
        }

        $grandTotals = $grandTotals->selectRaw('
            COUNT(DISTINCT id) as total_invoices,
            SUM(fat_total) as grand_total_sales,
            SUM(fat_disc) as grand_total_discount,
            SUM(fat_net) as grand_net_sales
        ')
            ->first();

        // حساب المتوسطات والإحصائيات
        $totalInvoices = $grandTotals->total_invoices ?? 0;
        $grandTotalSales = $grandTotals->grand_total_sales ?? 0;
        $grandTotalDiscount = $grandTotals->grand_total_discount ?? 0;
        $grandNetSales = $grandTotals->grand_net_sales ?? 0;

        $totalReps = $salesByRep->total();
        $averageSalesPerRep = $totalReps > 0 ? $grandNetSales / $totalReps : 0;
        $averageInvoicesPerRep = $totalReps > 0 ? $totalInvoices / $totalReps : 0;
        $averageInvoiceValue = $totalInvoices > 0 ? $grandNetSales / $totalInvoices : 0;

        // أعلى وأقل مندوب
        $topRep = $salesByRep->first();
        $topRepName = $topRep && $topRep->representative ? $topRep->representative->name : '---';
        $topRepSales = $topRep ? $topRep->net_sales : 0;

        // جلب قائمة المندوبين للفلتر
        $representatives = DB::table('acc_head')
            ->where('code', '210201') // أو الشرط المناسب للمندوبين
            ->select('id', 'aname')
            ->orderBy('aname')
            ->get();

        return view('reports::sales.sales-by-representative', compact(
            'salesByRep',
            'representatives',
            'totalInvoices',
            'grandTotalSales',
            'grandTotalDiscount',
            'grandNetSales',
            'totalReps',
            'averageSalesPerRep',
            'averageInvoicesPerRep',
            'averageInvoiceValue',
            'topRepName',
            'topRepSales'
        ));
    }
    public function daily(Request $request)
    {
        $customers = AccHead::where('code', 'like', '1103%')
            ->where('isdeleted', 0)
            ->orderBy('aname')
            ->get();

        $fromDate = $request->input('from_date', today()->format('Y-m-d'));
        $toDate = $request->input('to_date', today()->format('Y-m-d'));
        $customerId = $request->input('customer_id');

        // جلب الفواتير مع الأصناف
        $sales = OperHead::where('pro_type', 10)
            ->with(['acc1Head', 'operationItems'])
            ->whereDate('pro_date', '>=', $fromDate)
            ->whereDate('pro_date', '<=', $toDate)
            ->when($customerId, fn($q) => $q->where('acc1', $customerId))
            ->orderBy('pro_date', 'desc')
            ->orderBy('pro_num', 'desc')
            ->paginate(50)
            ->appends($request->query());

        // === الحسابات من الفواتير (OperHead) ===
        $totalSales = $sales->sum('fat_total');
        $totalDiscount = $sales->sum('fat_disc');
        $totalNetSales = $sales->sum('fat_net');
        $totalInvoices = $sales->count();
        $averageInvoiceValue = $totalInvoices > 0 ? $totalNetSales / $totalInvoices : 0;

        // === الحسابات من الأصناف (OperationItems) ===
        $totalQuantity = 0;
        $totalItemsCount = 0;

        foreach ($sales as $sale) {
            if ($sale->items) {
                $totalQuantity += $sale->items->sum('fat_quantity'); // الكمية الفعلية
                $totalItemsCount += $sale->items->count();
            }
        }

        return view('reports.sales.daily', compact(
            'customers',
            'sales',
            'fromDate',
            'toDate',
            'customerId',
            'totalQuantity',
            'totalSales',
            'totalDiscount',
            'totalNetSales',
            'totalInvoices',
            'averageInvoiceValue',
            'totalItemsCount'
        ));
    }
}
