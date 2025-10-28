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
            ])
            ->when($search, function ($query) use ($search) {
                $query
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%')
                    ->orWhereHas('barcodes', function ($q) use ($search) {
                        $q->where('barcode', 'like', '%' . $search . '%');
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
     * Calculates actual inventory: qty_in - qty_out
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

        $totalIn = (clone $query)->sum('qty_in');
        $totalOut = (clone $query)->sum('qty_out');

        return $totalIn - $totalOut;
    }

    /**
     * Get total amount using SQL aggregation
     * Calculates: (qty_in - qty_out) * price for each item
     */
    public function getTotalAmount(string $search = '', ?int $selectedGroup = null, ?int $selectedCategory = null, string $priceType = 'average_cost', ?int $warehouseId = null): float
    {
        $itemIds = $this->buildFilteredQuery($search, $selectedGroup, $selectedCategory)
            ->pluck('id');

        if ($itemIds->isEmpty()) {
            return 0;
        }

        // Get quantities per item
        $quantities = DB::table('operation_items')
            ->select('item_id', 
                DB::raw('SUM(qty_in) as total_in'),
                DB::raw('SUM(qty_out) as total_out'),
                DB::raw('SUM(qty_in) - SUM(qty_out) as net_quantity')
            )
            ->whereIn('item_id', $itemIds)
            ->where('isdeleted', 0)
            ->when($warehouseId, fn($q) => $q->where('detail_store', $warehouseId))
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
            // Use last cost from operation_items
            return DB::table('operation_items')
                ->select('item_id', DB::raw('MAX(cost_price) as last_cost'))
                ->whereIn('item_id', $itemIds)
                ->where('isdeleted', 0)
                ->when($warehouseId, fn($q) => $q->where('detail_store', $warehouseId))
                ->groupBy('item_id')
                ->get()
                ->sum(function ($item) use ($quantities) {
                    $qty = $quantities[$item->item_id]->net_quantity ?? 0;
                    return $qty * $item->last_cost;
                });
        } else {
            // Price from item_prices table
            return DB::table('item_prices')
                ->join('items', 'item_prices.item_id', '=', 'items.id')
                ->whereIn('item_prices.item_id', $itemIds)
                ->where('item_prices.price_id', $priceType)
                ->get()
                ->sum(function ($item) use ($quantities) {
                    $qty = $quantities[$item->item_id]->net_quantity ?? 0;
                    return $qty * $item->price;
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

        // Count items that have actual stock (qty_in - qty_out > 0)
        return DB::table('operation_items')
            ->select('item_id')
            ->whereIn('item_id', $itemIds)
            ->where('isdeleted', 0)
            ->when($warehouseId, fn($q) => $q->where('detail_store', $warehouseId))
            ->groupBy('item_id')
            ->havingRaw('SUM(qty_in) - SUM(qty_out) > 0')
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
            ->join('item_units as iu', function($join) {
                $join->on('iu.item_id', '=', 'oi.item_id')
                     ->on('iu.unit_id', '=', 'oi.unit_id');
            })
            ->where('oi.isdeleted', 0)
            ->whereIn('oi.item_id', $itemIds)
            ->select([
                'oi.item_id',
                DB::raw('SUM((oi.qty_in - oi.qty_out) * iu.u_val) as base_qty')
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
