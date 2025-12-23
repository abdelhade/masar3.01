<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Batch Processing Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control how items are processed in batches during
    | average cost recalculation operations.
    |
    */

    // Number of items to process in each batch during recalculation
    'batch_size' => env('RECALC_BATCH_SIZE', 100),

    // Number of items to process per chunk when recalculating all items
    'chunk_size' => env('RECALC_CHUNK_SIZE', 500),

    /*
    |--------------------------------------------------------------------------
    | Strategy Selection Thresholds
    |--------------------------------------------------------------------------
    |
    | These thresholds determine which recalculation strategy to use based
    | on the number of items being processed.
    |
    */

    // Minimum number of items to use stored procedures (if enabled)
    'stored_procedure_threshold' => env('RECALC_SP_THRESHOLD', 1000),

    // Minimum number of items to use queue jobs (if enabled)
    'queue_threshold' => env('RECALC_QUEUE_THRESHOLD', 5000),

    // Threshold for operation count to determine if stored procedures should be used
    'operation_count_threshold' => env('RECALC_OP_THRESHOLD', 100000),

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific features of the recalculation system.
    |
    */

    // Enable stored procedures for large datasets
    'use_stored_procedures' => env('RECALC_USE_SP', false),

    // Enable queue jobs for very large datasets
    'use_queue' => env('RECALC_USE_QUEUE', true),

    // Enable manufacturing chain recalculation
    'manufacturing_chain_enabled' => env('RECALC_MFG_CHAIN', true),

    // Enable consistency checking
    'consistency_check_enabled' => env('RECALC_CONSISTENCY_CHECK', true),

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Settings for performance monitoring and logging.
    |
    */

    // Threshold in seconds for logging performance warnings
    'performance_warning_threshold' => env('RECALC_PERF_WARNING', 30.0),

    // Enable detailed performance logging
    'log_performance' => env('RECALC_LOG_PERF', true),

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for queue job processing.
    |
    */

    // Queue name for standard recalculation jobs
    'queue_name' => env('RECALC_QUEUE_NAME', 'recalculation'),

    // Queue name for large recalculation jobs
    'queue_name_large' => env('RECALC_QUEUE_LARGE', 'recalculation-large'),

    // Maximum execution time for queue jobs (in seconds)
    'queue_timeout' => env('RECALC_QUEUE_TIMEOUT', 600),

    // Number of retry attempts for failed queue jobs
    'queue_tries' => env('RECALC_QUEUE_TRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Manufacturing Chain Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for manufacturing invoice chain recalculation.
    |
    */

    // Operation types that represent manufacturing operations
    'manufacturing_operation_types' => [59],

    // Cost allocation method for distributing raw material costs to products
    // Options: 'proportional' (based on quantity), 'equal' (split equally)
    'manufacturing_cost_allocation' => env('RECALC_MFG_COST_ALLOCATION', 'proportional'),

    /*
    |--------------------------------------------------------------------------
    | Consistency Checking Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for average cost consistency verification.
    |
    */

    // Tolerance for average cost differences (in currency units)
    // Values within this tolerance are considered consistent
    'consistency_tolerance' => env('RECALC_CONSISTENCY_TOLERANCE', 0.01),

    // Batch size for consistency checking operations
    'consistency_check_batch_size' => env('RECALC_CONSISTENCY_BATCH', 500),

    /*
    |--------------------------------------------------------------------------
    | Error Handling Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for error handling and retry logic.
    |
    */

    // Maximum number of retry attempts for transient errors
    'max_retries' => env('RECALC_MAX_RETRIES', 3),

    // Initial retry delay in milliseconds
    'retry_delay_ms' => env('RECALC_RETRY_DELAY', 1000),

    // Use exponential backoff for retries
    'retry_exponential_backoff' => env('RECALC_RETRY_BACKOFF', true),
];
