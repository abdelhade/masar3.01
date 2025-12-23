<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Item;
use App\Models\OperHead;
use App\Models\OperationItems;
use App\Models\ProType;
use App\Models\User;
use App\Services\AverageCostRecalculationServiceOptimized;
use App\Services\Manufacturing\ManufacturingChainHandler;
use App\Services\RecalculationServiceHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Branches\Models\Branch;
use Tests\TestCase;

/**
 * Integration test for manufacturing chain recalculation
 * 
 * Tests the complete workflow:
 * 1. Create purchase invoice with raw materials
 * 2. Create manufacturing invoice using those materials
 * 3. Modify purchase invoice
 * 4. Verify manufacturing products are updated
 * 5. Verify subsequent operations reflect changes
 * 
 * Requirements: 16.1, 16.2, 16.3, 18.1, 18.2, 18.3
 */
class ManufacturingChainIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Branch $branch;
    private ProType $purchaseType;
    private ProType $manufacturingType;
    private ProType $salesType;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user and branch
        $this->user = User::factory()->create();
        $this->branch = Branch::create([
            'name' => 'Test Branch',
            'code' => 'TB001',
            'address' => 'Test Address',
            'is_active' => true,
        ]);
        
        // Associate user with branch
        $this->user->branches()->attach($this->branch->id);
        
        // Authenticate user
        Auth::login($this->user);

        // Create operation types
        $this->purchaseType = ProType::create([
            'id' => 11,
            'pname' => 'Purchase Invoice',
            'ptext' => 'Purchase',
            'ptype' => 'purchase',
            'branch_id' => $this->branch->id,
        ]);

        $this->manufacturingType = ProType::create([
            'id' => 59,
            'pname' => 'Manufacturing Invoice',
            'ptext' => 'Manufacturing',
            'ptype' => 'manufacturing',
            'branch_id' => $this->branch->id,
        ]);

        $this->salesType = ProType::create([
            'id' => 10,
            'pname' => 'Sales Invoice',
            'ptext' => 'Sales',
            'ptype' => 'sales',
            'branch_id' => $this->branch->id,
        ]);
    }

    /**
     * Test complete manufacturing chain workflow
     * 
     * @test
     */
    public function test_manufacturing_chain_recalculation_workflow(): void
    {
        // Step 1: Create raw materials
        $rawMaterial1 = Item::factory()->create([
            'name' => 'Raw Material 1',
            'average_cost' => 0,
        ]);

        $rawMaterial2 = Item::factory()->create([
            'name' => 'Raw Material 2',
            'average_cost' => 0,
        ]);

        // Step 2: Create finished product
        $finishedProduct = Item::factory()->create([
            'name' => 'Finished Product',
            'average_cost' => 0,
        ]);

        // Step 3: Create purchase invoice for raw materials
        $purchaseInvoice = OperHead::factory()->create([
            'pro_type' => $this->purchaseType->id,
            'pro_date' => '2024-01-01',
            'isdeleted' => 0,
            'branch_id' => $this->branch->id,
            'user' => $this->user->id,
            'created_at' => '2024-01-01 10:00:00',
        ]);

        // Add raw material 1 to purchase invoice (100 units @ 10 each = 1000)
        OperationItems::create([
            'pro_id' => $purchaseInvoice->id,
            'item_id' => $rawMaterial1->id,
            'qty_in' => 100,
            'qty_out' => 0,
            'detail_value' => 1000,
            'is_stock' => 1,
        ]);

        // Add raw material 2 to purchase invoice (50 units @ 20 each = 1000)
        OperationItems::create([
            'pro_id' => $purchaseInvoice->id,
            'item_id' => $rawMaterial2->id,
            'qty_in' => 50,
            'qty_out' => 0,
            'detail_value' => 1000,
            'is_stock' => 1,
        ]);

        // Recalculate average cost for raw materials
        $service = app(AverageCostRecalculationServiceOptimized::class);
        $service->recalculateAverageCostForItem($rawMaterial1->id, null, false);
        $service->recalculateAverageCostForItem($rawMaterial2->id, null, false);

        // Debug: Check if operations are visible
        $ops1 = OperationItems::where('item_id', $rawMaterial1->id)->get();
        $this->assertCount(1, $ops1, 'Should have 1 operation for raw material 1');

        // Verify raw material costs
        $rawMaterial1->refresh();
        $rawMaterial2->refresh();
        $this->assertEquals(10.0, $rawMaterial1->average_cost, 'Raw material 1 should have average cost of 10');
        $this->assertEquals(20.0, $rawMaterial2->average_cost, 'Raw material 2 should have average cost of 20');

        // Step 4: Create manufacturing invoice
        $manufacturingInvoice = OperHead::factory()->create([
            'pro_type' => $this->manufacturingType->id,
            'pro_date' => '2024-01-05',
            'isdeleted' => 0,
            'branch_id' => $this->branch->id,
            'user' => $this->user->id,
            'created_at' => '2024-01-05 14:00:00',
        ]);

        // Add raw materials as inputs (qty_out)
        OperationItems::create([
            'pro_id' => $manufacturingInvoice->id,
            'item_id' => $rawMaterial1->id,
            'qty_in' => 0,
            'qty_out' => 50, // Use 50 units
            'detail_value' => 500, // 50 * 10 = 500
            'is_stock' => 1,
        ]);

        OperationItems::create([
            'pro_id' => $manufacturingInvoice->id,
            'item_id' => $rawMaterial2->id,
            'qty_in' => 0,
            'qty_out' => 25, // Use 25 units
            'detail_value' => 500, // 25 * 20 = 500
            'is_stock' => 1,
        ]);

        // Add finished product as output (qty_in)
        // Total cost = 500 + 500 = 1000, producing 20 units = 50 per unit
        OperationItems::create([
            'pro_id' => $manufacturingInvoice->id,
            'item_id' => $finishedProduct->id,
            'qty_in' => 20,
            'qty_out' => 0,
            'detail_value' => 1000, // Total raw material cost
            'is_stock' => 1,
        ]);

        // Recalculate average cost for finished product
        $service = app(AverageCostRecalculationServiceOptimized::class);
        $service->recalculateAverageCostForItem($finishedProduct->id, null, false);

        // Verify finished product cost
        $finishedProduct->refresh();
        $this->assertEquals(50.0, $finishedProduct->average_cost, 'Finished product should have average cost of 50');

        // Step 5: Create a sales invoice using the finished product
        $salesInvoice = OperHead::factory()->create([
            'pro_type' => $this->salesType->id,
            'pro_date' => '2024-01-10',
            'isdeleted' => 0,
            'branch_id' => $this->branch->id,
            'user' => $this->user->id,
            'created_at' => '2024-01-10 09:00:00',
        ]);

        OperationItems::create([
            'pro_id' => $salesInvoice->id,
            'item_id' => $finishedProduct->id,
            'qty_in' => 0,
            'qty_out' => 10, // Sell 10 units
            'detail_value' => 500, // 10 * 50 = 500
            'is_stock' => 1,
        ]);

        // Step 6: Modify purchase invoice (change raw material 1 cost)
        // Update raw material 1 cost from 1000 to 1500 (new average cost = 15)
        DB::table('operation_items')
            ->where('pro_id', $purchaseInvoice->id)
            ->where('item_id', $rawMaterial1->id)
            ->update(['detail_value' => 1500]);

        // Trigger manufacturing chain recalculation
        RecalculationServiceHelper::recalculateManufacturingChain(
            [$rawMaterial1->id],
            '2024-01-01'
        );

        // Step 7: Verify raw material 1 cost is updated
        $rawMaterial1->refresh();
        $this->assertEquals(15.0, $rawMaterial1->average_cost, 'Raw material 1 should have new average cost of 15');

        // Step 8: Verify manufacturing invoice product cost is updated
        // New total cost = (50 * 15) + (25 * 20) = 750 + 500 = 1250
        // New cost per unit = 1250 / 20 = 62.5
        $manufacturingProductItem = OperationItems::where('pro_id', $manufacturingInvoice->id)
            ->where('item_id', $finishedProduct->id)
            ->first();

        $this->assertEquals(1250.0, $manufacturingProductItem->detail_value, 'Manufacturing product detail_value should be updated to 1250');

        // Step 9: Verify finished product average cost is recalculated
        $finishedProduct->refresh();
        $this->assertEquals(62.5, $finishedProduct->average_cost, 'Finished product should have new average cost of 62.5');

        // Step 10: Verify sales invoice reflects the new cost
        // The sales invoice detail_value should remain 500 (historical cost at time of sale)
        // but if we recalculate from the sales date, it should use the new average cost
        $salesProductItem = OperationItems::where('pro_id', $salesInvoice->id)
            ->where('item_id', $finishedProduct->id)
            ->first();

        // Sales invoice should still have original cost (historical)
        $this->assertEquals(500.0, $salesProductItem->detail_value, 'Sales invoice should maintain historical cost');
    }

    /**
     * Test manufacturing chain with multiple levels
     * 
     * @test
     */
    public function test_multi_level_manufacturing_chain(): void
    {
        // Create items
        $rawMaterial = Item::factory()->create(['name' => 'Raw Material', 'average_cost' => 0]);
        $intermediateProduct = Item::factory()->create(['name' => 'Intermediate Product', 'average_cost' => 0]);
        $finalProduct = Item::factory()->create(['name' => 'Final Product', 'average_cost' => 0]);

        // Purchase raw material
        $purchaseInvoice = OperHead::factory()->create([
            'pro_type' => $this->purchaseType->id,
            'pro_date' => '2024-01-01',
            'isdeleted' => 0,
            'branch_id' => $this->branch->id,
            'user' => $this->user->id,
            'created_at' => '2024-01-01 10:00:00',
        ]);

        OperationItems::create([
            'pro_id' => $purchaseInvoice->id,
            'item_id' => $rawMaterial->id,
            'qty_in' => 100,
            'qty_out' => 0,
            'detail_value' => 1000, // Cost: 10 per unit
            'is_stock' => 1,
        ]);

        $service = app(AverageCostRecalculationServiceOptimized::class);
        $service->recalculateAverageCostForItem($rawMaterial->id, null, false);

        // First manufacturing: raw material -> intermediate product
        $mfg1 = OperHead::factory()->create([
            'pro_type' => $this->manufacturingType->id,
            'pro_date' => '2024-01-02',
            'isdeleted' => 0,
            'branch_id' => $this->branch->id,
            'user' => $this->user->id,
            'created_at' => '2024-01-02 10:00:00',
        ]);

        OperationItems::create([
            'pro_id' => $mfg1->id,
            'item_id' => $rawMaterial->id,
            'qty_in' => 0,
            'qty_out' => 50,
            'detail_value' => 500,
            'is_stock' => 1,
        ]);

        OperationItems::create([
            'pro_id' => $mfg1->id,
            'item_id' => $intermediateProduct->id,
            'qty_in' => 25,
            'qty_out' => 0,
            'detail_value' => 500, // Cost: 20 per unit
            'is_stock' => 1,
        ]);

        $service->recalculateAverageCostForItem($intermediateProduct->id, null, false);

        // Second manufacturing: intermediate product -> final product
        $mfg2 = OperHead::factory()->create([
            'pro_type' => $this->manufacturingType->id,
            'pro_date' => '2024-01-03',
            'isdeleted' => 0,
            'branch_id' => $this->branch->id,
            'user' => $this->user->id,
            'created_at' => '2024-01-03 10:00:00',
        ]);

        OperationItems::create([
            'pro_id' => $mfg2->id,
            'item_id' => $intermediateProduct->id,
            'qty_in' => 0,
            'qty_out' => 10,
            'detail_value' => 200,
            'is_stock' => 1,
        ]);

        OperationItems::create([
            'pro_id' => $mfg2->id,
            'item_id' => $finalProduct->id,
            'qty_in' => 5,
            'qty_out' => 0,
            'detail_value' => 200, // Cost: 40 per unit
            'is_stock' => 1,
        ]);

        $service->recalculateAverageCostForItem($finalProduct->id, null, false);

        // Verify initial costs
        $rawMaterial->refresh();
        $intermediateProduct->refresh();
        $finalProduct->refresh();
        $this->assertEquals(10.0, $rawMaterial->average_cost);
        $this->assertEquals(20.0, $intermediateProduct->average_cost);
        $this->assertEquals(40.0, $finalProduct->average_cost);

        // Modify purchase invoice (double the cost)
        DB::table('operation_items')
            ->where('pro_id', $purchaseInvoice->id)
            ->where('item_id', $rawMaterial->id)
            ->update(['detail_value' => 2000]);

        // Trigger chain recalculation
        RecalculationServiceHelper::recalculateManufacturingChain(
            [$rawMaterial->id],
            '2024-01-01'
        );

        // Verify all costs are updated through the chain
        $rawMaterial->refresh();
        $intermediateProduct->refresh();
        $finalProduct->refresh();

        $this->assertEquals(20.0, $rawMaterial->average_cost, 'Raw material cost should double');
        $this->assertEquals(40.0, $intermediateProduct->average_cost, 'Intermediate product cost should double');
        $this->assertEquals(80.0, $finalProduct->average_cost, 'Final product cost should double');
    }

    /**
     * Test manufacturing chain when purchase invoice is deleted
     * 
     * @test
     */
    public function test_manufacturing_chain_with_deleted_purchase_invoice(): void
    {
        // Create items
        $rawMaterial = Item::factory()->create(['name' => 'Raw Material', 'average_cost' => 0]);
        $product = Item::factory()->create(['name' => 'Product', 'average_cost' => 0]);

        // Create two purchase invoices
        $purchase1 = OperHead::factory()->create([
            'pro_type' => $this->purchaseType->id,
            'pro_date' => '2024-01-01',
            'isdeleted' => 0,
            'branch_id' => $this->branch->id,
            'user' => $this->user->id,
            'created_at' => '2024-01-01 10:00:00',
        ]);

        OperationItems::create([
            'pro_id' => $purchase1->id,
            'item_id' => $rawMaterial->id,
            'qty_in' => 100,
            'qty_out' => 0,
            'detail_value' => 1000, // Cost: 10 per unit
            'is_stock' => 1,
        ]);

        $purchase2 = OperHead::factory()->create([
            'pro_type' => $this->purchaseType->id,
            'pro_date' => '2024-01-02',
            'isdeleted' => 0,
            'branch_id' => $this->branch->id,
            'user' => $this->user->id,
            'created_at' => '2024-01-02 10:00:00',
        ]);

        OperationItems::create([
            'pro_id' => $purchase2->id,
            'item_id' => $rawMaterial->id,
            'qty_in' => 100,
            'qty_out' => 0,
            'detail_value' => 2000, // Cost: 20 per unit
            'is_stock' => 1,
        ]);

        $service = app(AverageCostRecalculationServiceOptimized::class);
        $service->recalculateAverageCostForItem($rawMaterial->id, null, false);

        // Average cost should be (1000 + 2000) / (100 + 100) = 15
        $rawMaterial->refresh();
        $this->assertEquals(15.0, $rawMaterial->average_cost);

        // Create manufacturing invoice
        $mfg = OperHead::factory()->create([
            'pro_type' => $this->manufacturingType->id,
            'pro_date' => '2024-01-05',
            'isdeleted' => 0,
            'branch_id' => $this->branch->id,
            'user' => $this->user->id,
            'created_at' => '2024-01-05 10:00:00',
        ]);

        OperationItems::create([
            'pro_id' => $mfg->id,
            'item_id' => $rawMaterial->id,
            'qty_in' => 0,
            'qty_out' => 50,
            'detail_value' => 750, // 50 * 15
            'is_stock' => 1,
        ]);

        OperationItems::create([
            'pro_id' => $mfg->id,
            'item_id' => $product->id,
            'qty_in' => 25,
            'qty_out' => 0,
            'detail_value' => 750, // Cost: 30 per unit
            'is_stock' => 1,
        ]);

        $service->recalculateAverageCostForItem($product->id, null, false);

        $product->refresh();
        $this->assertEquals(30.0, $product->average_cost);

        // Delete first purchase invoice
        DB::table('operhead')
            ->where('id', $purchase1->id)
            ->update(['isdeleted' => 1]);

        // Trigger chain recalculation
        RecalculationServiceHelper::recalculateManufacturingChain(
            [$rawMaterial->id],
            '2024-01-01'
        );

        // Verify costs are recalculated without deleted invoice
        // New average cost = 2000 / 100 = 20
        $rawMaterial->refresh();
        $this->assertEquals(20.0, $rawMaterial->average_cost);

        // Product cost should be updated: 50 * 20 = 1000, 1000 / 25 = 40
        $product->refresh();
        $this->assertEquals(40.0, $product->average_cost);
    }
}
