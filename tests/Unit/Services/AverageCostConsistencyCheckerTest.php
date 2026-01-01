<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Item;
use App\Models\ProType;
use App\Models\OperHead;
use InvalidArgumentException;
use App\Models\OperationItems;
use Illuminate\Support\Facades\DB;
use Modules\Branches\Models\Branch;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Invoices\Services\Consistency\AverageCostConsistencyChecker;

class AverageCostConsistencyCheckerTest extends TestCase
{
    use RefreshDatabase;

    private AverageCostConsistencyChecker $checker;
    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checker = new AverageCostConsistencyChecker();

        // Create a branch first (required for ProType foreign key)
        $this->branch = Branch::create([
            'name' => 'Test Branch',
            'code' => 'TB001',
        ]);
    }

    /**
     * Test checkItems detects inconsistencies
     */
    public function test_check_items_detects_inconsistencies(): void
    {
        // Create item with incorrect average cost
        $item = Item::factory()->create(['average_cost' => 100.00]);

        // Create operations that should result in different average cost
        $this->createOperationsForItem($item->id, [
            ['qty_in' => 10, 'qty_out' => 0, 'detail_value' => 500], // 50 per unit
            ['qty_in' => 10, 'qty_out' => 0, 'detail_value' => 700], // 70 per unit
        ]);
        // Expected average: (500 + 700) / (10 + 10) = 1200 / 20 = 60

        $inconsistencies = $this->checker->checkItems([$item->id]);

        $this->assertCount(1, $inconsistencies);
        $this->assertEquals($item->id, $inconsistencies[0]['item_id']);
        $this->assertEquals(100.00, $inconsistencies[0]['stored_average']);
        $this->assertEquals(60.00, $inconsistencies[0]['calculated_average']);
        $this->assertEquals(40.00, $inconsistencies[0]['difference']);
    }

    /**
     * Test checkItems returns empty for consistent items
     */
    public function test_check_items_returns_empty_for_consistent_items(): void
    {
        // Create item with correct average cost
        $item = Item::factory()->create(['average_cost' => 60.00]);

        // Create operations that match the average cost
        $this->createOperationsForItem($item->id, [
            ['qty_in' => 10, 'qty_out' => 0, 'detail_value' => 500],
            ['qty_in' => 10, 'qty_out' => 0, 'detail_value' => 700],
        ]);
        // Average: (500 + 700) / (10 + 10) = 60

        $inconsistencies = $this->checker->checkItems([$item->id]);

        $this->assertEmpty($inconsistencies);
    }

    /**
     * Test tolerance threshold is respected
     */
    public function test_tolerance_threshold_is_respected(): void
    {
        // Set tolerance to 0.10
        Config::set('recalculation.consistency_tolerance', 0.10);

        // Create item with average cost within tolerance
        $item = Item::factory()->create(['average_cost' => 60.05]);

        // Create operations that result in 60.00
        $this->createOperationsForItem($item->id, [
            ['qty_in' => 10, 'qty_out' => 0, 'detail_value' => 600],
        ]);
        // Average: 600 / 10 = 60.00
        // Difference: 0.05 (within tolerance of 0.10)

        $inconsistencies = $this->checker->checkItems([$item->id]);

        $this->assertEmpty($inconsistencies);
    }

    /**
     * Test tolerance threshold detects inconsistencies beyond threshold
     */
    public function test_tolerance_threshold_detects_beyond_threshold(): void
    {
        // Set tolerance to 0.10
        Config::set('recalculation.consistency_tolerance', 0.10);

        // Create item with average cost beyond tolerance
        $item = Item::factory()->create(['average_cost' => 60.20]);

        // Create operations that result in 60.00
        $this->createOperationsForItem($item->id, [
            ['qty_in' => 10, 'qty_out' => 0, 'detail_value' => 600],
        ]);
        // Average: 600 / 10 = 60.00
        // Difference: 0.20 (beyond tolerance of 0.10)

        $inconsistencies = $this->checker->checkItems([$item->id]);

        $this->assertCount(1, $inconsistencies);
    }

    /**
     * Test checkItems with empty array
     */
    public function test_check_items_with_empty_array(): void
    {
        $inconsistencies = $this->checker->checkItems([]);

        $this->assertEmpty($inconsistencies);
    }

    /**
     * Test checkItems with invalid item IDs
     */
    public function test_check_items_rejects_invalid_item_ids(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid item ID');

        $this->checker->checkItems([1, -5, 3]);
    }

    /**
     * Test checkItems with non-existing items
     */
    public function test_check_items_handles_non_existing_items(): void
    {
        $inconsistencies = $this->checker->checkItems([99999]);

        $this->assertEmpty($inconsistencies);
    }

    /**
     * Test fixInconsistencies corrects values
     */
    public function test_fix_inconsistencies_corrects_values(): void
    {
        // Create item with incorrect average cost
        $item = Item::factory()->create(['average_cost' => 100.00]);

        // Create operations
        $this->createOperationsForItem($item->id, [
            ['qty_in' => 10, 'qty_out' => 0, 'detail_value' => 600],
        ]);
        // Expected average: 600 / 10 = 60

        $result = $this->checker->fixInconsistencies([$item->id], false);

        $this->assertEquals(1, $result['items_fixed']);
        $this->assertFalse($result['dry_run']);
        $this->assertCount(1, $result['fixed_items']);
        $this->assertEquals(100.00, $result['fixed_items'][0]['old_average']);
        $this->assertEquals(60.00, $result['fixed_items'][0]['new_average']);

        // Verify database was updated
        $item->refresh();
        $this->assertEquals(60.00, $item->average_cost);
    }

    /**
     * Test fixInconsistencies dry-run mode doesn't modify data
     */
    public function test_fix_inconsistencies_dry_run_does_not_modify_data(): void
    {
        // Create item with incorrect average cost
        $item = Item::factory()->create(['average_cost' => 100.00]);

        // Create operations
        $this->createOperationsForItem($item->id, [
            ['qty_in' => 10, 'qty_out' => 0, 'detail_value' => 600],
        ]);

        $result = $this->checker->fixInconsistencies([$item->id], true);

        $this->assertEquals(1, $result['items_fixed']);
        $this->assertTrue($result['dry_run']);

        // Verify database was NOT updated
        $item->refresh();
        $this->assertEquals(100.00, $item->average_cost);
    }

    /**
     * Test fixInconsistencies with empty array
     */
    public function test_fix_inconsistencies_with_empty_array(): void
    {
        $result = $this->checker->fixInconsistencies([], false);

        $this->assertEquals(0, $result['items_fixed']);
        $this->assertEmpty($result['fixed_items']);
    }

    /**
     * Test fixInconsistencies with invalid item IDs
     */
    public function test_fix_inconsistencies_rejects_invalid_item_ids(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid item ID');

        $this->checker->fixInconsistencies([1, 0, 3], false);
    }

    /**
     * Test checkAllItems processes all items
     */
    public function test_check_all_items_processes_all_items(): void
    {
        // Create multiple items
        $item1 = Item::factory()->create(['average_cost' => 100.00]);
        $item2 = Item::factory()->create(['average_cost' => 50.00]);
        $item3 = Item::factory()->create(['average_cost' => 75.00]);

        // Create operations for item1 (inconsistent)
        $this->createOperationsForItem($item1->id, [
            ['qty_in' => 10, 'qty_out' => 0, 'detail_value' => 600],
        ]);

        // Create operations for item2 (consistent)
        $this->createOperationsForItem($item2->id, [
            ['qty_in' => 10, 'qty_out' => 0, 'detail_value' => 500],
        ]);

        // No operations for item3 (inconsistent - should be 0)

        $summary = $this->checker->checkAllItems(100);

        $this->assertEquals(3, $summary['total_items']);
        $this->assertEquals(3, $summary['items_checked']);
        $this->assertEquals(2, $summary['inconsistencies_found']);
    }

    /**
     * Test generateReport includes statistics
     */
    public function test_generate_report_includes_statistics(): void
    {
        // Create items
        $item1 = Item::factory()->create(['average_cost' => 100.00]);
        $item2 = Item::factory()->create(['average_cost' => 50.00]);

        // Create operations for item1 (inconsistent)
        $this->createOperationsForItem($item1->id, [
            ['qty_in' => 10, 'qty_out' => 0, 'detail_value' => 600],
        ]);

        // Create operations for item2 (consistent)
        $this->createOperationsForItem($item2->id, [
            ['qty_in' => 10, 'qty_out' => 0, 'detail_value' => 500],
        ]);

        $report = $this->checker->generateReport();

        $this->assertArrayHasKey('timestamp', $report);
        $this->assertArrayHasKey('total_items', $report);
        $this->assertArrayHasKey('items_checked', $report);
        $this->assertArrayHasKey('inconsistencies_found', $report);
        $this->assertArrayHasKey('consistency_rate', $report);
        $this->assertArrayHasKey('tolerance_threshold', $report);
        $this->assertArrayHasKey('inconsistencies', $report);

        $this->assertEquals(2, $report['total_items']);
        $this->assertEquals(1, $report['inconsistencies_found']);
        $this->assertEquals(50.00, $report['consistency_rate']); // 1 out of 2 consistent
    }

    /**
     * Test checkItems handles items with no operations
     */
    public function test_check_items_handles_items_with_no_operations(): void
    {
        // Create item with non-zero average cost but no operations
        $item = Item::factory()->create(['average_cost' => 50.00]);

        $inconsistencies = $this->checker->checkItems([$item->id]);

        $this->assertCount(1, $inconsistencies);
        $this->assertEquals(0.00, $inconsistencies[0]['calculated_average']);
        $this->assertEquals(50.00, $inconsistencies[0]['difference']);
    }

    /**
     * Test checkItems excludes deleted operations
     */
    public function test_check_items_excludes_deleted_operations(): void
    {
        $item = Item::factory()->create(['average_cost' => 60.00]);

        // Create non-deleted operations
        $this->createOperationsForItem($item->id, [
            ['qty_in' => 10, 'qty_out' => 0, 'detail_value' => 600],
        ]);

        // Create deleted operation (should be excluded)
        $this->createOperationsForItem($item->id, [
            ['qty_in' => 10, 'qty_out' => 0, 'detail_value' => 400],
        ], true);

        // Expected: 600 / 10 = 60 (deleted operation excluded)

        $inconsistencies = $this->checker->checkItems([$item->id]);

        $this->assertEmpty($inconsistencies);
    }

    /**
     * Helper method to create operations for an item
     */
    private function createOperationsForItem(int $itemId, array $operations, bool $isDeleted = false): void
    {
        // Ensure ProType exists
        $proType = ProType::where('id', 11)->first();
        if (!$proType) {
            ProType::create([
                'id' => 11,
                'pname' => 'Purchase Invoice',
                'branch_id' => $this->branch->id,
            ]);
        }

        foreach ($operations as $operation) {
            $operHead = OperHead::factory()->create([
                'pro_type' => 11,
                'isdeleted' => $isDeleted ? 1 : 0,
                'pro_date' => now()->format('Y-m-d'),
            ]);

            OperationItems::create([
                'pro_id' => $operHead->id,
                'item_id' => $itemId,
                'qty_in' => $operation['qty_in'],
                'qty_out' => $operation['qty_out'],
                'detail_value' => $operation['detail_value'],
                'is_stock' => 1,
            ]);
        }
    }
}
