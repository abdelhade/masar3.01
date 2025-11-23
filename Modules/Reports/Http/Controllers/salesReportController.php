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
        $fromDate = request('from_date') ?? today()->format('Y-m-d');
        $toDate = request('to_date') ?? today()->format('Y-m-d');
        if ($fromDate) {
            $query->whereHas('operhead', function ($q) use ($fromDate) {
                $q->whereDate('pro_date', '>=', $fromDate);
            });
        }
        if ($toDate) {
            $query->whereHas('operhead', function ($q) use ($toDate) {
                $q->whereDate('pro_date', '<=', $toDate);
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
        $allData = OperationItems::whereHas('operhead', function ($q) use ($fromDate, $toDate) {
            $q->where('pro_type', 10);

            if ($fromDate) {
                $q->whereDate('pro_date', '>=', $fromDate);
            }
            if ($toDate) {
                $q->whereDate('pro_date', '<=', $toDate);
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
            'fromDate',
            'toDate',
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
        return view('reports::sales.general-sales-total');
    }

    // أضف هذه الدالة في salesReportController

    public function salesByRepresentativeReport()
    {
        $fromDate = request('from_date') ?? today()->format('Y-m-d');
        $toDate = request('to_date') ?? today()->format('Y-m-d');
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
            'fromDate',
            'toDate',
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
    // public function daily(Request $request)
    // {
    //     $customers = AccHead::where('code', 'like', '1103%')
    //         ->where('isdeleted', 0)
    //         ->orderBy('aname')
    //         ->get();

    //     $fromDate = $request->input('from_date', today()->format('Y-m-d'));
    //     $toDate = $request->input('to_date', today()->format('Y-m-d'));
    //     $customerId = $request->input('customer_id');

    //     // جلب الفواتير مع الأصناف
    //     $sales = OperHead::where('pro_type', 10)
    //         ->with(['acc1Head', 'operationItems'])
    //         ->whereDate('pro_date', '>=', $fromDate)
    //         ->whereDate('pro_date', '<=', $toDate)
    //         ->when($customerId, fn($q) => $q->where('acc1', $customerId))
    //         ->orderBy('pro_date', 'desc')
    //         ->orderBy('pro_num', 'desc')
    //         ->paginate(50)
    //         ->appends($request->query());

    //     // === الحسابات من الفواتير (OperHead) ===
    //     $totalSales = $sales->sum('fat_total');
    //     $totalDiscount = $sales->sum('fat_disc');
    //     $totalNetSales = $sales->sum('fat_net');
    //     $totalInvoices = $sales->count();
    //     $averageInvoiceValue = $totalInvoices > 0 ? $totalNetSales / $totalInvoices : 0;

    //     // === الحسابات من الأصناف (OperationItems) ===
    //     $totalQuantity = 0;
    //     $totalItemsCount = 0;

    //     foreach ($sales as $sale) {
    //         if ($sale->items) {
    //             $totalQuantity += $sale->items->sum('fat_quantity'); // الكمية الفعلية
    //             $totalItemsCount += $sale->items->count();
    //         }
    //     }

    //     return view('reports::sales.daily', compact(
    //         'customers',
    //         'sales',
    //         'fromDate',
    //         'toDate',
    //         'customerId',
    //         'totalQuantity',
    //         'totalSales',
    //         'totalDiscount',
    //         'totalNetSales',
    //         'totalInvoices',
    //         'averageInvoiceValue',
    //         'totalItemsCount'
    //     ));
    // }

    // public function generalSalesDailyReport(Request $request)
    // {
    //     $customers = AccHead::where('code', 'like', '1103%')
    //         ->where('isdeleted', 0)
    //         ->orderBy('aname')
    //         ->get();

    //     $fromDate = $request->input('from_date', today()->format('Y-m-d'));
    //     $toDate = $request->input('to_date', today()->format('Y-m-d'));
    //     $customerId = $request->input('customer_id');

    //     // جلب الفواتير مع الأصناف
    //     $query = OperHead::where('pro_type', 10)
    //         ->with(['acc1Head', 'operationItems'])
    //         ->whereDate('pro_date', '>=', $fromDate)
    //         ->whereDate('pro_date', '<=', $toDate)
    //         ->when($customerId, fn($q) => $q->where('acc1', $customerId));

    //     // حساب عدد الأصناف لكل فاتورة
    //     $sales = $query->get()->map(function ($sale) {
    //         $sale->items_count = $sale->operationItems->count() ?? 0;
    //         $sale->total_quantity = $sale->operationItems->sum('qty_out') ?? 0;
    //         $sale->total_sales = $sale->fat_total ?? 0;
    //         $sale->net_sales = $sale->fat_net ?? 0;
    //         $sale->status = $sale->isdeleted == 0 ? 'completed' : 'pending';
    //         return $sale;
    //     });

    //     // حساب الإجماليات من جميع البيانات (قبل pagination)
    //     $totalQuantity = $sales->sum('total_quantity');
    //     $totalSalesAmount = $sales->sum('total_sales');
    //     $totalDiscount = $sales->sum('fat_disc') ?? 0;
    //     $totalNetSales = $sales->sum('net_sales');
    //     $totalInvoices = $sales->count();
    //     $averageInvoiceValue = $totalInvoices > 0 ? $totalNetSales / $totalInvoices : 0;

    //     // Pagination manual
    //     $perPage = 50;
    //     $currentPage = $request->input('page', 1);
    //     $items = $sales->slice(($currentPage - 1) * $perPage, $perPage)->values();
    //     $paginatedSales = new \Illuminate\Pagination\LengthAwarePaginator(
    //         $items,
    //         $sales->count(),
    //         $perPage,
    //         $currentPage,
    //         ['path' => $request->url(), 'query' => $request->query()]
    //     );

    //     return view('reports::sales.general-sales-daily-report', [
    //         'customers' => $customers,
    //         'sales' => $paginatedSales,
    //         'fromDate' => $fromDate,
    //         'toDate' => $toDate,
    //         'customerId' => $customerId,
    //         'totalQuantity' => $totalQuantity,
    //         'totalSales' => $totalSalesAmount,
    //         'totalDiscount' => $totalDiscount,
    //         'totalNetSales' => $totalNetSales,
    //         'totalInvoices' => $totalInvoices,
    //         'averageInvoiceValue' => $averageInvoiceValue
    //     ]);
    // }

    public function generalSalesReport()
    {
        return view('reports::sales.general-sales-report');
    }

    public function salesReportByAddress()
    {
        return view('reports::sales.manage-sales-report-by-adress');
    }

    public function manageItemSales()
    {
        return view('reports::sales.manage-item-sales');
    }
}
