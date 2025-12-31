<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Item;
use App\Models\OperationItems;
use App\Models\OperHead;
use App\Models\ProType;
use App\Models\User;
use App\Services\Invoice\DetailValueCalculator;
use App\Services\Invoice\DetailValueValidator;
use App\Services\SaveInvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Accounts\Models\AccHead;
use Modules\Branches\Models\Branch;
use Tests\TestCase;

/**
 * Integration tests for VAT and Withholding Tax in invoice processing
 *
 * These tests verify that VAT and Withholding Tax are correctly:
 * 1. Distributed across invoice items
 * 2. Included in detail_value calculations
 * 3. Reflected in average cost calculations
 * 4. Saved to the database correctly
 */
class InvoiceTaxIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected SaveInvoiceService $service;

    protected Branch $branch;

    protected User $user;

    protected AccHead $supplier;

    protected AccHead $store;

    protected function setUp(): void
    {
        parent::setUp();

        // Create dependencies
        $calculator = new DetailValueCalculator;
        $validator = new DetailValueValidator;
        $this->service = new SaveInvoiceService($calculator, $validator);

        // Create branch
        $this->branch = Branch::create([
            'name' => 'Test Branch',
            'code' => 'TB001',
        ]);

        // Create user
        $this->user = User::factory()->create();
        $this->user->branches()->attach($this->branch->id);
        Auth::login($this->user);

        // Create ProTypes
        ProType::create(['id' => 10, 'pname' => 'Sales Invoice', 'branch_id' => $this->branch->id]);
        ProType::create(['id' => 11, 'pname' => 'Purchase Invoice', 'branch_id' => $this->branch->id]);
        ProType::create(['id' => 12, 'pname' => 'Purchase Return', 'branch_id' => $this->branch->id]);
        ProType::create(['id' => 13, 'pname' => 'Sales Return', 'branch_id' => $this->branch->id]);

        // Create accounts
        $this->supplier = AccHead::create([
            'aname' => 'Test Supplier',
            'acc_type' => 2,
            'branch_id' => $this->branch->id,
        ]);

        $this->store = AccHead::create([
            'aname' => 'Test Store',
            'acc_type' => 3,
            'branch_id' => $this->branch->id,
        ]);
    }

    /**
     * Test purchase invoice with VAT only
     *
     * @test
     */
    public function it_processes_purchase_invoice_with_vat(): void
    {
        // Arrange: Create item
        $item = Item::factory()->create(['average_cost' => 0]);

        // Create mock component for purchase invoice with VAT
        $component = $this->createMockComponent([
            'type' => 11, // Purchase invoice
            'acc1_id' => $this->supplier->id,
            'acc2_id' => $this->store->id,
            'pro_date' => '2024-01-01',
            'subtotal' => 1000, // Item subtotal
            'discount_value' => 0,
            'discount_percentage' => 0,
            'additional_value' => 0,
            'additional_percentage' => 0,
            'vat_value' => 150, // 15% VAT = 150 EGP
            'vat_percentage' => 15,
            'withholding_tax_value' => 0,
            'withholding_tax_percentage' => 0,
            'total_after_additional' => 1150, // 1000 + 150 VAT
            'invoiceItems' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 10,
                    'price' => 100, // 10 * 100 = 1000
                    'discount' => 0,
                    'additional' => 0,
                    'unit_id' => null,
                    'sub_value' => 1000,
                ],
            ],
        ]);

        // Act: Save invoice
        $operationId = $this->service->saveInvoice($component, false);

        // Assert: Invoice created successfully
        $this->assertNotFalse($operationId);

        // Verify operation head
        $operation = OperHead::find($operationId);
        $this->assertNotNull($operation);
        $this->assertEquals(1150, $operation->pro_value);
        $this->assertEquals(150, $operation->vat_value);
        $this->assertEquals(15, $operation->vat_percentage);

        // Verify operation item
        $operationItem = OperationItems::where('pro_id', $operationId)->first();
        $this->assertNotNull($operationItem);

        // Detail value should be: 1000 + 150 VAT = 1150
        $this->assertEquals(1150.0, $operationItem->detail_value);

        // Verify average cost updated (should include VAT)
        $item->refresh();
        // Average cost = 1150 / 10 = 115 EGP per unit
        $this->assertEquals(115.0, $item->average_cost);
    }

    /**
     * Test purchase invoice with Withholding Tax only
     *
     * @test
     */
    public function it_processes_purchase_invoice_with_withholding_tax(): void
    {
        // Arrange: Create item
        $item = Item::factory()->create(['average_cost' => 0]);

        // Create mock component for purchase invoice with withholding tax
        $component = $this->createMockComponent([
            'type' => 11, // Purchase invoice
            'acc1_id' => $this->supplier->id,
            'acc2_id' => $this->store->id,
            'pro_date' => '2024-01-01',
            'subtotal' => 1000,
            'discount_value' => 0,
            'discount_percentage' => 0,
            'additional_value' => 0,
            'additional_percentage' => 0,
            'vat_value' => 0,
            'vat_percentage' => 0,
            'withholding_tax_value' => 50, // 5% withholding tax = 50 EGP
            'withholding_tax_percentage' => 5,
            'total_after_additional' => 950, // 1000 - 50 withholding tax
            'invoiceItems' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 10,
                    'price' => 100,
                    'discount' => 0,
                    'additional' => 0,
                    'unit_id' => null,
                    'sub_value' => 1000,
                ],
            ],
        ]);

        // Act: Save invoice
        $operationId = $this->service->saveInvoice($component, false);

        // Assert: Invoice created successfully
        $this->assertNotFalse($operationId);

        // Verify operation head
        $operation = OperHead::find($operationId);
        $this->assertNotNull($operation);
        $this->assertEquals(950, $operation->pro_value);
        $this->assertEquals(50, $operation->withholding_tax_value);
        $this->assertEquals(5, $operation->withholding_tax_percentage);

        // Verify operation item
        $operationItem = OperationItems::where('pro_id', $operationId)->first();
        $this->assertNotNull($operationItem);

        // Detail value should be: 1000 - 50 withholding tax = 950
        $this->assertEquals(950.0, $operationItem->detail_value);

        // Verify average cost updated (should exclude withholding tax)
        $item->refresh();
        // Average cost = 950 / 10 = 95 EGP per unit
        $this->assertEquals(95.0, $item->average_cost);
    }

    /**
     * Test purchase invoice with both VAT and Withholding Tax
     *
     * @test
     */
    public function it_processes_purchase_invoice_with_both_taxes(): void
    {
        // Arrange: Create item
        $item = Item::factory()->create(['average_cost' => 0]);

        // Create mock component with both taxes
        $component = $this->createMockComponent([
            'type' => 11,
            'acc1_id' => $this->supplier->id,
            'acc2_id' => $this->store->id,
            'pro_date' => '2024-01-01',
            'subtotal' => 1000,
            'discount_value' => 0,
            'discount_percentage' => 0,
            'additional_value' => 0,
            'additional_percentage' => 0,
            'vat_value' => 150, // 15% VAT
            'vat_percentage' => 15,
            'withholding_tax_value' => 50, // 5% withholding tax
            'withholding_tax_percentage' => 5,
            'total_after_additional' => 1100, // 1000 + 150 VAT - 50 withholding = 1100
            'invoiceItems' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 10,
                    'price' => 100,
                    'discount' => 0,
                    'additional' => 0,
                    'unit_id' => null,
                    'sub_value' => 1000,
                ],
            ],
        ]);

        // Act: Save invoice
        $operationId = $this->service->saveInvoice($component, false);

        // Assert: Invoice created successfully
        $this->assertNotFalse($operationId);

        // Verify operation head
        $operation = OperHead::find($operationId);
        $this->assertNotNull($operation);
        $this->assertEquals(1100, $operation->pro_value);
        $this->assertEquals(150, $operation->vat_value);
        $this->assertEquals(50, $operation->withholding_tax_value);

        // Verify operation item
        $operationItem = OperationItems::where('pro_id', $operationId)->first();
        $this->assertNotNull($operationItem);

        // Detail value should be: 1000 + 150 VAT - 50 withholding = 1100
        $this->assertEquals(1100.0, $operationItem->detail_value);

        // Verify average cost updated
        $item->refresh();
        // Average cost = 1100 / 10 = 110 EGP per unit
        $this->assertEquals(110.0, $item->average_cost);
    }

    /**
     * Test purchase invoice with taxes, discounts, and additions
     *
     * @test
     */
    public function it_processes_complex_purchase_invoice_with_all_adjustments(): void
    {
        // Arrange: Create item
        $item = Item::factory()->create(['average_cost' => 0]);

        // Complex invoice with all adjustments
        $component = $this->createMockComponent([
            'type' => 11,
            'acc1_id' => $this->supplier->id,
            'acc2_id' => $this->store->id,
            'pro_date' => '2024-01-01',
            'subtotal' => 1000, // Item subtotal before invoice adjustments
            'discount_value' => 100, // Invoice discount
            'discount_percentage' => 10,
            'additional_value' => 50, // Invoice additional
            'additional_percentage' => 5,
            'vat_value' => 150, // VAT
            'vat_percentage' => 15,
            'withholding_tax_value' => 50, // Withholding tax
            'withholding_tax_percentage' => 5,
            // Total: 1000 - 100 + 50 + 150 - 50 = 1050
            'total_after_additional' => 1050,
            'invoiceItems' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 10,
                    'price' => 100,
                    'discount' => 0,
                    'additional' => 0,
                    'unit_id' => null,
                    'sub_value' => 1000,
                ],
            ],
        ]);

        // Act: Save invoice
        $operationId = $this->service->saveInvoice($component, false);

        // Assert: Invoice created successfully
        $this->assertNotFalse($operationId);

        // Verify operation item
        $operationItem = OperationItems::where('pro_id', $operationId)->first();
        $this->assertNotNull($operationItem);

        // Detail value: 1000 - 100 + 50 + 150 - 50 = 1050
        $this->assertEquals(1050.0, $operationItem->detail_value);

        // Verify average cost
        $item->refresh();
        // Average cost = 1050 / 10 = 105 EGP per unit
        $this->assertEquals(105.0, $item->average_cost);
    }

    /**
     * Test purchase invoice with multiple items and proportional tax distribution
     *
     * @test
     */
    public function it_distributes_taxes_proportionally_across_multiple_items(): void
    {
        // Arrange: Create items
        $item1 = Item::factory()->create(['average_cost' => 0]);
        $item2 = Item::factory()->create(['average_cost' => 0]);

        // Invoice with 2 items of different values
        $component = $this->createMockComponent([
            'type' => 11,
            'acc1_id' => $this->supplier->id,
            'acc2_id' => $this->store->id,
            'pro_date' => '2024-01-01',
            'subtotal' => 1500, // 1000 + 500
            'discount_value' => 0,
            'discount_percentage' => 0,
            'additional_value' => 0,
            'additional_percentage' => 0,
            'vat_value' => 225, // 15% of 1500
            'vat_percentage' => 15,
            'withholding_tax_value' => 75, // 5% of 1500
            'withholding_tax_percentage' => 5,
            'total_after_additional' => 1650, // 1500 + 225 - 75
            'invoiceItems' => [
                [
                    'item_id' => $item1->id,
                    'quantity' => 10,
                    'price' => 100, // 1000 EGP (66.67% of total)
                    'discount' => 0,
                    'additional' => 0,
                    'unit_id' => null,
                    'sub_value' => 1000,
                ],
                [
                    'item_id' => $item2->id,
                    'quantity' => 5,
                    'price' => 100, // 500 EGP (33.33% of total)
                    'discount' => 0,
                    'additional' => 0,
                    'unit_id' => null,
                    'sub_value' => 500,
                ],
            ],
        ]);

        // Act: Save invoice
        $operationId = $this->service->saveInvoice($component, false);

        // Assert: Invoice created successfully
        $this->assertNotFalse($operationId);

        // Verify operation items
        $operationItems = OperationItems::where('pro_id', $operationId)->get();
        $this->assertCount(2, $operationItems);

        // Item 1: 1000/1500 = 66.67%
        // VAT: 225 * 0.6667 = 150
        // Withholding: 75 * 0.6667 = 50
        // Detail value: 1000 + 150 - 50 = 1100
        $item1Operation = $operationItems->where('item_id', $item1->id)->first();
        $this->assertEquals(1100.0, $item1Operation->detail_value);

        // Item 2: 500/1500 = 33.33%
        // VAT: 225 * 0.3333 = 75
        // Withholding: 75 * 0.3333 = 25
        // Detail value: 500 + 75 - 25 = 550
        $item2Operation = $operationItems->where('item_id', $item2->id)->first();
        $this->assertEquals(550.0, $item2Operation->detail_value);

        // Verify average costs
        $item1->refresh();
        $item2->refresh();

        // Item 1: 1100 / 10 = 110 EGP per unit
        $this->assertEquals(110.0, $item1->average_cost);

        // Item 2: 550 / 5 = 110 EGP per unit
        $this->assertEquals(110.0, $item2->average_cost);
    }

    /**
     * Test purchase return with taxes (negative effect)
     *
     * @test
     */
    public function it_processes_purchase_return_with_taxes(): void
    {
        // Arrange: Create item with existing average cost
        $item = Item::factory()->create(['average_cost' => 100]);

        // Add initial stock
        $initialOperation = OperHead::create([
            'pro_type' => 11,
            'acc1' => $this->supplier->id,
            'acc2' => $this->store->id,
            'pro_date' => '2024-01-01',
            'pro_value' => 1000,
            'branch_id' => $this->branch->id,
            'user' => $this->user->id,
        ]);

        OperationItems::create([
            'pro_id' => $initialOperation->id,
            'item_id' => $item->id,
            'qty_in' => 20,
            'qty_out' => 0,
            'detail_value' => 2000,
            'is_stock' => 1,
            'pro_tybe' => 11,
            'detail_store' => $this->store->id,
        ]);

        // Create purchase return with taxes
        $component = $this->createMockComponent([
            'type' => 12, // Purchase return
            'acc1_id' => $this->supplier->id,
            'acc2_id' => $this->store->id,
            'pro_date' => '2024-01-02',
            'subtotal' => 500, // Returning 500 EGP worth
            'discount_value' => 0,
            'discount_percentage' => 0,
            'additional_value' => 0,
            'additional_percentage' => 0,
            'vat_value' => 75, // 15% VAT on return
            'vat_percentage' => 15,
            'withholding_tax_value' => 25, // 5% withholding tax on return
            'withholding_tax_percentage' => 5,
            'total_after_additional' => 550, // 500 + 75 - 25
            'invoiceItems' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 5,
                    'price' => 100,
                    'discount' => 0,
                    'additional' => 0,
                    'unit_id' => null,
                    'sub_value' => 500,
                ],
            ],
        ]);

        // Act: Save purchase return
        $operationId = $this->service->saveInvoice($component, false);

        // Assert: Return created successfully
        $this->assertNotFalse($operationId);

        // Verify operation item
        $operationItem = OperationItems::where('pro_id', $operationId)->first();
        $this->assertNotNull($operationItem);

        // Detail value for return: 500 + 75 VAT - 25 withholding = 550
        $this->assertEquals(550.0, $operationItem->detail_value);

        // Verify average cost recalculated
        $item->refresh();
        // Average cost should be adjusted based on the return
        $this->assertNotEquals(100.0, $item->average_cost);
    }

    /**
     * Helper method to create mock component
     */
    private function createMockComponent(array $data): object
    {
        return (object) array_merge([
            'type' => 11,
            'acc1_id' => $this->supplier->id,
            'acc2_id' => $this->store->id,
            'emp_id' => null,
            'delivery_id' => null,
            'pro_date' => '2024-01-01',
            'accural_date' => null,
            'serial_number' => null,
            'notes' => null,
            'status' => 0,
            'cash_box_id' => null,
            'received_from_client' => 0,
            'branch_id' => $this->branch->id,
            'selectedPriceType' => 1,
            'pro_id' => null,
            'operationId' => null,
            'op2' => null,
            'subtotal' => 0,
            'discount_value' => 0,
            'discount_percentage' => 0,
            'additional_value' => 0,
            'additional_percentage' => 0,
            'vat_value' => 0,
            'vat_percentage' => 0,
            'withholding_tax_value' => 0,
            'withholding_tax_percentage' => 0,
            'total_after_additional' => 0,
            'invoiceItems' => [],
        ], $data);
    }
}
