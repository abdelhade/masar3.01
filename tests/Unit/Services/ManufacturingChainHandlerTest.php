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

class ManufacturingChainHandlerTest extends TestCase
{
    use RefreshDatabase;

    private ManufacturingChainHandler $handler;
    private $branch;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new ManufacturingChainHandler();
        
        // Disable Scout (Meilisearch) syncing for tests
        config(['scout.driver' => null]);
        
        // Setup test environment
        $this->setupTestEnvironment();
    }
    
    /**
     * Setup test environment with required data
     */
    private function setupTestEnvironment(): void
    {
        // Create a branch
        $this->branch = \Modules\Branches\Models\Branch::create([
            'name' => 'Test Branch',
            'is_active' => 1,
        ]);

        // Create a user and associate with branch
        $this->user = \App\Models\User::factory()->create();

        // Attach branch to user
        $this->user->branches()->attach($this->branch->id);

        // Authenticate the user so BranchScope works
        $this->actingAs($this->user);

        // Create ProType for manufacturing (type 59)
        \App\Models\ProType::withoutGlobalScopes()->create([
            'id' => 59,
            'pname' => 'Manufacturing Invoice',
            'ptext' => 'manufacturing_invoice',
            'ptype' => 'manufacturing',
            'info' => 'Manufacturing operation',
            'isdeleted' => 0,
            'tenant' => 1,
            'branch_id' => $this->branch->id,
        ]);
    }

    public function test_empty_item_ids_returns_empty_array(): void
    {
        $affected = $this->handler->findAffectedManufacturingInvoices([], '2024-01-01');
        $this->assertEmpty($affected);
    }

    public function test_invalid_invoice_id_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->handler->getManufacturingInvoiceDetails(-1);
    }

    public function test_invalid_date_format_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->handler->findAffectedManufacturingInvoices([1], 'invalid-date');
    }

    public function test_find_affected_manufacturing_invoices_finds_correct_invoices(): void
    {
        $rawMaterial1 = Item::factory()->create(['name' => 'Raw Material 1']);

        $mfgInvoice1 = OperHead::factory()->create([
            'pro_date' => '2024-01-05',
            'pro_type' => 59,
            'isdeleted' => 0,
        ]);

        $mfgInvoice2 = OperHead::factory()->create([
            'pro_date' => '2024-01-10',
            'pro_type' => 59,
            'isdeleted' => 0,
        ]);

        OperationItems::create([
            'pro_id' => $mfgInvoice1->id,
            'item_id' => $rawMaterial1->id,
            'qty_out' => 10,
            'qty_in' => 0,
            'detail_value' => 100,
            'is_stock' => 1,
        ]);

        OperationItems::create([
            'pro_id' => $mfgInvoice2->id,
            'item_id' => $rawMaterial1->id,
            'qty_out' => 5,
            'qty_in' => 0,
            'detail_value' => 50,
            'is_stock' => 1,
        ]);

        $affected = $this->handler->findAffectedManufacturingInvoices(
            [$rawMaterial1->id],
            '2024-01-01'
        );

        $this->assertCount(2, $affected);
        $this->assertEquals($mfgInvoice1->id, $affected[0]->invoice_id);
        $this->assertEquals($mfgInvoice2->id, $affected[1]->invoice_id);
    }

    public function test_chronological_ordering_by_date_and_time(): void
    {
        $rawMaterial = Item::factory()->create();

        $mfgInvoice1 = OperHead::factory()->create([
            'pro_date' => '2024-01-05',
            'pro_type' => 59,
            'isdeleted' => 0,
            'created_at' => '2024-01-05 10:00:00',
        ]);

        $mfgInvoice2 = OperHead::factory()->create([
            'pro_date' => '2024-01-05',
            'pro_type' => 59,
            'isdeleted' => 0,
            'created_at' => '2024-01-05 08:00:00',
        ]);

        $mfgInvoice3 = OperHead::factory()->create([
            'pro_date' => '2024-01-03',
            'pro_type' => 59,
            'isdeleted' => 0,
            'created_at' => '2024-01-03 15:00:00',
        ]);

        foreach ([$mfgInvoice1, $mfgInvoice2, $mfgInvoice3] as $invoice) {
            OperationItems::create([
                'pro_id' => $invoice->id,
                'item_id' => $rawMaterial->id,
                'qty_out' => 10,
                'qty_in' => 0,
                'detail_value' => 100,
                'is_stock' => 1,
            ]);
        }

        $affected = $this->handler->findAffectedManufacturingInvoices(
            [$rawMaterial->id],
            '2024-01-01'
        );

        $this->assertCount(3, $affected);
        $this->assertEquals($mfgInvoice3->id, $affected[0]->invoice_id);
        $this->assertEquals($mfgInvoice2->id, $affected[1]->invoice_id);
        $this->assertEquals($mfgInvoice1->id, $affected[2]->invoice_id);
    }
}
