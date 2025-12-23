<?php

declare(strict_types=1);

namespace App\Services\Monitoring;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Performance monitoring service for average cost recalculation operations.
 *
 * This class tracks execution time, memory usage, and other performance metrics
 * for recalculation operations. It provides structured logging and performance
 * warnings when operations exceed configured thresholds.
 *
 * @example
 * $monitor = new RecalculationPerformanceMonitor();
 * $operationId = $monitor->start('batch_recalculation', ['item_count' => 100]);
 * // ... perform recalculation ...
 * $monitor->end($operationId, ['items_processed' => 100, 'success' => true]);
 */
class RecalculationPerformanceMonitor
{
    /**
     * Active operations being monitored.
     *
     * @var array<string, array{
     *     operation_type: string,
     *     start_time: float,
     *     start_memory: int,
     *     context: array
     * }>
     */
    private array $operations = [];

    /**
     * Completed operations statistics.
     *
     * @var array<int, array{
     *     operation_id: string,
     *     operation_type: string,
     *     duration: float,
     *     memory_used: int,
     *     context: array,
     *     results: array,
     *     timestamp: int
     * }>
     */
    private array $statistics = [];

    /**
     * Performance warning threshold in seconds.
     * Operations exceeding this duration will trigger a warning.
     */
    private float $warningThreshold;

    /**
     * Create a new performance monitor instance.
     *
     * @param  float|null  $warningThreshold  Warning threshold in seconds (default: 30.0)
     */
    public function __construct(?float $warningThreshold = null)
    {
        $this->warningThreshold = $warningThreshold ?? config('recalculation.performance_warning_threshold', 30.0);
    }

    /**
     * Start monitoring a recalculation operation.
     *
     * Generates a unique operation ID and records the start time, memory usage,
     * and context information. Logs the operation start with structured data.
     *
     * @param  string  $operationType  Type of operation (e.g., 'single', 'batch', 'queue', 'manufacturing_chain')
     * @param  array  $context  Additional context (item count, strategy, dates, etc.)
     * @return string Unique operation ID for tracking
     *
     * @example
     * $operationId = $monitor->start('batch_recalculation', [
     *     'item_count' => 150,
     *     'from_date' => '2024-01-01',
     *     'strategy' => 'php_optimized'
     * ]);
     */
    public function start(string $operationType, array $context = []): string
    {
        $operationId = Str::uuid()->toString();

        $this->operations[$operationId] = [
            'operation_type' => $operationType,
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(true),
            'context' => $context,
        ];

        Log::info('Recalculation operation started', [
            'operation_id' => $operationId,
            'operation_type' => $operationType,
            'context' => $context,
            'timestamp' => now()->toIso8601String(),
        ]);

        return $operationId;
    }

    /**
     * End monitoring and log results for a recalculation operation.
     *
     * Calculates duration and memory usage, logs completion with results,
     * and checks if the operation exceeded the warning threshold.
     *
     * @param  string  $operationId  Operation ID from start()
     * @param  array  $results  Results data (items processed, errors, success status, etc.)
     *
     * @throws \InvalidArgumentException if operation ID is not found
     *
     * @example
     * $monitor->end($operationId, [
     *     'items_processed' => 150,
     *     'success' => true,
     *     'errors' => []
     * ]);
     */
    public function end(string $operationId, array $results = []): void
    {
        if (! isset($this->operations[$operationId])) {
            throw new \InvalidArgumentException("Operation ID not found: {$operationId}");
        }

        $operation = $this->operations[$operationId];
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);

        $duration = $endTime - $operation['start_time'];
        $memoryUsed = $endMemory - $operation['start_memory'];

        // Store statistics
        $this->statistics[] = [
            'operation_id' => $operationId,
            'operation_type' => $operation['operation_type'],
            'duration' => $duration,
            'memory_used' => $memoryUsed,
            'context' => $operation['context'],
            'results' => $results,
            'timestamp' => time(),
        ];

        // Log completion
        Log::info('Recalculation operation completed', [
            'operation_id' => $operationId,
            'operation_type' => $operation['operation_type'],
            'duration_seconds' => round($duration, 3),
            'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
            'context' => $operation['context'],
            'results' => $results,
            'timestamp' => now()->toIso8601String(),
        ]);

        // Check if operation was slow
        if ($duration > $this->warningThreshold) {
            $this->logSlowOperation($operationId, $duration, [
                'operation_type' => $operation['operation_type'],
                'context' => $operation['context'],
                'results' => $results,
            ]);
        }

        // Clean up
        unset($this->operations[$operationId]);
    }

    /**
     * Log a warning for slow operations.
     *
     * Called automatically by end() when an operation exceeds the warning threshold,
     * but can also be called manually for custom performance warnings.
     *
     * @param  string  $operationId  Operation ID
     * @param  float  $duration  Duration in seconds
     * @param  array  $context  Additional context for the warning
     *
     * @example
     * $monitor->logSlowOperation($operationId, 45.2, [
     *     'operation_type' => 'batch_recalculation',
     *     'item_count' => 5000,
     *     'reason' => 'Large dataset'
     * ]);
     */
    public function logSlowOperation(string $operationId, float $duration, array $context = []): void
    {
        Log::warning('Slow recalculation operation detected', [
            'operation_id' => $operationId,
            'duration_seconds' => round($duration, 3),
            'threshold_seconds' => $this->warningThreshold,
            'exceeded_by_seconds' => round($duration - $this->warningThreshold, 3),
            'context' => $context,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get performance statistics for completed operations.
     *
     * Returns statistics for recent operations, optionally filtered by operation type.
     * Useful for performance analysis and monitoring dashboards.
     *
     * @param  string|null  $operationType  Filter by operation type (null for all)
     * @param  int  $limit  Number of recent operations to return (default: 100)
     * @return array Performance statistics with aggregated metrics
     *
     * @example
     * $stats = $monitor->getStatistics('batch_recalculation', 50);
     * // Returns: [
     * //   'operations' => [...],
     * //   'summary' => [
     * //     'total_operations' => 50,
     * //     'avg_duration' => 12.5,
     * //     'max_duration' => 45.2,
     * //     'min_duration' => 2.1,
     * //     'total_memory_mb' => 1024.5
     * //   ]
     * // ]
     */
    public function getStatistics(?string $operationType = null, int $limit = 100): array
    {
        $filtered = $this->statistics;

        // Filter by operation type if specified
        if ($operationType !== null) {
            $filtered = array_filter($filtered, fn ($stat) => $stat['operation_type'] === $operationType);
        }

        // Get most recent operations
        $filtered = array_slice(array_reverse($filtered), 0, $limit);

        // Calculate summary statistics
        $summary = [
            'total_operations' => count($filtered),
            'avg_duration' => 0,
            'max_duration' => 0,
            'min_duration' => 0,
            'total_memory_mb' => 0,
        ];

        if (count($filtered) > 0) {
            $durations = array_column($filtered, 'duration');
            $memoryUsed = array_column($filtered, 'memory_used');

            $summary['avg_duration'] = round(array_sum($durations) / count($durations), 3);
            $summary['max_duration'] = round(max($durations), 3);
            $summary['min_duration'] = round(min($durations), 3);
            $summary['total_memory_mb'] = round(array_sum($memoryUsed) / 1024 / 1024, 2);
        }

        return [
            'operations' => $filtered,
            'summary' => $summary,
        ];
    }

    /**
     * Get the current warning threshold.
     *
     * @return float Warning threshold in seconds
     */
    public function getWarningThreshold(): float
    {
        return $this->warningThreshold;
    }

    /**
     * Set a new warning threshold.
     *
     * @param  float  $threshold  Warning threshold in seconds
     */
    public function setWarningThreshold(float $threshold): void
    {
        $this->warningThreshold = $threshold;
    }
}
