<?php

namespace App\Support;

use App\Models\Item;
use Illuminate\Support\Facades\DB;

class ItemDataTransformer
{
    /**
     * Transform item data for display
     * Lightweight alternative to ItemViewModel
     */
    public static function transform(Item $item, ?int $unitId, ?int $warehouseId = null, ?float $precomputedBaseQty = null): array
    {
        return [
            'id' => $item->id,
            'code' => $item->code,
            'name' => $item->name,
            'unitOptions' => self::getUnitOptions($item),
            'formattedQuantity' => self::getFormattedQuantity($item, $unitId, $warehouseId, $precomputedBaseQty),
            'unitCostPrice' => self::getUnitCostPrice($item, $unitId),
            'quantityCost' => self::getQuantityCost($item, $unitId, $warehouseId, $precomputedBaseQty),
            'unitAverageCost' => self::getUnitAverageCost($item, $unitId),
            'quantityAverageCost' => self::getQuantityAverageCost($item, $unitId, $warehouseId, $precomputedBaseQty),
            'unitSalePrices' => self::getUnitSalePrices($item, $unitId),
            'unitBarcodes' => self::getUnitBarcodes($item, $unitId),
            'itemNotes' => self::getItemNotes($item),
        ];
    }

    /**
     * Get unit options for dropdown
     */
    private static function getUnitOptions(Item $item): array
    {
        return $item->units->map(fn($unit) => [
            'value' => $unit->id,
            'label' => $unit->name . ' [' . number_format($unit->pivot->u_val ?? 1) . ']',
        ])->toArray();
    }

    /**
     * Get total base quantity (u_val = 1) from operation_items
     */
    private static function getTotalBaseQuantity(Item $item, ?int $warehouseId, ?float $precomputedBaseQty = null): float
    {
        // Use precomputed value if available
        if ($precomputedBaseQty !== null) {
            return $precomputedBaseQty;
        }

        $query = DB::table('operation_items')
            ->where('item_id', $item->id)
            ->where('isdeleted', 0);

        if ($warehouseId) {
            $query->where('detail_store', $warehouseId);
        }

        $rows = $query->get();

        $totalBaseQty = 0;
        foreach ($rows as $row) {
            $unit = $item->units->firstWhere('id', $row->unit_id);
            $u_val = $unit && isset($unit->pivot) ? $unit->pivot->u_val : 1;
            $qty = ($row->qty_in - $row->qty_out) * $u_val;
            $totalBaseQty += $qty;
        }

        return $totalBaseQty;
    }

    /**
     * Get formatted quantity (integer + remainder)
     */
    private static function getFormattedQuantity(Item $item, ?int $unitId, ?int $warehouseId, ?float $precomputedBaseQty = null): array
    {
        $selectedUnit = $item->units->firstWhere('id', $unitId);
        $selectedUVal = $selectedUnit && isset($selectedUnit->pivot) ? $selectedUnit->pivot->u_val : 1;
        $unitName = $selectedUnit->name ?? '';
        $smallerUnit = $item->units->sortBy('pivot.u_val')->first();
        $smallerUnitName = $smallerUnit->name ?? '';

        $totalBaseQty = self::getTotalBaseQuantity($item, $warehouseId, $precomputedBaseQty);

        $integer = $selectedUVal > 0 ? floor($totalBaseQty / $selectedUVal) : 0;
        $remainder = $selectedUVal > 0 ? $totalBaseQty % $selectedUVal : 0;

        return [
            'quantity' => [
                'integer' => $integer,
                'remainder' => $remainder
            ],
            'unitName' => $unitName,
            'smallerUnitName' => $smallerUnitName,
        ];
    }

    /**
     * Get current unit quantity as float
     */
    private static function getCurrentUnitQuantity(Item $item, ?int $unitId, ?int $warehouseId, ?float $precomputedBaseQty = null): float
    {
        $selectedUnit = $item->units->firstWhere('id', $unitId);
        $selectedUVal = $selectedUnit && isset($selectedUnit->pivot) ? $selectedUnit->pivot->u_val : 1;
        $totalBaseQty = self::getTotalBaseQuantity($item, $warehouseId, $precomputedBaseQty);

        return $selectedUVal > 0 ? $totalBaseQty / $selectedUVal : 0;
    }

    /**
     * Get unit cost price
     */
    private static function getUnitCostPrice(Item $item, ?int $unitId): float
    {
        return $item->units->firstWhere('id', $unitId)?->pivot->cost ?? 0;
    }

    /**
     * Get quantity cost
     */
    private static function getQuantityCost(Item $item, ?int $unitId, ?int $warehouseId, ?float $precomputedBaseQty = null): float
    {
        $quantity = self::getCurrentUnitQuantity($item, $unitId, $warehouseId, $precomputedBaseQty);
        $cost = self::getUnitCostPrice($item, $unitId);
        return $quantity * $cost;
    }

    /**
     * Get unit average cost
     */
    private static function getUnitAverageCost(Item $item, ?int $unitId): float
    {
        $uVal = $item->units->firstWhere('id', $unitId)?->pivot->u_val ?? 0;
        return $item->average_cost * $uVal;
    }

    /**
     * Get quantity average cost
     */
    private static function getQuantityAverageCost(Item $item, ?int $unitId, ?int $warehouseId, ?float $precomputedBaseQty = null): float
    {
        $quantity = self::getCurrentUnitQuantity($item, $unitId, $warehouseId, $precomputedBaseQty);
        $avgCost = self::getUnitAverageCost($item, $unitId);
        return $quantity * $avgCost;
    }

    /**
     * Get unit sale prices
     */
    private static function getUnitSalePrices(Item $item, ?int $unitId): array
    {
        if (!$unitId) {
            return [];
        }

        return $item->prices
            ->where('pivot.unit_id', $unitId)
            ->mapWithKeys(fn($priceTypeModel) => [
                $priceTypeModel->id => [
                    'name' => $priceTypeModel->name,
                    'price' => $priceTypeModel->pivot->price,
                ]
            ])->toArray();
    }

    /**
     * Get unit barcodes
     */
    private static function getUnitBarcodes(Item $item, ?int $unitId): array
    {
        return $item->barcodes
            ->where('unit_id', $unitId)
            ->map(fn($barcode) => [
                'id' => $barcode->id,
                'barcode' => $barcode->barcode,
            ])->toArray();
    }

    /**
     * Get item notes
     */
    private static function getItemNotes(Item $item): array
    {
        return $item->notes
            ->mapWithKeys(function ($note) {
                return [$note->id => $note->pivot->note_detail_name];
            })
            ->all();
    }

    /**
     * Get item data for Alpine.js (all units, prices, barcodes)
     */
    public static function getItemDataForAlpine(Item $item, ?int $warehouseId = null, ?float $precomputedBaseQty = null): array
    {
        $baseQty = $precomputedBaseQty ?? self::getTotalBaseQuantity($item, $warehouseId);
        
        // Prepare all units data
        $unitsData = [];
        foreach ($item->units as $unit) {
            $unitsData[$unit->id] = [
                'id' => $unit->id,
                'name' => $unit->name,
                'u_val' => $unit->pivot->u_val ?? 1,
                'cost' => $unit->pivot->cost ?? 0,
            ];
        }

        // Prepare all prices data (grouped by unit_id)
        $pricesData = [];
        foreach ($item->prices as $price) {
            $unitId = $price->pivot->unit_id;
            if (!isset($pricesData[$unitId])) {
                $pricesData[$unitId] = [];
            }
            $pricesData[$unitId][$price->id] = [
                'id' => $price->id,
                'name' => $price->name,
                'price' => $price->pivot->price ?? 0,
                'discount' => $price->pivot->discount ?? 0,
                'tax_rate' => $price->pivot->tax_rate ?? 0,
            ];
        }

        // Prepare all barcodes data (grouped by unit_id)
        $barcodesData = [];
        foreach ($item->barcodes as $barcode) {
            $unitId = $barcode->unit_id;
            if (!isset($barcodesData[$unitId])) {
                $barcodesData[$unitId] = [];
            }
            $barcodesData[$unitId][] = [
                'id' => $barcode->id,
                'barcode' => $barcode->barcode,
            ];
        }

        return [
            'id' => $item->id,
            'code' => $item->code,
            'name' => $item->name,
            'average_cost' => $item->average_cost,
            'base_quantity' => $baseQty,
            'units' => $unitsData,
            'prices' => $pricesData,
            'barcodes' => $barcodesData,
            'notes' => self::getItemNotes($item),
        ];
    }
}

