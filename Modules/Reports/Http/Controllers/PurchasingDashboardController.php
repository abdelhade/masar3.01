<?php

declare(strict_types=1);

namespace Modules\Reports\Http\Controllers;

use Carbon\Carbon;
use App\Models\OperHead;
use App\Models\OperationItems;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Models\AccHead;
use Illuminate\Support\Facades\Schema;

class PurchasingDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Purchase Invoice');
    }

    /**
     * لوحة تحكم المشتريات: طلبات متأخرة، أفضل 5 موردين، متوسط أسعار 6 أشهر.
     */
    public function index()
    {
        $delayedOrders = $this->getDelayedOrders();
        $topSuppliersOnTime = $this->getTopSuppliersOnTime(5, now()->subMonths(6), now());
        $averagePricePerProduct = $this->getAveragePricePerProductLastMonths(6);
        $suppliersList = AccHead::where('code', 'like', '2101%')->where('isdeleted', 0)->orderBy('aname')->get();

        return view('reports::purchasing.dashboard', compact(
            'delayedOrders',
            'topSuppliersOnTime',
            'averagePricePerProduct',
            'suppliersList'
        ));
    }

    /**
     * أوامر شراء متأخرة: pro_type=15، تاريخ الاستلام المتوقع مضى، ولم تُحوَّل بعد لفاتورة.
     */
    protected function getDelayedOrders()
    {
        return OperHead::with('acc1Head')
            ->where('pro_type', 15)
            ->where('isdeleted', 0)
            ->whereNotNull('expected_delivery_date')
            ->whereDate('expected_delivery_date', '<', today())
            ->when(Schema::hasColumn('operhead', 'workflow_state'), function ($q) {
                $q->where('workflow_state', 3); // 3 = أمر شراء (لم يُحوَّل لفاتورة)
            })
            ->orderBy('expected_delivery_date')
            ->limit(50)
            ->get();
    }

    /**
     * أفضل N موردين في الالتزام بالوقت خلال الفترة.
     * المعيار: فواتير مشتريات (11) لها parent أمر شراء (15)؛ مقارنة تاريخ الفاتورة مع expected_delivery_date للأمر.
     */
    protected function getTopSuppliersOnTime(int $limit, Carbon $from, Carbon $to)
    {
        $invoices = DB::table('operhead as inv')
            ->join('operhead as po', function ($join) {
                $join->on(DB::raw('COALESCE(inv.parent_id, inv.origin_id)'), '=', 'po.id')
                    ->whereNotNull('po.expected_delivery_date');
            })
            ->where('inv.pro_type', 11)
            ->where('inv.isdeleted', 0)
            ->where(function ($q) {
                $q->whereNotNull('inv.parent_id')->orWhereNotNull('inv.origin_id');
            })
            ->whereDate('inv.pro_date', '>=', $from)
            ->whereDate('inv.pro_date', '<=', $to)
            ->select([
                'inv.acc1 as supplier_id',
                DB::raw('COUNT(inv.id) as total_deliveries'),
                DB::raw('SUM(CASE WHEN inv.pro_date <= po.expected_delivery_date THEN 1 ELSE 0 END) as on_time_deliveries'),
            ])
            ->groupBy('inv.acc1')
            ->havingRaw('COUNT(inv.id) > 0')
            ->get();

        $supplierIds = $invoices->pluck('supplier_id')->unique()->filter()->values();
        $suppliers = AccHead::whereIn('id', $supplierIds)->get()->keyBy('id');

        return $invoices
            ->map(function ($row) use ($suppliers) {
                $total = (int) $row->total_deliveries;
                $onTime = (int) $row->on_time_deliveries;
                return (object) [
                    'supplier_id' => $row->supplier_id,
                    'supplier_name' => $suppliers->get($row->supplier_id)?->aname ?? '—',
                    'total_deliveries' => $total,
                    'on_time_deliveries' => $onTime,
                    'on_time_rate' => $total > 0 ? round(($onTime / $total) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('on_time_rate')
            ->take($limit)
            ->values();
    }

    /**
     * متوسط سعر الشراء لكل صنف خلال آخر N أشهر (فواتير مشتريات فقط).
     */
    protected function getAveragePricePerProductLastMonths(int $months)
    {
        $from = now()->subMonths($months)->startOfDay();

        $rows = OperationItems::query()
            ->whereHas('operhead', function ($q) use ($from) {
                $q->where('pro_type', 11)->where('isdeleted', 0)->whereDate('pro_date', '>=', $from);
            })
            ->selectRaw('
                item_id,
                SUM(qty_in) as total_qty,
                SUM(qty_in * item_price) as total_value,
                COUNT(DISTINCT pro_id) as invoices_count
            ')
            ->groupBy('item_id')
            ->orderByDesc('total_value')
            ->limit(100)
            ->get();

        $itemIds = $rows->pluck('item_id')->unique()->filter()->values()->all();
        $items = \App\Models\Item::whereIn('id', $itemIds)->get()->keyBy('id');

        return $rows->map(function ($row) use ($items) {
            $qty = (float) $row->total_qty;
            $avgPrice = $qty > 0 ? (float) $row->total_value / $qty : 0;
            return (object) [
                'item_id' => $row->item_id,
                'item_name' => $items->get($row->item_id)?->name ?? '—',
                'total_qty' => $qty,
                'total_value' => (float) $row->total_value,
                'average_price' => round($avgPrice, 2),
                'invoices_count' => (int) $row->invoices_count,
            ];
        });
    }

    /**
     * صفحة قائمة الطلبات المتأخرة فقط (للتقارير).
     */
    public function delayedOrders()
    {
        $delayedOrders = $this->getDelayedOrders();

        return view('reports::purchasing.delayed-orders', compact('delayedOrders'));
    }
}
