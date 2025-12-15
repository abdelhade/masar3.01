<?php

namespace Modules\Checks\Tests\Feature\Volt;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Modules\Checks\Models\Check;
use Tests\TestCase;

class ChecksManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_checks_management_component(): void
    {
        $user = \App\Models\User::factory()->create();
        Check::factory()->count(5)->create();

        $component = Volt::test('checks::livewire.checks-management')
            ->actingAs($user);

        $component->assertSuccessful();
        $component->assertSee('إدارة الشيكات');
    }

    public function test_can_search_checks(): void
    {
        $user = \App\Models\User::factory()->create();
        Check::factory()->create(['check_number' => '123456']);
        Check::factory()->create(['check_number' => '789012']);

        $component = Volt::test('checks::livewire.checks-management')
            ->actingAs($user)
            ->set('search', '123456');

        $component->assertSee('123456');
        $component->assertDontSee('789012');
    }

    public function test_can_filter_by_status(): void
    {
        $user = \App\Models\User::factory()->create();
        Check::factory()->create(['status' => Check::STATUS_PENDING]);
        Check::factory()->create(['status' => Check::STATUS_CLEARED]);

        $component = Volt::test('checks::livewire.checks-management')
            ->actingAs($user)
            ->set('statusFilter', Check::STATUS_PENDING);

        $component->assertSet('statusFilter', Check::STATUS_PENDING);
    }

    public function test_can_open_modal(): void
    {
        $user = \App\Models\User::factory()->create();

        $component = Volt::test('checks::livewire.checks-management')
            ->actingAs($user)
            ->call('openModal');

        $component->assertSet('showModal', true);
        $component->assertSet('editingCheckId', null);
    }
}
