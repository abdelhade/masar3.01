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
     * Caches results for 30 minutes based on filters.
     * If no term provided, returns all items (for client-side fuzzy search).
     */
    public function lite(Request $request)
    {
        $term = trim((string)$request->input('term', ''));
        $branchId = $request->input('branch_id');
        $type = $request->input('type');

        // ✅ Build cache key with version
        $cacheVersion = Cache::get('items_cache_version', 1);
        $cacheKey = 'items_lite_v' . $cacheVersion . '_' . md5($term . '_' . $branchId . '_' . $type);

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($term, $branchId, $type) {
            $query = Item::query()
                ->select(['id', 'name', 'code'])
                ->with(['barcodes:id,item_id,barcode'])
                ->where('isdeleted', 0);

            // Filter by branch if provided
            if ($branchId) {
                $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            }

            // If term provided, search for it
            if (strlen($term) >= 1) {
                $query->where(function($q) use ($term) {
                    // Search in name (substring)
                    $q->where('name', 'LIKE', '%' . $term . '%');

                    // If numeric, also check for exact Code or Barcode match
                    if (is_numeric($term)) {
                        $q->orWhere('code', $term)
                          ->orWhereHas('barcodes', function($bq) use ($term) {
                              $bq->where('barcode', $term);
                          });
                    }
                });

                $items = $query->limit(20)->get();
            } else {
                // No term: return items for client-side fuzzy search (limited to 500)
                $items = $query->limit(500)->get();
            }

            // Format items with barcode for client-side search
            return $items->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'barcode' => $item->barcodes->first()->barcode ?? null,
                ];
            });
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
