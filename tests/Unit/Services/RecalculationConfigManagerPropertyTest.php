<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\Config\RecalculationConfigManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Property-Based Tests for RecalculationConfigManager
 *
 * Feature: average-cost-recalculation-improvements
 * Property 17: Configuration Fallback
 * Validates: Requirements 5.5
 *
 * For any invalid configuration value, the system should use safe default values
 * and log a warning about the invalid configuration.
 */
class RecalculationConfigManagerPropertyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Log::spy();
    }

    /**
     * Property 17: Configuration Fallback
     *
     * Test that for any invalid configuration value, the system uses safe defaults
     * and logs warnings.
     *
     * @dataProvider invalidConfigurationProvider
     */
    public function test_property_configuration_fallback_uses_safe_defaults(
        string $configKey,
        mixed $invalidValue,
        string $getterMethod,
        mixed $expectedDefault
    ): void {
        // Arrange: Set invalid configuration value
        Config::set("recalculation.{$configKey}", $invalidValue);

        // Act: Call the getter method
        $result = RecalculationConfigManager::$getterMethod();

        // Assert: Should return safe default value
        $this->assertEquals(
            $expectedDefault,
            $result,
            "Configuration key '{$configKey}' with invalid value should return default"
        );

        // Assert: Should log a warning (for non-boolean configs)
        if (!is_bool($expectedDefault)) {
            Log::shouldHaveReceived('warning')->atLeast()->once();
        }
    }

    /**
     * Data provider for invalid configuration values
     *
     * Generates various invalid inputs to test the fallback behavior
     *
     * @return array<string, array{string, mixed, string, mixed}>
     */
    public static function invalidConfigurationProvider(): array
    {
        return [
            // Integer configurations with negative values
            'batch_size_negative' => [
                'batch_size',
                -100,
                'getBatchSize',
                100,
            ],
            'batch_size_zero' => [
                'batch_size',
                0,
                'getBatchSize',
                100,
            ],
            'batch_size_string' => [
                'batch_size',
                'invalid',
                'getBatchSize',
                100,
            ],
            'chunk_size_negative' => [
                'chunk_size',
                -500,
                'getChunkSize',
                500,
            ],
            'chunk_size_float' => [
                'chunk_size',
                123.45,
                'getChunkSize',
                500,
            ],

            // Threshold configurations with negative values
            'stored_procedure_threshold_negative' => [
                'stored_procedure_threshold',
                -1000,
                'getStoredProcedureThreshold',
                1000,
            ],
            'queue_threshold_negative' => [
                'queue_threshold',
                -5000,
                'getQueueThreshold',
                5000,
            ],
            'queue_threshold_string' => [
                'queue_threshold',
                'not_a_number',
                'getQueueThreshold',
                5000,
            ],

            // Float configurations with negative values
            'performance_warning_threshold_negative' => [
                'performance_warning_threshold',
                -30.0,
                'getPerformanceWarningThreshold',
                30.0,
            ],
            'performance_warning_threshold_zero' => [
                'performance_warning_threshold',
                0,
                'getPerformanceWarningThreshold',
                30.0,
            ],
            'performance_warning_threshold_string' => [
                'performance_warning_threshold',
                'invalid',
                'getPerformanceWarningThreshold',
                30.0,
            ],

            // String configurations with empty or invalid values
            'queue_name_empty' => [
                'queue_name',
                '',
                'getQueueName',
                'recalculation',
            ],
            'queue_name_numeric' => [
                'queue_name',
                123,
                'getQueueName',
                'recalculation',
            ],
            'queue_name_large_empty' => [
                'queue_name_large',
                '',
                'getQueueNameLarge',
                'recalculation-large',
            ],

            // Array configurations with empty or invalid values
            'manufacturing_operation_types_empty' => [
                'manufacturing_operation_types',
                [],
                'getManufacturingOperationTypes',
                [59],
            ],
            'manufacturing_operation_types_string' => [
                'manufacturing_operation_types',
                'not_an_array',
                'getManufacturingOperationTypes',
                [59],
            ],

            // Enum-like configurations with invalid values
            'manufacturing_cost_allocation_invalid' => [
                'manufacturing_cost_allocation',
                'invalid_method',
                'getManufacturingCostAllocation',
                'proportional',
            ],
            'manufacturing_cost_allocation_numeric' => [
                'manufacturing_cost_allocation',
                123,
                'getManufacturingCostAllocation',
                'proportional',
            ],

            // Tolerance configurations with negative values
            'consistency_tolerance_negative' => [
                'consistency_tolerance',
                -0.01,
                'getConsistencyTolerance',
                0.01,
            ],
            'consistency_tolerance_string' => [
                'consistency_tolerance',
                'invalid',
                'getConsistencyTolerance',
                0.01,
            ],

            // Retry configurations with negative values
            'max_retries_negative' => [
                'max_retries',
                -3,
                'getMaxRetries',
                3,
            ],
            'retry_delay_ms_negative' => [
                'retry_delay_ms',
                -1000,
                'getRetryDelayMs',
                1000,
            ],

            // Queue configurations with invalid values
            'queue_timeout_negative' => [
                'queue_timeout',
                -600,
                'getQueueTimeout',
                600,
            ],
            'queue_timeout_zero' => [
                'queue_timeout',
                0,
                'getQueueTimeout',
                600,
            ],
            'queue_tries_negative' => [
                'queue_tries',
                -3,
                'getQueueTries',
                3,
            ],

            // Batch size configurations with invalid values
            'consistency_check_batch_size_negative' => [
                'consistency_check_batch_size',
                -500,
                'getConsistencyCheckBatchSize',
                500,
            ],
            'consistency_check_batch_size_zero' => [
                'consistency_check_batch_size',
                0,
                'getConsistencyCheckBatchSize',
                500,
            ],
        ];
    }

    /**
     * Test that multiple invalid configurations all use safe defaults
     *
     * This tests the property across multiple configuration keys simultaneously
     */
    public function test_property_multiple_invalid_configurations_use_safe_defaults(): void
    {
        // Arrange: Set multiple invalid configuration values
        Config::set('recalculation', [
            'batch_size' => -100,
            'chunk_size' => 0,
            'stored_procedure_threshold' => -1000,
            'queue_threshold' => 'invalid',
            'performance_warning_threshold' => -30.0,
            'queue_name' => '',
            'manufacturing_operation_types' => [],
            'manufacturing_cost_allocation' => 'invalid_method',
            'consistency_tolerance' => -0.01,
            'max_retries' => -3,
        ]);

        // Act & Assert: Each getter should return safe default
        $this->assertSame(100, RecalculationConfigManager::getBatchSize());
        $this->assertSame(500, RecalculationConfigManager::getChunkSize());
        $this->assertSame(1000, RecalculationConfigManager::getStoredProcedureThreshold());
        $this->assertSame(5000, RecalculationConfigManager::getQueueThreshold());
        $this->assertSame(30.0, RecalculationConfigManager::getPerformanceWarningThreshold());
        $this->assertSame('recalculation', RecalculationConfigManager::getQueueName());
        $this->assertSame([59], RecalculationConfigManager::getManufacturingOperationTypes());
        $this->assertSame('proportional', RecalculationConfigManager::getManufacturingCostAllocation());
        $this->assertSame(0.01, RecalculationConfigManager::getConsistencyTolerance());
        $this->assertSame(3, RecalculationConfigManager::getMaxRetries());

        // Assert: Warnings should be logged for all invalid values
        Log::shouldHaveReceived('warning')->atLeast()->times(10);
    }

    /**
     * Test that validation detects all invalid configurations
     */
    public function test_property_validation_detects_all_invalid_configurations(): void
    {
        // Arrange: Set multiple invalid configuration values
        Config::set('recalculation', [
            'batch_size' => -100,
            'chunk_size' => 0,
            'stored_procedure_threshold' => -1000,
            'queue_threshold' => -5000,
            'performance_warning_threshold' => -30.0,
            'queue_name' => '',
            'queue_name_large' => '',
            'manufacturing_cost_allocation' => 'invalid',
            'consistency_tolerance' => -0.01,
        ]);

        // Act: Validate configuration
        $result = RecalculationConfigManager::validateConfiguration();

        // Assert: Should detect all invalid configurations
        $this->assertFalse($result['valid']);
        $this->assertGreaterThanOrEqual(9, count($result['warnings']));

        // Assert: Should log warning about validation issues
        Log::shouldHaveReceived('warning')->once();
    }

    /**
     * Test that valid configurations don't trigger warnings
     *
     * This is the inverse property: valid configs should NOT use fallbacks
     */
    public function test_property_valid_configurations_do_not_trigger_warnings(): void
    {
        // Arrange: Set all valid configuration values
        Config::set('recalculation', [
            'batch_size' => 200,
            'chunk_size' => 1000,
            'stored_procedure_threshold' => 2000,
            'queue_threshold' => 10000,
            'performance_warning_threshold' => 60.0,
            'queue_name' => 'custom-queue',
            'queue_name_large' => 'custom-large-queue',
            'manufacturing_operation_types' => [59, 60],
            'manufacturing_cost_allocation' => 'equal',
            'consistency_tolerance' => 0.05,
            'max_retries' => 5,
            'retry_delay_ms' => 2000,
            'queue_timeout' => 1200,
            'queue_tries' => 5,
            'consistency_check_batch_size' => 1000,
        ]);

        // Act: Call all getters
        RecalculationConfigManager::getBatchSize();
        RecalculationConfigManager::getChunkSize();
        RecalculationConfigManager::getStoredProcedureThreshold();
        RecalculationConfigManager::getQueueThreshold();
        RecalculationConfigManager::getPerformanceWarningThreshold();
        RecalculationConfigManager::getQueueName();
        RecalculationConfigManager::getQueueNameLarge();
        RecalculationConfigManager::getManufacturingOperationTypes();
        RecalculationConfigManager::getManufacturingCostAllocation();
        RecalculationConfigManager::getConsistencyTolerance();
        RecalculationConfigManager::getMaxRetries();
        RecalculationConfigManager::getRetryDelayMs();
        RecalculationConfigManager::getQueueTimeout();
        RecalculationConfigManager::getQueueTries();
        RecalculationConfigManager::getConsistencyCheckBatchSize();

        // Assert: No warnings should be logged for valid configurations
        Log::shouldNotHaveReceived('warning');
    }
}
