<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\AverageCostRecalculationServiceOptimized;
use App\Services\Monitoring\RecalculationPerformanceMonitor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Background job for average cost recalculation.
 *
 * This job handles large-scale average cost recalculation in the background
 * to avoid blocking the main application. It's automatically dispatched when:
 * - Item count exceeds 5000 items
 * - Operation count exceeds 500,000 operations
 * - Force queue flag is set
 *
 * Features:
 * - Automatic queue assignment based on data size
 * - 10-minute timeout for large datasets
 * - 3 retry attempts with exponential backoff
 * - Performance monitoring integration
 * - Comprehensive error logging
 *
 * @example
 * // Dispatch for large dataset
 * RecalculateAverageCostJob::dispatch([1, 2, 3, ...], '2024-01-01', false);
 * @example
 * // Full recalculation
 * RecalculateAverageCostJob::dispatch([], null, true);
 */
class RecalculateAverageCostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Job timeout in seconds (10 minutes).
     */
    public int $timeout = 600;

    /**
     * Number of retry attempts.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     *
     * Automatically assigns the job to the appropriate queue based on data size:
     * - 'recalculation' queue for <1000 items
     * - 'recalculation-large' queue for >=1000 items or full recalculation
     *
     * @param  array  $itemIds  Array of item IDs to recalculate
     * @param  string|null  $fromDate  Start date (Y-m-d format), null for all operations
     * @param  bool  $isFullRecalculation  True for full recalculation of all items
     */
    public function __construct(
        public array $itemIds,
        public ?string $fromDate = null,
        public bool $isFullRecalculation = false
    ) {
        // تحديد queue connection حسب الحجم
        $queueName = 'recalculation';
        if (count($itemIds) > 1000 || $isFullRecalculation) {
            $queueName = 'recalculation-large';
        }

        Log::info('Queue assignment for recalculation job', [
            'queue' => $queueName,
            'item_count' => count($itemIds),
            'is_full' => $isFullRecalculation,
        ]);

        $this->onQueue($queueName);
    }

    /**
     * Execute the job.
     *
     * Performs average cost recalculation using the optimized service.
     * Tracks performance metrics and logs detailed information about
     * the operation for monitoring and debugging.
     *
     * @param  AverageCostRecalculationServiceOptimized  $service  Recalculation service instance
     *
     * @throws \Exception if recalculation fails
     */
    public function handle(AverageCostRecalculationServiceOptimized $service): void
    {
        $monitor = new RecalculationPerformanceMonitor;
        $operationId = $monitor->start('queue_job', [
            'item_count' => count($this->itemIds),
            'from_date' => $this->fromDate,
            'is_full' => $this->isFullRecalculation,
            'queue' => $this->queue,
        ]);

        try {
            Log::info('Starting average cost recalculation job', [
                'item_count' => count($this->itemIds),
                'from_date' => $this->fromDate,
                'is_full' => $this->isFullRecalculation,
                'queue' => $this->queue,
            ]);

            if ($this->isFullRecalculation) {
                $service->recalculateAllItems($this->fromDate);
            } else {
                $service->recalculateAverageCostForItems($this->itemIds, $this->fromDate);
            }

            Log::info('Completed average cost recalculation job', [
                'item_count' => count($this->itemIds),
            ]);

            $monitor->end($operationId, [
                'success' => true,
                'items_processed' => count($this->itemIds),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in average cost recalculation job', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $monitor->end($operationId, [
                'success' => false,
                'error' => 'exception',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Average cost recalculation job failed', [
            'item_ids' => $this->itemIds,
            'error' => $exception->getMessage(),
        ]);
    }
}
