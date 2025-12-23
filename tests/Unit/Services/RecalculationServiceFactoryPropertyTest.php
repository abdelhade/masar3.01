<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Item;
use App\Models\OperationItems;
use App\Models\OperHead;
use App\Services\AverageCostRecalculationServiceOptimized;
use App\Services\AverageCostRecalculationServiceStoredProcedure;
use App\Services\Monitoring\RecalculationPerformanceMonitor;
use App\Services\RecalculationServiceFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

/**
 * Property-Based Tests for RecalculationServiceFactory
 *
 * Feature: average-cost-recalculation-improvements
 * Property 14: Configuration-Based Strategy Selection
 * Validates: Requirements 5.1
 *
 * For any recalculation request, the strategy selection should respect
 * configuration thresholds for stored procedures and queue jobs.
 */
class RecalculationServiceFactoryPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Disable global scopes for testing
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Disable global scopes that might interfere with tests
        \App\Models\OperationItems::withoutGlobalScopes();

        Log::spy();

        // Mock RecalculationPerformanceMonitor
        $this->app->instance(
            RecalculationPerformanceMonitor::class,
            Mockery::mock(RecalculationPerformanceMonitor::class)
        );

        // Create test data
        $this->createTestData();
    }

    /**
     * Create test data for database queries
     */
    private function createTestData(): void
    {
        // Create test items (1-100)
        for ($i = 1; $i <= 100; $i++) {
            Item::factory()->create(['id' => $i]);
        }

        // Create a test operation header
        $operHead = OperHead::factory()->create([
            'pro_date' => now()->format('Y-m-d'),
            'isdeleted' => 0,
            'pro_tybe' => 11,
        ]);

        // Create operation items for the first 50 items
        for ($i = 1; $i <= 50; $i++) {
            OperationItems::create([
                'pro_id' => $operHead->id,
                'item_id' => $i,
                'is_stock' => 1,
                'qty_in' => 10,
                'qty_out' => 0,
                'detail_value' => 100,
                'branch_id' => 1, // Add branch_id for BranchScope
            ]);
        }
    }

    /**
     * Property 14: Configuration-Based Strategy Selection
     *
     * Test that strategy selection respects configuration thresholds
     * with real database queries
     *
     * @dataProvider strategySelectionProvider
     */
    public function test_property_strategy_selection_respects_configuration(
        int $itemCount,
        int $storedProcedureThreshold,
        int $operationCountThreshold,
        bool $spEnabled,
        string $expectedServiceClass
    ): void {
        // Arrange: Set configuration
        Config::set('recalculation', [
            'stored_procedure_threshold' => $storedProcedureThreshold,
            'operation_count_threshold' => $operationCountThreshold,
            'use_stored_procedures' => $spEnabled,
        ]);

        // Generate item IDs based on count (use existing items from test data)
        $itemIds = range(1, min($itemCount, 100));

        // Act: Create service
        $service = RecalculationServiceFactory::createAverageCostService($itemIds, null);

        // Assert: Should return expected service type
        $this->assertInstanceOf(
            $expectedServiceClass,
            $service,
            "With {$itemCount} items, SP threshold {$storedProcedureThreshold}, ".
            "operation threshold {$operationCountThreshold}, ".
            'SP enabled: '.($spEnabled ? 'yes' : 'no').
            ", expected {$expectedServiceClass}"
        );
    }

    /**
     * Data provider for strategy selection scenarios
     *
     * @return array<string, array{int, int, int, bool, string}>
     */
    public static function strategySelectionProvider(): array
    {
        return [
            // Below threshold - should use PHP optimized
            'small_dataset_sp_disabled' => [
                10,   // item count
                1000, // SP threshold
                100000, // operation count threshold
                false, // SP enabled
                AverageCostRecalculationServiceOptimized::class,
            ],
            'small_dataset_sp_enabled' => [
                10,
                1000,
                100000,
                true,
                AverageCostRecalculationServiceOptimized::class,
            ],

            // At threshold boundary
            'at_sp_threshold_sp_disabled' => [
                50,
                50,
                100000,
                false,
                AverageCostRecalculationServiceOptimized::class,
            ],
            'at_sp_threshold_sp_enabled' => [
                50,
                50,
                100000,
                true,
                AverageCostRecalculationServiceOptimized::class,
            ],

            // Above threshold - should use stored procedures if enabled
            'above_sp_threshold_sp_disabled' => [
                60,
                50,
                100000,
                false,
                AverageCostRecalculationServiceOptimized::class,
            ],
            'above_sp_threshold_sp_enabled' => [
                60,
                50,
                100000,
                true,
                AverageCostRecalculationServiceStoredProcedure::class,
            ],

            // Large dataset with different thresholds
            'large_dataset_low_sp_threshold' => [
                80,
                30,
                100000,
                true,
                AverageCostRecalculationServiceStoredProcedure::class,
            ],
            'large_dataset_high_sp_threshold' => [
                80,
                100,
                100000,
                true,
                AverageCostRecalculationServiceOptimized::class,
            ],

            // Edge cases
            'single_item_sp_enabled' => [
                1,
                50,
                100000,
                true,
                AverageCostRecalculationServiceOptimized::class,
            ],

            // Different threshold configurations
            'custom_low_threshold' => [
                40,
                20,
                100000,
                true,
                AverageCostRecalculationServiceStoredProcedure::class,
            ],
            'custom_high_threshold' => [
                40,
                60,
                100000,
                true,
                AverageCostRecalculationServiceOptimized::class,
            ],

            // Zero threshold edge case
            'zero_sp_threshold_sp_enabled' => [
                1,
                0,
                100000,
                true,
                AverageCostRecalculationServiceStoredProcedure::class,
            ],

            // Operation count threshold scenarios
            'low_operation_threshold_triggers_sp' => [
                10,
                1000, // High item threshold (won't trigger)
                10,   // Low operation threshold (will trigger - we have 50 operations)
                true,
                AverageCostRecalculationServiceStoredProcedure::class,
            ],
            'high_operation_threshold_uses_php' => [
                10,
                1000,
                100000, // High operation threshold (won't trigger)
                true,
                AverageCostRecalculationServiceOptimized::class,
            ],
        ];
    }

    /**
     * Test that strategy selection logs decisions
     */
    public function test_property_strategy_selection_logs_decisions(): void
    {
        // Arrange: Configure to use stored procedures
        Config::set('recalculation', [
            'stored_procedure_threshold' => 10,
            'operation_count_threshold' => 100000,
            'use_stored_procedures' => true,
        ]);

        // Generate item IDs above threshold
        $itemIds = range(1, 20);

        // Act: Create service
        RecalculationServiceFactory::createAverageCostService($itemIds, null);

        // Assert: Should log the decision
        Log::shouldHaveReceived('info')->once();
    }

    /**
     * Test that disabled features are respected regardless of thresholds
     */
    public function test_property_disabled_features_always_use_php_service(): void
    {
        // Test various item counts with features disabled
        $itemCounts = [10, 20, 30, 40, 50];

        foreach ($itemCounts as $count) {
            // Arrange: Disable stored procedures
            Config::set('recalculation', [
                'stored_procedure_threshold' => 10,
                'operation_count_threshold' => 100000,
                'use_stored_procedures' => false,
            ]);

            $itemIds = range(1, min($count, 100));

            // Act: Create service
            $service = RecalculationServiceFactory::createAverageCostService($itemIds, null);

            // Assert: Should always use PHP optimized service
            $this->assertInstanceOf(
                AverageCostRecalculationServiceOptimized::class,
                $service,
                "With {$count} items and features disabled, should use PHP optimized service"
            );
        }
    }

    /**
     * Test that threshold changes affect strategy selection
     */
    public function test_property_threshold_changes_affect_strategy(): void
    {
        $itemIds = range(1, 50);

        // Test with low threshold - should use stored procedures
        Config::set('recalculation', [
            'stored_procedure_threshold' => 10,
            'operation_count_threshold' => 100000,
            'use_stored_procedures' => true,
        ]);

        $service1 = RecalculationServiceFactory::createAverageCostService($itemIds, null);
        $this->assertInstanceOf(
            AverageCostRecalculationServiceStoredProcedure::class,
            $service1,
            'With threshold 10 and 50 items, should use stored procedures'
        );

        // Test with high threshold - should use PHP
        Config::set('recalculation', [
            'stored_procedure_threshold' => 100,
            'operation_count_threshold' => 100000,
            'use_stored_procedures' => true,
        ]);

        $service2 = RecalculationServiceFactory::createAverageCostService($itemIds, null);
        $this->assertInstanceOf(
            AverageCostRecalculationServiceOptimized::class,
            $service2,
            'With threshold 100 and 50 items, should use PHP optimized'
        );
    }

    /**
     * Test that configuration validation is performed
     */
    public function test_property_configuration_validation_is_performed(): void
    {
        // Arrange: Set invalid configuration
        Config::set('recalculation', [
            'stored_procedure_threshold' => -100,
            'operation_count_threshold' => 100000,
            'use_stored_procedures' => true,
        ]);

        $itemIds = range(1, 10);

        // Act: Create service (should trigger validation)
        RecalculationServiceFactory::createAverageCostService($itemIds, null);

        // Assert: Should log warning about invalid configuration
        Log::shouldHaveReceived('warning')->atLeast()->once();
    }

    /**
     * Test that strategy selection is consistent for same inputs
     */
    public function test_property_strategy_selection_is_consistent(): void
    {
        // Arrange: Set configuration
        Config::set('recalculation', [
            'stored_procedure_threshold' => 30,
            'operation_count_threshold' => 100000,
            'use_stored_procedures' => true,
        ]);

        $itemIds = range(1, 50);

        // Act: Create service multiple times
        $service1 = RecalculationServiceFactory::createAverageCostService($itemIds, null);
        $service2 = RecalculationServiceFactory::createAverageCostService($itemIds, null);
        $service3 = RecalculationServiceFactory::createAverageCostService($itemIds, null);

        // Assert: Should return same service type every time
        $this->assertInstanceOf(AverageCostRecalculationServiceStoredProcedure::class, $service1);
        $this->assertInstanceOf(AverageCostRecalculationServiceStoredProcedure::class, $service2);
        $this->assertInstanceOf(AverageCostRecalculationServiceStoredProcedure::class, $service3);
    }

    /**
     * Test boundary conditions for strategy selection
     */
    public function test_property_boundary_conditions(): void
    {
        Config::set('recalculation', [
            'stored_procedure_threshold' => 30,
            'operation_count_threshold' => 100000,
            'use_stored_procedures' => true,
        ]);

        // Test at boundary - 1
        $itemIds29 = range(1, 29);
        $service29 = RecalculationServiceFactory::createAverageCostService($itemIds29, null);
        $this->assertInstanceOf(
            AverageCostRecalculationServiceOptimized::class,
            $service29,
            'With 29 items (threshold - 1), should use PHP optimized'
        );

        // Test at boundary
        $itemIds30 = range(1, 30);
        $service30 = RecalculationServiceFactory::createAverageCostService($itemIds30, null);
        $this->assertInstanceOf(
            AverageCostRecalculationServiceOptimized::class,
            $service30,
            'With 30 items (at threshold), should use PHP optimized'
        );

        // Test at boundary + 1
        $itemIds31 = range(1, 31);
        $service31 = RecalculationServiceFactory::createAverageCostService($itemIds31, null);
        $this->assertInstanceOf(
            AverageCostRecalculationServiceStoredProcedure::class,
            $service31,
            'With 31 items (threshold + 1), should use stored procedures'
        );
    }
}
