# Average Cost Recalculation System - Migration Guide

## Overview

This guide helps you migrate from the old average cost recalculation system to the new improved system. The new system provides better performance, comprehensive error handling, input validation, and manufacturing chain support.

## What's New

### Major Improvements

1. **Automatic Strategy Selection**: System automatically chooses the optimal recalculation strategy
2. **Input Validation**: All inputs are validated before processing
3. **Performance Monitoring**: Track execution time, memory usage, and metrics
4. **Manufacturing Chain Support**: Cascading recalculation for manufacturing invoices
5. **Consistency Checking**: Verify and fix data integrity issues
6. **Configuration Management**: Centralized configuration with validation
7. **Comprehensive Error Handling**: Robust error handling with detailed logging
8. **PHPDoc Documentation**: Complete API documentation

### New Components

- `RecalculationServiceHelper` - Unified interface for all recalculation operations
- `RecalculationServiceFactory` - Automatic strategy selection
- `RecalculationInputValidator` - Input validation
- `RecalculationPerformanceMonitor` - Performance tracking
- `ManufacturingChainHandler` - Manufacturing chain recalculation
- `AverageCostConsistencyChecker` - Consistency checking
- `RecalculationConfigManager` - Configuration management

## Breaking Changes

### None

The new system is **fully backward compatible** with the existing system. All existing code will continue to work without modifications.

## Migration Steps

### Step 1: Update Configuration (Optional)

Create or update `config/recalculation.php` with your preferred settings:

```php
<?php

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
    'manufacturing_operation_types' => [59],
    'manufacturing_cost_allocation' => env('RECALC_MFG_ALLOCATION', 'proportional'),
    
    // Consistency checking
    'consistency_check_enabled' => env('RECALC_CONSISTENCY_CHECK', true),
    'consistency_tolerance' => env('RECALC_CONSISTENCY_TOLERANCE', 0.01),
    'consistency_check_batch_size' => env('RECALC_CONSISTENCY_BATCH', 500),
    
    // Retry configuration
    'max_retries' => env('RECALC_MAX_RETRIES', 3),
    'retry_delay_ms' => env('RECALC_RETRY_DELAY', 1000),
    'retry_exponential_backoff' => env('RECALC_RETRY_BACKOFF', true),
];
```

### Step 2: Update Environment Variables (Optional)

Add to your `.env` file:

```env
# Average Cost Recalculation Configuration
RECALC_BATCH_SIZE=100
RECALC_CHUNK_SIZE=500
RECALC_SP_THRESHOLD=1000
RECALC_QUEUE_THRESHOLD=5000
RECALC_USE_SP=false
RECALC_USE_QUEUE=true
RECALC_PERF_WARNING=30.0
RECALC_MFG_CHAIN=true
RECALC_CONSISTENCY_CHECK=true
RECALC_CONSISTENCY_TOLERANCE=0.01
```

### Step 3: Update Code (Recommended)

While existing code will continue to work, we recommend updating to use the new unified interface:

#### Old Code

```php
// Old way - direct service instantiation
$service = new AverageCostRecalculationServiceOptimized();
$service->recalculateAverageCostForItems($itemIds, $fromDate);
```

#### New Code

```php
// New way - unified interface with automatic strategy selection
use App\Services\RecalculationServiceHelper;

RecalculationServiceHelper::recalculateAverageCost($itemIds, $fromDate);
```

### Step 4: Add Manufacturing Chain Support (If Applicable)

If your application uses manufacturing invoices, add manufacturing chain recalculation:

```php
// In SaveInvoiceService or similar
if ($isPurchaseInvoice && $isModifiedOrDeleted) {
    // Identify raw material items
    $rawMaterialIds = $this->getRawMaterialIds($invoice);
    
    // Trigger manufacturing chain recalculation
    RecalculationServiceHelper::recalculateManufacturingChain(
        $rawMaterialIds,
        $invoice->pro_date
    );
}
```

### Step 5: Run Consistency Check

After migration, verify data integrity:

```bash
# Check all items for consistency
php artisan recalculation:check-consistency --all

# Fix any inconsistencies found
php artisan recalculation:fix-inconsistencies --all
```

### Step 6: Monitor Performance

Review logs to ensure the system is working correctly:

```bash
# Monitor recalculation operations
tail -f storage/logs/laravel.log | grep "Recalculation operation"
```

## Code Migration Examples

### Example 1: Invoice Creation

#### Before

```php
// Old code
$service = new AverageCostRecalculationServiceOptimized();
$service->recalculateAverageCostForItems($itemIds, $invoiceDate);

$profitService = new ProfitAndJournalRecalculationServiceOptimized();
$profitService->recalculateAllAffectedOperations($itemIds, $invoiceDate);
```

#### After

```php
// New code - single call handles everything
use App\Services\RecalculationServiceHelper;

RecalculationServiceHelper::recalculateAll(
    $itemIds,
    $invoiceDate,
    false,  // Don't force queue
    $currentInvoiceId,
    $currentInvoiceCreatedAt
);
```

### Example 2: Invoice Deletion

#### Before

```php
// Old code
$service = new AverageCostRecalculationServiceOptimized();
$service->recalculateAverageCostForItems($itemIds, null);  // Recalculate from all
```

#### After

```php
// New code - explicit delete flag
use App\Services\RecalculationServiceHelper;

RecalculationServiceHelper::recalculateAverageCost(
    $itemIds,
    null,   // From date (ignored for delete)
    false,  // Don't force queue
    true    // Is delete (recalculates from all operations)
);
```

### Example 3: Large Dataset Processing

#### Before

```php
// Old code - manual queue dispatch
if (count($itemIds) > 5000) {
    RecalculateAverageCostJob::dispatch($itemIds, $fromDate);
} else {
    $service = new AverageCostRecalculationServiceOptimized();
    $service->recalculateAverageCostForItems($itemIds, $fromDate);
}
```

#### After

```php
// New code - automatic queue selection
use App\Services\RecalculationServiceHelper;

RecalculationServiceHelper::recalculateAverageCost($itemIds, $fromDate);
// Automatically uses queue if needed
```

### Example 4: Manufacturing Invoice

#### Before

```php
// Old code - no manufacturing chain support
$service = new AverageCostRecalculationServiceOptimized();
$service->recalculateAverageCostForItems($productIds, $invoiceDate);
```

#### After

```php
// New code - with manufacturing chain support
use App\Services\RecalculationServiceHelper;

// When purchase invoice is modified/deleted
RecalculationServiceHelper::recalculateManufacturingChain(
    $rawMaterialIds,
    $invoiceDate
);

// When manufacturing invoice is modified
RecalculationServiceHelper::recalculateAverageCost(
    $productIds,
    $invoiceDate
);
```

## Testing After Migration

### 1. Unit Tests

Run all unit tests to ensure nothing broke:

```bash
php artisan test --filter=Recalculation
```

### 2. Integration Tests

Test end-to-end workflows:

```bash
php artisan test tests/Feature/ManufacturingChainIntegrationTest.php
```

### 3. Manual Testing

Test common scenarios:

1. Create a purchase invoice
2. Modify the invoice
3. Delete the invoice
4. Create a manufacturing invoice
5. Modify raw material costs
6. Verify product costs are updated

### 4. Performance Testing

Compare performance before and after:

```bash
# Monitor execution time
tail -f storage/logs/laravel.log | grep "duration_seconds"
```

## Rollback Plan

If you encounter issues, you can rollback by:

1. **Revert configuration changes** - Remove `config/recalculation.php`
2. **Revert code changes** - Use old service instantiation
3. **Clear cache** - `php artisan config:clear`
4. **Restart queue workers** - `php artisan queue:restart`

The old services are still available and functional.

## FAQ

### Q: Do I need to update my existing code?

**A:** No, existing code will continue to work. However, we recommend updating to use the new unified interface for better performance and features.

### Q: Will this affect my existing data?

**A:** No, the migration only affects how calculations are performed, not the data itself. Run a consistency check after migration to verify.

### Q: How do I enable stored procedures?

**A:** Set `RECALC_USE_SP=true` in your `.env` file and ensure stored procedures are installed in your database.

### Q: What if I encounter performance issues?

**A:** Check the troubleshooting section in README.md. Common solutions include enabling stored procedures, using queue for large datasets, and verifying database indexes.

### Q: Can I migrate gradually?

**A:** Yes, you can migrate one module at a time. The old and new systems can coexist.

### Q: How do I test manufacturing chain recalculation?

**A:** Create a test scenario with purchase and manufacturing invoices, then modify the purchase invoice and verify product costs are updated.

### Q: What happens if validation fails?

**A:** The system throws an `InvalidArgumentException` with a descriptive error message. Handle it in your code or let it bubble up to the error handler.

### Q: How do I monitor performance?

**A:** Check logs for performance metrics. Look for "Recalculation operation completed" entries with duration and memory usage.

### Q: Can I customize the calculation formula?

**A:** The formula is fixed: `average_cost = SUM(detail_value) / SUM(qty_in - qty_out)`. If you need a different formula, you'll need to create a custom service.

### Q: How do I handle errors?

**A:** Wrap recalculation calls in try-catch blocks and handle `InvalidArgumentException` (validation errors) and `RuntimeException` (runtime errors) appropriately.

## Support

For additional help:

1. Review the README.md for detailed documentation
2. Check the PHPDoc blocks in the service classes
3. Review the test files for usage examples
4. Contact the development team

## Changelog

### Version 1.0 (Current)

- Initial release of improved recalculation system
- Automatic strategy selection
- Input validation
- Performance monitoring
- Manufacturing chain support
- Consistency checking
- Configuration management
- Comprehensive error handling
- Full backward compatibility

## Next Steps

After completing the migration:

1. Monitor performance for a few days
2. Run consistency checks weekly
3. Review logs for any warnings or errors
4. Consider enabling stored procedures for production
5. Train team members on new features
6. Update internal documentation

## Conclusion

The new average cost recalculation system provides significant improvements in performance, reliability, and maintainability while maintaining full backward compatibility. Follow this guide to migrate smoothly and take advantage of the new features.
