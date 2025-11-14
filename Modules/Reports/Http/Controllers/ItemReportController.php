<?php

namespace Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Modules\Accounts\Models\AccHead;
use App\Models\OperationItems;

class ItemReportController extends Controller
{
    public function itemInactiveReport()
    {
        $items = Item::Inactive()->paginate(50);
        return view('reports::items.inactive-items', compact('items'));
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
