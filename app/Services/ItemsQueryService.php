<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ItemsQueryService
{
    /**
     * Build base query with filters applied
     */
    public function buildFilteredQuery(string $search = '', ?int $selectedGroup = null, ?int $selectedCategory = null): Builder
    {
        return Item::select(['id', 'name', 'code', 'info', 'average_cost', 'min_order_quantity', 'max_order_quantity', 'created_at', 'updated_at'])
            ->with([
                'units' => function ($query) {
                    $query->select('units.id', 'units.name')
                        ->orderBy('item_units.u_val');
                },
                'prices' => function ($query) {
                    $query->select('prices.id', 'prices.name');
                },
                'barcodes' => function ($query) {
                    $query->select('id', 'item_id', 'unit_id', 'barcode');
                },
                'notes' => function ($query) {
                    $query->select('notes.id', 'notes.name');
                },
                'media' => function ($query) {
                    $query->where('collection_name', 'item-thumbnail');
                },
            ])
            ->when($search, function ($query) use ($search) {
                $query
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('code', 'like', '%'.$search.'%')
                    ->orWhereHas('barcodes', function ($q) use ($search) {
                        $q->where('barcode', 'like', '%'.$search.'%');
                    });
            })
            ->when($selectedGroup, function ($query) use ($selectedGroup) {
                $query->whereHas('notes', function ($q) use ($selectedGroup) {
                    $q->where('note_id', 1) // Groups have note_id = 1
                        ->where('note_detail_name', function ($subQuery) use ($selectedGroup) {
                            $subQuery->select('name')->from('note_details')->where('id', $selectedGroup);
                        });
                });
            })
            ->when($selectedCategory, function ($query) use ($selectedCategory) {
                $query->whereHas('notes', function ($q) use ($selectedCategory) {
                    $q->where('note_id', 2) // Categories have note_id = 2
                        ->where('note_detail_name', function ($subQuery) use ($selectedCategory) {
                            $subQuery->select('name')->from('note_details')->where('id', $selectedCategory);
                        });
                });
            });

    }

    /**
     * Get total quantity using SQL aggregation from operation_items
     * Calculates actual inventory: (qty_in - qty_out) * unit_value for each row
     */
    public function getTotalQuantity(string $search = '', ?int $selectedGroup = null, ?int $selectedCategory = null, ?int $warehouseId = null): float
    {
        $itemIds = $this->buildFilteredQuery($search, $selectedGroup, $selectedCategory)
            ->pluck('id');

        if ($itemIds->isEmpty()) {
            return 0;
        }

        $query = DB::table('operation_items')
            ->whereIn('item_id', $itemIds)
            ->where('isdeleted', 0);

        // Filter by warehouse if specified
        if ($warehouseId) {
            $query->where('detail_store', $warehouseId);
        }

        return (float) $query->sum(DB::raw('(qty_in - qty_out) * unit_value'));
    }

    /**
     * Get total amount using SQL aggregation
     * Calculates: (qty_in - qty_out) * unit_value * price for each item
     */
    public function getTotalAmount(string $search = '', ?int $selectedGroup = null, ?int $selectedCategory = null, string $priceType = 'average_cost', ?int $warehouseId = null): float
    {
        $itemIds = $this->buildFilteredQuery($search, $selectedGroup, $selectedCategory)
            ->pluck('id');

        if ($itemIds->isEmpty()) {
            return 0;
        }

        // Get actual quantities in base units per item
        $quantities = DB::table('operation_items')
            ->select('item_id',
                DB::raw('SUM((qty_in - qty_out) * unit_value) as net_quantity')
            )
            ->whereIn('item_id', $itemIds)
            ->where('isdeleted', 0)
            ->when($warehouseId, fn ($q) => $q->where('detail_store', $warehouseId))
            ->groupBy('item_id')
            ->get()
            ->keyBy('item_id');

        if ($quantities->isEmpty()) {
            return 0;
        }

        // Get prices based on type
        if ($priceType === 'average_cost') {
            return Item::whereIn('id', $itemIds)
                ->get()
                ->sum(function ($item) use ($quantities) {
                    $qty = $quantities[$item->id]->net_quantity ?? 0;

                    return $qty * $item->average_cost;
                });
        } elseif ($priceType === 'cost') {
            // Get the ID of the latest purchase invoice for each item (last item cost from purchase invoice)
            $latestPurchaseIds = DB::table('operation_items')
                ->select('item_id', DB::raw('MAX(id) as max_id'))
                ->whereIn('item_id', $itemIds)
                ->where('isdeleted', 0)
                ->where('pro_tybe', 11) // 11 = Purchase Invoice
                ->when($warehouseId, fn ($q) => $q->where('detail_store', $warehouseId))
                ->groupBy('item_id');

            // Join with operation_items to get the cost_price from those latest IDs
            return DB::table('operation_items as oi')
                ->joinSub($latestPurchaseIds, 'latest', function ($join) {
                    $join->on('oi.id', '=', 'latest.max_id');
                })
                ->get()
                ->sum(function ($item) use ($quantities) {
                    $qty = $quantities[$item->item_id]->net_quantity ?? 0;

                    return $qty * $item->cost_price;
                });
        } else {
            // Price from item_prices table (it's per specified unit in that table, we need to match it carefully)
            // For now, assume it's per the base unit if not specified otherwise, 
            // but usually item_prices are per specific units. 
            // In the context of "index.blade.php", this priceType is the price_id from the prices table.
            
            return DB::table('item_prices')
                ->join('item_units', function($join) {
                    $join->on('item_prices.item_id', '=', 'item_units.item_id')
                         ->on('item_prices.unit_id', '=', 'item_units.unit_id');
                })
                ->whereIn('item_prices.item_id', $itemIds)
                ->where('item_prices.price_id', (int) $priceType)
                ->get()
                ->sum(function ($item) use ($quantities) {
                    $baseQty = $quantities[$item->item_id]->net_quantity ?? 0;
                    // convert base quantity to the unit specified for this price
                    $unitQty = $item->u_val > 0 ? $baseQty / $item->u_val : 0;

                    return $unitQty * $item->price;
                });
        }
    }

    /**
     * Get total items count (items with actual stock)
     */
    public function getTotalItems(string $search = '', ?int $selectedGroup = null, ?int $selectedCategory = null, ?int $warehouseId = null): int
    {
        $itemIds = $this->buildFilteredQuery($search, $selectedGroup, $selectedCategory)
            ->pluck('id');

        if ($itemIds->isEmpty()) {
            return 0;
        }

        // Count items that have actual stock ((qty_in - qty_out) * unit_value > 0)
        return DB::table('operation_items')
            ->select('item_id')
            ->whereIn('item_id', $itemIds)
            ->where('isdeleted', 0)
            ->when($warehouseId, fn ($q) => $q->where('detail_store', $warehouseId))
            ->groupBy('item_id')
            ->havingRaw('SUM((qty_in - qty_out) * unit_value) > 0')
            ->get()
            ->count();
    }

    /**
     * Get base quantities for multiple items in one query
     * Returns array of [item_id => base_quantity]
     */
    public function getBaseQuantitiesForItems(array $itemIds, ?int $warehouseId = null): array
    {
        if (empty($itemIds)) {
            return [];
        }

        $query = DB::table('operation_items as oi')
            ->where('oi.isdeleted', 0)
            ->whereIn('oi.item_id', $itemIds)
            ->select([
                'oi.item_id',
                DB::raw('SUM((oi.qty_in - oi.qty_out) * oi.unit_value) as base_qty'),
            ])
            ->groupBy('oi.item_id');

        // Filter by warehouse if specified
        if ($warehouseId) {
            $query->where('oi.detail_store', $warehouseId);
        }

        $results = $query->get();

        return $results->pluck('base_qty', 'item_id')->toArray();
    }
}

