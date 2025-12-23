<?php

declare(strict_types=1);

namespace App\Services\Config;

use Illuminate\Support\Facades\Log;

/**
 * Configuration Manager for Average Cost Recalculation System
 *
 * Provides centralized access to recalculation configuration with validation
 * and safe defaults for missing or invalid configuration values.
 */
class RecalculationConfigManager
{
    /**
     * Get batch size for processing items
     *
     * @return int Batch size (default: 100)
     */
    public static function getBatchSize(): int
    {
        $value = config('recalculation.batch_size', 100);

        if (! is_int($value) || $value <= 0) {
            Log::warning('Invalid batch_size configuration, using default', [
                'configured_value' => $value,
                'default_value' => 100,
            ]);

            return 100;
        }

        return $value;
    }

    /**
     * Get chunk size for processing all items
     *
     * @return int Chunk size (default: 500)
     */
    public static function getChunkSize(): int
    {
        $value = config('recalculation.chunk_size', 500);

        if (! is_int($value) || $value <= 0) {
            Log::warning('Invalid chunk_size configuration, using default', [
                'configured_value' => $value,
                'default_value' => 500,
            ]);

            return 500;
        }

        return $value;
    }

    /**
     * Get threshold for using stored procedures
     *
     * @return int Item count threshold (default: 1000)
     */
    public static function getStoredProcedureThreshold(): int
    {
        $value = config('recalculation.stored_procedure_threshold', 1000);

        if (! is_int($value) || $value < 0) {
            Log::warning('Invalid stored_procedure_threshold configuration, using default', [
                'configured_value' => $value,
                'default_value' => 1000,
            ]);

            return 1000;
        }

        return $value;
    }

    /**
     * Get threshold for using queue jobs
     *
     * @return int Item count threshold (default: 5000)
     */
    public static function getQueueThreshold(): int
    {
        $value = config('recalculation.queue_threshold', 5000);

        if (! is_int($value) || $value < 0) {
            Log::warning('Invalid queue_threshold configuration, using default', [
                'configured_value' => $value,
                'default_value' => 5000,
            ]);

            return 5000;
        }

        return $value;
    }

    /**
     * Get threshold for operation count
     *
     * @return int Operation count threshold (default: 100000)
     */
    public static function getOperationCountThreshold(): int
    {
        $value = config('recalculation.operation_count_threshold', 100000);

        if (! is_int($value) || $value < 0) {
            Log::warning('Invalid operation_count_threshold configuration, using default', [
                'configured_value' => $value,
                'default_value' => 100000,
            ]);

            return 100000;
        }

        return $value;
    }

    /**
     * Check if stored procedures are enabled
     *
     * @return bool True if enabled
     */
    public static function isStoredProceduresEnabled(): bool
    {
        return (bool) config('recalculation.use_stored_procedures', false);
    }

    /**
     * Check if queue is enabled
     *
     * @return bool True if queue is enabled
     */
    public static function isQueueEnabled(): bool
    {
        return (bool) config('recalculation.use_queue', true);
    }

    /**
     * Check if manufacturing chain recalculation is enabled
     *
     * @return bool True if enabled
     */
    public static function isManufacturingChainEnabled(): bool
    {
        return (bool) config('recalculation.manufacturing_chain_enabled', true);
    }

    /**
     * Check if consistency checking is enabled
     *
     * @return bool True if enabled
     */
    public static function isConsistencyCheckEnabled(): bool
    {
        return (bool) config('recalculation.consistency_check_enabled', true);
    }

    /**
     * Get performance warning threshold (seconds)
     *
     * @return float Warning threshold in seconds (default: 30.0)
     */
    public static function getPerformanceWarningThreshold(): float
    {
        $value = config('recalculation.performance_warning_threshold', 30.0);

        if (! is_numeric($value) || $value <= 0) {
            Log::warning('Invalid performance_warning_threshold configuration, using default', [
                'configured_value' => $value,
                'default_value' => 30.0,
            ]);

            return 30.0;
        }

        return (float) $value;
    }

    /**
     * Check if performance logging is enabled
     *
     * @return bool True if enabled
     */
    public static function isPerformanceLoggingEnabled(): bool
    {
        return (bool) config('recalculation.log_performance', true);
    }

    /**
     * Get queue name for standard jobs
     *
     * @return string Queue name (default: 'recalculation')
     */
    public static function getQueueName(): string
    {
        $value = config('recalculation.queue_name', 'recalculation');

        if (! is_string($value) || empty($value)) {
            Log::warning('Invalid queue_name configuration, using default', [
                'configured_value' => $value,
                'default_value' => 'recalculation',
            ]);

            return 'recalculation';
        }

        return $value;
    }

    /**
     * Get queue name for large jobs
     *
     * @return string Queue name (default: 'recalculation-large')
     */
    public static function getQueueNameLarge(): string
    {
        $value = config('recalculation.queue_name_large', 'recalculation-large');

        if (! is_string($value) || empty($value)) {
            Log::warning('Invalid queue_name_large configuration, using default', [
                'configured_value' => $value,
                'default_value' => 'recalculation-large',
            ]);

            return 'recalculation-large';
        }

        return $value;
    }

    /**
     * Get queue timeout in seconds
     *
     * @return int Timeout in seconds (default: 600)
     */
    public static function getQueueTimeout(): int
    {
        $value = config('recalculation.queue_timeout', 600);

        if (! is_int($value) || $value <= 0) {
            Log::warning('Invalid queue_timeout configuration, using default', [
                'configured_value' => $value,
                'default_value' => 600,
            ]);

            return 600;
        }

        return $value;
    }

    /**
     * Get number of queue retry attempts
     *
     * @return int Number of retries (default: 3)
     */
    public static function getQueueTries(): int
    {
        $value = config('recalculation.queue_tries', 3);

        if (! is_int($value) || $value < 0) {
            Log::warning('Invalid queue_tries configuration, using default', [
                'configured_value' => $value,
                'default_value' => 3,
            ]);

            return 3;
        }

        return $value;
    }

    /**
     * Get manufacturing operation types
     *
     * @return array Array of operation type IDs (default: [59])
     */
    public static function getManufacturingOperationTypes(): array
    {
        $value = config('recalculation.manufacturing_operation_types', [59]);

        if (! is_array($value) || empty($value)) {
            Log::warning('Invalid manufacturing_operation_types configuration, using default', [
                'configured_value' => $value,
                'default_value' => [59],
            ]);

            return [59];
        }

        return $value;
    }

    /**
     * Get manufacturing cost allocation method
     *
     * @return string Allocation method (default: 'proportional')
     */
    public static function getManufacturingCostAllocation(): string
    {
        $value = config('recalculation.manufacturing_cost_allocation', 'proportional');
        $validMethods = ['proportional', 'equal'];

        if (! is_string($value) || ! in_array($value, $validMethods, true)) {
            Log::warning('Invalid manufacturing_cost_allocation configuration, using default', [
                'configured_value' => $value,
                'default_value' => 'proportional',
                'valid_methods' => $validMethods,
            ]);

            return 'proportional';
        }

        return $value;
    }

    /**
     * Get consistency tolerance
     *
     * @return float Tolerance in currency units (default: 0.01)
     */
    public static function getConsistencyTolerance(): float
    {
        $value = config('recalculation.consistency_tolerance', 0.01);

        if (! is_numeric($value) || $value < 0) {
            Log::warning('Invalid consistency_tolerance configuration, using default', [
                'configured_value' => $value,
                'default_value' => 0.01,
            ]);

            return 0.01;
        }

        return (float) $value;
    }

    /**
     * Get consistency check batch size
     *
     * @return int Batch size (default: 500)
     */
    public static function getConsistencyCheckBatchSize(): int
    {
        $value = config('recalculation.consistency_check_batch_size', 500);

        if (! is_int($value) || $value <= 0) {
            Log::warning('Invalid consistency_check_batch_size configuration, using default', [
                'configured_value' => $value,
                'default_value' => 500,
            ]);

            return 500;
        }

        return $value;
    }

    /**
     * Get maximum retry attempts
     *
     * @return int Maximum retries (default: 3)
     */
    public static function getMaxRetries(): int
    {
        $value = config('recalculation.max_retries', 3);

        if (! is_int($value) || $value < 0) {
            Log::warning('Invalid max_retries configuration, using default', [
                'configured_value' => $value,
                'default_value' => 3,
            ]);

            return 3;
        }

        return $value;
    }

    /**
     * Get retry delay in milliseconds
     *
     * @return int Delay in milliseconds (default: 1000)
     */
    public static function getRetryDelayMs(): int
    {
        $value = config('recalculation.retry_delay_ms', 1000);

        if (! is_int($value) || $value < 0) {
            Log::warning('Invalid retry_delay_ms configuration, using default', [
                'configured_value' => $value,
                'default_value' => 1000,
            ]);

            return 1000;
        }

        return $value;
    }

    /**
     * Check if exponential backoff is enabled for retries
     *
     * @return bool True if enabled
     */
    public static function isRetryExponentialBackoffEnabled(): bool
    {
        return (bool) config('recalculation.retry_exponential_backoff', true);
    }

    /**
     * Validate all configuration values
     *
     * @return array Validation results with warnings
     */
    public static function validateConfiguration(): array
    {
        $warnings = [];

        // Validate batch size
        $batchSize = config('recalculation.batch_size');
        if ($batchSize !== null && (! is_int($batchSize) || $batchSize <= 0)) {
            $warnings[] = 'batch_size must be a positive integer';
        }

        // Validate chunk size
        $chunkSize = config('recalculation.chunk_size');
        if ($chunkSize !== null && (! is_int($chunkSize) || $chunkSize <= 0)) {
            $warnings[] = 'chunk_size must be a positive integer';
        }

        // Validate thresholds
        $spThreshold = config('recalculation.stored_procedure_threshold');
        if ($spThreshold !== null && (! is_int($spThreshold) || $spThreshold < 0)) {
            $warnings[] = 'stored_procedure_threshold must be a non-negative integer';
        }

        $queueThreshold = config('recalculation.queue_threshold');
        if ($queueThreshold !== null && (! is_int($queueThreshold) || $queueThreshold < 0)) {
            $warnings[] = 'queue_threshold must be a non-negative integer';
        }

        // Validate performance threshold
        $perfThreshold = config('recalculation.performance_warning_threshold');
        if ($perfThreshold !== null && (! is_numeric($perfThreshold) || $perfThreshold <= 0)) {
            $warnings[] = 'performance_warning_threshold must be a positive number';
        }

        // Validate queue names
        $queueName = config('recalculation.queue_name');
        if ($queueName !== null && (! is_string($queueName) || empty($queueName))) {
            $warnings[] = 'queue_name must be a non-empty string';
        }

        $queueNameLarge = config('recalculation.queue_name_large');
        if ($queueNameLarge !== null && (! is_string($queueNameLarge) || empty($queueNameLarge))) {
            $warnings[] = 'queue_name_large must be a non-empty string';
        }

        // Validate manufacturing cost allocation
        $costAllocation = config('recalculation.manufacturing_cost_allocation');
        if ($costAllocation !== null && ! in_array($costAllocation, ['proportional', 'equal'], true)) {
            $warnings[] = 'manufacturing_cost_allocation must be either "proportional" or "equal"';
        }

        // Validate consistency tolerance
        $tolerance = config('recalculation.consistency_tolerance');
        if ($tolerance !== null && (! is_numeric($tolerance) || $tolerance < 0)) {
            $warnings[] = 'consistency_tolerance must be a non-negative number';
        }

        // Log warnings if any
        if (! empty($warnings)) {
            Log::warning('Configuration validation found issues', [
                'warnings' => $warnings,
            ]);
        }

        return [
            'valid' => empty($warnings),
            'warnings' => $warnings,
        ];
    }
}
