# Implementation Plan: Average Cost Recalculation System Improvements

## Overview

This implementation plan breaks down the improvements to the Average Cost Recalculation System into discrete, manageable tasks. The plan follows a phased approach, starting with code quality improvements, then adding new features, and finally comprehensive testing and documentation.

## Tasks

- [-] 1. Phase 1: Code Quality and Input Validation
  - [x] 1.1 Remove unused imports from AverageCostRecalculationServiceOptimized
    - Remove `use App\Models\OperHead;`
    - Remove `use App\Models\OperationItems;`
    - Run Pint to verify code style
    - _Requirements: 1.1_

  - [x] 1.2 Create RecalculationInputValidator class
    - Create `app/Services/Validation/RecalculationInputValidator.php`
    - Implement `validateItemIds()` method with positive integer check
    - Implement `validateDate()` method with Y-m-d format check
    - Implement `validateItemsExist()` method with database check
    - Implement `validateBoolean()` method
    - Add comprehensive PHPDoc blocks
    - _Requirements: 3.1, 3.2, 3.4, 3.5_

  - [x] 1.3 Write unit tests for RecalculationInputValidator
    - Test `validateItemIds()` with valid and invalid inputs
    - Test `validateDate()` with various date formats
    - Test `validateItemsExist()` with existing and non-existing IDs
    - Test `validateBoolean()` with various input types
    - _Requirements: 3.1, 3.2, 3.4, 3.5_

  - [ ] 1.4 Write property test for input validation
    - **Property 4: Item ID Validation**
    - **Validates: Requirements 3.1**
    - Generate random item ID arrays (including negative, zero, non-integer)
    - Verify validation rejects invalid IDs with descriptive exceptions

  - [ ] 1.5 Write property test for date validation
    - **Property 5: Date Format Validation**
    - **Validates: Requirements 3.2**
    - Generate random date strings
    - Verify only valid Y-m-d format is accepted

  - [x] 1.6 Add input validation to AverageCostRecalculationServiceOptimized
    - Add validation calls at the start of each public method
    - Add try-catch blocks for validation exceptions
    - Update method signatures with @throws annotations
    - _Requirements: 1.4, 3.1, 3.2, 3.4, 3.5_

  - [x] 1.7 Add comprehensive error handling to AverageCostRecalculationServiceOptimized
    - Wrap database operations in try-catch blocks
    - Add detailed error logging with context (item IDs, dates, error messages)
    - Implement retry logic for transient database errors
    - Add error context to all Log statements
    - _Requirements: 1.2, 1.3_

  - [ ] 1.8 Write property test for error handling
    - **Property 2: Database Error Handling**
    - **Validates: Requirements 1.3**
    - Mock database failures
    - Verify exceptions are caught and logged with context

- [ ] 2. Checkpoint - Verify Phase 1 completion
  - Ensure all tests pass, ask the user if questions arise.

- [x] 3. Phase 2: Performance Monitoring
  - [x] 3.1 Create RecalculationPerformanceMonitor class
    - Create `app/Services/Monitoring/RecalculationPerformanceMonitor.php`
    - Implement `start()` method to begin monitoring
    - Implement `end()` method to log results
    - Implement `logSlowOperation()` for performance warnings
    - Implement `getStatistics()` for performance metrics
    - Use Laravel's Log facade for structured logging
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

  - [x] 3.2 Write unit tests for RecalculationPerformanceMonitor
    - Test `start()` generates unique operation IDs
    - Test `end()` logs completion with duration
    - Test `logSlowOperation()` logs warnings for slow operations
    - Test `getStatistics()` returns performance data
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

  - [x] 3.3 Integrate performance monitoring into AverageCostRecalculationServiceOptimized
    - Inject RecalculationPerformanceMonitor via constructor
    - Add monitoring calls to all public methods
    - Log operation start with context (item count, strategy, etc.)
    - Log operation end with results (items processed, duration, etc.)
    - _Requirements: 2.1, 2.2, 2.3_

  - [x] 3.4 Add performance monitoring to RecalculationServiceHelper
    - Add monitoring to `recalculateAverageCost()` method
    - Add monitoring to `recalculateProfitsAndJournals()` method
    - Log strategy selection decisions
    - _Requirements: 2.4_

  - [x] 3.5 Add performance monitoring to RecalculateAverageCostJob
    - Add monitoring to `handle()` method
    - Log job start and completion
    - Log queue assignment decisions
    - _Requirements: 2.1, 2.2_

- [x] 4. Checkpoint - Verify Phase 2 completion
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Phase 3: Configuration Management
  - [x] 5.1 Create recalculation configuration file
    - Create `config/recalculation.php` with all configuration options
    - Add batch_size, chunk_size, thresholds, feature flags
    - Add queue configuration, manufacturing chain config
    - Add consistency checking configuration
    - Document each configuration option with comments
    - _Requirements: 5.1, 5.4_

  - [x] 5.2 Create RecalculationConfigManager class
    - Create `app/Services/Config/RecalculationConfigManager.php`
    - Implement getter methods for all configuration values
    - Implement `validateConfiguration()` method
    - Add default values for missing configuration
    - Add warning logs for invalid configuration
    - _Requirements: 5.1, 5.4, 5.5_

  - [x] 5.3 Write unit tests for RecalculationConfigManager
    - Test all getter methods return correct values
    - Test default values when config is missing
    - Test validation detects invalid configuration
    - Test warning logs for invalid values
    - _Requirements: 5.1, 5.4, 5.5_

  - [x] 5.4 Write property test for configuration fallback
    - **Property 17: Configuration Fallback**
    - **Validates: Requirements 5.5**
    - Provide invalid configuration values
    - Verify safe defaults are used with warnings logged

  - [x] 5.5 Update RecalculationServiceFactory to use configuration
    - Replace hardcoded thresholds with config values
    - Use RecalculationConfigManager for all configuration reads
    - Add configuration validation on first use
    - _Requirements: 5.1, 5.2, 5.3_

  - [x] 5.6 Write property test for strategy selection
    - **Property 14: Configuration-Based Strategy Selection**
    - **Validates: Requirements 5.1**
    - Test with various configuration thresholds
    - Verify strategy selection respects configuration
    - **PBT Status**: ✅ PASSED (19 tests, 28 assertions)
    - **Test File**: `tests/Unit/Services/RecalculationServiceFactoryPropertyTest.php`
    - **Iterations**: 13 data provider scenarios + 6 additional tests
    - **Key Findings**: 
      - Strategy selection correctly respects item count thresholds
      - Stored procedures are used when item count exceeds threshold
      - PHP optimized service is used when features are disabled
      - Configuration validation is performed on first use
      - Strategy selection is consistent for same inputs
      - Boundary conditions are handled correctly (threshold-1, threshold, threshold+1)
    - **Implementation Notes**:
      - Added optimization to skip operation count check when threshold is very high (PHP_INT_MAX)
      - This prevents unnecessary database queries in test environment
      - Factory validates configuration on first use and logs warnings for invalid values

- [x] 6. Checkpoint - Verify Phase 3 completion
  - Ensure all tests pass, ask the user if questions arise.
  - **Status**: ✅ COMPLETE
  - **Summary**: All Phase 3 tasks completed successfully
    - Configuration file created with comprehensive options
    - RecalculationConfigManager implemented with validation
    - Unit tests passing (39 tests)
    - Property tests passing (30 tests for config fallback, 19 tests for strategy selection)
    - RecalculationServiceFactory updated to use configuration
    - All tests passing, code formatted with Pint

- [-] 7. Phase 4: Manufacturing Chain Support
  - [x] 7.1 Create ManufacturingChainHandler class
    - Create `app/Services/Manufacturing/ManufacturingChainHandler.php`
    - Implement `findAffectedManufacturingInvoices()` method
    - Query operation_items to find manufacturing invoices using specified raw materials
    - Order results by date and created_at (chronological)
    - _Requirements: 16.1, 16.4_

  - [x] 7.2 Implement manufacturing invoice details retrieval
    - Implement `getManufacturingInvoiceDetails()` method
    - Query operation_items for the invoice
    - Separate raw materials (qty_out > 0) from products (qty_in > 0)
    - Return structured data with both sections
    - _Requirements: 16.5, 17.5_

  - [x] 7.3 Implement product cost update from raw materials
    - Implement `updateProductCostsFromRawMaterials()` method
    - Calculate total raw material cost for the invoice
    - Distribute cost to products based on configured allocation method
    - Update product detail_value in operation_items
    - Return updated product item IDs and new costs
    - _Requirements: 16.3, 16.6, 17.2_

  - [x] 7.4 Implement manufacturing chain recalculation
    - Implement `recalculateChain()` method
    - Process manufacturing invoices in chronological order
    - For each invoice, update product costs from raw materials
    - Trigger average cost recalculation for updated products
    - Use database transaction for consistency
    - _Requirements: 16.2, 18.2, 18.3, 18.4, 18.5_

  - [x] 7.5 Write unit tests for ManufacturingChainHandler
    - Test `findAffectedManufacturingInvoices()` finds correct invoices
    - Test chronological ordering by date and time
    - Test `getManufacturingInvoiceDetails()` separates sections correctly
    - Test `updateProductCostsFromRawMaterials()` calculates costs correctly
    - Test `recalculateChain()` processes in correct order
    - _Requirements: 16.1, 16.2, 16.3, 16.4, 16.5, 16.6_

  - [x] 7.6 Write property test for manufacturing chain identification
    - **Property 29: Manufacturing Invoice Identification**
    - **Validates: Requirements 16.1, 16.4**
    - Create random purchase and manufacturing invoices
    - Verify affected invoices are identified correctly
    - Verify chronological ordering
    - **PBT Status**: ✅ PASSED (15 tests, 72 assertions)
    - **Test File**: `tests/Unit/Services/ManufacturingChainHandlerPropertyTest.php`
    - **Iterations**: 7 data provider scenarios for invoice identification + 5 scenarios for chronological ordering + 3 additional tests
    - **Key Findings**:
      - Manufacturing invoice identification correctly filters by operation type (type 59)
      - Chronological ordering by date and time works correctly
      - Multiple raw materials are handled correctly (ANY logic)
      - Date filtering respects fromDate parameter
      - Deleted invoices are excluded from results
      - Non-manufacturing invoices (purchase, sales) are excluded
    - **Implementation Notes**:
      - Test setup creates Branch, User, and ProTypes to satisfy foreign key constraints
      - BranchScope requires authenticated user with branch associations
      - Factory updated to use specific pro_type instead of random selection
      - All tests passing with proper database relationships

  - [ ] 7.7 Write property test for cost propagation
    - **Property 30: Raw Material Cost Propagation**
    - **Validates: Requirements 16.2, 16.3, 16.6**
    - Change raw material costs
    - Verify product costs are updated accordingly

  - [x] 7.8 Add manufacturing chain support to RecalculationServiceHelper
    - Implement `recalculateManufacturingChain()` method
    - Integrate ManufacturingChainHandler
    - Add input validation for raw material item IDs
    - Add performance monitoring
    - Add error handling
    - _Requirements: 16.1, 16.2, 16.3, 16.4, 16.5, 16.6_

  - [x] 7.9 Update SaveInvoiceService to trigger manufacturing chain recalculation
    - When purchase invoice is modified/deleted, identify affected raw materials
    - Call `RecalculationServiceHelper::recalculateManufacturingChain()`
    - Add logging for manufacturing chain recalculation
    - _Requirements: 16.1, 16.2_

  - [x] 7.10 Update ManufacturingInvoiceService for proper recalculation
    - When manufacturing invoice is modified, recalculate products in subsequent operations
    - When manufacturing invoice is deleted, recalculate products after deletion date/time
    - Use date AND time for chronological ordering
    - _Requirements: 17.1, 17.3, 17.4, 17.6_

  - [x] 7.11 Write integration test for manufacturing chain
    - Create purchase invoice with raw materials
    - Create manufacturing invoice using those materials
    - Modify purchase invoice
    - Verify manufacturing products are updated
    - Verify subsequent operations reflect changes
    - _Requirements: 16.1, 16.2, 16.3, 18.1, 18.2, 18.3_

- [x] 8. Checkpoint - Verify Phase 4 completion
  - Ensure all tests pass, ask the user if questions arise.

- [-] 9. Phase 5: Consistency Checking
  - [x] 9.1 Create AverageCostConsistencyChecker class
    - Create `app/Services/Consistency/AverageCostConsistencyChecker.php`
    - Implement `checkItems()` method to check specific items
    - Implement `checkAllItems()` method with chunking
    - Implement `fixInconsistencies()` method with dry-run support
    - Implement `generateReport()` method for statistics
    - _Requirements: 14.1, 14.2, 14.3, 14.4, 14.5_

  - [x] 9.2 Implement consistency check logic
    - For each item, calculate expected average cost from operations
    - Compare with stored average_cost value
    - Use tolerance threshold from configuration
    - Log discrepancies with item details
    - _Requirements: 14.2, 14.4_

  - [x] 9.3 Write unit tests for AverageCostConsistencyChecker
    - Test `checkItems()` detects inconsistencies
    - Test tolerance threshold is respected
    - Test `fixInconsistencies()` corrects values
    - Test dry-run mode doesn't modify data
    - Test `generateReport()` includes statistics
    - _Requirements: 14.1, 14.2, 14.3, 14.4, 14.5_

  - [ ] 9.4 Write property test for consistency detection
    - **Property 28: Consistency Detection**
    - **Validates: Requirements 14.4**
    - Create items with known inconsistencies
    - Verify checker detects them correctly
    - Verify tolerance threshold works

  - [x] 9.5 Create artisan command for consistency check
    - Create `app/Console/Commands/CheckAverageCostConsistency.php`
    - Add options for specific items or all items
    - Add option for auto-fix
    - Display results in table format
    - _Requirements: 14.1_

  - [x] 9.6 Create artisan command for fixing inconsistencies
    - Create `app/Console/Commands/FixAverageCostInconsistencies.php`
    - Add dry-run option
    - Add confirmation prompt
    - Display fixed items count
    - _Requirements: 14.3_

- [ ] 10. Checkpoint - Verify Phase 5 completion
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 11. Phase 6: Core Calculation Tests
  - [ ] 11.1 Write property test for calculation accuracy
    - **Property 8: Average Cost Calculation Accuracy**
    - **Validates: Requirements 4.1, 12.1**
    - Generate random operation data
    - Verify calculated average cost matches formula: SUM(detail_value) / SUM(qty_in - qty_out)
    - Test with various quantities and values

  - [ ] 11.2 Write property test for batch equivalence
    - **Property 9: Batch Processing Equivalence**
    - **Validates: Requirements 4.2**
    - Generate random item sets
    - Process individually and in batches
    - Verify results are identical

  - [ ] 11.3 Write property test for deleted operations exclusion
    - **Property 10: Deleted Operations Exclusion**
    - **Validates: Requirements 4.3, 12.6**
    - Generate operations with various deleted states
    - Verify deleted operations are excluded from calculations

  - [ ] 11.4 Write property test for delete flag behavior
    - **Property 13: Delete Flag Behavior**
    - **Validates: Requirements 4.7, 12.4**
    - Test with various dates and isDelete flag
    - Verify isDelete=true ignores fromDate parameter

  - [ ] 11.5 Write property test for date filtering
    - **Property 26: Date Filtering**
    - **Validates: Requirements 12.3**
    - Generate operations with various dates
    - Verify only operations >= fromDate are included

  - [ ] 11.6 Write property test for operation type filtering
    - **Property 27: Operation Type Filtering**
    - **Validates: Requirements 12.5**
    - Generate operations with various types and is_stock values
    - Verify only correct operations are included

  - [ ] 11.7 Write edge case tests
    - Test zero quantity handling (should return 0)
    - Test negative quantity handling (should return 0)
    - Test empty operations (should return 0)
    - Test single operation
    - _Requirements: 4.6, 12.2_

- [ ] 12. Phase 7: Integration and End-to-End Tests
  - [ ] 12.1 Write integration test for invoice creation
    - Create invoice with items
    - Verify average cost is recalculated
    - Verify correct values in database
    - _Requirements: 10.1_

  - [ ] 12.2 Write integration test for invoice modification
    - Modify existing invoice
    - Verify affected items are recalculated
    - Verify subsequent operations are updated
    - _Requirements: 10.3_

  - [ ] 12.3 Write integration test for invoice deletion
    - Delete invoice
    - Verify recalculation from all operations
    - Verify correct final values
    - _Requirements: 10.2_

  - [ ] 12.4 Write integration test for queue job flow
    - Trigger recalculation for >5000 items
    - Verify job is queued correctly
    - Process job
    - Verify results are correct
    - _Requirements: 9.3, 9.1_

  - [ ] 12.5 Write integration test for stored procedure fallback
    - Enable stored procedures in config
    - Simulate missing stored procedure
    - Verify fallback to PHP implementation
    - Verify warning is logged
    - _Requirements: 7.2, 7.5_

- [x] 13. Phase 8: Documentation and Cleanup
  - [x] 13.1 Add comprehensive PHPDoc blocks
    - Add PHPDoc to all public methods in all classes
    - Include @param, @return, @throws annotations
    - Add usage examples in class-level PHPDoc
    - Document calculation formulas
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

  - [x] 13.2 Update README with usage examples
    - Add section on average cost recalculation
    - Include code examples for common use cases
    - Document configuration options
    - Add troubleshooting section
    - _Requirements: 6.3_

  - [x] 13.3 Create migration guide
    - Document changes from old to new system
    - Provide migration steps for existing code
    - List breaking changes (if any)
    - Add FAQ section
    - _Requirements: 10.5_

  - [x] 13.4 Run Pint for code formatting
    - Run `vendor/bin/pint` on all modified files
    - Verify code style compliance
    - _Requirements: 1.1_

  - [x] 13.5 Run PHPStan for static analysis
    - Run PHPStan on all modified files
    - Fix any type errors or issues
    - Verify level 5 compliance minimum
    - _Requirements: 1.1_

- [ ] 14. Final Checkpoint - Complete verification
  - Run full test suite
  - Verify all tests pass
  - Check code coverage (target: >80%)
  - Review all documentation
  - Ask the user for final approval

## Notes

- All tasks are required for comprehensive implementation
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties (minimum 100 iterations each)
- Unit tests validate specific examples and edge cases
- Integration tests validate end-to-end flows
- All code must follow Laravel best practices and PSR-12 coding standards
- Use Laravel's built-in features (validation, logging, queues) where possible
- Maintain backward compatibility with existing code
