<?php

namespace Modules\Checks\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Accounts\Models\AccHead;
use Modules\Branches\Models\Branch;
use Modules\Checks\Models\Check;
use Tests\TestCase;

class CheckManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_checks_index(): void
    {
        $user = \App\Models\User::factory()->create();
        Check::factory()->count(5)->create();

        $response = $this->actingAs($user)->get(route('checks.incoming'));

        $response->assertStatus(200);
        $response->assertViewIs('checks::index');
    }

    public function test_can_create_incoming_check(): void
    {
        $user = \App\Models\User::factory()->create();
        $account = AccHead::factory()->create(['code' => '1103001']);
        $portfolio = AccHead::factory()->create(['code' => '110501']);
        $branch = Branch::factory()->create();

        $response = $this->actingAs($user)->get(route('checks.incoming.create'));

        $response->assertStatus(200);
        $response->assertViewIs('checks::create');
    }

    public function test_can_filter_checks_by_status(): void
    {
        $user = \App\Models\User::factory()->create();
        Check::factory()->create(['status' => Check::STATUS_PENDING]);
        Check::factory()->create(['status' => Check::STATUS_CLEARED]);

        $response = $this->actingAs($user)
            ->get(route('checks.incoming', ['status' => Check::STATUS_PENDING]));

        $response->assertStatus(200);
    }

    public function test_can_export_checks(): void
    {
        $user = \App\Models\User::factory()->create();
        Check::factory()->count(5)->create();

        $response = $this->actingAs($user)->get(route('checks.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
