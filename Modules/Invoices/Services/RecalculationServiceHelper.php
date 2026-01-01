<?php

declare(strict_types=1);

namespace Modules\Invoices\Services;

use RuntimeException;
use InvalidArgumentException;
use Illuminate\Support\Facades\Config;
use App\Jobs\RecalculateAverageCostJob;
use App\Services\Manufacturing\ManufacturingChainHandler;
use App\Services\Monitoring\RecalculationPerformanceMonitor;
use Modules\Invoices\Services\Validation\RecalculationInputValidator;

/**
 * Unified helper for average cost recalculation with automatic strategy selection.
 *
 * This class provides a single entry point for all average cost recalculation operations.
 * It automatically selects the optimal strategy based on data size and configuration:
 * - Queue Jobs for very large datasets (>5000 items)
 * - Stored Procedures for large datasets (>1000 items, if enabled)
 * - PHP Optimized Service for small/medium datasets (<1000 items)
 *
 * @example Basic usage:
 * RecalculationServiceHelper::recalculateAverageCost([1, 2, 3], '2024-01-01');
 * @example Force queue processing:
 * RecalculationServiceHelper::recalculateAverageCost($itemIds, null, true);
 * @example Manufacturing chain recalculation:
 * RecalculationServiceHelper::recalculateManufacturingChain([10, 20], '2024-01-01');
 */
class RecalculationServiceHelper
{
    /**
     * Performance monitor instance for tracking operations.
     */
    private static ?RecalculationPerformanceMonitor $monitor = null;

    /**
     * Get or create performance monitor instance.
     *
     * @return RecalculationPerformanceMonitor Performance monitor instance
     */
    private static function getMonitor(): RecalculationPerformanceMonitor
    {
        if (self::$monitor === null) {
            self::$monitor = new RecalculationPerformanceMonitor;
        }

        return self::$monitor;
    }

    /**
     * Recalculate average cost with automatic strategy selection.
     *
     * Automatically selects the optimal recalculation strategy based on:
     * - Item count (queue for >5000, stored procedures for >1000 if enabled)
     * - Configuration settings (stored procedures enabled, queue enabled)
     * - Force queue flag (overrides automatic selection)
     *
     * Formula: average_cost = SUM(detail_value) / SUM(qty_in - qty_out)
     *
     * @param  array  $itemIds  Array of item IDs to recalculate
     * @param  string|null  $fromDate  Start date (Y-m-d format), null for all operations
     * @param  bool  $forceQueue  Force use of queue job regardless of data size
     * @param  bool  $isDelete  True if triggered by delete operation (ignores fromDate)
     *
     * @throws InvalidArgumentException if parameters are invalid
     * @throws RuntimeException if recalculation fails
     *
     * @example
     * // Recalculate from specific date
     * RecalculationServiceHelper::recalculateAverageCost([1, 2, 3], '2024-01-01');
     * @example
     * // Recalculate all operations (delete scenario)
     * RecalculationServiceHelper::recalculateAverageCost([1, 2, 3], null, false, true);
     */
    public static function recalculateAverageCost(array $itemIds, ?string $fromDate = null, bool $forceQueue = false, bool $isDelete = false): void
    {
        if (empty($itemIds)) {
            return;
        }

        $monitor = self::getMonitor();
        $operationId = $monitor->start('helper_average_cost', [
            'item_count' => count($itemIds),
            'from_date' => $fromDate,
            'force_queue' => $forceQueue,
            'is_delete' => $isDelete,
        ]);

        try {
            $queueEnabled = Config::get('queue.default') !== 'sync';
            $storedProceduresEnabled = Config::get('app.use_stored_procedures_for_recalculation', false);

            // 1. استخدام Queue للبيانات الكبيرة جداً
            if ($forceQueue || self::shouldUseQueue($itemIds, $fromDate)) {
                $strategy = 'queue';

                RecalculateAverageCostJob::dispatch($itemIds, $fromDate, false);
                $monitor->end($operationId, [
                    'success' => true,
                    'strategy' => $strategy,
                    'queued' => true,
                ]);

                return;
            }

            // 2. استخدام Factory لاختيار Stored Procedures أو PHP Services
            $service = RecalculationServiceFactory::createAverageCostService($itemIds, $fromDate);
            $strategy = $service instanceof AverageCostRecalculationServiceStoredProcedure ? 'stored_procedure' : 'php_optimized';

            $service->recalculateFromOperationWithItems($itemIds, $fromDate ?? date('Y-m-d'), $isDelete);

            $monitor->end($operationId, [
                'success' => true,
                'strategy' => $strategy,
                'items_processed' => count($itemIds),
            ]);
        } catch (\Exception $e) {
            $monitor->end($operationId, [
                'success' => false,
                'error' => 'exception',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Recalculate profits and journals with automatic strategy selection.
     *
     * Recalculates profit margins and accounting journal entries for affected operations.
     * Automatically selects between stored procedures and PHP implementation based on
     * data size and configuration.
     *
     * @param  array  $itemIds  Array of affected item IDs
     * @param  string|null  $fromDate  Start date for affected operations (Y-m-d format)
     * @param  int|null  $currentInvoiceId  Current invoice ID to exclude from recalculation
     * @param  string|null  $currentInvoiceCreatedAt  Current invoice creation time for same-day comparison
     *
     * @throws InvalidArgumentException if parameters are invalid
     * @throws RuntimeException if recalculation fails
     *
     * @example
     * RecalculationServiceHelper::recalculateProfitsAndJournals([1, 2, 3], '2024-01-01', 100);
     */
    public static function recalculateProfitsAndJournals(array $itemIds, ?string $fromDate = null, ?int $currentInvoiceId = null, ?string $currentInvoiceCreatedAt = null): void
    {
        if (empty($itemIds)) {
            return;
        }

        $monitor = self::getMonitor();
        $operationId = $monitor->start('helper_profits_journals', [
            'item_count' => count($itemIds),
            'from_date' => $fromDate,
            'current_invoice_id' => $currentInvoiceId,
        ]);

        try {
            // استخدام Factory لاختيار Stored Procedures أو PHP Services
            $service = RecalculationServiceFactory::createProfitService($itemIds, $fromDate);
            $strategy = $service instanceof ProfitAndJournalRecalculationServiceStoredProcedure ? 'stored_procedure' : 'php_optimized';

            $service->recalculateAllAffectedOperations($itemIds, $fromDate ?? date('Y-m-d'), $currentInvoiceId, $currentInvoiceCreatedAt);

            $monitor->end($operationId, [
                'success' => true,
                'strategy' => $strategy,
                'items_processed' => count($itemIds),
            ]);
        } catch (\Exception $e) {
            $monitor->end($operationId, [
                'success' => false,
                'error' => 'exception',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Recalculate everything (average cost + profits + journals).
     *
     * Performs complete recalculation for all affected items:
     * 1. Recalculates average cost (may use queue for large datasets)
     * 2. Recalculates profits and journal entries (always synchronous)
     *
     * @param  array  $itemIds  Array of item IDs to recalculate
     * @param  string|null  $fromDate  Start date for recalculation (Y-m-d format)
     * @param  bool  $forceQueue  Force use of queue job for average cost
     * @param  int|null  $currentInvoiceId  Current invoice ID to exclude
     * @param  string|null  $currentInvoiceCreatedAt  Current invoice creation time
     *
     * @throws InvalidArgumentException if parameters are invalid
     * @throws RuntimeException if recalculation fails
     *
     * @example
     * RecalculationServiceHelper::recalculateAll([1, 2, 3], '2024-01-01', false, 100);
     */
    public static function recalculateAll(array $itemIds, ?string $fromDate = null, bool $forceQueue = false, ?int $currentInvoiceId = null, ?string $currentInvoiceCreatedAt = null): void
    {
        if (empty($itemIds)) {
            return;
        }

        // إعادة حساب average_cost
        self::recalculateAverageCost($itemIds, $fromDate, $forceQueue);

        // إعادة حساب الأرباح والقيود (دائماً مباشر، لا queue)
        self::recalculateProfitsAndJournals($itemIds, $fromDate, $currentInvoiceId, $currentInvoiceCreatedAt);
    }

    /**
     * Recalculate manufacturing chain when raw materials change.
     *
     * This method handles cascading recalculation for manufacturing invoices
     * when raw material costs change (e.g., purchase invoice modified/deleted).
     * It identifies affected manufacturing invoices, processes them in chronological
     * order, and triggers average cost recalculation for affected products.
     *
     * @param  array  $rawMaterialItemIds  Array of raw material item IDs
     * @param  string  $fromDate  Start date for affected manufacturing invoices (Y-m-d format)
     *
     * @throws InvalidArgumentException if parameters are invalid
     * @throws RuntimeException if recalculation fails
     */
    public static function recalculateManufacturingChain(array $rawMaterialItemIds, string $fromDate): void
    {
        // Validate inputs
        try {
            RecalculationInputValidator::validateItemIds($rawMaterialItemIds);
            RecalculationInputValidator::validateDate($fromDate);
        } catch (InvalidArgumentException $e) {
            throw $e;
        }

        if (empty($rawMaterialItemIds)) {

            return;
        }

        // Check if manufacturing chain recalculation is enabled
        $chainEnabled = Config::get('recalculation.manufacturing_chain_enabled', true);
        if (! $chainEnabled) {

            return;
        }

        $monitor = self::getMonitor();
        $operationId = $monitor->start('manufacturing_chain', [
            'raw_material_count' => count($rawMaterialItemIds),
            'from_date' => $fromDate,
        ]);

        try {
            // Initialize manufacturing chain handler
            $chainHandler = new ManufacturingChainHandler;

            // Find affected manufacturing invoices
            $affectedInvoices = $chainHandler->findAffectedManufacturingInvoices(
                $rawMaterialItemIds,
                $fromDate
            );

            if (empty($affectedInvoices)) {

                $monitor->end($operationId, [
                    'success' => true,
                    'affected_invoices' => 0,
                    'updated_items' => 0,
                ]);

                return;
            }

            // Extract invoice IDs
            $invoiceIds = array_column($affectedInvoices, 'invoice_id');

            // Recalculate the chain
            $results = $chainHandler->recalculateChain($invoiceIds, $fromDate);

            // Trigger average cost recalculation for affected product items
            if (! empty($results['updated_items'])) {

                // Recalculate average cost for affected products from the fromDate
                self::recalculateAverageCost(
                    $results['updated_items'],
                    $fromDate,
                    false, // Don't force queue
                    false  // Not a delete operation
                );
            }

            $monitor->end($operationId, [
                'success' => true,
                'affected_invoices' => $results['processed_invoices'],
                'updated_items' => count($results['updated_items']),
            ]);
        } catch (\Exception $e) {
            $monitor->end($operationId, [
                'success' => false,
                'error' => 'exception',
                'error_message' => $e->getMessage(),
            ]);

            throw new RuntimeException(
                'Failed to recalculate manufacturing chain: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Determine if queue should be used for recalculation.
     *
     * Checks if queue processing should be used based on:
     * - Queue configuration (must not be 'sync')
     * - Item count (>5000 items)
     * - Operation count (>500,000 operations)
     *
     * @param  array  $itemIds  Array of item IDs
     * @param  string|null  $fromDate  Start date for filtering operations
     * @return bool True if queue should be used
     */
    private static function shouldUseQueue(array $itemIds, ?string $fromDate = null): bool
    {
        $queueEnabled = Config::get('queue.default') !== 'sync';
        if (! $queueEnabled) {
            return false;
        }

        // إذا كان عدد الأصناف كبير جداً (> 5000)
        if (count($itemIds) > 5000) {
            return true;
        }

        // إذا كان عدد العمليات المتأثرة كبير جداً (> 500,000)
        // استخدام DB facade لتجنب مشاكل ambiguous columns
        $query = \Illuminate\Support\Facades\DB::table('operation_items')
            ->whereIn('operation_items.item_id', $itemIds)
            ->where('operation_items.is_stock', 1)
            ->join('operhead', 'operation_items.pro_id', '=', 'operhead.id')
            ->where('operhead.isdeleted', 0);

        if ($fromDate) {
            $query->where('operhead.pro_date', '>=', $fromDate);
        }

        $operationCount = $query->count();

        return $operationCount > 500000;
    }
}
