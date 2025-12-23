<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\Config\RecalculationConfigManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class RecalculationConfigManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Log::spy();
    }

    public function test_get_batch_size_returns_configured_value(): void
    {
        Config::set('recalculation.batch_size', 200);

        $result = RecalculationConfigManager::getBatchSize();

        $this->assertSame(200, $result);
    }

    public function test_get_batch_size_returns_default_when_not_configured(): void
    {
        Config::set('recalculation.batch_size', null);

        $result = RecalculationConfigManager::getBatchSize();

        $this->assertSame(100, $result);
    }

    public function test_get_batch_size_returns_default_for_invalid_value(): void
    {
        Config::set('recalculation.batch_size', -50);

        $result = RecalculationConfigManager::getBatchSize();

        $this->assertSame(100, $result);
        Log::shouldHaveReceived('warning')->once();
    }

    public function test_get_chunk_size_returns_configured_value(): void
    {
        Config::set('recalculation.chunk_size', 1000);

        $result = RecalculationConfigManager::getChunkSize();

        $this->assertSame(1000, $result);
    }

    public function test_get_chunk_size_returns_default_when_not_configured(): void
    {
        Config::set('recalculation.chunk_size', null);

        $result = RecalculationConfigManager::getChunkSize();

        $this->assertSame(500, $result);
    }

    public function test_get_stored_procedure_threshold_returns_configured_value(): void
    {
        Config::set('recalculation.stored_procedure_threshold', 2000);

        $result = RecalculationConfigManager::getStoredProcedureThreshold();

        $this->assertSame(2000, $result);
    }

    public function test_get_stored_procedure_threshold_returns_default_for_negative_value(): void
    {
        Config::set('recalculation.stored_procedure_threshold', -100);

        $result = RecalculationConfigManager::getStoredProcedureThreshold();

        $this->assertSame(1000, $result);
        Log::shouldHaveReceived('warning')->once();
    }

    public function test_get_queue_threshold_returns_configured_value(): void
    {
        Config::set('recalculation.queue_threshold', 10000);

        $result = RecalculationConfigManager::getQueueThreshold();

        $this->assertSame(10000, $result);
    }

    public function test_get_queue_threshold_returns_default_when_not_configured(): void
    {
        Config::set('recalculation.queue_threshold', null);

        $result = RecalculationConfigManager::getQueueThreshold();

        $this->assertSame(5000, $result);
    }

    public function test_is_stored_procedures_enabled_returns_configured_value(): void
    {
        Config::set('recalculation.use_stored_procedures', true);

        $result = RecalculationConfigManager::isStoredProceduresEnabled();

        $this->assertTrue($result);
    }

    public function test_is_stored_procedures_enabled_returns_false_by_default(): void
    {
        Config::set('recalculation.use_stored_procedures', null);

        $result = RecalculationConfigManager::isStoredProceduresEnabled();

        $this->assertFalse($result);
    }

    public function test_is_queue_enabled_returns_configured_value(): void
    {
        Config::set('recalculation.use_queue', false);

        $result = RecalculationConfigManager::isQueueEnabled();

        $this->assertFalse($result);
    }

    public function test_is_queue_enabled_returns_true_by_default(): void
    {
        // Clear any existing config and rely on the method's default
        Config::set('recalculation', []);

        $result = RecalculationConfigManager::isQueueEnabled();

        // The method should return true as the default when config is missing
        $this->assertTrue($result);
    }

    public function test_get_performance_warning_threshold_returns_configured_value(): void
    {
        Config::set('recalculation.performance_warning_threshold', 60.5);

        $result = RecalculationConfigManager::getPerformanceWarningThreshold();

        $this->assertSame(60.5, $result);
    }

    public function test_get_performance_warning_threshold_returns_default_for_invalid_value(): void
    {
        Config::set('recalculation.performance_warning_threshold', -10);

        $result = RecalculationConfigManager::getPerformanceWarningThreshold();

        $this->assertSame(30.0, $result);
        Log::shouldHaveReceived('warning')->once();
    }

    public function test_get_queue_name_returns_configured_value(): void
    {
        Config::set('recalculation.queue_name', 'custom-queue');

        $result = RecalculationConfigManager::getQueueName();

        $this->assertSame('custom-queue', $result);
    }

    public function test_get_queue_name_returns_default_for_empty_string(): void
    {
        Config::set('recalculation.queue_name', '');

        $result = RecalculationConfigManager::getQueueName();

        $this->assertSame('recalculation', $result);
        Log::shouldHaveReceived('warning')->once();
    }

    public function test_get_queue_name_large_returns_configured_value(): void
    {
        Config::set('recalculation.queue_name_large', 'custom-large-queue');

        $result = RecalculationConfigManager::getQueueNameLarge();

        $this->assertSame('custom-large-queue', $result);
    }

    public function test_get_manufacturing_operation_types_returns_configured_value(): void
    {
        Config::set('recalculation.manufacturing_operation_types', [59, 60]);

        $result = RecalculationConfigManager::getManufacturingOperationTypes();

        $this->assertSame([59, 60], $result);
    }

    public function test_get_manufacturing_operation_types_returns_default_for_empty_array(): void
    {
        Config::set('recalculation.manufacturing_operation_types', []);

        $result = RecalculationConfigManager::getManufacturingOperationTypes();

        $this->assertSame([59], $result);
        Log::shouldHaveReceived('warning')->once();
    }

    public function test_get_manufacturing_cost_allocation_returns_configured_value(): void
    {
        Config::set('recalculation.manufacturing_cost_allocation', 'equal');

        $result = RecalculationConfigManager::getManufacturingCostAllocation();

        $this->assertSame('equal', $result);
    }

    public function test_get_manufacturing_cost_allocation_returns_default_for_invalid_value(): void
    {
        Config::set('recalculation.manufacturing_cost_allocation', 'invalid');

        $result = RecalculationConfigManager::getManufacturingCostAllocation();

        $this->assertSame('proportional', $result);
        Log::shouldHaveReceived('warning')->once();
    }

    public function test_get_consistency_tolerance_returns_configured_value(): void
    {
        Config::set('recalculation.consistency_tolerance', 0.05);

        $result = RecalculationConfigManager::getConsistencyTolerance();

        $this->assertSame(0.05, $result);
    }

    public function test_get_consistency_tolerance_returns_default_for_negative_value(): void
    {
        Config::set('recalculation.consistency_tolerance', -0.01);

        $result = RecalculationConfigManager::getConsistencyTolerance();

        $this->assertSame(0.01, $result);
        Log::shouldHaveReceived('warning')->once();
    }

    public function test_get_max_retries_returns_configured_value(): void
    {
        Config::set('recalculation.max_retries', 5);

        $result = RecalculationConfigManager::getMaxRetries();

        $this->assertSame(5, $result);
    }

    public function test_get_max_retries_returns_default_for_negative_value(): void
    {
        Config::set('recalculation.max_retries', -1);

        $result = RecalculationConfigManager::getMaxRetries();

        $this->assertSame(3, $result);
        Log::shouldHaveReceived('warning')->once();
    }

    public function test_validate_configuration_returns_valid_for_correct_config(): void
    {
        Config::set('recalculation', [
            'batch_size' => 100,
            'chunk_size' => 500,
            'stored_procedure_threshold' => 1000,
            'queue_threshold' => 5000,
            'performance_warning_threshold' => 30.0,
            'queue_name' => 'recalculation',
            'queue_name_large' => 'recalculation-large',
            'manufacturing_cost_allocation' => 'proportional',
            'consistency_tolerance' => 0.01,
        ]);

        $result = RecalculationConfigManager::validateConfiguration();

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['warnings']);
    }

    public function test_validate_configuration_detects_invalid_batch_size(): void
    {
        Config::set('recalculation.batch_size', -50);

        $result = RecalculationConfigManager::validateConfiguration();

        $this->assertFalse($result['valid']);
        $this->assertContains('batch_size must be a positive integer', $result['warnings']);
        Log::shouldHaveReceived('warning')->once();
    }

    public function test_validate_configuration_detects_invalid_chunk_size(): void
    {
        Config::set('recalculation.chunk_size', 0);

        $result = RecalculationConfigManager::validateConfiguration();

        $this->assertFalse($result['valid']);
        $this->assertContains('chunk_size must be a positive integer', $result['warnings']);
    }

    public function test_validate_configuration_detects_invalid_threshold(): void
    {
        Config::set('recalculation.stored_procedure_threshold', -100);

        $result = RecalculationConfigManager::validateConfiguration();

        $this->assertFalse($result['valid']);
        $this->assertContains('stored_procedure_threshold must be a non-negative integer', $result['warnings']);
    }

    public function test_validate_configuration_detects_invalid_queue_name(): void
    {
        Config::set('recalculation.queue_name', '');

        $result = RecalculationConfigManager::validateConfiguration();

        $this->assertFalse($result['valid']);
        $this->assertContains('queue_name must be a non-empty string', $result['warnings']);
    }

    public function test_validate_configuration_detects_invalid_cost_allocation(): void
    {
        Config::set('recalculation.manufacturing_cost_allocation', 'invalid');

        $result = RecalculationConfigManager::validateConfiguration();

        $this->assertFalse($result['valid']);
        $this->assertContains('manufacturing_cost_allocation must be either "proportional" or "equal"', $result['warnings']);
    }

    public function test_validate_configuration_detects_multiple_issues(): void
    {
        Config::set('recalculation', [
            'batch_size' => -50,
            'chunk_size' => 0,
            'queue_name' => '',
        ]);

        $result = RecalculationConfigManager::validateConfiguration();

        $this->assertFalse($result['valid']);
        $this->assertCount(3, $result['warnings']);
    }

    public function test_is_manufacturing_chain_enabled_returns_configured_value(): void
    {
        Config::set('recalculation.manufacturing_chain_enabled', false);

        $result = RecalculationConfigManager::isManufacturingChainEnabled();

        $this->assertFalse($result);
    }

    public function test_is_consistency_check_enabled_returns_configured_value(): void
    {
        Config::set('recalculation.consistency_check_enabled', false);

        $result = RecalculationConfigManager::isConsistencyCheckEnabled();

        $this->assertFalse($result);
    }

    public function test_get_queue_timeout_returns_configured_value(): void
    {
        Config::set('recalculation.queue_timeout', 1200);

        $result = RecalculationConfigManager::getQueueTimeout();

        $this->assertSame(1200, $result);
    }

    public function test_get_queue_tries_returns_configured_value(): void
    {
        Config::set('recalculation.queue_tries', 5);

        $result = RecalculationConfigManager::getQueueTries();

        $this->assertSame(5, $result);
    }

    public function test_get_retry_delay_ms_returns_configured_value(): void
    {
        Config::set('recalculation.retry_delay_ms', 2000);

        $result = RecalculationConfigManager::getRetryDelayMs();

        $this->assertSame(2000, $result);
    }

    public function test_is_retry_exponential_backoff_enabled_returns_configured_value(): void
    {
        Config::set('recalculation.retry_exponential_backoff', false);

        $result = RecalculationConfigManager::isRetryExponentialBackoffEnabled();

        $this->assertFalse($result);
    }
}
