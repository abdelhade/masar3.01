<?php

namespace Modules\MyResources\Tests\Unit;

use Tests\TestCase;
use Modules\MyResources\Models\Resource;
use Modules\MyResources\Models\ResourceCategory;
use Modules\MyResources\Models\ResourceType;
use Modules\MyResources\Models\ResourceStatus;
use Modules\Branches\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResourceModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_code_is_generated_with_correct_format(): void
    {
        $branch = Branch::factory()->create();

        $category = ResourceCategory::create([
            'name' => 'Machinery',
            'name_ar' => 'معدات',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $type = ResourceType::create([
            'resource_category_id' => $category->id,
            'name' => 'Excavator',
            'name_ar' => 'حفارة',
            'is_active' => true,
        ]);

        $status = ResourceStatus::create([
            'name' => 'Available',
            'name_ar' => 'متاح',
            'color' => 'success',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $resource = Resource::create([
            'name' => 'Test Resource',
            'resource_category_id' => $category->id,
            'resource_type_id' => $type->id,
            'resource_status_id' => $status->id,
            'branch_id' => $branch->id,
        ]);

        $this->assertMatchesRegularExpression('/^RES-\d{5}$/', $resource->code);
    }

    public function test_search_scope_works_correctly(): void
    {
        $branch = Branch::factory()->create();

        $category = ResourceCategory::create([
            'name' => 'Machinery',
            'name_ar' => 'معدات',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $type = ResourceType::create([
            'resource_category_id' => $category->id,
            'name' => 'Excavator',
            'name_ar' => 'حفارة',
            'is_active' => true,
        ]);

        $status = ResourceStatus::create([
            'name' => 'Available',
            'name_ar' => 'متاح',
            'color' => 'success',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Resource::create([
            'name' => 'Special Resource',
            'resource_category_id' => $category->id,
            'resource_type_id' => $type->id,
            'resource_status_id' => $status->id,
            'branch_id' => $branch->id,
        ]);

        Resource::create([
            'name' => 'Another Resource',
            'resource_category_id' => $category->id,
            'resource_type_id' => $type->id,
            'resource_status_id' => $status->id,
            'branch_id' => $branch->id,
        ]);

        $results = Resource::search('Special')->get();

        $this->assertEquals(1, $results->count());
        $this->assertEquals('Special Resource', $results->first()->name);
    }
}

