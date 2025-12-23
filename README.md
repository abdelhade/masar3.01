# MASSAR ERP System

## Average Cost Recalculation System

### Overview

The Average Cost Recalculation System is a high-performance, hybrid solution for calculating and maintaining accurate average costs for inventory items. It automatically selects the optimal strategy based on data size and configuration.

### Features

- **Automatic Strategy Selection**: Chooses between Queue Jobs, Stored Procedures, or PHP Services based on data size
- **Performance Monitoring**: Tracks execution time, memory usage, and operation metrics
- **Input Validation**: Comprehensive validation of all inputs before processing
- **Manufacturing Chain Support**: Handles cascading recalculation for manufacturing invoices
- **Consistency Checking**: Verifies and fixes data integrity issues
- **Configurable Thresholds**: Tune the system for different environments
- **Error Handling**: Robust error handling with detailed logging

### Architecture

```
RecalculationServiceHelper (Unified Interface)
    ├── Queue Jobs (>5000 items)
    ├── Stored Procedures (>1000 items, if enabled)
    └── PHP Optimized Service (<1000 items)
```

### Installation

The system is already integrated into the MASSAR ERP application. No additional installation is required.

### Configuration

Configuration is managed through `config/recalculation.php`:

```php
return [
    // Batch processing
    'batch_size' => 100,
    'chunk_size' => 500,
    
    // Strategy selection thresholds
    'stored_procedure_threshold' => 1000,
    'queue_threshold' => 5000,
    
    // Feature flags
    'use_stored_procedures' => false,
    'use_queue' => true,
    
    // Performance monitoring
    'performance_warning_threshold' => 30.0,
    
    // Manufacturing chain
    'manufacturing_chain_enabled' => true,
    'manufacturing_cost_allocation' => 'proportional',
    
    // Consistency checking
    'consistency_check_enabled' => true,
    'consistency_tolerance' => 0.01,
];
```

### Usage Examples

#### Basic Recalculation

```php
use App\Services\RecalculationServiceHelper;

// Recalculate average cost for specific items from a date
RecalculationServiceHelper::recalculateAverageCost(
    [1, 2, 3],           // Item IDs
    '2024-01-01',        // From date
    false,               // Force queue
    false                // Is delete
);
```

#### Invoice Creation/Modification

```php
// When creating or modifying an invoice
RecalculationServiceHelper::recalculateAll(
    $affectedItemIds,
    $invoiceDate,
    false,               // Force queue
    $currentInvoiceId,
    $currentInvoiceCreatedAt
);
```

#### Invoice Deletion

```php
// When deleting an invoice, recalculate from ALL operations
RecalculationServiceHelper::recalculateAverageCost(
    $affectedItemIds,
    null,                // From date (ignored for delete)
    false,               // Force queue
    true                 // Is delete (recalculates from all operations)
);
```

#### Manufacturing Chain Recalculation

```php
// When purchase invoice affecting raw materials is modified/deleted
RecalculationServiceHelper::recalculateManufacturingChain(
    $rawMaterialItemIds,
    $fromDate
);
```

#### Force Queue Processing

```php
// Force use of queue job for large datasets
RecalculationServiceHelper::recalculateAverageCost(
    $itemIds,
    $fromDate,
    true,                // Force queue
    false
);
```

### Calculation Formula

```
average_cost = SUM(detail_value) / SUM(qty_in - qty_out)
```

Where:
- `detail_value`: Total value of the operation
- `qty_in`: Quantity received (purchases, manufacturing output)
- `qty_out`: Quantity issued (sales, manufacturing input)

### Filters Applied

The calculation includes only:
- Stock operations (`is_stock = 1`)
- Specific operation types: 11 (Purchase), 12 (Sales), 20 (Opening Balance), 59 (Manufacturing)
- Non-deleted operations (`isdeleted = 0`)
- Operations on or after the specified date (when not a delete operation)

### Artisan Commands

#### Check Consistency

```bash
# Check specific items
php artisan recalculation:check-consistency --items=1,2,3

# Check all items
php artisan recalculation:check-consistency --all

# Check with custom tolerance
php artisan recalculation:check-consistency --all --tolerance=0.05
```

#### Fix Inconsistencies

```bash
# Dry run (preview changes)
php artisan recalculation:fix-inconsistencies --items=1,2,3 --dry-run

# Fix specific items
php artisan recalculation:fix-inconsistencies --items=1,2,3

# Fix all inconsistent items
php artisan recalculation:fix-inconsistencies --all
```

### Performance Optimization

#### Strategy Selection

The system automatically selects the optimal strategy:

1. **Queue Jobs** (>5000 items or >500,000 operations)
   - Processes in background
   - Prevents blocking main application
   - Automatic retry on failure

2. **Stored Procedures** (>1000 items, if enabled)
   - Database-level processing
   - Fastest for large datasets
   - Requires stored procedures to be installed

3. **PHP Optimized Service** (<1000 items)
   - Single SQL query per item
   - Batch processing (100 items per batch)
   - Best for small/medium datasets

#### Batch Processing

Items are processed in batches to optimize performance:

```php
// Batch size is configurable
'batch_size' => 100,  // Process 100 items at a time
```

### Monitoring

#### Performance Metrics

The system tracks:
- Execution time
- Memory usage
- Items processed
- Strategy selected
- Errors and warnings

#### Logging

All operations are logged with structured data:

```php
Log::info('Recalculation operation completed', [
    'operation_id' => $operationId,
    'operation_type' => 'batch_recalculation',
    'duration_seconds' => 12.5,
    'memory_used_mb' => 45.2,
    'items_processed' => 150,
    'strategy' => 'php_optimized',
]);
```

### Error Handling

#### Validation Errors

```php
try {
    RecalculationServiceHelper::recalculateAverageCost($itemIds, $fromDate);
} catch (InvalidArgumentException $e) {
    // Handle validation error
    Log::error('Invalid parameters', ['error' => $e->getMessage()]);
}
```

#### Runtime Errors

```php
try {
    RecalculationServiceHelper::recalculateAverageCost($itemIds, $fromDate);
} catch (RuntimeException $e) {
    // Handle runtime error (database, calculation, etc.)
    Log::error('Recalculation failed', ['error' => $e->getMessage()]);
}
```

### Troubleshooting

#### Issue: Slow Recalculation

**Symptoms**: Recalculation takes longer than expected

**Solutions**:
1. Check if stored procedures are enabled and installed
2. Verify database indexes are in place
3. Consider using queue for large datasets
4. Review performance logs for bottlenecks

```bash
# Check performance logs
tail -f storage/logs/laravel.log | grep "Recalculation operation"
```

#### Issue: Inconsistent Average Costs

**Symptoms**: Stored average_cost doesn't match calculated value

**Solutions**:
1. Run consistency check to identify issues
2. Fix inconsistencies using artisan command
3. Verify operation data integrity

```bash
# Check and fix
php artisan recalculation:check-consistency --all
php artisan recalculation:fix-inconsistencies --all
```

#### Issue: Queue Jobs Not Processing

**Symptoms**: Queue jobs are dispatched but not executed

**Solutions**:
1. Verify queue worker is running
2. Check queue configuration
3. Review queue logs for errors

```bash
# Start queue worker
php artisan queue:work --queue=recalculation,recalculation-large

# Check failed jobs
php artisan queue:failed
```

#### Issue: Manufacturing Chain Not Updating

**Symptoms**: Product costs not updated when raw materials change

**Solutions**:
1. Verify manufacturing chain is enabled in config
2. Check manufacturing operation types configuration
3. Review logs for manufacturing chain operations

```php
// In config/recalculation.php
'manufacturing_chain_enabled' => true,
'manufacturing_operation_types' => [59],
```

### Testing

#### Unit Tests

```bash
# Run all recalculation tests
php artisan test --filter=Recalculation

# Run specific test class
php artisan test tests/Unit/Services/RecalculationServiceHelperTest.php
```

#### Property-Based Tests

```bash
# Run property tests
php artisan test --filter=Property
```

#### Integration Tests

```bash
# Run integration tests
php artisan test tests/Feature/ManufacturingChainIntegrationTest.php
```

### Best Practices

1. **Always validate inputs** before calling recalculation methods
2. **Use appropriate strategy** based on data size
3. **Monitor performance** regularly using logs
4. **Run consistency checks** periodically
5. **Test thoroughly** before deploying changes
6. **Use queue for large datasets** to avoid timeouts
7. **Enable stored procedures** for production environments with large data

### API Reference

See PHPDoc blocks in the following classes for detailed API documentation:

- `App\Services\RecalculationServiceHelper` - Main entry point
- `App\Services\RecalculationServiceFactory` - Strategy selection
- `App\Services\AverageCostRecalculationServiceOptimized` - PHP implementation
- `App\Services\Manufacturing\ManufacturingChainHandler` - Manufacturing chain
- `App\Services\Consistency\AverageCostConsistencyChecker` - Consistency checking
- `App\Services\Config\RecalculationConfigManager` - Configuration management
- `App\Services\Monitoring\RecalculationPerformanceMonitor` - Performance monitoring
- `App\Services\Validation\RecalculationInputValidator` - Input validation

### Support

For issues or questions, please:
1. Check the troubleshooting section above
2. Review the logs in `storage/logs/laravel.log`
3. Contact the development team

### License

This system is part of the MASSAR ERP application and is proprietary software.
