<?php

declare(strict_types=1);

namespace App\Services\Consistency;

use App\Models\Item;
use App\Services\Config\RecalculationConfigManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Service for checking and fixing average cost consistency
 *
 * This service verifies that stored average_cost values match calculated values
 * from operations, and provides methods to fix inconsistencies.
 */
class AverageCostConsistencyChecker
{
    /**
     * Check average cost consistency for specific items
     *
     * @param  array  $itemIds  Array of item IDs to check
     * @return array Inconsistencies found with details
     *
     * @throws InvalidArgumentException if item IDs are invalid
     */
    public function checkItems(array $itemIds): array
    {
        if (empty($itemIds)) {
            return [];
        }

        // Validate item IDs
        foreach ($itemIds as $itemId) {
            if (! is_int($itemId) || $itemId <= 0) {
                throw new InvalidArgumentException("Invalid item ID: {$itemId}. All item IDs must be positive integers.");
            }
        }

        $tolerance = RecalculationConfigManager::getConsistencyTolerance();
        $inconsistencies = [];

        foreach ($itemIds as $itemId) {
            $result = $this->checkSingleItem($itemId, $tolerance);
            if ($result !== null) {
                $inconsistencies[] = $result;
            }
        }

        Log::info('Consistency check completed', [
            'items_checked' => count($itemIds),
            'inconsistencies_found' => count($inconsistencies),
        ]);

        return $inconsistencies;
    }

    /**
     * Check average cost consistency for all items
     *
     * @param  int  $chunkSize  Number of items to process at once
     * @return array Summary of inconsistencies
     */
    public function checkAllItems(int $chunkSize = 500): array
    {
        $totalItems = Item::count();
        $inconsistencies = [];
        $processed = 0;

        Log::info("Starting consistency check for {$totalItems} items");

        Item::chunk($chunkSize, function ($items) use (&$inconsistencies, &$processed, $totalItems) {
            $itemIds = $items->pluck('id')->toArray();
            $chunkInconsistencies = $this->checkItems($itemIds);
            $inconsistencies = array_merge($inconsistencies, $chunkInconsistencies);
            $processed += count($itemIds);

            Log::info("Consistency check progress: {$processed} / {$totalItems} items");
        });

        $summary = [
            'total_items' => $totalItems,
            'items_checked' => $processed,
            'inconsistencies_found' => count($inconsistencies),
            'inconsistencies' => $inconsistencies,
        ];

        Log::info('Consistency check completed', $summary);

        return $summary;
    }

    /**
     * Fix inconsistent average costs
     *
     * @param  array  $itemIds  Array of item IDs to fix
     * @param  bool  $dryRun  If true, only report what would be fixed
     * @return array Results with fixed items
     *
     * @throws InvalidArgumentException if item IDs are invalid
     */
    public function fixInconsistencies(array $itemIds, bool $dryRun = false): array
    {
        if (empty($itemIds)) {
            return [
                'dry_run' => $dryRun,
                'items_fixed' => 0,
                'fixed_items' => [],
            ];
        }

        // Validate item IDs
        foreach ($itemIds as $itemId) {
            if (! is_int($itemId) || $itemId <= 0) {
                throw new InvalidArgumentException("Invalid item ID: {$itemId}. All item IDs must be positive integers.");
            }
        }

        $tolerance = RecalculationConfigManager::getConsistencyTolerance();
        $fixedItems = [];

        foreach ($itemIds as $itemId) {
            $inconsistency = $this->checkSingleItem($itemId, $tolerance);

            if ($inconsistency !== null) {
                if (! $dryRun) {
                    // Update the item with the calculated average cost
                    DB::table('items')
                        ->where('id', $itemId)
                        ->update(['average_cost' => $inconsistency['calculated_average']]);

                    Log::info("Fixed average cost for item {$itemId}", [
                        'old_value' => $inconsistency['stored_average'],
                        'new_value' => $inconsistency['calculated_average'],
                        'difference' => $inconsistency['difference'],
                    ]);
                }

                $fixedItems[] = [
                    'item_id' => $itemId,
                    'old_average' => $inconsistency['stored_average'],
                    'new_average' => $inconsistency['calculated_average'],
                    'difference' => $inconsistency['difference'],
                ];
            }
        }

        $result = [
            'dry_run' => $dryRun,
            'items_fixed' => count($fixedItems),
            'fixed_items' => $fixedItems,
        ];

        Log::info('Fix inconsistencies completed', $result);

        return $result;
    }

    /**
     * Generate consistency report
     *
     * @return array Detailed report with statistics
     */
    public function generateReport(): array
    {
        $summary = $this->checkAllItems();

        $report = [
            'timestamp' => now()->toDateTimeString(),
            'total_items' => $summary['total_items'],
            'items_checked' => $summary['items_checked'],
            'inconsistencies_found' => $summary['inconsistencies_found'],
            'consistency_rate' => $summary['total_items'] > 0
                ? round((($summary['total_items'] - $summary['inconsistencies_found']) / $summary['total_items']) * 100, 2)
                : 100,
            'tolerance_threshold' => RecalculationConfigManager::getConsistencyTolerance(),
            'inconsistencies' => $summary['inconsistencies'],
        ];

        Log::info('Consistency report generated', [
            'total_items' => $report['total_items'],
            'inconsistencies_found' => $report['inconsistencies_found'],
            'consistency_rate' => $report['consistency_rate'],
        ]);

        return $report;
    }

    /**
     * Check consistency for a single item
     *
     * @param  int  $itemId  Item ID to check
     * @param  float  $tolerance  Tolerance threshold for comparison
     * @return array|null Inconsistency details or null if consistent
     */
    private function checkSingleItem(int $itemId, float $tolerance): ?array
    {
        // Get stored average cost
        $item = Item::find($itemId);
        if (! $item) {
            Log::warning("Item not found for consistency check: {$itemId}");

            return null;
        }

        $storedAverage = (float) $item->average_cost;

        // Calculate expected average cost from operations
        $sql = '
            SELECT 
                SUM(oi.qty_in - oi.qty_out) as total_qty,
                SUM(oi.detail_value) as total_value
            FROM operation_items oi
            INNER JOIN operhead oh ON oi.pro_id = oh.id
            WHERE oi.item_id = ?
                AND oi.is_stock = 1
                AND oh.pro_type IN (11, 12, 20, 59)
                AND oh.isdeleted = 0
        ';

        $result = DB::selectOne($sql, [$itemId]);

        $totalQty = (float) ($result->total_qty ?? 0);
        $totalValue = (float) ($result->total_value ?? 0);
        $calculatedAverage = $totalQty > 0 ? ($totalValue / $totalQty) : 0;

        // Compare with tolerance
        $difference = abs($storedAverage - $calculatedAverage);

        if ($difference > $tolerance) {
            return [
                'item_id' => $itemId,
                'stored_average' => $storedAverage,
                'calculated_average' => $calculatedAverage,
                'difference' => $difference,
                'total_qty' => $totalQty,
                'total_value' => $totalValue,
            ];
        }

        return null;
    }
}
