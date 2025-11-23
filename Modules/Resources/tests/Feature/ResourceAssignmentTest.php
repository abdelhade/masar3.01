<?php

namespace Modules\MyResources\Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use Modules\MyResources\Models\Resource;
use Modules\MyResources\Models\ResourceAssignment;
use Modules\MyResources\Models\ResourceCategory;
use Modules\MyResources\Models\ResourceType;
use Modules\MyResources\Models\ResourceStatus;
use Modules\MyResources\Enums\ResourceAssignmentStatus;
use Modules\Branches\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResourceAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Resource $resource;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
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

        $this->resource = Resource::create([
            'name' => 'Test Resource',
            'resource_category_id' => $category->id,
            'resource_type_id' => $type->id,
            'resource_status_id' => $status->id,
            'branch_id' => $branch->id,
        ]);

        $this->project = Project::create([
            'name' => 'Test Project',
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'status' => 'in_progress',
            'branch_id' => $branch->id,
        ]);
    }

    public function test_can_assign_resource_to_project(): void
    {
        $assignment = ResourceAssignment::create([
            'resource_id' => $this->resource->id,
            'project_id' => $this->project->id,
            'assigned_by' => $this->user->id,
            'start_date' => now(),
            'end_date' => now()->addDays(10),
            'status' => ResourceAssignmentStatus::ACTIVE,
            'assignment_type' => 'current',
        ]);

        $this->assertDatabaseHas('resource_assignments', [
            'resource_id' => $this->resource->id,
            'project_id' => $this->project->id,
        ]);

        $this->assertInstanceOf(Resource::class, $assignment->resource);
        $this->assertInstanceOf(Project::class, $assignment->project);
    }

    public function test_resource_can_have_multiple_assignments(): void
    {
        $project2 = Project::create([
            'name' => 'Test Project 2',
            'start_date' => now()->addDays(15),
            'end_date' => now()->addDays(45),
            'status' => 'pending',
            'branch_id' => $this->resource->branch_id,
        ]);

        ResourceAssignment::create([
            'resource_id' => $this->resource->id,
            'project_id' => $this->project->id,
            'assigned_by' => $this->user->id,
            'start_date' => now(),
            'end_date' => now()->addDays(10),
            'status' => ResourceAssignmentStatus::ACTIVE,
            'assignment_type' => 'current',
        ]);

        ResourceAssignment::create([
            'resource_id' => $this->resource->id,
            'project_id' => $project2->id,
            'assigned_by' => $this->user->id,
            'start_date' => now()->addDays(15),
            'end_date' => now()->addDays(25),
            'status' => ResourceAssignmentStatus::SCHEDULED,
            'assignment_type' => 'upcoming',
        ]);

        $this->assertEquals(2, $this->resource->assignments()->count());
    }
}

