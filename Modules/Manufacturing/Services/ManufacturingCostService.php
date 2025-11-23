<?php

namespace Modules\Manufacturing\Services;

use App\Models\Item;
use App\Models\OperHead;
use App\Models\OperationItems;

class ManufacturingCostService
{
    /**
     * Calculate the manufacturing cost and breakdown for an item recursively.
     *
     * @param int $itemId The ID of the item to calculate cost for.
     * @param float $quantity The quantity to manufacture.
     * @param array $visitedItems To prevent infinite recursion.
     * @return array
     */
    public function calculateItemCost(int $itemId, float $quantity = 1, array $visitedItems = [])
    {
        $item = Item::find($itemId);
        if (!$item) {
            \Illuminate\Support\Facades\Log::warning("ManufacturingCostService: Item not found for ID $itemId");
            return null;
        }

        // Prevent infinite recursion
        if (in_array($itemId, $visitedItems)) {
            return [
                'item_id' => $itemId,
                'name' => $item->name,
                'unit_cost' => $item->average_cost,
                'total_cost' => $item->average_cost * $quantity,
                'quantity_needed' => $quantity,
                'has_recipe' => false,
                'is_recursive_loop' => true,
                'components' => []
            ];
        }

        $visitedItems[] = $itemId;

        // Find the latest manufacturing invoice (pro_type 59 or 63) where this item is the PRODUCT
        // We filter by checking if unit_id is null (standard for Product in Massar)
        $latestProductionItem = OperationItems::where('item_id', $itemId)
            ->whereHas('operhead', function ($q) {
                $q->whereIn('pro_type', [59, 63]);
            })
            ->orderBy('id', 'desc')
            ->get()
            ->filter(function ($opItem) {
                // Product has NO unit_id
                return is_null($opItem->unit_id);
            })
            ->first();

        if (!$latestProductionItem) {
            // No manufacturing recipe found
            return [
                'item_id' => $itemId,
                'name' => $item->name,
                'unit_cost' => $item->average_cost, // Default to current average cost
                'total_cost' => $item->average_cost * $quantity,
                'quantity_needed' => $quantity,
                'has_recipe' => false,
                'components' => []
            ];
        }

        $invoice = $latestProductionItem->operhead;
        // Use fat_quantity as the primary quantity source for Type 63/59
        $producedQuantity = $latestProductionItem->fat_quantity ?? $latestProductionItem->qty ?? 1;
        
        $ratio = $quantity / ($producedQuantity > 0 ? $producedQuantity : 1);

        $components = [];
        $totalCost = 0;

        $invoiceItems = OperationItems::where('pro_id', $invoice->id)->get();

        foreach ($invoiceItems as $invItem) {
            // Skip the product itself
            if ($invItem->id == $latestProductionItem->id) {
                continue;
            }

            // Skip other products (items with no unit_id)
            if (is_null($invItem->unit_id)) {
                continue;
            }

            // It's a raw material (has unit_id)
            $itemQty = $invItem->fat_quantity ?? $invItem->qty ?? 0;
            $componentQty = $itemQty * $ratio;
            
            // Recursively calculate
            $componentCostData = $this->calculateItemCost($invItem->item_id, $componentQty, $visitedItems);
            
            if ($componentCostData) {
                // If this component has NO recipe (it's a raw material), use the price from the INVOICE (Model)
                // instead of the current average cost.
                if (!$componentCostData['has_recipe']) {
                    // Use item_price from the invoice row
                    $modelUnitCost = $invItem->item_price ?? $invItem->price ?? 0;
                    $componentCostData['unit_cost'] = $modelUnitCost;
                    $componentCostData['total_cost'] = $modelUnitCost * $componentQty;
                }

                $components[] = $componentCostData;
                $totalCost += $componentCostData['total_cost'];
            }
        }

        // If no components (weird), fallback
        if (empty($components)) {
             return [
                'item_id' => $itemId,
                'name' => $item->name,
                'unit_cost' => $item->average_cost,
                'total_cost' => $item->average_cost * $quantity,
                'quantity_needed' => $quantity,
                'has_recipe' => false,
                'components' => []
            ];
        }

        return [
            'item_id' => $itemId,
            'name' => $item->name,
            'unit_cost' => $totalCost / ($quantity > 0 ? $quantity : 1),
            'total_cost' => $totalCost,
            'quantity_needed' => $quantity,
            'has_recipe' => true,
            'source_invoice_id' => $invoice->id,
            'components' => $components
        ];
    }
}
