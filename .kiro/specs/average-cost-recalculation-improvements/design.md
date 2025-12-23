# Design Document: Average Cost Recalculation System Improvements

## Overview

This document describes the design for improving the Average Cost Recalculation System in the MASSAR application. The system uses a hybrid approach that automatically selects the optimal recalculation strategy based on data size: PHP-optimized services for small/medium datasets, Stored Procedures for large datasets, and Queue Jobs for very large datasets.

The improvements focus on:
1. Code quality (removing unused imports, adding error handling)
2. Performance monitoring and logging
3. Input validation and data integrity
4. Comprehensive testing
5. Manufacturing invoice chain recalculation
6. Configuration management
7. Documentation

## Architecture

### Current Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Application Layer                         │
│  (Controllers, Services: SaveInvoiceService,                 │
│   ManufacturingInvoiceService, InvoiceController)            │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│              RecalculationServiceHelper                      │
│         (Unified Interface - Auto Strategy Selection)        │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│            RecalculationServiceFactory                       │
│          (Strategy Selection Based on Data Size)             │
└────────┬───────────────┬────────────────────┬───────────────┘
         │               │                    │
         ▼               ▼                    ▼
┌────────────────┐ ┌──────────────┐ ┌──────────────────────┐
│  Queue Job     │ │   Stored     │ │  PHP Optimized       │
│  (>5000 items) │ │  Procedures  │ │  Service             │
│                │ │  (>1000      │ │  (<1000 items)       │
│                │ │   items)     │ │                      │
└────────────────┘ └──────────────┘ └──────────────────────┘
```

### Improved Architecture

The improved architecture adds:
1. **Input Validation Layer**: Validates all inputs before processing
2. **Performance Monitoring**: Tracks execution time, memory usage, query count
3. **Error Handling**: Comprehensive exception handling with detailed logging
4. **Manufacturing Chain Handler**: Handles cascading recalculation for manufacturing invoices
5. **Configuration Manager**: Centralized configuration with validation
6. **Consistency Checker**: Verifies data integrity and calculation accuracy

```
┌─────────────────────────────────────────────────────────────┐
│                    Application Layer                         │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│              RecalculationServiceHelper                      │
│                  + Input Validation                          │
│                  + Performance Monitoring                    │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│            RecalculationServiceFactory                       │
│              + Configuration Manager                         │
│              + Stored Procedure Validator                    │
└────────┬───────────────┬────────────────────┬───────────────┘
         │               │                    │
         ▼               ▼                    ▼
┌────────────────┐ ┌──────────────┐ ┌──────────────────────┐
│  Queue Job     │ │   Stored     │ │  PHP Optimized       │
│  + Retry Logic │ │  Procedures  │ │  Service             │
│  + Progress    │ │  + Fallback  │ │  + Error Handling    │
│    Tracking    │ │    to PHP    │ │  + Batch Optimization│
└────────────────┘ └──────────────┘ └──────────────────────┘
         │               │                    │
         └───────────────┴────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│           Manufacturing Chain Handler (NEW)                  │
│  - Identifies affected manufacturing invoices                │
│  - Processes in chronological order                          │
│  - Handles raw materials → products cascade                  │
└─────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. Input Validator (NEW)

**Purpose**: Validate all inputs before processing to prevent errors and data corruption.

**Interface**:
```php
class RecalculationInputValidator
{
    /**
     * Validate item IDs array
     * 
     * @param array $itemIds Array of item IDs to validate
     * @throws InvalidArgumentException if validation fails
     * @return void
     */
    public static function validateItemIds(array $itemIds): void;

    /**
     * Validate date format
     * 
     * @param string|null $date Date string in Y-m-d format
     * @throws InvalidArgumentException if date format is invalid
     * @return void
     */
    public static function validateDate(?string $date): void;

    /**
     * Validate items exist in database
     * 
     * @param array $itemIds Array of item IDs to check
     * @return array Array of existing item IDs
     */
    public static function validateItemsExist(array $itemIds): array;

    /**
     * Validate boolean flag
     * 
     * @param mixed $value Value to validate as boolean
     * @return bool Validated boolean value
     */
    public static function validateBoolean($value): bool;
}
```

### 2. Performance Monitor (NEW)

**Purpose**: Track and log performance metrics for recalculation operations.

**Interface**:
```php
class RecalculationPerformanceMonitor
{
    /**
     * Start monitoring a recalculation operation
     * 
     * @param string $operationType Type of operation (single, batch, queue)
     * @param array $context Additional context (item count, strategy, etc.)
     * @return string Unique operation ID
     */
    public function start(string $operationType, array $context): string;

    /**
     * End monitoring and log results
     * 
     * @param string $operationId Operation ID from start()
     * @param array $results Results (items processed, errors, etc.)
     * @return void
     */
    public function end(string $operationId, array $results): void;

    /**
     * Log a warning for slow operations
     * 
     * @param string $operationId Operation ID
     * @param float $duration Duration in seconds
     * @param array $context Additional context
     * @return void
     */
    public function logSlowOperation(string $operationId, float $duration, array $context): void;

    /**
     * Get performance statistics
     * 
     * @param string|null $operationType Filter by operation type
     * @param int $limit Number of recent operations to return
     * @return array Performance statistics
     */
    public function getStatistics(?string $operationType = null, int $limit = 100): array;
}
```

### 3. Manufacturing Chain Handler (NEW)

**Purpose**: Handle cascading recalculation for manufacturing invoices when raw material costs change.

**Interface**:
```php
class ManufacturingChainHandler
{
    /**
     * Find all manufacturing invoices affected by raw material cost changes
     * 
     * @param array $rawMaterialItemIds Array of raw material item IDs
     * @param string $fromDate Start date for affected invoices
     * @return array Array of affected manufacturing invoice IDs with dates
     */
    public function findAffectedManufacturingInvoices(array $rawMaterialItemIds, string $fromDate): array;

    /**
     * Recalculate manufacturing chain in chronological order
     * 
     * @param array $manufacturingInvoiceIds Array of manufacturing invoice IDs
     * @param string $fromDate Start date for recalculation
     * @return array Results with processed invoices and updated items
     */
    public function recalculateChain(array $manufacturingInvoiceIds, string $fromDate): array;

    /**
     * Update product costs when raw material costs change
     * 
     * @param int $manufacturingInvoiceId Manufacturing invoice ID
     * @return array Updated product item IDs and new costs
     */
    public function updateProductCostsFromRawMaterials(int $manufacturingInvoiceId): array;

    /**
     * Get manufacturing invoice details (raw materials and products)
     * 
     * @param int $manufacturingInvoiceId Manufacturing invoice ID
     * @return array Invoice details with raw materials and products
     */
    public function getManufacturingInvoiceDetails(int $manufacturingInvoiceId): array;
}
```

### 4. Configuration Manager (NEW)

**Purpose**: Centralize and validate configuration for recalculation services.

**Interface**:
```php
class RecalculationConfigManager
{
    /**
     * Get batch size for processing
     * 
     * @return int Batch size (default: 100)
     */
    public static function getBatchSize(): int;

    /**
     * Get threshold for using stored procedures
     * 
     * @return int Item count threshold (default: 1000)
     */
    public static function getStoredProcedureThreshold(): int;

    /**
     * Get threshold for using queue jobs
     * 
     * @return int Item count threshold (default: 5000)
     */
    public static function getQueueThreshold(): int;

    /**
     * Check if stored procedures are enabled
     * 
     * @return bool True if enabled
     */
    public static function isStoredProceduresEnabled(): bool;

    /**
     * Check if queue is enabled
     * 
     * @return bool True if queue is enabled
     */
    public static function isQueueEnabled(): bool;

    /**
     * Get performance warning threshold (seconds)
     * 
     * @return float Warning threshold in seconds (default: 30.0)
     */
    public static function getPerformanceWarningThreshold(): float;

    /**
     * Validate all configuration values
     * 
     * @return array Validation results with warnings
     */
    public static function validateConfiguration(): array;
}
```

### 5. Consistency Checker (NEW)

**Purpose**: Verify data integrity and calculation accuracy.

**Interface**:
```php
class AverageCostConsistencyChecker
{
    /**
     * Check average cost consistency for specific items
     * 
     * @param array $itemIds Array of item IDs to check
     * @return array Inconsistencies found with details
     */
    public function checkItems(array $itemIds): array;

    /**
     * Check average cost consistency for all items
     * 
     * @param int $chunkSize Number of items to process at once
     * @return array Summary of inconsistencies
     */
    public function checkAllItems(int $chunkSize = 500): array;

    /**
     * Fix inconsistent average costs
     * 
     * @param array $itemIds Array of item IDs to fix
     * @param bool $dryRun If true, only report what would be fixed
     * @return array Results with fixed items
     */
    public function fixInconsistencies(array $itemIds, bool $dryRun = false): array;

    /**
     * Generate consistency report
     * 
     * @return array Detailed report with statistics
     */
    public function generateReport(): array;
}
```

### 6. Enhanced AverageCostRecalculationServiceOptimized

**Improvements**:
- Remove unused imports (`OperHead`, `OperationItems`)
- Add comprehensive error handling
- Add input validation
- Add performance monitoring
- Improve logging with context
- Add PHPDoc blocks

**Updated Interface**:
```php
class AverageCostRecalculationServiceOptimized
{
    private RecalculationPerformanceMonitor $monitor;

    public function __construct(RecalculationPerformanceMonitor $monitor);

    /**
     * Recalculate average cost for a single item
     * 
     * @param int $itemId Item ID to recalculate
     * @param string|null $fromDate Start date (Y-m-d format), null for all operations
     * @param bool $isDelete True if triggered by delete operation
     * @throws InvalidArgumentException if parameters are invalid
     * @throws RuntimeException if recalculation fails
     * @return void
     */
    public function recalculateAverageCostForItem(int $itemId, ?string $fromDate = null, bool $isDelete = false): void;

    /**
     * Recalculate average cost for multiple items in batches
     * 
     * @param array $itemIds Array of item IDs to recalculate
     * @param string|null $fromDate Start date (Y-m-d format), null for all operations
     * @param bool $isDelete True if triggered by delete operation
     * @throws InvalidArgumentException if parameters are invalid
     * @throws RuntimeException if recalculation fails
     * @return void
     */
    public function recalculateAverageCostForItems(array $itemIds, ?string $fromDate = null, bool $isDelete = false): void;

    /**
     * Recalculate average cost after operation modification/deletion
     * 
     * @param array $itemIds Array of item IDs affected
     * @param string $fromDate Operation date (Y-m-d format)
     * @param bool $isDelete True if operation was deleted
     * @throws InvalidArgumentException if parameters are invalid
     * @throws RuntimeException if recalculation fails
     * @return void
     */
    public function recalculateFromOperationWithItems(array $itemIds, string $fromDate, bool $isDelete = false): void;

    /**
     * Recalculate average cost for all items (maintenance operation)
     * 
     * @param string|null $fromDate Start date (Y-m-d format), null for all operations
     * @param int $chunkSize Number of items to process per batch
     * @throws RuntimeException if recalculation fails
     * @return void
     */
    public function recalculateAllItems(?string $fromDate = null, int $chunkSize = 500): void;

    /**
     * Calculate average cost for given data
     * Formula: SUM(detail_value) / SUM(qty_in - qty_out)
     * 
     * @param float $totalValue Total value from operations
     * @param float $totalQty Total quantity from operations
     * @return float Calculated average cost (0 if quantity is zero or negative)
     */
    private function calculateAverageCost(float $totalValue, float $totalQty): float;
}
```

### 7. Enhanced RecalculationServiceHelper

**Improvements**:
- Add input validation
- Add performance monitoring
- Add manufacturing chain handling
- Improve error handling
- Add detailed logging

**Updated Interface**:
```php
class RecalculationServiceHelper
{
    /**
     * Recalculate average cost with automatic strategy selection
     * 
     * @param array $itemIds Array of item IDs to recalculate
     * @param string|null $fromDate Start date (Y-m-d format), null for all operations
     * @param bool $forceQueue Force use of queue job regardless of data size
     * @param bool $isDelete True if triggered by delete operation
     * @throws InvalidArgumentException if parameters are invalid
     * @throws RuntimeException if recalculation fails
     * @return void
     */
    public static function recalculateAverageCost(
        array $itemIds, 
        ?string $fromDate = null, 
        bool $forceQueue = false, 
        bool $isDelete = false
    ): void;

    /**
     * Recalculate profits and journals with automatic strategy selection
     * 
     * @param array $itemIds Array of item IDs affected
     * @param string|null $fromDate Start date for affected operations
     * @param int|null $currentInvoiceId Current invoice ID to exclude from recalculation
     * @param string|null $currentInvoiceCreatedAt Current invoice creation time for same-day comparison
     * @throws InvalidArgumentException if parameters are invalid
     * @throws RuntimeException if recalculation fails
     * @return void
     */
    public static function recalculateProfitsAndJournals(
        array $itemIds, 
        ?string $fromDate = null, 
        ?int $currentInvoiceId = null, 
        ?string $currentInvoiceCreatedAt = null
    ): void;

    /**
     * Recalculate everything (average cost + profits + journals)
     * 
     * @param array $itemIds Array of item IDs to recalculate
     * @param string|null $fromDate Start date for recalculation
     * @param bool $forceQueue Force use of queue job
     * @param int|null $currentInvoiceId Current invoice ID to exclude
     * @param string|null $currentInvoiceCreatedAt Current invoice creation time
     * @throws InvalidArgumentException if parameters are invalid
     * @throws RuntimeException if recalculation fails
     * @return void
     */
    public static function recalculateAll(
        array $itemIds, 
        ?string $fromDate = null, 
        bool $forceQueue = false, 
        ?int $currentInvoiceId = null, 
        ?string $currentInvoiceCreatedAt = null
    ): void;

    /**
     * Recalculate manufacturing chain when raw materials change
     * 
     * @param array $rawMaterialItemIds Array of raw material item IDs
     * @param string $fromDate Start date for affected manufacturing invoices
     * @throws InvalidArgumentException if parameters are invalid
     * @throws RuntimeException if recalculation fails
     * @return void
     */
    public static function recalculateManufacturingChain(
        array $rawMaterialItemIds, 
        string $fromDate
    ): void;
}
```

## Data Models

### Existing Models (No Changes)

The system uses existing Laravel Eloquent models:
- `Item`: Inventory items with `average_cost` field
- `OperHead`: Operation/invoice headers with `pro_date`, `pro_tybe`, `isdeleted`
- `OperationItems`: Operation line items with `item_id`, `qty_in`, `qty_out`, `detail_value`, `is_stock`

### New Configuration Structure

Add to `config/recalculation.php`:

```php
return [
    // Batch processing configuration
    'batch_size' => env('RECALC_BATCH_SIZE', 100),
    'chunk_size' => env('RECALC_CHUNK_SIZE', 500),
    
    // Strategy selection thresholds
    'stored_procedure_threshold' => env('RECALC_SP_THRESHOLD', 1000),
    'queue_threshold' => env('RECALC_QUEUE_THRESHOLD', 5000),
    'operation_count_threshold' => env('RECALC_OP_THRESHOLD', 100000),
    
    // Feature flags
    'use_stored_procedures' => env('RECALC_USE_SP', false),
    'use_queue' => env('RECALC_USE_QUEUE', true),
    
    // Performance monitoring
    'performance_warning_threshold' => env('RECALC_PERF_WARNING', 30.0),
    'log_performance' => env('RECALC_LOG_PERF', true),
    
    // Queue configuration
    'queue_name' => env('RECALC_QUEUE_NAME', 'recalculation'),
    'queue_name_large' => env('RECALC_QUEUE_LARGE', 'recalculation-large'),
    'queue_timeout' => env('RECALC_QUEUE_TIMEOUT', 600),
    'queue_tries' => env('RECALC_QUEUE_TRIES', 3),
    
    // Manufacturing chain configuration
    'manufacturing_chain_enabled' => env('RECALC_MFG_CHAIN', true),
    'manufacturing_operation_types' => [59], // Manufacturing operation type
    
    // Consistency checking
    'consistency_check_enabled' => env('RECALC_CONSISTENCY_CHECK', true),
    'consistency_tolerance' => env('RECALC_CONSISTENCY_TOLERANCE', 0.01), // 1 cent tolerance
];
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Error Logging Completeness
*For any* error that occurs during recalculation, the system should log detailed error information including item IDs, operation type, error message, and stack trace.
**Validates: Requirements 1.2**

### Property 2: Database Error Handling
*For any* database query failure during recalculation, the system should catch the exception, log the error with context, and either retry or fail gracefully without corrupting data.
**Validates: Requirements 1.3**

### Property 3: Input Validation Before Processing
*For any* recalculation request, the system should validate all input parameters (item IDs, dates, flags) before performing any database operations or calculations.
**Validates: Requirements 1.4**

### Property 4: Item ID Validation
*For any* array of item IDs, all IDs must be positive integers, otherwise the system should reject the input with a descriptive exception.
**Validates: Requirements 3.1**

### Property 5: Date Format Validation
*For any* date parameter provided, the system should validate that it matches the Y-m-d format, otherwise throw an InvalidArgumentException.
**Validates: Requirements 3.2**

### Property 6: Invalid Parameter Rejection
*For any* invalid parameter (negative IDs, malformed dates, invalid types), the system should throw a descriptive exception explaining what is invalid and why.
**Validates: Requirements 3.4**

### Property 7: Item Existence Validation
*For any* array of item IDs, the system should verify that items exist in the database before processing, and filter out or report non-existent IDs.
**Validates: Requirements 3.5**

### Property 8: Average Cost Calculation Accuracy
*For any* set of valid operations for an item, the calculated average cost should equal SUM(detail_value) / SUM(qty_in - qty_out), or zero if total quantity is zero or negative.
**Validates: Requirements 4.1, 12.1**

### Property 9: Batch Processing Equivalence
*For any* set of item IDs, processing them in batches should produce the same average cost values as processing them individually.
**Validates: Requirements 4.2**

### Property 10: Deleted Operations Exclusion
*For any* item, the average cost calculation should exclude all operations where isdeleted = 1, regardless of other filters.
**Validates: Requirements 4.3, 12.6**

### Property 11: Strategy Selection Based on Data Size
*For any* recalculation request, the factory should select PHP service for <1000 items, stored procedures for 1000-5000 items (if enabled), and queue jobs for >5000 items (if enabled).
**Validates: Requirements 4.4**

### Property 12: Error Handling Graceful Degradation
*For any* error condition (database failure, invalid data, timeout), the system should handle it gracefully by logging the error, cleaning up resources, and either retrying or failing with a clear error message.
**Validates: Requirements 4.5**

### Property 13: Delete Flag Behavior
*For any* recalculation with isDelete=true, the system should ignore the fromDate parameter and recalculate from all non-deleted operations.
**Validates: Requirements 4.7, 12.4**

### Property 14: Configuration-Based Strategy Selection
*For any* recalculation request, the strategy selection should respect configuration thresholds for stored procedures and queue jobs.
**Validates: Requirements 5.1**

### Property 15: Stored Procedure Conditional Usage
*For any* large dataset (>threshold), if stored procedures are enabled in configuration, the system should use stored procedures; otherwise use PHP implementation.
**Validates: Requirements 5.2**

### Property 16: Queue Conditional Usage
*For any* very large dataset (>queue threshold), if queue is enabled in configuration, the system should dispatch a queue job; otherwise process synchronously.
**Validates: Requirements 5.3**

### Property 17: Configuration Fallback
*For any* invalid configuration value, the system should use safe default values and log a warning about the invalid configuration.
**Validates: Requirements 5.5**

### Property 18: Stored Procedure Existence Check
*For any* recalculation using stored procedures, the system should verify that required procedures exist in the database before attempting to call them.
**Validates: Requirements 7.1**

### Property 19: Stored Procedure Fallback
*For any* stored procedure call that fails or finds missing procedures, the system should fall back to PHP implementation and log a warning.
**Validates: Requirements 7.2, 7.5**

### Property 20: Stored Procedure Error Handling
*For any* database-specific error when calling stored procedures, the system should catch the exception, log it with context, and fall back to PHP implementation.
**Validates: Requirements 7.3**

### Property 21: Configurable Batch Size
*For any* batch processing operation, the system should use the batch size specified in configuration, not a hardcoded value.
**Validates: Requirements 8.1**

### Property 22: Transaction Wrapping for Batches
*For any* batch update operation, all updates in the batch should be wrapped in a database transaction to ensure atomicity.
**Validates: Requirements 8.3**

### Property 23: Batch Retry Isolation
*For any* batch that fails during processing, only that specific batch should be retried, not all batches in the operation.
**Validates: Requirements 8.5**

### Property 24: Queue Job Retry with Backoff
*For any* queue job that fails, the system should retry up to 3 times with exponential backoff between attempts.
**Validates: Requirements 9.1**

### Property 25: Queue Assignment Based on Size
*For any* queue job, it should be assigned to the 'recalculation' queue for <1000 items, or 'recalculation-large' queue for >=1000 items.
**Validates: Requirements 9.3**

### Property 26: Date Filtering
*For any* recalculation with a fromDate parameter (and isDelete=false), only operations with pro_date >= fromDate should be included in the calculation.
**Validates: Requirements 12.3**

### Property 27: Operation Type Filtering
*For any* average cost calculation, only operations with is_stock=1 and pro_tybe IN (11, 12, 20, 59) should be included.
**Validates: Requirements 12.5**

### Property 28: Consistency Detection
*For any* item, if the stored average_cost differs from the calculated average_cost by more than the tolerance threshold, the consistency checker should report it as inconsistent.
**Validates: Requirements 14.4**

### Property 29: Manufacturing Invoice Identification
*For any* purchase invoice that is deleted or modified, the system should identify all manufacturing invoices that use items from that purchase invoice, ordered chronologically by date and time.
**Validates: Requirements 16.1, 16.4**

### Property 30: Raw Material Cost Propagation
*For any* manufacturing invoice, when raw material costs change, the system should recalculate and update the costs of all products in that invoice.
**Validates: Requirements 16.2, 16.3, 16.6**

### Property 31: Manufacturing Invoice Section Updates
*For any* manufacturing invoice recalculation, both the raw materials section (inputs) and products section (outputs) should be processed and updated.
**Validates: Requirements 16.5**

### Property 32: Manufacturing Invoice Modification Impact
*For any* manufacturing invoice that is modified, the system should recalculate average cost for all product items in all operations after the invoice date and time.
**Validates: Requirements 17.1, 17.3**

### Property 33: Manufacturing Invoice Cost Propagation
*For any* manufacturing invoice where raw materials or expenses change, the system should update product costs to reflect the new input costs.
**Validates: Requirements 17.2**

### Property 34: Manufacturing Invoice Deletion Impact
*For any* manufacturing invoice that is deleted, the system should recalculate average cost for all product items in all operations after the deleted invoice date and time.
**Validates: Requirements 17.4**

### Property 35: Manufacturing Input-Output Handling
*For any* manufacturing invoice operation, the system should correctly distinguish and process both raw materials (inputs with qty_out) and products (outputs with qty_in).
**Validates: Requirements 17.5**

### Property 36: Chronological Ordering with Time
*For any* set of manufacturing operations on the same date, the system should process them in chronological order using both date and time (created_at timestamp).
**Validates: Requirements 17.6, 18.4**

### Property 37: Manufacturing Chain Identification
*For any* purchase invoice change affecting raw materials, the system should identify all manufacturing invoices in the chain that use those materials, directly or indirectly.
**Validates: Requirements 18.1**

### Property 38: Cascading Cost Updates
*For any* manufacturing invoice cost recalculation, the system should update product average costs in all subsequent operations that use those products.
**Validates: Requirements 18.2**

### Property 39: Multi-Level Chain Processing
*For any* manufacturing chain where products from one invoice are used as raw materials in another, the system should process the chain in correct chronological order.
**Validates: Requirements 18.3**

### Property 40: Transactional Chain Processing
*For any* manufacturing chain recalculation affecting multiple invoices, all updates should be processed in a single database transaction to ensure consistency.
**Validates: Requirements 18.5**

## Error Handling

### Error Categories

1. **Validation Errors** (InvalidArgumentException)
   - Invalid item IDs (non-positive integers)
   - Invalid date format
   - Empty required parameters
   - Type mismatches

2. **Database Errors** (RuntimeException)
   - Connection failures
   - Query execution failures
   - Transaction failures
   - Stored procedure errors

3. **Business Logic Errors** (RuntimeException)
   - Item not found
   - Inconsistent data state
   - Calculation overflow
   - Manufacturing chain circular dependency

4. **Configuration Errors** (RuntimeException)
   - Invalid configuration values
   - Missing required configuration
   - Stored procedure not found

### Error Handling Strategy

```php
try {
    // Validate inputs first
    RecalculationInputValidator::validateItemIds($itemIds);
    RecalculationInputValidator::validateDate($fromDate);
    
    // Start performance monitoring
    $operationId = $monitor->start('batch_recalculation', [
        'item_count' => count($itemIds),
        'from_date' => $fromDate,
        'is_delete' => $isDelete,
    ]);
    
    // Perform recalculation
    $service->recalculateAverageCostForItems($itemIds, $fromDate, $isDelete);
    
    // End monitoring
    $monitor->end($operationId, ['success' => true]);
    
} catch (InvalidArgumentException $e) {
    // Log validation error with context
    Log::error('Validation error in recalculation', [
        'error' => $e->getMessage(),
        'item_ids' => $itemIds,
        'from_date' => $fromDate,
    ]);
    throw $e;
    
} catch (QueryException $e) {
    // Log database error with query details
    Log::error('Database error in recalculation', [
        'error' => $e->getMessage(),
        'sql' => $e->getSql(),
        'bindings' => $e->getBindings(),
        'item_ids' => $itemIds,
    ]);
    throw new RuntimeException('Failed to recalculate average cost: ' . $e->getMessage(), 0, $e);
    
} catch (\Exception $e) {
    // Log unexpected error with full context
    Log::error('Unexpected error in recalculation', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'item_ids' => $itemIds,
        'from_date' => $fromDate,
    ]);
    throw new RuntimeException('Unexpected error during recalculation', 0, $e);
}
```

### Retry Logic

For transient errors (database connection failures, timeouts), implement retry logic:

```php
$maxRetries = 3;
$retryDelay = 1000; // milliseconds

for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        // Attempt operation
        return $this->performRecalculation($itemIds, $fromDate);
        
    } catch (QueryException $e) {
        if ($attempt === $maxRetries) {
            throw $e;
        }
        
        Log::warning("Recalculation attempt {$attempt} failed, retrying", [
            'error' => $e->getMessage(),
            'retry_delay_ms' => $retryDelay,
        ]);
        
        usleep($retryDelay * 1000);
        $retryDelay *= 2; // Exponential backoff
    }
}
```

## Testing Strategy

### Unit Tests

Unit tests will verify individual components in isolation:

1. **Input Validator Tests**
   - Test validation of item IDs (positive, negative, zero, non-integer)
   - Test date format validation (valid, invalid, null)
   - Test boolean validation

2. **Configuration Manager Tests**
   - Test reading configuration values
   - Test default values when config is missing
   - Test validation of configuration

3. **Calculation Tests**
   - Test average cost formula with various inputs
   - Test zero/negative quantity handling
   - Test edge cases (empty operations, single operation)

4. **Strategy Selection Tests**
   - Test factory selects correct service based on item count
   - Test configuration overrides
   - Test stored procedure availability check

### Property-Based Tests

Property-based tests will verify universal properties across many generated inputs:

1. **Calculation Accuracy** (Property 8)
   - Generate random operation data
   - Verify calculated average cost matches formula
   - Test with various quantities and values

2. **Batch Equivalence** (Property 9)
   - Generate random item sets
   - Process individually and in batches
   - Verify results are identical

3. **Filtering Properties** (Properties 10, 26, 27)
   - Generate random operations with various states
   - Verify correct filtering by deleted status, date, type

4. **Delete Flag Behavior** (Property 13)
   - Generate random dates and operations
   - Verify isDelete=true ignores fromDate

5. **Input Validation** (Properties 4, 5, 6)
   - Generate random invalid inputs
   - Verify all are rejected with appropriate exceptions

### Integration Tests

Integration tests will verify end-to-end workflows:

1. **Invoice Creation Flow**
   - Create invoice with items
   - Verify average cost is recalculated
   - Verify correct values in database

2. **Invoice Modification Flow**
   - Modify existing invoice
   - Verify affected items are recalculated
   - Verify subsequent operations are updated

3. **Invoice Deletion Flow**
   - Delete invoice
   - Verify recalculation from all operations
   - Verify correct final values

4. **Manufacturing Chain Flow**
   - Create purchase invoice
   - Create manufacturing invoice using purchased items
   - Modify purchase invoice
   - Verify manufacturing products are updated

5. **Queue Job Flow**
   - Trigger recalculation for >5000 items
   - Verify job is queued
   - Process job
   - Verify results are correct

### Test Configuration

All property-based tests should run with minimum 100 iterations:

```php
/**
 * @test
 * Feature: average-cost-recalculation-improvements, Property 8: Average Cost Calculation Accuracy
 */
public function test_average_cost_calculation_accuracy(): void
{
    $this->forAll(
        Generator\seq(Generator\operation()) // Generate random operations
    )->then(function ($operations) {
        $totalValue = array_sum(array_column($operations, 'detail_value'));
        $totalQty = array_sum(array_map(
            fn($op) => $op['qty_in'] - $op['qty_out'],
            $operations
        ));
        
        $expected = $totalQty > 0 ? $totalValue / $totalQty : 0;
        $actual = $this->service->calculateAverageCost($totalValue, $totalQty);
        
        $this->assertEquals($expected, $actual, 'Average cost calculation should match formula');
    })->runs(100);
}
```

## Implementation Notes

### Phase 1: Code Quality and Validation
1. Remove unused imports from AverageCostRecalculationServiceOptimized
2. Create RecalculationInputValidator class
3. Add input validation to all service methods
4. Add comprehensive error handling with try-catch blocks
5. Improve logging with structured context

### Phase 2: Performance Monitoring
1. Create RecalculationPerformanceMonitor class
2. Integrate monitoring into all services
3. Add performance warning thresholds
4. Create performance statistics methods

### Phase 3: Configuration Management
1. Create config/recalculation.php configuration file
2. Create RecalculationConfigManager class
3. Update factory to use configuration
4. Add configuration validation

### Phase 4: Manufacturing Chain Support
1. Create ManufacturingChainHandler class
2. Implement chain identification logic
3. Implement cascading recalculation
4. Add chronological ordering with time
5. Integrate with RecalculationServiceHelper

### Phase 5: Consistency Checking
1. Create AverageCostConsistencyChecker class
2. Implement consistency check logic
3. Create artisan commands for checking and fixing
4. Add consistency reporting

### Phase 6: Testing
1. Write unit tests for all new classes
2. Write property-based tests for core properties
3. Write integration tests for end-to-end flows
4. Achieve >80% code coverage

### Phase 7: Documentation
1. Add PHPDoc blocks to all public methods
2. Update README with usage examples
3. Create migration guide for existing code
4. Document configuration options