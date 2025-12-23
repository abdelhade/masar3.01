# Requirements Document

## Introduction

This document outlines the requirements for improving the Average Cost Recalculation System in the MASSAR application. The system currently uses a hybrid approach to recalculate average costs for inventory items, but has several issues including unused imports, missing error handling, lack of tests, and potential performance bottlenecks.

## Glossary

- **Average_Cost**: The weighted average cost of an inventory item calculated as total value divided by total quantity
- **Item**: An inventory item (product) in the system stored in the `items` table
- **Operation**: An invoice/transaction that affects inventory (stored in `operhead` and `operation_items` tables)
- **Recalculation_Service**: A service class responsible for recalculating average costs
- **Stored_Procedure**: A database procedure that performs calculations at the database level
- **Queue_Job**: A background job that processes tasks asynchronously
- **Factory**: A class that creates appropriate service instances based on data size
- **Helper**: A unified interface class for accessing recalculation services
- **Batch_Processing**: Processing multiple items in groups to optimize performance
- **Stock_Operation**: An operation that affects inventory (is_stock = 1)

## Requirements

### Requirement 1: Code Quality Improvements

**User Story:** As a developer, I want clean, maintainable code without unused imports and with proper error handling, so that the codebase is easier to maintain and debug.

#### Acceptance Criteria

1. WHEN the system loads service classes, THE System SHALL NOT include unused imports
2. WHEN an error occurs during recalculation, THE System SHALL log detailed error information including item IDs and error context
3. WHEN a database query fails, THE System SHALL handle the exception gracefully and provide meaningful error messages
4. WHEN processing batches, THE System SHALL validate input parameters before processing
5. THE System SHALL use consistent error handling patterns across all service classes

### Requirement 2: Performance Monitoring and Logging

**User Story:** As a system administrator, I want detailed performance metrics and logging, so that I can monitor system performance and identify bottlenecks.

#### Acceptance Criteria

1. WHEN a recalculation starts, THE System SHALL log the start time, item count, and recalculation type
2. WHEN a recalculation completes, THE System SHALL log the end time, duration, and items processed
3. WHEN using batch processing, THE System SHALL log batch size and processing time per batch
4. WHEN the system chooses a recalculation strategy, THE System SHALL log which strategy was selected and why
5. WHEN performance thresholds are exceeded, THE System SHALL log warning messages

### Requirement 3: Input Validation and Data Integrity

**User Story:** As a developer, I want robust input validation, so that invalid data doesn't cause system errors or data corruption.

#### Acceptance Criteria

1. WHEN receiving item IDs, THE System SHALL validate that all IDs are positive integers
2. WHEN receiving a date parameter, THE System SHALL validate the date format (Y-m-d)
3. WHEN receiving an empty array of item IDs, THE System SHALL return early without processing
4. WHEN receiving invalid parameters, THE System SHALL throw a descriptive exception
5. WHEN validating items exist, THE System SHALL check the database before processing

### Requirement 4: Comprehensive Testing

**User Story:** As a developer, I want comprehensive tests for all recalculation services, so that I can ensure correctness and prevent regressions.

#### Acceptance Criteria

1. WHEN testing single item recalculation, THE System SHALL correctly calculate average cost for valid data
2. WHEN testing batch recalculation, THE System SHALL process all items correctly
3. WHEN testing with deleted operations, THE System SHALL exclude deleted operations from calculations
4. WHEN testing the factory, THE System SHALL select the correct service based on data size
5. WHEN testing error conditions, THE System SHALL handle errors gracefully
6. WHEN testing with missing items, THE System SHALL set average cost to zero
7. WHEN testing the delete flag, THE System SHALL recalculate from all operations when isDelete is true

### Requirement 5: Configuration Management

**User Story:** As a system administrator, I want configurable thresholds and settings, so that I can tune the system for different environments and data sizes.

#### Acceptance Criteria

1. WHEN the system determines strategy, THE System SHALL read threshold values from configuration
2. WHERE stored procedures are enabled, THE System SHALL use stored procedures for large datasets
3. WHERE queue is enabled, THE System SHALL use background jobs for very large datasets
4. THE System SHALL provide default configuration values that work for typical use cases
5. WHEN configuration is invalid, THE System SHALL use safe default values and log a warning

### Requirement 6: Documentation and Code Comments

**User Story:** As a developer, I want clear documentation and comments, so that I can understand the system's behavior and maintain it effectively.

#### Acceptance Criteria

1. THE System SHALL include PHPDoc blocks for all public methods with @param, @return, and @throws annotations
2. THE System SHALL document complex algorithms and business logic with inline comments
3. THE System SHALL include examples in documentation for common use cases
4. THE System SHALL document the calculation formula for average cost
5. THE System SHALL document the criteria for strategy selection

### Requirement 7: Stored Procedure Support Validation

**User Story:** As a system administrator, I want the system to validate stored procedure availability, so that I don't encounter runtime errors when stored procedures are missing.

#### Acceptance Criteria

1. WHEN stored procedures are enabled, THE System SHALL verify that required procedures exist in the database
2. WHEN a stored procedure is missing, THE System SHALL fall back to PHP implementation and log a warning
3. WHEN calling a stored procedure, THE System SHALL handle database-specific errors gracefully
4. THE System SHALL provide a command to check stored procedure availability
5. WHEN stored procedures fail, THE System SHALL retry with PHP implementation

### Requirement 8: Batch Processing Optimization

**User Story:** As a developer, I want optimized batch processing, so that large datasets are processed efficiently without memory issues.

#### Acceptance Criteria

1. WHEN processing items in batches, THE System SHALL use configurable batch sizes
2. WHEN memory usage is high, THE System SHALL reduce batch size automatically
3. WHEN processing large batches, THE System SHALL use database transactions for data integrity
4. THE System SHALL process batches in parallel when possible
5. WHEN a batch fails, THE System SHALL retry only the failed batch, not all batches

### Requirement 9: Queue Job Improvements

**User Story:** As a system administrator, I want reliable queue jobs with proper error handling and retry logic, so that background processing is robust.

#### Acceptance Criteria

1. WHEN a queue job fails, THE System SHALL retry up to 3 times with exponential backoff
2. WHEN all retries fail, THE System SHALL log detailed failure information
3. WHEN a job is queued, THE System SHALL assign it to the appropriate queue based on size
4. WHEN processing in background, THE System SHALL update job progress for monitoring
5. WHEN a job times out, THE System SHALL log timeout information and mark the job as failed

### Requirement 10: Integration with Existing System

**User Story:** As a developer, I want seamless integration with existing invoice and manufacturing systems, so that recalculation happens automatically when needed.

#### Acceptance Criteria

1. WHEN a new invoice is added, THE System SHALL calculate and update average cost for all affected items
2. WHEN an invoice is deleted, THE System SHALL recalculate from all non-deleted operations
3. WHEN an invoice is updated, THE System SHALL recalculate from the invoice date
4. WHEN a manufacturing operation is completed, THE System SHALL recalculate affected products
5. THE System SHALL maintain backward compatibility with existing service calls

### Requirement 16: Manufacturing Invoice Raw Materials Impact

**User Story:** As a business analyst, I want the system to handle raw material cost changes in manufacturing invoices, so that product costs are accurately calculated when purchase invoices are modified or deleted.

#### Acceptance Criteria

1. WHEN a purchase invoice is deleted, THE System SHALL identify all manufacturing invoices that used items from that purchase invoice
2. WHEN a purchase invoice is modified, THE System SHALL recalculate average cost for raw materials in all subsequent manufacturing invoices
3. WHEN raw material costs change, THE System SHALL update the cost of manufactured products in affected manufacturing invoices
4. THE System SHALL process manufacturing invoices in chronological order (by date and time)
5. WHEN recalculating manufacturing costs, THE System SHALL update both raw materials section and products section
6. THE System SHALL ensure that changes in raw materials costs (section 1) propagate to product costs (section 2)

### Requirement 17: Manufacturing Invoice Modification and Deletion

**User Story:** As a business analyst, I want accurate recalculation when manufacturing invoices are modified or deleted, so that product average costs remain correct throughout the system.

#### Acceptance Criteria

1. WHEN a manufacturing invoice is modified, THE System SHALL recalculate average cost for all product items in that invoice
2. WHEN raw materials or expenses change in a manufacturing invoice, THE System SHALL update product costs accordingly
3. WHEN a manufacturing invoice is modified, THE System SHALL recalculate average cost for product items in all operations after the invoice date and time
4. WHEN a manufacturing invoice is deleted, THE System SHALL recalculate average cost for all product items in all operations after the deleted invoice date and time
5. WHEN processing manufacturing invoice changes, THE System SHALL handle both raw materials (inputs) and products (outputs) correctly
6. THE System SHALL use both date and time for chronological ordering of manufacturing operations

### Requirement 18: Cascading Recalculation for Manufacturing Chain

**User Story:** As a business analyst, I want cascading recalculation through the manufacturing chain, so that cost changes propagate correctly from raw materials to finished products.

#### Acceptance Criteria

1. WHEN a purchase invoice affecting raw materials is changed, THE System SHALL identify all manufacturing invoices using those raw materials
2. WHEN manufacturing invoice costs are recalculated, THE System SHALL update product average costs in subsequent operations
3. WHEN a product from one manufacturing invoice is used as raw material in another, THE System SHALL recalculate the chain in correct order
4. THE System SHALL process the recalculation chain in chronological order (date and time)
5. WHEN multiple manufacturing invoices are affected, THE System SHALL process them in a single transaction to ensure consistency
6. THE System SHALL log the recalculation chain for audit and debugging purposes

### Requirement 11: Performance Benchmarking

**User Story:** As a developer, I want performance benchmarks, so that I can measure improvements and identify regressions.

#### Acceptance Criteria

1. THE System SHALL provide a command to benchmark different recalculation strategies
2. WHEN benchmarking, THE System SHALL test with various data sizes (100, 1000, 10000 items)
3. WHEN benchmarking, THE System SHALL measure execution time, memory usage, and query count
4. THE System SHALL compare PHP implementation vs Stored Procedures vs Queue Jobs
5. THE System SHALL generate a benchmark report with recommendations

### Requirement 12: Calculation Accuracy Verification

**User Story:** As a business analyst, I want to verify calculation accuracy, so that I can trust the average cost values in the system.

#### Acceptance Criteria

1. WHEN calculating average cost, THE System SHALL use the formula: SUM(detail_value) / SUM(qty_in - qty_out)
2. WHEN total quantity is zero or negative, THE System SHALL set average cost to zero
3. WHEN operations are filtered by date, THE System SHALL only include operations on or after the specified date
4. WHEN the delete flag is true, THE System SHALL ignore the date filter and recalculate from all operations
5. THE System SHALL only include stock operations (is_stock = 1) and specific operation types (11, 12, 20, 59)
6. THE System SHALL exclude deleted operations (isdeleted = 0)

### Requirement 13: Monitoring and Alerting

**User Story:** As a system administrator, I want monitoring and alerting for recalculation issues, so that I can proactively address problems.

#### Acceptance Criteria

1. WHEN recalculation takes longer than expected, THE System SHALL log a warning
2. WHEN recalculation fails repeatedly, THE System SHALL send an alert notification
3. WHEN queue jobs are backing up, THE System SHALL alert administrators
4. THE System SHALL track recalculation success rate and alert on degradation
5. THE System SHALL provide a dashboard showing recalculation metrics

### Requirement 14: Data Consistency Checks

**User Story:** As a developer, I want data consistency checks, so that I can identify and fix data integrity issues.

#### Acceptance Criteria

1. THE System SHALL provide a command to verify average cost accuracy for all items
2. WHEN inconsistencies are found, THE System SHALL log the discrepancies with item details
3. THE System SHALL provide a command to fix inconsistent average costs
4. WHEN verifying consistency, THE System SHALL compare calculated values with stored values
5. THE System SHALL generate a consistency report with statistics

### Requirement 15: Rollback and Recovery

**User Story:** As a system administrator, I want rollback capabilities, so that I can recover from failed recalculations.

#### Acceptance Criteria

1. WHEN starting a recalculation, THE System SHALL backup current average cost values
2. WHEN a recalculation fails, THE System SHALL provide an option to rollback to previous values
3. WHEN rolling back, THE System SHALL restore all affected items to their previous state
4. THE System SHALL log all rollback operations for audit purposes
5. WHEN a partial failure occurs, THE System SHALL identify which items were successfully updated
