<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\Monitoring\RecalculationPerformanceMonitor;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Tests\TestCase;

class RecalculationPerformanceMonitorTest extends TestCase
{
    private RecalculationPerformanceMonitor $monitor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->monitor = new RecalculationPerformanceMonitor();
    }

    /**
     * Test start() generates unique operation IDs
     */
    public function test_start_generates_unique_operation_ids(): void
    {
        $operationId1 = $this->monitor->start('test_operation', ['test' => 'data1']);
        $operationId2 = $this->monitor->start('test_operation', ['test' => 'data2']);
        $operationId3 = $this->monitor->start('another_operation', ['test' => 'data3']);

        $this->assertNotEmpty($operationId1);
        $this->assertNotEmpty($operationId2);
        $this->assertNotEmpty($operationId3);
        $this->assertNotEquals($operationId1, $operationId2);
        $this->assertNotEquals($operationId1, $operationId3);
        $this->assertNotEquals($operationId2, $operationId3);
    }

    /**
     * Test start() logs operation start
     */
    public function test_start_logs_operation_start(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Recalculation operation started', \Mockery::on(function ($data) {
                return isset($data['operation_id'])
                    && isset($data['operation_type'])
                    && $data['operation_type'] === 'batch_recalculation'
                    && isset($data['context'])
                    && $data['context']['item_count'] === 100
                    && isset($data['timestamp']);
            }));

        $this->monitor->start('batch_recalculation', ['item_count' => 100]);
    }

    /**
     * Test end() logs completion with duration
     */
    public function test_end_logs_completion_with_duration(): void
    {
        Log::shouldReceive('info')->once(); // For start()
        Log::shouldReceive('info')
            ->once()
            ->with('Recalculation operation completed', \Mockery::on(function ($data) {
                return isset($data['operation_id'])
                    && isset($data['operation_type'])
                    && isset($data['duration_seconds'])
                    && isset($data['memory_used_mb'])
                    && isset($data['context'])
                    && isset($data['results'])
                    && isset($data['timestamp']);
            }));

        $operationId = $this->monitor->start('test_operation', ['test' => 'context']);
        
        // Add small delay to ensure measurable duration
        usleep(10000); // 10ms
        
        $this->monitor->end($operationId, ['items_processed' => 50, 'success' => true]);
    }

    /**
     * Test end() throws exception for invalid operation ID
     */
    public function test_end_throws_exception_for_invalid_operation_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Operation ID not found');

        $this->monitor->end('non-existent-id', []);
    }

    /**
     * Test end() calculates duration correctly
     */
    public function test_end_calculates_duration_correctly(): void
    {
        Log::shouldReceive('info')->twice(); // For start() and end()

        $operationId = $this->monitor->start('test_operation', []);
        
        usleep(50000); // 50ms delay
        
        $this->monitor->end($operationId, []);

        $stats = $this->monitor->getStatistics();
        $this->assertCount(1, $stats['operations']);
        // Allow for slight timing variations (40ms to 200ms)
        $this->assertGreaterThanOrEqual(0.04, $stats['operations'][0]['duration']);
        $this->assertLessThan(0.2, $stats['operations'][0]['duration']); // Should be less than 200ms
    }

    /**
     * Test logSlowOperation() logs warnings for slow operations
     */
    public function test_log_slow_operation_logs_warnings(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->with('Slow recalculation operation detected', \Mockery::on(function ($data) {
                return isset($data['operation_id'])
                    && $data['operation_id'] === 'test-op-123'
                    && isset($data['duration_seconds'])
                    && $data['duration_seconds'] === 45.5
                    && isset($data['threshold_seconds'])
                    && isset($data['exceeded_by_seconds'])
                    && isset($data['context'])
                    && isset($data['timestamp']);
            }));

        $this->monitor->logSlowOperation('test-op-123', 45.5, ['test' => 'context']);
    }

    /**
     * Test end() automatically logs slow operations
     */
    public function test_end_automatically_logs_slow_operations(): void
    {
        // Create monitor with very low threshold for testing
        $monitor = new RecalculationPerformanceMonitor(0.01); // 10ms threshold

        Log::shouldReceive('info')->twice(); // For start() and end()
        Log::shouldReceive('warning')
            ->once()
            ->with('Slow recalculation operation detected', \Mockery::any());

        $operationId = $monitor->start('test_operation', []);
        
        usleep(20000); // 20ms delay (exceeds 10ms threshold)
        
        $monitor->end($operationId, []);
    }

    /**
     * Test getStatistics() returns performance data
     */
    public function test_get_statistics_returns_performance_data(): void
    {
        Log::shouldReceive('info')->times(4); // 2 operations × 2 logs each

        $operationId1 = $this->monitor->start('batch_recalculation', ['item_count' => 100]);
        usleep(10000);
        $this->monitor->end($operationId1, ['items_processed' => 100]);

        $operationId2 = $this->monitor->start('single_recalculation', ['item_count' => 1]);
        usleep(5000);
        $this->monitor->end($operationId2, ['items_processed' => 1]);

        $stats = $this->monitor->getStatistics();

        $this->assertArrayHasKey('operations', $stats);
        $this->assertArrayHasKey('summary', $stats);
        $this->assertCount(2, $stats['operations']);
        
        $this->assertArrayHasKey('total_operations', $stats['summary']);
        $this->assertArrayHasKey('avg_duration', $stats['summary']);
        $this->assertArrayHasKey('max_duration', $stats['summary']);
        $this->assertArrayHasKey('min_duration', $stats['summary']);
        $this->assertArrayHasKey('total_memory_mb', $stats['summary']);
        
        $this->assertEquals(2, $stats['summary']['total_operations']);
        $this->assertGreaterThan(0, $stats['summary']['avg_duration']);
        $this->assertGreaterThan(0, $stats['summary']['max_duration']);
        $this->assertGreaterThan(0, $stats['summary']['min_duration']);
    }

    /**
     * Test getStatistics() filters by operation type
     */
    public function test_get_statistics_filters_by_operation_type(): void
    {
        Log::shouldReceive('info')->times(6); // 3 operations × 2 logs each

        $operationId1 = $this->monitor->start('batch_recalculation', []);
        $this->monitor->end($operationId1, []);

        $operationId2 = $this->monitor->start('single_recalculation', []);
        $this->monitor->end($operationId2, []);

        $operationId3 = $this->monitor->start('batch_recalculation', []);
        $this->monitor->end($operationId3, []);

        $allStats = $this->monitor->getStatistics();
        $this->assertEquals(3, $allStats['summary']['total_operations']);

        $batchStats = $this->monitor->getStatistics('batch_recalculation');
        $this->assertEquals(2, $batchStats['summary']['total_operations']);

        $singleStats = $this->monitor->getStatistics('single_recalculation');
        $this->assertEquals(1, $singleStats['summary']['total_operations']);
    }

    /**
     * Test getStatistics() respects limit parameter
     */
    public function test_get_statistics_respects_limit_parameter(): void
    {
        Log::shouldReceive('info')->times(10); // 5 operations × 2 logs each

        for ($i = 0; $i < 5; $i++) {
            $operationId = $this->monitor->start('test_operation', []);
            $this->monitor->end($operationId, []);
        }

        $stats = $this->monitor->getStatistics(null, 3);
        $this->assertCount(3, $stats['operations']);
        $this->assertEquals(3, $stats['summary']['total_operations']);
    }

    /**
     * Test getStatistics() returns empty summary for no operations
     */
    public function test_get_statistics_returns_empty_summary_for_no_operations(): void
    {
        $stats = $this->monitor->getStatistics();

        $this->assertEmpty($stats['operations']);
        $this->assertEquals(0, $stats['summary']['total_operations']);
        $this->assertEquals(0, $stats['summary']['avg_duration']);
        $this->assertEquals(0, $stats['summary']['max_duration']);
        $this->assertEquals(0, $stats['summary']['min_duration']);
        $this->assertEquals(0, $stats['summary']['total_memory_mb']);
    }

    /**
     * Test getWarningThreshold() returns correct value
     */
    public function test_get_warning_threshold_returns_correct_value(): void
    {
        $monitor = new RecalculationPerformanceMonitor(45.0);
        $this->assertEquals(45.0, $monitor->getWarningThreshold());
    }

    /**
     * Test setWarningThreshold() updates threshold
     */
    public function test_set_warning_threshold_updates_threshold(): void
    {
        $this->monitor->setWarningThreshold(60.0);
        $this->assertEquals(60.0, $this->monitor->getWarningThreshold());
    }

    /**
     * Test constructor uses default threshold from config
     */
    public function test_constructor_uses_default_threshold_from_config(): void
    {
        config(['recalculation.performance_warning_threshold' => 25.0]);
        
        $monitor = new RecalculationPerformanceMonitor();
        $this->assertEquals(25.0, $monitor->getWarningThreshold());
    }

    /**
     * Test constructor uses fallback threshold when config is missing
     */
    public function test_constructor_uses_fallback_threshold_when_config_missing(): void
    {
        // Don't set config at all, so it returns null
        // The constructor should use the default 30.0
        $monitor = new RecalculationPerformanceMonitor();
        $this->assertEquals(30.0, $monitor->getWarningThreshold());
    }

    /**
     * Test statistics include all required fields
     */
    public function test_statistics_include_all_required_fields(): void
    {
        Log::shouldReceive('info')->twice();

        $operationId = $this->monitor->start('test_operation', ['item_count' => 50]);
        $this->monitor->end($operationId, ['items_processed' => 50, 'success' => true]);

        $stats = $this->monitor->getStatistics();
        $operation = $stats['operations'][0];

        $this->assertArrayHasKey('operation_id', $operation);
        $this->assertArrayHasKey('operation_type', $operation);
        $this->assertArrayHasKey('duration', $operation);
        $this->assertArrayHasKey('memory_used', $operation);
        $this->assertArrayHasKey('context', $operation);
        $this->assertArrayHasKey('results', $operation);
        $this->assertArrayHasKey('timestamp', $operation);

        $this->assertEquals('test_operation', $operation['operation_type']);
        $this->assertEquals(['item_count' => 50], $operation['context']);
        $this->assertEquals(['items_processed' => 50, 'success' => true], $operation['results']);
    }
}
