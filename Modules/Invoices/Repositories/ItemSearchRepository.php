<?php

declare(strict_types=1);

namespace Modules\Invoices\Repositories;

use App\Models\Item;
use Illuminate\Support\Facades\DB;

/**
 * Repository for optimized item search operations
 */
class ItemSearchRepository
{
    /**
     * Search items by term (name or barcode)
     *
     * @param string $term
     * @param int|null $branchId
     * @param int $limit
     * @return array
     */
    public function searchItems(string $term, ?int $branchId = null, int $limit = 50): array
    {
        $query = Item::query()
            ->with([
                'units' => function ($query) {
                    $query->select('units.id', 'units.name', 'units.symbol');
                },
                'barcodes' => function ($query) {
                    $query->select('barcodes.id', 'barcodes.item_id', 'barcodes.barcode', 'barcodes.unit_id');
                }
            ])
            ->where('active', 1)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%")
                    ->orWhereHas('barcodes', function ($bq) use ($term) {
                        $bq->where('barcode', 'like', "%{$term}%");
                    });
            })
            ->select('id', 'name', 'code', 'price1', 'price2', 'price3', 'price4', 'price5', 'default_unit_id')
            ->limit($limit);

        $items = $query->get();

        return $items->map(function ($item) use ($branchId) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'default_unit_id' => $item->default_unit_id,
                'units' => $this->getItemUnitsWithPrices($item, $branchId),
                'barcodes' => $item->barcodes->pluck('barcode')->toArray(),
            ];
        })->toArray();
    }

    /**
     * Get item details with pricing and stock information
     *
     * @param int $itemId
     * @param int|null $customerId
     * @param int|null $branchId
     * @return array
     */
    public function getItemDetails(int $itemId, ?int $customerId = null, ?int $branchId = null): array
    {
        $item = Item::with([
            'units' => function ($query) {
                $query->select('units.id', 'units.name', 'units.symbol');
            },
            'barcodes'
        ])->findOrFail($itemId);

        $units = $this->getItemUnitsWithPrices($item, $branchId);
        $lastPrice = $this->getLastPriceForCustomer($itemId, $customerId);
        $pricingAgreement = $this->getPricingAgreement($itemId, $customerId);
        $stockQuantity = $this->getStockQuantity($itemId, $branchId);

        return [
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'default_unit_id' => $item->default_unit_id,
            ],
            'units' => $units,
            'last_price' => $lastPrice,
            'pricing_agreement' => $pricingAgreement,
            'stock_quantity' => $stockQuantity,
            'barcodes' => $item->barcodes->pluck('barcode')->toArray(),
        ];
    }

    /**
     * Get item units with prices and conversion factors
     *
     * @param Item $item
     * @param int|null $branchId
     * @return array
     */
    private function getItemUnitsWithPrices(Item $item, ?int $branchId = null): array
    {
        $units = DB::table('item_units as iu')
            ->join('units as u', 'iu.unit_id', '=', 'u.id')
            ->where('iu.item_id', $item->id)
            ->select(
                'u.id',
                'u.name',
                'u.symbol',
                'iu.conversion_factor',
                'iu.price1',
                'iu.price2',
                'iu.price3',
                'iu.price4',
                'iu.price5',
                'iu.barcode'
            )
            ->get();

        return $units->map(function ($unit) use ($item, $branchId) {
            $stockQty = $this->getStockQuantityForUnit($item->id, $unit->id, $branchId);

            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'symbol' => $unit->symbol,
                'conversion_factor' => (float) $unit->conversion_factor,
                'price1' => (float) $unit->price1,
                'price2' => (float) $unit->price2,
                'price3' => (float) $unit->price3,
                'price4' => (float) $unit->price4,
                'price5' => (float) $unit->price5,
                'barcode' => $unit->barcode,
                'stock_quantity' => $stockQty,
            ];
        })->toArray();
    }

    /**
     * Get last price for customer
     *
     * @param int $itemId
     * @param int|null $customerId
     * @return float|null
     */
    private function getLastPriceForCustomer(int $itemId, ?int $customerId = null): ?float
    {
        if (!$customerId) {
            return null;
        }

        $lastPrice = DB::table('operation_items as oi')
            ->join('oper_head as oh', 'oi.oper_id', '=', 'oh.id')
            ->where('oh.acc1_id', $customerId)
            ->where('oi.item_id', $itemId)
            ->whereIn('oh.type', [10, 12, 14, 16]) // Sales invoice types
            ->orderBy('oh.pro_date', 'desc')
            ->orderBy('oh.id', 'desc')
            ->value('oi.price');

        return $lastPrice ? (float) $lastPrice : null;
    }

    /**
     * Get pricing agreement for customer
     *
     * @param int $itemId
     * @param int|null $customerId
     * @return array|null
     */
    private function getPricingAgreement(int $itemId, ?int $customerId = null): ?array
    {
        if (!$customerId) {
            return null;
        }

        $agreement = DB::table('pricing_agreements')
            ->where('customer_id', $customerId)
            ->where('item_id', $itemId)
            ->where('active', 1)
            ->where(function ($q) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            })
            ->first();

        if (!$agreement) {
            return null;
        }

        return [
            'id' => $agreement->id,
            'price' => (float) $agreement->price,
            'discount_percentage' => (float) ($agreement->discount_percentage ?? 0),
            'valid_until' => $agreement->valid_until,
        ];
    }

    /**
     * Get stock quantity for item
     *
     * @param int $itemId
     * @param int|null $branchId
     * @return float
     */
    private function getStockQuantity(int $itemId, ?int $branchId = null): float
    {
        $query = DB::table('operation_items as oi')
            ->join('oper_head as oh', 'oi.oper_id', '=', 'oh.id')
            ->where('oi.item_id', $itemId);

        if ($branchId) {
            $query->where('oh.branch_id', $branchId);
        }

        $result = $query->selectRaw('
            SUM(CASE
                WHEN oh.type IN (11, 13, 15, 17, 20, 23, 30, 32) THEN oi.quantity
                WHEN oh.type IN (10, 12, 14, 16, 19, 22, 31, 33) THEN -oi.quantity
                ELSE 0
            END) as stock_quantity
        ')->first();

        return (float) ($result->stock_quantity ?? 0);
    }

    /**
     * Get stock quantity for specific unit
     *
     * @param int $itemId
     * @param int $unitId
     * @param int|null $branchId
     * @return float
     */
    private function getStockQuantityForUnit(int $itemId, int $unitId, ?int $branchId = null): float
    {
        $query = DB::table('operation_items as oi')
            ->join('oper_head as oh', 'oi.oper_id', '=', 'oh.id')
            ->where('oi.item_id', $itemId)
            ->where('oi.unit_id', $unitId);

        if ($branchId) {
            $query->where('oh.branch_id', $branchId);
        }

        $result = $query->selectRaw('
            SUM(CASE
                WHEN oh.type IN (11, 13, 15, 17, 20, 23, 30, 32) THEN oi.quantity
                WHEN oh.type IN (10, 12, 14, 16, 19, 22, 31, 33) THEN -oi.quantity
                ELSE 0
            END) as stock_quantity
        ')->first();

        return (float) ($result->stock_quantity ?? 0);
    }

    /**
     * Get recommended items for customer
     *
     * @param int $customerId
     * @param int $limit
     * @return array
     */
    public function getRecommendedItems(int $customerId, int $limit = 10): array
    {
        $items = DB::table('operation_items as oi')
            ->join('oper_head as oh', 'oi.oper_id', '=', 'oh.id')
            ->join('items as i', 'oi.item_id', '=', 'i.id')
            ->where('oh.acc1_id', $customerId)
            ->whereIn('oh.type', [10, 12, 14, 16])
            ->where('i.active', 1)
            ->select(
                'i.id',
                'i.name',
                'i.code',
                DB::raw('COUNT(*) as purchase_count'),
                DB::raw('MAX(oh.pro_date) as last_purchase_date'),
                DB::raw('AVG(oi.price) as avg_price')
            )
            ->groupBy('i.id', 'i.name', 'i.code')
            ->orderBy('purchase_count', 'desc')
            ->orderBy('last_purchase_date', 'desc')
            ->limit($limit)
            ->get();

        return $items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'purchase_count' => $item->purchase_count,
                'last_purchase_date' => $item->last_purchase_date,
                'avg_price' => (float) $item->avg_price,
            ];
        })->toArray();
    }

    /**
     * Get all items in lite format (for client-side search)
     *
     * @param int|null $branchId
     * @param int|null $type
     * @return array
     */
    public function getAllItemsLite(?int $branchId = null, ?int $type = null): array
    {
        $query = DB::table('items')
            ->select([
                'items.id',
                'items.name',
                'items.code',
                'items.barcode',
                'items.price',
                'items.unit_id as default_unit_id',
            ])
            ->where('items.active', 1);

        // Add branch filter if provided
        if ($branchId) {
            $query->where('items.branch_id', $branchId);
        }

        $items = $query->limit(8000)->get()->toArray();

        // Get units for each item
        foreach ($items as &$item) {
            $item = (array) $item;

            // Get units
            $units = DB::table('item_units')
                ->where('item_id', $item['id'])
                ->select(['id', 'name', 'u_val'])
                ->get()
                ->toArray();

            $item['units'] = array_map(fn($u) => (array) $u, $units);
        }

        return array_values($items);
    }


    /**
     * Quick create item (for inline creation during invoice)
     *
     * @param array $data
     * @return array
     */
    public function quickCreateItem(array $data): array
    {
        // Generate code if AUTO
        if (!isset($data['code']) || $data['code'] === 'AUTO') {
            $lastItem = DB::table('items')->orderBy('id', 'desc')->first();
            $data['code'] = 'ITEM-' . str_pad(($lastItem->id ?? 0) + 1, 6, '0', STR_PAD_LEFT);
        }

        // Insert item
        $itemId = DB::table('items')->insertGetId([
            'name' => $data['name'],
            'code' => $data['code'],
            'price' => $data['price'],
            'unit_id' => $data['unit_id'],
            'active' => 1,
            'branch_id' => auth()->user()->branch_id ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create default unit relation
        DB::table('item_units')->insert([
            'item_id' => $itemId,
            'unit_id' => $data['unit_id'],
            'name' => DB::table('units')->where('id', $data['unit_id'])->value('name') ?? 'قطعة',
            'u_val' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get unit info
        $unit = DB::table('units')->where('id', $data['unit_id'])->first();

        // Return item in same format as getAllItemsLite
        return [
            'id' => $itemId,
            'name' => $data['name'],
            'code' => $data['code'],
            'barcode' => null,
            'price' => $data['price'],
            'default_unit_id' => $data['unit_id'],
            'units' => [
                [
                    'id' => $data['unit_id'],
                    'name' => $unit->name ?? 'قطعة',
                    'u_val' => 1,
                ]
            ],
        ];
    }
}
