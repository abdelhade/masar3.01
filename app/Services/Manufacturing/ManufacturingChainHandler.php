<?php

declare(strict_types=1);

namespace App\Services\Manufacturing;

use App\Models\Item;
use RuntimeException;
use App\Models\OperHead;
use InvalidArgumentException;
use App\Models\OperationItems;
use Illuminate\Support\Facades\DB;
use Modules\Invoices\Services\Config\RecalculationConfigManager;
use Modules\Invoices\Services\Validation\RecalculationInputValidator;

/**
 * Handles cascading recalculation for manufacturing invoice chains.
 *
 * When raw material costs change (e.g., purchase invoice modified/deleted),
 * this handler identifies affected manufacturing invoices and recalculates
 * product costs in chronological order.
 */
class ManufacturingChainHandler
{
    /**
     * Find all manufacturing invoices affected by raw material cost changes.
     *
     * Identifies manufacturing invoices that use the specified raw materials
     * and orders them chronologically by date and time.
     *
     * @param  array  $rawMaterialItemIds  Array of raw material item IDs
     * @param  string  $fromDate  Start date for affected invoices (Y-m-d format)
     * @return array Array of affected manufacturing invoice data with dates
     *
     * @throws InvalidArgumentException if parameters are invalid
     */
    public function findAffectedManufacturingInvoices(array $rawMaterialItemIds, string $fromDate): array
    {
        // Validate inputs
        RecalculationInputValidator::validateItemIds($rawMaterialItemIds);
        RecalculationInputValidator::validateDate($fromDate);

        if (empty($rawMaterialItemIds)) {
            return [];
        }

        try {
            $manufacturingTypes = RecalculationConfigManager::getManufacturingOperationTypes();

            // Find manufacturing invoices that use these raw materials
            // Raw materials have qty_out > 0 in manufacturing invoices
            $affectedInvoices = DB::table('operation_items as oi')
                ->join('operhead as oh', 'oi.pro_id', '=', 'oh.id')
                ->whereIn('oi.item_id', $rawMaterialItemIds)
                ->where('oi.qty_out', '>', 0) // Raw materials (inputs)
                ->whereIn('oh.pro_type', $manufacturingTypes)
                ->where('oh.isdeleted', 0)
                ->where('oh.pro_date', '>=', $fromDate)
                ->select(
                    'oh.id as invoice_id',
                    'oh.pro_date as invoice_date',
                    'oh.created_at as invoice_created_at',
                    'oh.pro_type as operation_type'
                )
                ->distinct()
                ->orderBy('oh.pro_date', 'asc')
                ->orderBy('oh.created_at', 'asc')
                ->get()
                ->toArray();

            return $affectedInvoices;
        } catch (\Exception $e) {
            throw new RuntimeException('Failed to find affected manufacturing invoices: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get manufacturing invoice details (raw materials and products).
     *
     * Retrieves and separates the invoice into two sections:
     * - Raw materials (inputs): items with qty_out > 0
     * - Products (outputs): items with qty_in > 0
     *
     * @param  int  $manufacturingInvoiceId  Manufacturing invoice ID
     * @return array Invoice details with 'raw_materials' and 'products' sections
     *
     * @throws InvalidArgumentException if invoice ID is invalid
     * @throws RuntimeException if retrieval fails
     */
    public function getManufacturingInvoiceDetails(int $manufacturingInvoiceId): array
    {
        if ($manufacturingInvoiceId <= 0) {
            throw new InvalidArgumentException('Manufacturing invoice ID must be a positive integer');
        }

        try {
            $items = OperationItems::where('pro_id', $manufacturingInvoiceId)
                ->with(['item', 'unit'])
                ->get();

            // Separate raw materials (inputs) from products (outputs)
            $rawMaterials = $items->filter(fn($item) => $item->qty_out > 0)->values();
            $products = $items->filter(fn($item) => $item->qty_in > 0)->values();

            return [
                'invoice_id' => $manufacturingInvoiceId,
                'raw_materials' => $rawMaterials,
                'products' => $products,
                'total_raw_material_cost' => $rawMaterials->sum('detail_value'),
                'total_product_quantity' => $products->sum('qty_in'),
            ];
        } catch (\Exception $e) {
            throw new RuntimeException('Failed to retrieve manufacturing invoice details: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Update product costs when raw material costs change.
     *
     * Calculates total raw material cost and distributes it to products
     * based on the configured allocation method (proportional or equal).
     *
     * @param  int  $manufacturingInvoiceId  Manufacturing invoice ID
     * @return array Updated product item IDs and new costs
     *
     * @throws InvalidArgumentException if invoice ID is invalid
     * @throws RuntimeException if update fails
     */
    public function updateProductCostsFromRawMaterials(int $manufacturingInvoiceId): array
    {
        if ($manufacturingInvoiceId <= 0) {
            throw new InvalidArgumentException('Manufacturing invoice ID must be a positive integer');
        }

        try {
            $details = $this->getManufacturingInvoiceDetails($manufacturingInvoiceId);
            $rawMaterials = $details['raw_materials'];
            $products = $details['products'];

            if ($products->isEmpty()) {

                return [];
            }

            // Calculate total raw material cost
            $totalRawMaterialCost = $rawMaterials->sum('detail_value');

            // Get allocation method from configuration
            $allocationMethod = RecalculationConfigManager::getManufacturingCostAllocation();

            $updatedProducts = [];

            // Distribute cost to products based on allocation method
            if ($allocationMethod === 'equal') {
                // Equal distribution
                $costPerProduct = $totalRawMaterialCost / $products->count();

                foreach ($products as $product) {
                    $product->detail_value = $costPerProduct;
                    $product->save();

                    $updatedProducts[] = [
                        'item_id' => $product->item_id,
                        'new_cost' => $costPerProduct,
                        'quantity' => $product->qty_in,
                    ];
                }
            } else {
                // Proportional distribution (default)
                $totalProductQuantity = $products->sum('qty_in');

                if ($totalProductQuantity <= 0) {
                    return [];
                }

                foreach ($products as $product) {
                    $proportion = $product->qty_in / $totalProductQuantity;
                    $productCost = $totalRawMaterialCost * $proportion;

                    $product->detail_value = $productCost;
                    $product->save();

                    $updatedProducts[] = [
                        'item_id' => $product->item_id,
                        'new_cost' => $productCost,
                        'quantity' => $product->qty_in,
                    ];
                }
            }
            return $updatedProducts;
        } catch (\Exception $e) {
            throw new RuntimeException('Failed to update product costs: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Recalculate manufacturing chain in chronological order.
     *
     * Processes manufacturing invoices in chronological order (by date and time),
     * updating product costs from raw materials and triggering average cost
     * recalculation for affected products.
     *
     * @param  array  $manufacturingInvoiceIds  Array of manufacturing invoice IDs
     * @param  string  $fromDate  Start date for recalculation
     * @return array Results with processed invoices and updated items
     *
     * @throws InvalidArgumentException if parameters are invalid
     * @throws RuntimeException if recalculation fails
     */
    public function recalculateChain(array $manufacturingInvoiceIds, string $fromDate): array
    {
        RecalculationInputValidator::validateDate($fromDate);

        if (empty($manufacturingInvoiceIds)) {
            return [
                'processed_invoices' => 0,
                'updated_items' => [],
            ];
        }

        try {
            $processedInvoices = 0;
            $allUpdatedItems = [];

            // Use database transaction for consistency
            DB::transaction(function () use ($manufacturingInvoiceIds, &$processedInvoices, &$allUpdatedItems) {
                // Get invoices ordered chronologically
                $invoices = OperHead::whereIn('id', $manufacturingInvoiceIds)
                    ->where('isdeleted', 0)
                    ->orderBy('pro_date', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($invoices as $invoice) {
                    // Update product costs from raw materials
                    $updatedProducts = $this->updateProductCostsFromRawMaterials($invoice->id);

                    if (! empty($updatedProducts)) {
                        // Collect unique item IDs for average cost recalculation
                        $itemIds = array_unique(array_column($updatedProducts, 'item_id'));
                        $allUpdatedItems = array_merge($allUpdatedItems, $itemIds);

                        $processedInvoices++;
                    }
                }

                // Remove duplicates from all updated items
                $allUpdatedItems = array_unique($allUpdatedItems);
            });
            return [
                'processed_invoices' => $processedInvoices,
                'updated_items' => $allUpdatedItems,
            ];
        } catch (\Exception $e) {
            throw new RuntimeException('Failed to recalculate manufacturing chain: ' . $e->getMessage(), 0, $e);
        }
    }
}
