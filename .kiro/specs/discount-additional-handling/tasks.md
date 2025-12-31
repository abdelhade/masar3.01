# Implementation Plan: Discount and Additional Handling

## Current Status Summary

**Completed Work:**
- ✅ Phase 1: Core services (DetailValueCalculator, DetailValueValidator) with comprehensive unit tests
- ✅ Phase 2: SaveInvoiceService integration with calculation and validation
- ✅ Phase 3: Historical data fix command (RecalculateDetailValuesCommand) with tests
- ✅ Phase 7: Comprehensive documentation (Developer Guide, User Guide, Tax Reference)
- ✅ Settings seeder updated with feature mode settings (discount_mode, additional_mode, tax_mode, tax_discount_mode)
- ✅ Integration test for purchase invoice flow with taxes

**Remaining Work:**
- ⏳ Property-based tests for core calculation properties (distribution sum, proportional distribution, non-negativity, accuracy)
- ⏳ Additional integration tests (sales flow, invoice modification, deletion, multi-currency)
- ⏳ Edge case testing (zero values, single item, negative handling, large values, rounding)
- ⏳ Performance testing (benchmarking, concurrent operations)
- ⏳ Feature Mode System (FeatureModeManager, InvoiceFormStateManager services)
- ⏳ Frontend integration (invoice form dynamic field control, aggregated values display)
- ⏳ Code quality checks (PHPStan, code review)
- ⏳ Deployment preparation (checklist, backup scripts, staging tests, monitoring)

## Overview

This implementation plan breaks down the discount and additional handling feature into discrete, manageable tasks. The plan follows a phased approach, starting with core services, then integration, testing, and finally deployment.

## Tasks

- [x] 1. Phase 1: Core Services Development
  - [x] 1.1 Create DetailValueCalculator service
    - Create `app/Services/Invoice/DetailValueCalculator.php`
    - Implement `calculate()` method with full calculation logic
    - Implement `calculateInvoiceSubtotal()` method
    - Implement `distributeInvoiceDiscount()` private method
    - Implement `distributeInvoiceAdditional()` private method
    - Add comprehensive PHPDoc blocks
    - _Requirements: 1.1, 1.2, 1.3, 2.1, 2.2, 2.3, 3.1, 3.2, 3.3_

  - [x] 1.2 Write unit tests for DetailValueCalculator
    - Test item subtotal calculation
    - Test invoice subtotal calculation
    - Test fixed amount discount distribution
    - Test percentage discount distribution
    - Test fixed amount additional distribution
    - Test percentage additional distribution
    - Test combined discounts and additions
    - Test edge cases (zero values, single item, empty invoice)
    - _Requirements: 1.1, 1.2, 1.3, 2.1, 2.2, 2.3, 3.1, 3.2, 3.3_

  - [x] 1.3 Create DetailValueValidator service
    - Create `app/Services/Invoice/DetailValueValidator.php`
    - Implement `validate()` method with all validation rules
    - Implement `isReasonable()` private method
    - Implement `verifyCalculation()` private method
    - Add comprehensive PHPDoc blocks
    - _Requirements: 8.1, 8.2, 8.3, 8.4_

  - [x] 1.4 Write unit tests for DetailValueValidator
    - Test negative value rejection
    - Test unreasonable value detection
    - Test calculation accuracy verification
    - Test tolerance handling (0.01 tolerance)
    - Test edge cases
    - _Requirements: 8.1, 8.2, 8.3, 8.4_

  - [ ] 1.5 Write property test for distribution sum
    - **Property 2: Invoice Discount Distribution Sum**
    - **Property 3: Invoice Additional Distribution Sum**
    - **Validates: Requirements 2.1, 2.2, 3.1, 3.2**
    - Generate random invoices with multiple items
    - Verify sum of distributed amounts equals invoice amount

  - [ ] 1.6 Write property test for proportional distribution
    - **Property 5: Proportional Distribution**
    - **Validates: Requirements 2.1, 3.1**
    - Generate random items with different values
    - Verify distribution is proportional to item values

  - [ ] 1.7 Write property test for non-negativity
    - **Property 1: Detail Value Non-Negativity**
    - **Validates: Requirements 4.4, 8.2**
    - Generate random valid item data
    - Verify detail_value is never negative

  - [ ] 1.8 Write property test for calculation accuracy
    - **Property 4: Detail Value Calculation Accuracy**
    - **Validates: Requirements 1.3, 8.2**
    - Generate random item and invoice data
    - Verify detail_value matches formula

- [ ] 2. Checkpoint - Verify Phase 1 completion
  - Ensure all tests pass, ask the user if questions arise.

- [x] 3. Phase 2: SaveInvoiceService Integration
  - [x] 3.1 Update SaveInvoiceService constructor
    - Inject `DetailValueCalculator` dependency
    - Inject `DetailValueValidator` dependency
    - Update PHPDoc blocks
    - _Requirements: 4.1, 4.2, 5.1_

  - [x] 3.2 Implement calculateItemDetailValues() method
    - Calculate invoice subtotal from all items
    - Loop through items and calculate detail_value for each
    - Validate each calculated detail_value
    - Return items with calculated detail_value
    - Add comprehensive error handling
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_

  - [x] 3.3 Update saveInvoice() for purchase invoices
    - Call `calculateItemDetailValues()` before saving items
    - Use calculated detail_value instead of frontend value
    - Add logging for audit trail
    - Handle validation errors
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_

  - [x] 3.4 Update saveInvoice() for sales invoices
    - Calculate detail_value for revenue calculation
    - Use current average_cost for cost calculation
    - Calculate profit correctly
    - Add logging for audit trail
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

  - [x] 3.5 Update saveInvoice() for purchase returns
    - Calculate detail_value as negative value
    - Ensure proper inventory reduction
    - Trigger average cost recalculation
    - Add logging for audit trail
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

  - [x] 3.6 Update saveInvoice() for sales returns
    - Calculate detail_value based on original sales
    - Restore inventory correctly
    - Update profit values
    - Add logging for audit trail
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_

  - [x] 3.7 Add comprehensive logging
    - Log all calculation details
    - Log original values, discounts, additions
    - Log final detail_value
    - Include invoice_id and item_id in logs
    - _Requirements: 9.1, 9.2, 9.3_

  - [x] 3.8 Write unit tests for SaveInvoiceService changes
    - Test detail_value calculation integration
    - Test validation integration
    - Test logging
    - Test error handling
    - Test all invoice types (purchase, sales, returns)
    - _Requirements: 4.1, 4.2, 4.3, 5.1, 5.2, 6.1, 7.1_

- [x] 4. Checkpoint - Verify Phase 2 completion
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Phase 3: Historical Data Fix Command
  - [x] 5.1 Create RecalculateDetailValuesCommand
    - Create `app/Console/Commands/RecalculateDetailValuesCommand.php`
    - Implement command signature with all options
    - Implement `handle()` method
    - Implement `recalculateInvoice()` private method
    - Add progress bar for user feedback
    - Add comprehensive PHPDoc blocks
    - _Requirements: 10.2, 10.3_

  - [x] 5.2 Implement invoice filtering logic
    - Filter by invoice_id if provided
    - Filter by date range if provided
    - Filter by operation_type if provided
    - Support batch processing
    - _Requirements: 10.2, 10.3_

  - [x] 5.3 Implement dry-run mode
    - Preview changes without saving
    - Display what would be changed
    - Show before/after values
    - _Requirements: 10.3_

  - [x] 5.4 Add error handling and reporting
    - Handle errors gracefully
    - Continue processing on error
    - Report summary at end
    - Log all errors
    - _Requirements: 10.3_

  - [x] 5.5 Write unit tests for command
    - Test invoice filtering
    - Test dry-run mode
    - Test actual recalculation
    - Test error handling
    - Test batch processing
    - _Requirements: 10.2, 10.3_

- [x] 6. Checkpoint - Verify Phase 3 completion
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 7. Phase 4: Integration Testing
  - [x] 7.1 Write integration test for purchase invoice flow
    - Create purchase invoice with item and invoice discounts
    - Verify detail_value is calculated correctly
    - Verify average cost is recalculated
    - Verify values in database
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_

  - [ ] 7.2 Write integration test for sales invoice flow
    - Create sales invoice with discounts
    - Verify detail_value calculation
    - Verify profit calculation
    - Verify average cost is not changed
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

  - [ ] 7.3 Write integration test for invoice modification
    - Create invoice
    - Modify discounts and additions
    - Verify detail_value is recalculated
    - Verify average cost is updated
    - _Requirements: 4.3, 10.4_

  - [ ] 7.4 Write integration test for invoice deletion
    - Create invoice
    - Delete invoice
    - Verify average cost is recalculated
    - Verify inventory is updated
    - _Requirements: 4.3, 10.4_

  - [ ] 7.5 Write integration test for historical data fix
    - Create invoices with incorrect detail_value
    - Run fix command
    - Verify values are corrected
    - Verify dry-run doesn't modify data
    - _Requirements: 10.2, 10.3_

  - [ ] 7.6 Write integration test for multi-currency
    - Create invoice in foreign currency
    - Verify detail_value calculation
    - Verify currency conversion
    - Verify average cost in base currency
    - _Requirements: 12.1, 12.2, 12.3, 12.4_

- [ ] 8. Checkpoint - Verify Phase 4 completion
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 9. Phase 5: Edge Cases and Error Handling
  - [ ] 9.1 Test zero discount/additional handling
    - Test invoices with no discounts
    - Test invoices with no additions
    - Verify detail_value equals item_price × quantity
    - _Requirements: 10.1_

  - [ ] 9.2 Test single item invoice
    - Test invoice with only one item
    - Verify distribution works correctly
    - Verify 100% of invoice discount goes to item
    - _Requirements: 2.1, 3.1_

  - [ ] 9.3 Test empty invoice handling
    - Test invoice with no items
    - Verify graceful error handling
    - Verify no database changes
    - _Requirements: 8.3, 8.4_

  - [ ] 9.4 Test negative discount handling
    - Test invoice with negative discount (should fail)
    - Verify validation rejects it
    - Verify descriptive error message
    - _Requirements: 8.1, 8.3_

  - [ ] 9.5 Test very large values
    - Test with very large prices and quantities
    - Verify no overflow errors
    - Verify calculations remain accurate
    - _Requirements: 8.2_

  - [ ] 9.6 Test rounding edge cases
    - Test values that cause rounding issues
    - Verify tolerance handling (0.01)
    - Verify no accumulation of rounding errors
    - _Requirements: 8.2_

- [ ] 10. Checkpoint - Verify Phase 5 completion
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 11. Phase 6: Performance Testing
  - [ ] 11.1 Benchmark invoice creation performance
    - Measure time for invoice with 10 items
    - Measure time for invoice with 100 items
    - Compare with old implementation
    - Verify acceptable performance (<100ms)
    - _Requirements: Performance considerations_

  - [ ] 11.2 Benchmark historical data fix performance
    - Measure time to fix 1000 invoices
    - Verify batch processing works
    - Verify memory usage is acceptable
    - _Requirements: Performance considerations_

  - [ ] 11.3 Test concurrent invoice creation
    - Create multiple invoices simultaneously
    - Verify no race conditions
    - Verify data consistency
    - _Requirements: 10.4_

- [x] 12. Phase 7: Documentation
  - [x] 12.1 Add PHPDoc to all new classes
    - DetailValueCalculator
    - DetailValueValidator
    - RecalculateDetailValuesCommand
    - Include @param, @return, @throws
    - Include usage examples
    - _Requirements: Documentation_

  - [x] 12.2 Update SaveInvoiceService documentation
    - Document new calculation logic
    - Document validation rules
    - Document error handling
    - Include examples
    - _Requirements: Documentation_

  - [x] 12.3 Create user documentation
    - How discounts affect average cost
    - How to verify calculations
    - How to fix historical data
    - Troubleshooting guide
    - _Requirements: Documentation_

  - [x] 12.4 Create developer documentation
    - API documentation
    - Calculation formulas
    - Testing guide
    - Deployment guide
    - _Requirements: Documentation_

  - [x] 12.5 Update README
    - Add section on discount handling
    - Include usage examples
    - Document artisan commands
    - Add FAQ
    - _Requirements: Documentation_

- [ ] 13. Phase 8: Code Quality
  - [x] 13.1 Run Pint for code formatting
    - Run on all new files
    - Run on modified files
    - Verify PSR-12 compliance
    - _Requirements: Code quality_

  - [ ] 13.2 Run PHPStan for static analysis
    - Run on all new files
    - Fix any type errors
    - Verify level 5 compliance
    - _Requirements: Code quality_

  - [ ] 13.3 Code review
    - Review all new code
    - Check for security issues
    - Check for performance issues
    - Verify best practices
    - _Requirements: Code quality_

- [ ] 14. Phase 9: Deployment Preparation
  - [ ] 14.1 Create deployment checklist
    - List all steps for deployment
    - Include rollback plan
    - Include verification steps
    - _Requirements: Deployment_

  - [ ] 14.2 Create database backup script
    - Backup operation_items table
    - Backup operhead table
    - Test restore procedure
    - _Requirements: Deployment_

  - [ ] 14.3 Test on staging environment
    - Deploy to staging
    - Run all tests
    - Test manually
    - Verify performance
    - _Requirements: Deployment_

  - [ ] 14.4 Run historical data fix on staging
    - Backup staging database
    - Run fix command with dry-run
    - Review results
    - Run actual fix
    - Verify results
    - _Requirements: 10.2, 10.3_

  - [ ] 14.5 Create monitoring dashboard
    - Track calculation errors
    - Track performance metrics
    - Track data quality
    - Set up alerts
    - _Requirements: Monitoring_

- [ ] 15. Final Checkpoint - Complete verification
  - Run full test suite
  - Verify all tests pass
  - Check code coverage (target: >80%)
  - Review all documentation
  - Verify staging deployment
  - Ask the user for final approval

- [ ] 16. Phase 10: Feature Mode System Implementation
  - [x] 16.1 Create FeatureModeManager service
    - Create `app/Services/Invoice/FeatureModeManager.php`
    - Implement `getMode()` method
    - Implement `isInvoiceLevelEnabled()` method
    - Implement `isItemLevelEnabled()` method
    - Implement `shouldShowAggregatedValues()` method
    - Implement `getAllModes()` method
    - Implement `isValidMode()` private method
    - Add comprehensive PHPDoc blocks
    - _Requirements: 13.1, 13.2, 13.3, 13.4, 13.7, 13.8, 13.9, 13.10_

  - [ ] 16.2 Write unit tests for FeatureModeManager
    - Test getMode() for all features
    - Test isInvoiceLevelEnabled() for all modes
    - Test isItemLevelEnabled() for all modes
    - Test shouldShowAggregatedValues() for all modes
    - Test getAllModes() returns correct structure
    - Test mode validation
    - Test invalid mode handling
    - _Requirements: 13.1, 13.2, 13.3, 13.4_

  - [x] 16.3 Create InvoiceFormStateManager service
    - Create `app/Services/Invoice/InvoiceFormStateManager.php`
    - Inject FeatureModeManager dependency
    - Implement `getFieldStates()` method
    - Implement `getJavaScriptConfig()` method
    - Implement `isFieldEnabled()` method
    - Add comprehensive PHPDoc blocks
    - _Requirements: 14.1, 14.2, 14.3, 14.4, 14.18, 14.19_

  - [ ] 16.4 Write unit tests for InvoiceFormStateManager
    - Test getFieldStates() for all mode combinations
    - Test getJavaScriptConfig() structure
    - Test isFieldEnabled() for all scenarios
    - Test integration with FeatureModeManager
    - Test all features (discount, additional, tax, tax_discount)
    - _Requirements: 14.1, 14.2, 14.3, 14.4_

  - [ ] 16.5 Write property test for feature mode consistency
    - **Property 11: Feature Mode Consistency**
    - **Property 12: Feature Mode Both Enablement**
    - **Property 13: Feature Mode Disabled State**
    - **Validates: Requirements 13.7, 13.8, 13.9, 13.10, 14.1, 14.3, 14.4**
    - Generate random feature mode settings
    - Verify field states match mode settings
    - Test all mode combinations

  - [x] 16.6 Update settings seeder with feature modes
    - Update `database/seeders/PurchaseDiscountMethodSeeder.php`
    - Remove old `discount_mode` setting (invoice_level/item_level)
    - Add `discount_mode` with new options (invoice_level, item_level, both, disabled)
    - Add `additional_mode` setting
    - Add `tax_mode` setting
    - Add `tax_discount_mode` setting
    - Set default values as specified in requirements
    - _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5, 13.6_

- [ ] 17. Checkpoint - Verify Phase 10 completion
  - Ensure all tests pass, ask the user if questions arise.

- [-] 18. Phase 11: Frontend Integration
  - [x] 18.1 Update invoice form Blade template
    - Locate invoice form template (likely in resources/views or Modules)
    - Inject InvoiceFormStateManager
    - Add Alpine.js x-data with field states
    - Update invoice discount field with :disabled binding
    - Update invoice additional field with :disabled binding
    - Update invoice tax field with :disabled binding
    - Update invoice tax discount field with :disabled binding
    - _Requirements: 14.1, 14.5, 14.9, 14.13, 14.17, 14.18_

  - [x] 18.2 Update invoice items table template
    - Update item discount column with :disabled binding
    - Update item additional column with :disabled binding
    - Update item tax column with :disabled binding
    - Update item tax discount column with :disabled binding
    - Apply disabled state based on mode settings
    - _Requirements: 14.2, 14.6, 14.10, 14.14, 14.17_

  - [x] 18.3 Implement aggregated values display
    - Add aggregated tax display section in invoice footer
    - Add aggregated tax discount display section
    - Use x-show directive to show/hide based on mode
    - Add Alpine.js method to calculate aggregated values
    - Format display with clear labels (Arabic)
    - _Requirements: 15.1, 15.2, 15.4, 15.6, 15.7_

  - [x] 18.4 Add real-time aggregated values calculation
    - Create Alpine.js computed property for aggregated tax
    - Create Alpine.js computed property for aggregated tax discount
    - Update calculations when items are added/removed
    - Update calculations when item values change
    - Test real-time updates
    - _Requirements: 15.3, 15.5_

  - [x] 18.5 Write property test for aggregated values accuracy
    - **Property 14: Aggregated Values Display Condition**
    - **Property 15: Aggregated Values Calculation Accuracy**
    - **Validates: Requirements 15.1, 15.2, 15.3, 15.5**
    - Generate random invoices with item-level taxes
    - Verify sum of item values equals aggregated display value
    - Test with different mode settings

  - [x] 18.6 Add settings page UI for feature modes
    - Update settings view to show mode dropdowns
    - Add select options (invoice_level, item_level, both, disabled)
    - Add Arabic labels for each option
    - Add help text explaining each mode
    - Test settings save/load
    - _Requirements: 13.5, 13.6_

- [ ] 19. Checkpoint - Verify Phase 11 completion
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 20. Phase 12: Integration Testing for Feature Modes
  - [ ] 20.1 Write integration test for invoice_level mode
    - Set all features to 'invoice_level'
    - Create invoice with invoice-level values
    - Verify invoice fields are enabled
    - Verify item fields are disabled
    - Verify no aggregated values displayed
    - _Requirements: 13.7, 14.1, 14.5, 14.9, 14.13_

  - [ ] 20.2 Write integration test for item_level mode
    - Set all features to 'item_level'
    - Create invoice with item-level values
    - Verify invoice fields are disabled
    - Verify item fields are enabled
    - Verify aggregated values displayed
    - _Requirements: 13.8, 14.2, 14.6, 14.10, 14.14, 15.1, 15.2_

  - [ ] 20.3 Write integration test for both mode
    - Set all features to 'both'
    - Create invoice with both invoice and item values
    - Verify all fields are enabled
    - Verify aggregated values displayed for item-level
    - _Requirements: 13.9, 14.3, 14.7, 14.11, 14.15_

  - [ ] 20.4 Write integration test for disabled mode
    - Set all features to 'disabled'
    - Verify all fields are disabled
    - Verify no aggregated values displayed
    - Verify validation rejects any values
    - _Requirements: 13.10, 14.4, 14.8, 14.12, 14.16_

  - [ ] 20.5 Write integration test for mode transitions
    - Change mode from 'invoice_level' to 'item_level'
    - Reload invoice form
    - Verify field states update correctly
    - Test all mode transitions
    - _Requirements: 14.19_

  - [ ] 20.6 Write integration test for mixed modes
    - Set discount to 'invoice_level'
    - Set tax to 'item_level'
    - Set additional to 'both'
    - Set tax_discount to 'disabled'
    - Verify each feature behaves independently
    - _Requirements: 13.1, 13.2, 13.3, 13.4_

- [ ] 21. Final Checkpoint - Complete verification with new features
  - Run full test suite including new feature mode tests
  - Verify all tests pass
  - Check code coverage (target: >80%)
  - Review all documentation
  - Test all mode combinations manually
  - Verify staging deployment
  - Ask the user for final approval

## Notes

- All tasks are required for comprehensive implementation
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties (minimum 100 iterations each)
- Unit tests validate specific examples and edge cases
- Integration tests validate end-to-end flows
- All code must follow Laravel best practices and PSR-12 coding standards
- Use Laravel's built-in features (validation, logging, transactions) where possible
- Maintain backward compatibility with existing code
- No database schema changes required

## Estimated Timeline

- **Phase 1**: 2-3 days (Core services and tests)
- **Phase 2**: 2 days (Integration with SaveInvoiceService)
- **Phase 3**: 1 day (Historical data fix command)
- **Phase 4**: 2 days (Integration testing)
- **Phase 5**: 1 day (Edge cases and error handling)
- **Phase 6**: 1 day (Performance testing)
- **Phase 7**: 1 day (Documentation)
- **Phase 8**: 0.5 day (Code quality)
- **Phase 9**: 1.5 days (Deployment preparation)
- **Phase 10**: 2-3 days (Feature mode system implementation)
- **Phase 11**: 2-3 days (Frontend integration)
- **Phase 12**: 2 days (Integration testing for feature modes)

**Total**: 18-21 days

## Success Criteria

1. ✅ All unit tests pass
2. ✅ All property tests pass (minimum 100 iterations each)
3. ✅ All integration tests pass
4. ✅ Code coverage >80%
5. ✅ Performance acceptable (<100ms per invoice)
6. ✅ Historical data successfully fixed
7. ✅ Documentation complete
8. ✅ Staging deployment successful
9. ✅ User approval received
10. ✅ Feature mode system working correctly
11. ✅ All mode combinations tested (invoice_level, item_level, both, disabled)
12. ✅ Frontend dynamic field control working
13. ✅ Aggregated values displaying correctly
14. ✅ Settings page updated with mode options
