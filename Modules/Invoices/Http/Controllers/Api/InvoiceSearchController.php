<?php

declare(strict_types=1);

namespace Modules\Invoices\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Item;
use Illuminate\Support\Facades\Cache;

class InvoiceSearchController extends Controller
{
    /**
     * Search for items specifically for the invoice interface.
     * Uses prefix search for performance and caches results.
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Speed up the response by releasing the session lock immediately
        if (session_id()) session_write_close();

        $term = trim((string)$request->input('q', ''));
        
        if (strlen($term) < 1) {
            return response()->json([]);
        }

        $cacheKey = 'inv_search_v3_' . md5($term);

        $items = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($term) {
            return Item::query()
                ->select(['id', 'name', 'code'])
                ->where('isdeleted', 0)
                ->where('name', 'LIKE', '%' . $term . '%') // البحث في الاسم فقط
                ->with(['barcodes' => function($q) { $q->select(['id', 'item_id', 'barcode']); }])
                ->with(['prices' => function($q) { 
                    $q->where('item_prices.price_id', 1)->select(['item_prices.item_id', 'item_prices.price']); 
                }])
                ->limit(15)
                ->get()
                ->map(function($item) {
                    $barcode = $item->barcodes->first()?->barcode ?? '';
                    $price = $item->prices->first()?->pivot?->price ?? 0;
                    
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'code' => $item->code,
                        'barcode' => $barcode,
                        'sale_price' => $price,
                        'quantity' => 0,
                    ];
                });
        });

        return response()->json($items);
    }
}