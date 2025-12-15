<?php

namespace Modules\Checks\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Checks\Models\Check;
use Tests\TestCase;

class CheckOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_check_details(): void
    {
        $user = \App\Models\User::factory()->create();
        $check = Check::factory()->create();

        $response = $this->actingAs($user)
            ->getJson(route('checks.show', $check));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'check_number',
            'bank_name',
            'amount',
        ]);
    }

    public function test_can_delete_check(): void
    {
        $user = \App\Models\User::factory()->create();
        $check = Check::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson(route('checks.destroy', $check));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('checks', ['id' => $check->id]);
    }

    public function test_can_get_statistics(): void
    {
        $user = \App\Models\User::factory()->create();
        Check::factory()->count(10)->create();

        $response = $this->actingAs($user)
            ->getJson(route('checks.statistics', ['period' => 'month']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total',
            'pending',
            'cleared',
            'bounced',
            'total_amount',
            'pending_amount',
            'cleared_amount',
        ]);
    }
}
