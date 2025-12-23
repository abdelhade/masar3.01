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
        // 1. Extract inputs & validation
        $branchId = $request->input('branch_id');
        $invoiceType = $request->input('type');
        // $limit = $request->input('limit', 2000); // Optional safety limit

        // 2. Generate Cache Key
        $cacheKey = sprintf(
            'items_lite_v2_%s_%s', // ✅ Version bump to force fresh cache (User cancelled cache:clear)
             $branchId ?? 'all',
             $invoiceType ?? 'all'
        );

        // 3. Fetch Data (Cached)
        // ✅ Reduced cache time to 5 seconds to ensure new items appear quickly
        return Cache::remember($cacheKey, 5, function () use ($branchId, $invoiceType) {
            try {
                $query = Item::query()
                    ->select([
                        'items.id',
                        'items.name',
                        'items.code',
                        // 'items.price', // ❌ Removed: Column does not exist
                    ])
                    ->where('items.isdeleted', 0)
                    ->where('items.is_active', 1);

                // Apply Branch Filter
                if ($branchId) {
                    $query->where(function ($q) use ($branchId) {
                        $q->where('items.branch_id', $branchId)
                          ->orWhereNull('items.branch_id');
                    });
                }

                // Apply Invoice Type Filter
                if (in_array($invoiceType, [11, 13, 15, 17])) {
                    $query->where('items.type', ItemType::Inventory->value);
                } 
                elseif ($invoiceType == 24) {
                    $query->where('items.type', ItemType::Service->value);
                }

                $items = $query->get();

                // Load relationships: prices (default price_id 1), barcodes, units
                $items->load(['barcodes:id,item_id,barcode', 'units:id,name', 'prices']);

                return $items->map(function ($item) {
                    // Try to find default price (id 1)
                    $price = $item->prices->where('price_id', 1)->first()->price ?? 0;
                    
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'code' => $item->code,
                        'barcode' => $item->barcodes->pluck('barcode')->toArray(),
                        'price' => $price, 
                        'unit_name' => $item->units->first()->name ?? '',
                    ];
                });
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('ItemsApiController Error: ' . $e->getMessage());
                throw $e;
            }
        });
    }
}
