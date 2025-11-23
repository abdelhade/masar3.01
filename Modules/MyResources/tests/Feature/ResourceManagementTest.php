<?php

namespace Modules\MyResources\Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Modules\MyResources\Models\Resource;
use Modules\MyResources\Models\ResourceCategory;
use Modules\MyResources\Models\ResourceType;
use Modules\MyResources\Models\ResourceStatus;
use Modules\Branches\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResourceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected ResourceCategory $category;
    protected ResourceType $type;
    protected ResourceStatus $status;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        
        $this->branch = Branch::factory()->create();

        $this->category = ResourceCategory::create([
            'name' => 'Machinery',
            'name_ar' => 'معدات',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->type = ResourceType::create([
            'resource_category_id' => $this->category->id,
            'name' => 'Excavator',
            'name_ar' => 'حفارة',
            'is_active' => true,
        ]);

        $this->status = ResourceStatus::create([
            'name' => 'Available',
            'name_ar' => 'متاح',
            'color' => 'success',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_can_create_resource(): void
    {
        $this->actingAs($this->user);

        $data = [
            'name' => 'Test Resource',
            'resource_category_id' => $this->category->id,
            'resource_type_id' => $this->type->id,
            'resource_status_id' => $this->status->id,
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ];

        $response = $this->post(route('myresources.store'), $data);

        $response->assertRedirect(route('myresources.index'));
        $this->assertDatabaseHas('resources', ['name' => 'Test Resource']);
    }

    public function test_resource_code_is_generated_automatically(): void
    {
        $resource = Resource::create([
            'name' => 'Test Resource',
            'resource_category_id' => $this->category->id,
            'resource_type_id' => $this->type->id,
            'resource_status_id' => $this->status->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->assertNotNull($resource->code);
        $this->assertStringStartsWith('RES-', $resource->code);
    }

    public function test_can_update_resource(): void
    {
        $this->actingAs($this->user);

        $resource = Resource::create([
            'name' => 'Old Name',
            'resource_category_id' => $this->category->id,
            'resource_type_id' => $this->type->id,
            'resource_status_id' => $this->status->id,
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->put(route('myresources.update', $resource), [
            'name' => 'New Name',
            'resource_category_id' => $this->category->id,
            'resource_type_id' => $this->type->id,
            'resource_status_id' => $this->status->id,
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('myresources.index'));
        $this->assertDatabaseHas('resources', ['name' => 'New Name']);
    }

    public function test_can_delete_resource(): void
    {
        $this->actingAs($this->user);

        $resource = Resource::create([
            'name' => 'Test Resource',
            'resource_category_id' => $this->category->id,
            'resource_type_id' => $this->type->id,
            'resource_status_id' => $this->status->id,
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->delete(route('myresources.destroy', $resource));

        $response->assertRedirect(route('myresources.index'));
        $this->assertDatabaseMissing('resources', ['id' => $resource->id]);
    }

    public function test_resource_relationships_work_correctly(): void
    {
        $resource = Resource::create([
            'name' => 'Test Resource',
            'resource_category_id' => $this->category->id,
            'resource_type_id' => $this->type->id,
            'resource_status_id' => $this->status->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->assertInstanceOf(ResourceCategory::class, $resource->category);
        $this->assertInstanceOf(ResourceType::class, $resource->type);
        $this->assertInstanceOf(ResourceStatus::class, $resource->status);
        $this->assertInstanceOf(Branch::class, $resource->branch);
    }
}

