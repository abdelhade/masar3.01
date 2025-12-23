<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\RecalculateAverageCostJob;
use App\Services\Config\RecalculationConfigManager;
use Illuminate\Support\Facades\Log;

/**
 * Factory for selecting appropriate recalculation service based on data size.
 *
 * Implements a hybrid approach that automatically selects the optimal service:
 * - Stored Procedures for large datasets (>1000 items or >100,000 operations)
 * - PHP Optimized Service for small/medium datasets (<1000 items)
 *
 * The factory validates configuration on first use and logs strategy selection
 * decisions for monitoring and debugging.
 *
 * @example
 * $service = RecalculationServiceFactory::createAverageCostService([1, 2, 3], '2024-01-01');
 * $service->recalculateFromOperationWithItems([1, 2, 3], '2024-01-01');
 */
class RecalculationServiceFactory
{
    /**
     * Flag to track if configuration has been validated.
     */
    private static bool $configValidated = false;

    /**
     * Validate configuration if not already validated.
     *
     * Performs one-time validation of recalculation configuration and logs
     * any warnings about invalid values. Uses safe defaults for invalid config.
     */
    private static function validateConfigurationOnce(): void
    {
        if (self::$configValidated) {
            return;
        }

        $validation = RecalculationConfigManager::validateConfiguration();
        if (! $validation['valid']) {
            Log::warning('Recalculation configuration has issues', [
                'warnings' => $validation['warnings'],
            ]);
        }

        self::$configValidated = true;
    }

    /**
     * Determine if stored procedures should be used for recalculation.
     *
     * Checks multiple criteria:
     * - Stored procedures must be enabled in configuration
     * - Item count must exceed threshold (default: 1000)
     * - OR operation count must exceed threshold (default: 100,000)
     *
     * @param  array  $itemIds  Array of item IDs to recalculate
     * @param  string|null  $fromDate  Start date for filtering operations
     * @return bool True if stored procedures should be used
     */
    private static function shouldUseStoredProcedures(array $itemIds, ?string $fromDate = null): bool
    {
        self::validateConfigurationOnce();

        // التحقق من الإعداد
        if (! RecalculationConfigManager::isStoredProceduresEnabled()) {
            return false;
        }

        // Get threshold from configuration
        $threshold = RecalculationConfigManager::getStoredProcedureThreshold();

        // إذا كان عدد الأصناف كبير جداً
        if (count($itemIds) > $threshold) {
            Log::info('Using stored procedures based on item count', [
                'item_count' => count($itemIds),
                'threshold' => $threshold,
            ]);

            return true;
        }

        // Get operation count threshold from configuration
        $operationThreshold = RecalculationConfigManager::getOperationCountThreshold();

        // Skip operation count check if threshold is very high (e.g., in tests)
        // This avoids unnecessary database queries when we know the result
        if ($operationThreshold >= PHP_INT_MAX - 1000) {
            return false;
        }

        // إذا كان عدد الفواتير المتأثرة كبير
        $operationCount = self::getOperationCount($itemIds, $fromDate);

        // إذا كان هناك أكثر من threshold عملية متأثرة، استخدم Stored Procedures
        if ($operationCount > $operationThreshold) {
            Log::info('Using stored procedures based on operation count', [
                'operation_count' => $operationCount,
                'threshold' => $operationThreshold,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Get operation count for given items.
     *
     * Counts the number of stock operations for the specified items,
     * optionally filtered by date. Extracted as a separate method to
     * allow mocking in tests.
     *
     * @param  array  $itemIds  Array of item IDs
     * @param  string|null  $fromDate  Start date for filtering (Y-m-d format)
     * @return int Number of operations
     */
    protected static function getOperationCount(array $itemIds, ?string $fromDate = null): int
    {
        return \App\Models\OperationItems::whereIn('operation_items.item_id', $itemIds)
            ->where('operation_items.is_stock', 1)
            ->join('operhead', 'operation_items.pro_id', '=', 'operhead.id')
            ->where('operhead.isdeleted', 0)
            ->when($fromDate, function ($query) use ($fromDate) {
                return $query->where('operhead.pro_date', '>=', $fromDate);
            })
            ->count();
    }

    /**
     * Determine if queue jobs should be used for recalculation.
     *
     * Checks if queue processing should be used based on:
     * - Queue must be enabled in configuration
     * - Item count must exceed threshold (default: 5000)
     *
     * @param  array  $itemIds  Array of item IDs to recalculate
     * @param  string|null  $fromDate  Start date for filtering operations
     * @return bool True if queue should be used
     */
    private static function shouldUseQueue(array $itemIds, ?string $fromDate = null): bool
    {
        self::validateConfigurationOnce();

        // Check if queue is enabled in configuration
        if (! RecalculationConfigManager::isQueueEnabled()) {
            return false;
        }

        // Get threshold from configuration
        $threshold = RecalculationConfigManager::getQueueThreshold();

        // إذا كان عدد الأصناف كبير جداً
        if (count($itemIds) > $threshold) {
            Log::info('Using queue based on item count', [
                'item_count' => count($itemIds),
                'threshold' => $threshold,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Recalculate average cost with automatic strategy selection.
     *
     * Main entry point for average cost recalculation. Automatically selects
     * between queue jobs, stored procedures, or PHP service based on data size.
     *
     * @param  array  $itemIds  Array of item IDs to recalculate
     * @param  string|null  $fromDate  Start date (Y-m-d format), null for all operations
     * @param  bool  $useQueue  Force use of queue job
     *
     * @example
     * RecalculationServiceFactory::recalculateAverageCost([1, 2, 3], '2024-01-01');
     */
    public static function recalculateAverageCost(array $itemIds, ?string $fromDate = null, bool $useQueue = false): void
    {
        if (empty($itemIds)) {
            return;
        }

        // استخدام Queue للبيانات الكبيرة جداً
        if ($useQueue || self::shouldUseQueue($itemIds, $fromDate)) {
            RecalculateAverageCostJob::dispatch($itemIds, $fromDate, false);

            return;
        }

        // استخدام Stored Procedures أو PHP Services
        $service = self::createAverageCostService($itemIds, $fromDate);
        $service->recalculateFromOperationWithItems($itemIds, $fromDate ?? date('Y-m-d'));
    }

    /**
     * Create average cost recalculation service instance.
     *
     * Factory method that creates the appropriate service based on data size:
     * - AverageCostRecalculationServiceStoredProcedure for large datasets
     * - AverageCostRecalculationServiceOptimized for small/medium datasets
     *
     * @param  array  $itemIds  Array of item IDs to recalculate
     * @param  string|null  $fromDate  Start date for filtering operations
     *
     * @example
     * $service = RecalculationServiceFactory::createAverageCostService([1, 2, 3], '2024-01-01');
     */
    public static function createAverageCostService(array $itemIds, ?string $fromDate = null): AverageCostRecalculationServiceOptimized|AverageCostRecalculationServiceStoredProcedure
    {
        if (self::shouldUseStoredProcedures($itemIds, $fromDate)) {
            return new AverageCostRecalculationServiceStoredProcedure;
        }

        // Inject RecalculationPerformanceMonitor dependency
        $monitor = app(\App\Services\Monitoring\RecalculationPerformanceMonitor::class);

        return new AverageCostRecalculationServiceOptimized($monitor);
    }

    /**
     * Create profit and journal recalculation service instance.
     *
     * Factory method that creates the appropriate service based on data size:
     * - ProfitAndJournalRecalculationServiceStoredProcedure for large datasets
     * - ProfitAndJournalRecalculationServiceOptimized for small/medium datasets
     *
     * @param  array  $itemIds  Array of item IDs to recalculate
     * @param  string|null  $fromDate  Start date for filtering operations
     *
     * @example
     * $service = RecalculationServiceFactory::createProfitService([1, 2, 3], '2024-01-01');
     */
    public static function createProfitService(array $itemIds, ?string $fromDate = null): ProfitAndJournalRecalculationServiceOptimized|ProfitAndJournalRecalculationServiceStoredProcedure
    {
        if (self::shouldUseStoredProcedures($itemIds, $fromDate)) {
            return new ProfitAndJournalRecalculationServiceStoredProcedure;
        }

        return new ProfitAndJournalRecalculationServiceOptimized;
    }
}
