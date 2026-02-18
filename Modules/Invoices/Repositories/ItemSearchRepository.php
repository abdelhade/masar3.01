<?php

declare(strict_types=1);

namespace Modules\Invoices\Repositories;

use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
                    $query->select('units.id', 'units.name');
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
    public function getItemDetails(int $itemId, ?int $customerId = null, ?int $branchId = null, ?int $warehouseId = null): array
    {
        $item = Item::with([
            'units' => function ($query) {
                $query->select('units.id', 'units.name');
            },
            'barcodes'
        ])->findOrFail($itemId);

        $units = $this->getItemUnitsWithPrices($item, $branchId);
        $lastSalePrice = $this->getLastSalePriceForCustomer($itemId, $customerId);
        $lastPurchasePrice = $this->getLastPurchasePrice($itemId);
        $pricingAgreement = $this->getPricingAgreement($itemId, $customerId);
        $totalStock = $this->getStockQuantity($itemId, $branchId);
        $warehouseStock = $warehouseId ? $this->getStockQuantity($itemId, $branchId, $warehouseId) : $totalStock;

        // Get sale price from item_prices or item table
        $salePrice = $this->getItemSalePrice($itemId);

        return [
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'default_unit_id' => $item->default_unit_id,
                'average_cost' => (float) ($item->average_cost ?? 0),
            ],
            'units' => $units,
            'last_sale_price' => $lastSalePrice,
            'last_purchase_price' => $lastPurchasePrice,
            'sale_price' => $salePrice,
            'pricing_agreement' => $pricingAgreement,
            'stock_quantity' => $totalStock,
            'warehouse_stock' => $warehouseStock,
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
                'iu.u_val as conversion_factor',
                'iu.cost'
            )
            ->get();

        return $units->map(function ($unit) use ($item, $branchId) {
            $stockQty = $this->getStockQuantityForUnit($item->id, $unit->id, $branchId);

            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'conversion_factor' => (float) ($unit->conversion_factor ?? 1),
                'cost' => (float) ($unit->cost ?? 0),
                'stock_quantity' => $stockQty,
            ];
        })->toArray();
    }

    /**
     * Get last sale price for customer
     *
     * @param int $itemId
     * @param int|null $customerId
     * @return float|null
     */
    private function getLastSalePriceForCustomer(int $itemId, ?int $customerId = null): float
    {
        if (!$customerId) {
            return 0;
        }

        $lastPrice = DB::table('operation_items as oi')
            ->join('operhead as oh', 'oi.pro_id', '=', 'oh.id')
            ->where('oh.acc1', $customerId)
            ->where('oi.item_id', $itemId)
            ->whereIn('oh.pro_type', [10, 12, 14, 16]) // Sales types
            ->where('oh.isdeleted', 0)
            ->orderBy('oh.pro_date', 'desc')
            ->orderBy('oh.id', 'desc')
            ->value('oi.item_price');

        return (float) ($lastPrice ?? 0);
    }

    /**
     * Get last purchase price for item
     *
     * @param int $itemId
     * @return float
     */
    private function getLastPurchasePrice(int $itemId): float
    {
        $lastPrice = DB::table('operation_items as oi')
            ->join('operhead as oh', 'oi.pro_id', '=', 'oh.id')
            ->where('oi.item_id', $itemId)
            ->whereIn('oh.pro_type', [11, 13, 15, 17, 20, 24, 25]) // Purchase types
            ->where('oh.isdeleted', 0)
            ->orderBy('oh.pro_date', 'desc')
            ->orderBy('oh.id', 'desc')
            ->value('oi.item_price');
        return (float) ($lastPrice ?? 0);
    }

    /**
     * Get item sale price
     *
     * @param int $itemId
     * @return float
     */
    private function getItemSalePrice(int $itemId): float
    {
        // Try to get from item_prices table first
        try {
            if (Schema::hasTable('item_prices')) {
                $price = DB::table('item_prices')
                    ->where('item_id', $itemId)
                    ->orderBy('id')
                    ->value('price');

                if ($price && $price > 0) {
                    return (float) $price;
                }
            }
        } catch (\Exception $e) {
            \Log::warning('getItemSalePrice from item_prices failed', ['error' => $e->getMessage()]);
        }

        // Try to get from item_units table
        try {
            $price = DB::table('item_units')
                ->where('item_id', $itemId)
                ->orderBy('id')
                ->value('cost');

            if ($price && $price > 0) {
                return (float) $price;
            }
        } catch (\Exception $e) {
            \Log::warning('getItemSalePrice from item_units failed', ['error' => $e->getMessage()]);
        }

        return 0;
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

        try {
            // Check if table exists first
            if (!Schema::hasTable('pricing_agreements')) {
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
        } catch (\Exception $e) {
            // If table doesn't exist or any other error, just return null
            \Log::warning('getPricingAgreement failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get stock quantity for item
     *
     * @param int $itemId
     * @param int|null $branchId
     * @param int|null $warehouseId
     * @return float
     */
    private function getStockQuantity(int $itemId, ?int $branchId = null, ?int $warehouseId = null): float
    {
        $query = DB::table('operation_items as oi')
            ->join('operhead as oh', 'oi.pro_id', '=', 'oh.id')
            ->where('oi.item_id', $itemId)
            ->where('oh.isdeleted', 0);

        if ($branchId) {
            $query->where('oh.branch_id', $branchId);
        }

        if ($warehouseId) {
            $query->where('oh.acc2', $warehouseId);
        }

        $result = $query->selectRaw('SUM(oi.qty_in - oi.qty_out) as stock_quantity')->first();

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
            ->join('operhead as oh', 'oi.pro_id', '=', 'oh.id')
            ->where('oi.item_id', $itemId)
            ->where('oi.unit_id', $unitId)
            ->where('oh.isdeleted', 0);

        if ($branchId) {
            $query->where('oh.branch_id', $branchId);
        }

        $result = $query->selectRaw('SUM(oi.qty_in - oi.qty_out) as stock_quantity')->first();

        return (float) ($result->stock_quantity ?? 0);
    }

    /**
     * Get recommended items for customer/supplier
     *
     * @param int $accountId
     * @param int $limit
     * @return array
     */
    public function getRecommendedItems(int $accountId, int $limit = 5): array
    {
        $items = DB::table('operation_items as oi')
            ->join('operhead as oh', 'oi.pro_id', '=', 'oh.id')
            ->join('items as i', 'oi.item_id', '=', 'i.id')
            ->where('oh.acc1', $accountId)
            ->where('oh.isdeleted', 0)
            ->where('i.isdeleted', 0)
            ->select(
                'i.id',
                'i.name',
                'i.code',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('MAX(oh.pro_date) as last_transaction_date'),
                DB::raw('SUM(oi.qty_in + oi.qty_out) as total_quantity'),
                DB::raw('AVG(oi.item_price) as avg_price')
            )
            ->groupBy('i.id', 'i.name', 'i.code')
            ->orderBy('transaction_count', 'desc')
            ->orderBy('last_transaction_date', 'desc')
            ->limit($limit)
            ->get();

        return $items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code ?? '',
                'transaction_count' => (int) $item->transaction_count,
                'last_transaction_date' => $item->last_transaction_date,
                'total_quantity' => (float) $item->total_quantity,
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
            ])
            ->where('items.isdeleted', 0);

        // Add branch filter if provided
        if ($branchId) {
            $query->where(function ($q) use ($branchId) {
                $q->where('items.branch_id', $branchId)
                    ->orWhereNull('items.branch_id');
            });
        }

        $items = $query->limit(8000)->get()->toArray();

        // Get units and barcodes for each item
        $result = [];
        foreach ($items as $item) {
            $item = (array) $item;

            // Get units with pivot data
            $units = DB::table('item_units')
                ->join('units', 'units.id', '=', 'item_units.unit_id')
                ->where('item_units.item_id', $item['id'])
                ->select([
                    'units.id',
                    'units.name',
                    'item_units.u_val',
                    'item_units.cost',
                ])
                ->get()
                ->toArray();

            // Get first barcode if exists
            $barcode = DB::table('barcodes')
                ->where('item_id', $item['id'])
                ->value('barcode');

            // Get first price if exists
            $price = DB::table('item_prices')
                ->where('item_id', $item['id'])
                ->value('price') ?? 0;

            $result[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'code' => $item['code'] ?? '',
                'barcode' => $barcode ?? '',
                'price' => (float) $price,
                'units' => array_map(function ($u) {
                    $u = (array) $u;
                    return [
                        'id' => $u['id'],
                        'name' => $u['name'],
                        'u_val' => (float) ($u['u_val'] ?? 1),
                        'cost' => (float) ($u['cost'] ?? 0),
                    ];
                }, $units),
            ];
        }

        return $result;
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
            $lastCode = DB::table('items')->max('code');
            $data['code'] = ((int) $lastCode) + 1;
        }

        // Insert item
        $itemId = DB::table('items')->insertGetId([
            'name' => $data['name'],
            'code' => $data['code'],
            'is_active' => 1,
            'isdeleted' => 0,
            'branch_id' => auth()->user()->branch_id ?? 1,
            'type' => 1, // Default type
            'average_cost' => 0,
            'min_order_quantity' => 0,
            'max_order_quantity' => 0,
            'tenant' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create default unit relation
        DB::table('item_units')->insert([
            'item_id' => $itemId,
            'unit_id' => $data['unit_id'],
            'u_val' => 1,
            'cost' => 0,
            'quick_access' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create default price if provided
        if (isset($data['price']) && $data['price'] > 0) {
            // Get first price type (usually "سعر البيع")
            $priceTypeId = DB::table('prices')->orderBy('id')->value('id') ?? 1;

            DB::table('item_prices')->insert([
                'item_id' => $itemId,
                'price_id' => $priceTypeId,
                'unit_id' => $data['unit_id'],
                'price' => $data['price'],
                'discount' => 0,
                'tax_rate' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Get unit info
        $unit = DB::table('units')->where('id', $data['unit_id'])->first();

        // Return item in same format as getAllItemsLite
        return [
            'id' => $itemId,
            'name' => $data['name'],
            'code' => (string) $data['code'],
            'barcode' => '',
            'price' => (float) ($data['price'] ?? 0),
            'units' => [
                [
                    'id' => $data['unit_id'],
                    'name' => $unit->name ?? 'قطعة',
                    'u_val' => 1,
                    'cost' => 0,
                ]
            ],
        ];
    }

    /**
     * Get item price for specific price list and unit
     *
     * @param int $itemId
     * @param int $priceListId
     * @param int $unitId
     * @return float|null
     */
    public function getItemPriceForPriceList(int $itemId, int $priceListId, int $unitId): ?float
    {
        // Try to get price from item_prices table
        $itemPrice = DB::table('item_prices')
            ->where('item_id', $itemId)
            ->where('price_id', $priceListId)
            ->where('unit_id', $unitId)
            ->first();

        if ($itemPrice) {
            return (float) $itemPrice->price;
        }

        // Fallback: Get price from item_units table
        $itemUnit = DB::table('item_units')
            ->where('item_id', $itemId)
            ->where('unit_id', $unitId)
            ->first();

        if ($itemUnit) {
            // Get price based on price list ID (price1, price2, etc.)
            $priceColumn = 'price' . $priceListId;
            if (isset($itemUnit->$priceColumn)) {
                return (float) $itemUnit->$priceColumn;
            }
        }

        // Last fallback: Get default price from items table
        $item = DB::table('items')->where('id', $itemId)->first();
        if ($item) {
            $priceColumn = 'price' . $priceListId;
            if (isset($item->$priceColumn)) {
                return (float) $item->$priceColumn;
            }
        }

        return null;
    }
}
