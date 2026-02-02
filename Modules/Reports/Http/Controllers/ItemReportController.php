<?php

namespace Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Modules\Accounts\Models\AccHead;
use App\Models\OperationItems;

class ItemReportController extends Controller
{
    /**
     * تقرير المنتجات الراكدة: أصناف لها رصيد ولم يحدث لها حركة مبيعات خلال الفترة.
     */
    public function idleItemsReport()
    {
        $days = (int) request('days', 90);
        $days = $days > 0 ? min($days, 365) : 90;
        $fromDate = now()->subDays($days)->format('Y-m-d');

        // أصناف لها حركة مبيعات (خروج) خلال الفترة — pro_type 10 فاتورة مبيعات، 102 كاشير
        $itemIdsWithSales = OperationItems::query()
            ->join('operhead', 'operation_items.pro_id', '=', 'operhead.id')
            ->whereIn('operhead.pro_type', [10, 102])
            ->where('operhead.pro_date', '>=', $fromDate)
            ->where('operhead.isdeleted', 0)
            ->where('operation_items.isdeleted', 0)
            ->where('operation_items.qty_out', '>', 0)
            ->distinct()
            ->pluck('operation_items.item_id')
            ->filter()
            ->values()
            ->toArray();

        // رصيد كل صنف (إجمالي qty_in - qty_out)
        $balances = OperationItems::query()
            ->where('isdeleted', 0)
            ->selectRaw('item_id, SUM(qty_in - qty_out) as balance, SUM((qty_in - qty_out) * cost_price) as balance_value')
            ->groupBy('item_id')
            ->having('balance', '>', 0)
            ->get()
            ->keyBy('item_id');

        // المنتجات الراكدة = لها رصيد > 0 وليست ضمن الأصناف التي لها مبيعات في الفترة
        $idleItemIds = $balances->keys()->diff($itemIdsWithSales)->values()->all();

        if (empty($idleItemIds)) {
            $items = Item::query()->whereRaw('1 = 0')->paginate(50)->withQueryString();
            $balances = collect();
        } else {
            $items = Item::query()
                ->whereIn('id', $idleItemIds)
                ->orderBy('name')
                ->paginate(50)
                ->withQueryString();
        }
        $balances = $balances->keyBy('item_id');

        return view('reports::items.idle-items', compact('items', 'days', 'balances'));
    }

    public function itemInactiveReport()
    {
        $items = Item::Inactive()->paginate(50);
        return view('reports::items.inactive-items', compact('items'));
    }

    /**
     * تقرير المنتجات الأكثر تكلفة: أصناف مرتبة حسب التكلفة المتوسطة (الأعلى أولاً).
     */
    public function mostExpensiveItemsReport()
    {
        $limit = (int) request('limit', 50);
        $limit = $limit > 0 ? min($limit, 500) : 50;

        $items = Item::query()
            ->where('isdeleted', 0)
            ->orderByDesc('average_cost')
            ->paginate($limit)
            ->withQueryString();

        $balances = OperationItems::query()
            ->where('isdeleted', 0)
            ->selectRaw('item_id, SUM(qty_in - qty_out) as balance, SUM((qty_in - qty_out) * cost_price) as balance_value')
            ->groupBy('item_id')
            ->get()
            ->keyBy('item_id');

        return view('reports::items.most-expensive-items', compact('items', 'limit', 'balances'));
    }

    public function itemsWithStoresReport()
    {
        $stores = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '1104%')
            ->select('id', 'aname')
            ->get();

        $balances = OperationItems::selectRaw('item_id, detail_store, SUM(qty_in - qty_out) as balance')
            ->where('isdeleted', 0)
            ->groupBy('item_id', 'detail_store')
            ->get()
            ->groupBy('item_id');
        $items = Item::paginate(50);
        return view('reports::items.items-stores-stock', compact('stores', 'items', 'balances'));
    }
}
