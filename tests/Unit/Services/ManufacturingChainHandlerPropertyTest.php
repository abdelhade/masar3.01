<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Item;
use App\Models\OperHead;
use App\Models\OperationItems;
use App\Services\Manufacturing\ManufacturingChainHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Property-Based Tests for ManufacturingChainHandler
 *
 * Feature: average-cost-recalculation-improvements
 * Property 29: Manufacturing Invoice Identification
 * Validates: Requirements 16.1, 16.4
 *
 * For any purchase invoice that is deleted or modified, the system should
 * identify all manufacturing invoices that use items from that purchase invoice,
 * ordered chronologically by date and time.
 */
class ManufacturingChainHandlerPropertyTest extends TestCase
{
    use RefreshDatabase;

    private ManufacturingChainHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new ManufacturingChainHandler();

        // Disable Scout (Meilisearch) syncing for tests
        config(['scout.driver' => null]);

        // Create necessary test data: Branch, User, and ProTypes
        $this->setupTestEnvironment();
    }

    /**
     * Setup test environment with required data
     */
    private function setupTestEnvironment(): void
    {
        // Create a branch
        $branch = \Modules\Branches\Models\Branch::create([
            'name' => 'Test Branch',
            'is_active' => 1,
        ]);

        // Create a user and associate with branch
        $user = \App\Models\User::factory()->create();

        // Attach branch to user
        $user->branches()->attach($branch->id);

        // Authenticate the user so BranchScope works
        $this->actingAs($user);

        // Create ProTypes for different operation types
        // Type 59 is manufacturing invoice (based on requirements)
        \App\Models\ProType::withoutGlobalScopes()->create([
            'id' => 59,
            'pname' => 'Manufacturing Invoice',
            'ptext' => 'manufacturing_invoice',
            'ptype' => 'manufacturing',
            'info' => 'Manufacturing operation',
            'isdeleted' => 0,
            'tenant' => 1,
            'branch_id' => $branch->id,
        ]);

        // Create other common operation types
        \App\Models\ProType::withoutGlobalScopes()->create([
            'id' => 1,
            'pname' => 'Purchase Invoice',
            'ptext' => 'purchase_invoice',
            'ptype' => 'purchase',
            'info' => 'Purchase operation',
            'isdeleted' => 0,
            'tenant' => 1,
            'branch_id' => $branch->id,
        ]);

        \App\Models\ProType::withoutGlobalScopes()->create([
            'id' => 2,
            'pname' => 'Sales Invoice',
            'ptext' => 'sales_invoice',
            'ptype' => 'sales',
            'info' => 'Sales operation',
            'isdeleted' => 0,
            'tenant' => 1,
            'branch_id' => $branch->id,
        ]);
    }

    /**
     * Property 29: Manufacturing Invoice Identification
     *
     * Test that affected manufacturing invoices are correctly identified
     * when raw materials are used in manufacturing operations.
     *
     * @dataProvider manufacturingInvoiceScenarios
     */
    public function test_property_identifies_affected_manufacturing_invoices(
        int $rawMaterialCount,
        int $manufacturingInvoiceCount,
        int $purchaseInvoiceCount,
        bool $includeDeleted
    ): void {
        // Arrange: Create random raw materials
        $rawMaterials = [];
        for ($i = 0; $i < $rawMaterialCount; $i++) {
            $rawMaterials[] = Item::factory()->create([
                'name' => "Raw Material {$i}",
            ]);
        }

        // Create purchase invoices using factory
        $purchaseInvoices = [];
        for ($i = 0; $i < $purchaseInvoiceCount; $i++) {
            $purchaseInvoices[] = OperHead::factory()->create([
                'pro_date' => now()->subDays($purchaseInvoiceCount - $i)->format('Y-m-d'),
                'isdeleted' => 0,
                'created_at' => now()->subDays($purchaseInvoiceCount - $i),
            ]);
        }

        // Create manufacturing invoices (type 59) that use these raw materials
        $manufacturingInvoices = [];
        $expectedAffectedInvoices = [];

        for ($i = 0; $i < $manufacturingInvoiceCount; $i++) {
            $isDeleted = $includeDeleted && $i % 3 === 0 ? 1 : 0;
            $date = now()->subDays($manufacturingInvoiceCount - $i - 1);

            $invoice = OperHead::factory()->create([
                'pro_date' => $date->format('Y-m-d'),
                'isdeleted' => $isDeleted,
                'created_at' => $date,
            ]);

            $manufacturingInvoices[] = $invoice;

            // Add raw materials to this manufacturing invoice
            $rawMaterialsToUse = array_slice($rawMaterials, 0, min($rawMaterialCount, 2));
            foreach ($rawMaterialsToUse as $rawMaterial) {
                OperationItems::create([
                    'pro_id' => $invoice->id,
                    'item_id' => $rawMaterial->id,
                    'qty_out' => rand(5, 20), // Raw materials have qty_out > 0
                    'qty_in' => 0,
                    'detail_value' => rand(50, 200),
                    'is_stock' => 1,
                ]);
            }

            // Add products to this manufacturing invoice
            $product = Item::factory()->create(['name' => "Product {$i}"]);
            OperationItems::create([
                'pro_id' => $invoice->id,
                'item_id' => $product->id,
                'qty_in' => rand(10, 30), // Products have qty_in > 0
                'qty_out' => 0,
                'detail_value' => rand(100, 500),
                'is_stock' => 1,
            ]);

            // Track expected affected invoices (non-deleted only)
            if ($isDeleted === 0) {
                $expectedAffectedInvoices[] = $invoice;
            }
        }

        // Act: Find affected manufacturing invoices
        $rawMaterialIds = array_map(fn($item) => $item->id, $rawMaterials);
        $fromDate = now()->subDays($manufacturingInvoiceCount + 10)->format('Y-m-d');

        $affectedInvoices = $this->handler->findAffectedManufacturingInvoices(
            $rawMaterialIds,
            $fromDate
        );

        // Assert: Should identify correct number of affected invoices (excluding deleted)
        $this->assertCount(
            count($expectedAffectedInvoices),
            $affectedInvoices,
            "Should identify " . count($expectedAffectedInvoices) . " non-deleted manufacturing invoices"
        );

        // Assert: All affected invoices should be in the result
        $affectedInvoiceIds = array_map(fn($inv) => $inv->invoice_id, $affectedInvoices);
        foreach ($expectedAffectedInvoices as $expectedInvoice) {
            $this->assertContains(
                $expectedInvoice->id,
                $affectedInvoiceIds,
                "Invoice {$expectedInvoice->id} should be in affected invoices"
            );
        }

        // Assert: Deleted invoices should NOT be in the result
        foreach ($manufacturingInvoices as $invoice) {
            if ($invoice->isdeleted === 1) {
                $this->assertNotContains(
                    $invoice->id,
                    $affectedInvoiceIds,
                    "Deleted invoice {$invoice->id} should NOT be in affected invoices"
                );
            }
        }
    }

    /**
     * Property 29: Chronological Ordering
     *
     * Test that affected manufacturing invoices are ordered chronologically
     * by date and time.
     *
     * @dataProvider chronologicalOrderingScenarios
     */
    public function test_property_chronological_ordering_by_date_and_time(
        array $invoiceDates,
        array $invoiceTimes
    ): void {
        // Arrange: Create raw material
        $rawMaterial = Item::factory()->create(['name' => 'Raw Material']);

        // Create manufacturing invoices with specified dates and times
        $createdInvoices = [];
        foreach ($invoiceDates as $index => $date) {
            $time = $invoiceTimes[$index] ?? '12:00:00';
            $dateTime = "{$date} {$time}";

            $invoice = OperHead::factory()->create([
                'pro_date' => $date,
                'isdeleted' => 0,
                'created_at' => $dateTime,
            ]);

            // Add raw material to invoice
            OperationItems::create([
                'pro_id' => $invoice->id,
                'item_id' => $rawMaterial->id,
                'qty_out' => 10,
                'qty_in' => 0,
                'detail_value' => 100,
                'is_stock' => 1,
            ]);

            $createdInvoices[] = [
                'id' => $invoice->id,
                'date' => $date,
                'time' => $time,
                'datetime' => $dateTime,
            ];
        }

        // Sort expected order by date then time
        usort($createdInvoices, function ($a, $b) {
            $dateCompare = strcmp($a['date'], $b['date']);
            if ($dateCompare !== 0) {
                return $dateCompare;
            }
            return strcmp($a['time'], $b['time']);
        });

        // Act: Find affected manufacturing invoices
        $affectedInvoices = $this->handler->findAffectedManufacturingInvoices(
            [$rawMaterial->id],
            '2024-01-01'
        );

        // Assert: Should return invoices in chronological order
        $this->assertCount(count($createdInvoices), $affectedInvoices);

        foreach ($affectedInvoices as $index => $affectedInvoice) {
            $expectedId = $createdInvoices[$index]['id'];
            $this->assertEquals(
                $expectedId,
                $affectedInvoice->invoice_id,
                "Invoice at position {$index} should be {$expectedId}, ".
                "expected order: ".json_encode(array_column($createdInvoices, 'id'))
            );
        }
    }

    /**
     * Property 29: Multiple Raw Materials
     *
     * Test that invoices using ANY of the specified raw materials are identified.
     */
    public function test_property_identifies_invoices_using_any_raw_material(): void
    {
        // Arrange: Create multiple raw materials
        $rawMaterial1 = Item::factory()->create(['name' => 'Raw Material 1']);
        $rawMaterial2 = Item::factory()->create(['name' => 'Raw Material 2']);
        $rawMaterial3 = Item::factory()->create(['name' => 'Raw Material 3']);

        // Create manufacturing invoices using different combinations
        $invoice1 = OperHead::factory()->create([
            'pro_date' => '2024-01-05',
            'isdeleted' => 0,
        ]);
        OperationItems::create([
            'pro_id' => $invoice1->id,
            'item_id' => $rawMaterial1->id,
            'qty_out' => 10,
            'qty_in' => 0,
            'detail_value' => 100,
            'is_stock' => 1,
        ]);

        $invoice2 = OperHead::factory()->create([
            'pro_date' => '2024-01-06',
            'isdeleted' => 0,
        ]);
        OperationItems::create([
            'pro_id' => $invoice2->id,
            'item_id' => $rawMaterial2->id,
            'qty_out' => 15,
            'qty_in' => 0,
            'detail_value' => 150,
            'is_stock' => 1,
        ]);

        $invoice3 = OperHead::factory()->create([
            'pro_date' => '2024-01-07',
            'isdeleted' => 0,
        ]);
        OperationItems::create([
            'pro_id' => $invoice3->id,
            'item_id' => $rawMaterial1->id,
            'qty_out' => 5,
            'qty_in' => 0,
            'detail_value' => 50,
            'is_stock' => 1,
        ]);
        OperationItems::create([
            'pro_id' => $invoice3->id,
            'item_id' => $rawMaterial2->id,
            'qty_out' => 8,
            'qty_in' => 0,
            'detail_value' => 80,
            'is_stock' => 1,
        ]);

        // Act: Find affected invoices for raw materials 1 and 2
        $affectedInvoices = $this->handler->findAffectedManufacturingInvoices(
            [$rawMaterial1->id, $rawMaterial2->id],
            '2024-01-01'
        );

        // Assert: Should find all three invoices
        $this->assertCount(3, $affectedInvoices);
        $affectedIds = array_map(fn($inv) => $inv->invoice_id, $affectedInvoices);
        $this->assertContains($invoice1->id, $affectedIds);
        $this->assertContains($invoice2->id, $affectedIds);
        $this->assertContains($invoice3->id, $affectedIds);

        // Act: Find affected invoices for only raw material 3
        $affectedInvoices3 = $this->handler->findAffectedManufacturingInvoices(
            [$rawMaterial3->id],
            '2024-01-01'
        );

        // Assert: Should find no invoices (raw material 3 not used)
        $this->assertCount(0, $affectedInvoices3);
    }

    /**
     * Property 29: Date Filtering
     *
     * Test that only invoices on or after the fromDate are identified.
     */
    public function test_property_respects_from_date_filter(): void
    {
        // Arrange: Create raw material
        $rawMaterial = Item::factory()->create(['name' => 'Raw Material']);

        // Create manufacturing invoices on different dates
        $invoice1 = OperHead::factory()->create([
            'pro_date' => '2024-01-01',
            'isdeleted' => 0,
        ]);
        OperationItems::create([
            'pro_id' => $invoice1->id,
            'item_id' => $rawMaterial->id,
            'qty_out' => 10,
            'qty_in' => 0,
            'detail_value' => 100,
            'is_stock' => 1,
        ]);

        $invoice2 = OperHead::factory()->create([
            'pro_date' => '2024-01-10',
            'isdeleted' => 0,
        ]);
        OperationItems::create([
            'pro_id' => $invoice2->id,
            'item_id' => $rawMaterial->id,
            'qty_out' => 15,
            'qty_in' => 0,
            'detail_value' => 150,
            'is_stock' => 1,
        ]);

        $invoice3 = OperHead::factory()->create([
            'pro_date' => '2024-01-20',
            'isdeleted' => 0,
        ]);
        OperationItems::create([
            'pro_id' => $invoice3->id,
            'item_id' => $rawMaterial->id,
            'qty_out' => 20,
            'qty_in' => 0,
            'detail_value' => 200,
            'is_stock' => 1,
        ]);

        // Act: Find affected invoices from 2024-01-10
        $affectedInvoices = $this->handler->findAffectedManufacturingInvoices(
            [$rawMaterial->id],
            '2024-01-10'
        );

        // Assert: Should only find invoices on or after 2024-01-10
        $this->assertCount(2, $affectedInvoices);
        $affectedIds = array_map(fn($inv) => $inv->invoice_id, $affectedInvoices);
        $this->assertNotContains($invoice1->id, $affectedIds, 'Invoice before fromDate should be excluded');
        $this->assertContains($invoice2->id, $affectedIds, 'Invoice on fromDate should be included');
        $this->assertContains($invoice3->id, $affectedIds, 'Invoice after fromDate should be included');
    }

    /**
     * Property 29: Non-Manufacturing Invoices Excluded
     *
     * Test that only manufacturing invoices (type 59) are identified,
     * not other invoice types.
     */
    public function test_property_excludes_non_manufacturing_invoices(): void
    {
        // Arrange: Create raw material
        $rawMaterial = Item::factory()->create(['name' => 'Raw Material']);

        // Create various invoice types using the raw material
        $purchaseInvoice = OperHead::factory()->create([
            'pro_type' => 1, // Purchase invoice type
            'pro_date' => '2024-01-05',
            'isdeleted' => 0,
        ]);
        OperationItems::create([
            'pro_id' => $purchaseInvoice->id,
            'item_id' => $rawMaterial->id,
            'qty_in' => 10,
            'qty_out' => 0,
            'detail_value' => 100,
            'is_stock' => 1,
        ]);

        $salesInvoice = OperHead::factory()->create([
            'pro_type' => 2, // Sales invoice type
            'pro_date' => '2024-01-06',
            'isdeleted' => 0,
        ]);
        OperationItems::create([
            'pro_id' => $salesInvoice->id,
            'item_id' => $rawMaterial->id,
            'qty_out' => 5,
            'qty_in' => 0,
            'detail_value' => 50,
            'is_stock' => 1,
        ]);

        $manufacturingInvoice = OperHead::factory()->create([
            'pro_type' => 59, // Manufacturing invoice type
            'pro_date' => '2024-01-07',
            'isdeleted' => 0,
        ]);
        OperationItems::create([
            'pro_id' => $manufacturingInvoice->id,
            'item_id' => $rawMaterial->id,
            'qty_out' => 8,
            'qty_in' => 0,
            'detail_value' => 80,
            'is_stock' => 1,
        ]);

        // Act: Find affected manufacturing invoices
        $affectedInvoices = $this->handler->findAffectedManufacturingInvoices(
            [$rawMaterial->id],
            '2024-01-01'
        );

        // Assert: Should only find the manufacturing invoice
        $this->assertCount(1, $affectedInvoices);
        $this->assertEquals($manufacturingInvoice->id, $affectedInvoices[0]->invoice_id);
    }

    /**
     * Data provider for manufacturing invoice scenarios
     *
     * @return array<string, array{int, int, int, bool}>
     */
    public static function manufacturingInvoiceScenarios(): array
    {
        return [
            'single_raw_material_single_invoice' => [1, 1, 1, false],
            'single_raw_material_multiple_invoices' => [1, 5, 1, false],
            'multiple_raw_materials_single_invoice' => [3, 1, 1, false],
            'multiple_raw_materials_multiple_invoices' => [3, 5, 2, false],
            'with_deleted_invoices' => [2, 6, 2, true],
            'many_raw_materials_many_invoices' => [5, 10, 3, false],
            'edge_case_no_purchase_invoices' => [2, 3, 0, false],
        ];
    }

    /**
     * Data provider for chronological ordering scenarios
     *
     * @return array<string, array{array<string>, array<string>}>
     */
    public static function chronologicalOrderingScenarios(): array
    {
        return [
            'same_date_different_times' => [
                ['2024-01-05', '2024-01-05', '2024-01-05'],
                ['10:00:00', '08:00:00', '15:00:00'],
            ],
            'different_dates_same_time' => [
                ['2024-01-05', '2024-01-03', '2024-01-07'],
                ['12:00:00', '12:00:00', '12:00:00'],
            ],
            'mixed_dates_and_times' => [
                ['2024-01-05', '2024-01-03', '2024-01-05', '2024-01-07'],
                ['10:00:00', '15:00:00', '08:00:00', '12:00:00'],
            ],
            'sequential_dates' => [
                ['2024-01-01', '2024-01-02', '2024-01-03', '2024-01-04'],
                ['09:00:00', '09:00:00', '09:00:00', '09:00:00'],
            ],
            'reverse_dates' => [
                ['2024-01-10', '2024-01-08', '2024-01-06', '2024-01-04'],
                ['10:00:00', '10:00:00', '10:00:00', '10:00:00'],
            ],
        ];
    }
}
