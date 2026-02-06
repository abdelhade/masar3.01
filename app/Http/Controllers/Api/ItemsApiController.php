<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Enums\ItemType;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ItemsApiController extends Controller
{
    /**
     * Get lightweight list of items for client-side search.
     * Caches results for 1 hour based on filters.
     */
    public function lite(Request $request)
    {
        $term = trim((string)$request->input('term', ''));
        
        if (strlen($term) < 1) return response()->json([]);

        $cacheKey = 'items_lite_v5_' . md5($term);

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($term) {
            $query = Item::query()->select(['id', 'name'])->where('isdeleted', 0);

            $query->where(function($q) use ($term) {
                // Always search in name (substring)
                $q->where('name', 'LIKE', '%' . $term . '%');
                
                // If numeric, also check for exact Code or Barcode match
                if (is_numeric($term)) {
                    $q->orWhere('code', $term)
                      ->orWhereHas('barcodes', function($bq) use ($term) {
                          $bq->where('barcode', $term);
                      });
                }
            });

            return $query->limit(10)->get();
        });
    }

    /**
     * Get full details for a single item via AJAX on selection.
     */
    public function details($id)
    {
        $item = Item::with(['barcodes:id,item_id,barcode', 'units:id,name', 'prices'])->find($id);

        if (!$item || $item->isdeleted) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        // Map data to match the JS expected format
        $priceRecord = $item->prices->where('pivot.price_id', 1)->first() ?? $item->prices->first();
        $price = $priceRecord ? ($priceRecord->pivot->price ?? 0) : 0;

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'barcodes' => $item->barcodes->pluck('barcode')->toArray(),
                'price' => $price,
                'average_cost' => $item->average_cost,
                'units' => $item->units->map(function($u) {
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'u_val' => $u->pivot->u_val ?? 1,
                        'cost' => $u->pivot->cost ?? 0,
                    ];
                }),
            ]
        ]);
    }
}
