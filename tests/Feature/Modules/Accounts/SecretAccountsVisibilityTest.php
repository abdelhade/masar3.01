<?php

namespace Tests\Feature\Modules\Accounts;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Accounts\Models\AccHead;
use Modules\Branches\Models\Branch;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SecretAccountsVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithBranch(): array
    {
        $branch = Branch::create([
            'name' => 'Main Branch',
            'code' => 'BR-1',
            'address' => 'Test Address',
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $user->branches()->attach($branch->id);

        return [$user, $branch];
    }

    private function createAccount(array $attributes = []): AccHead
    {
        return AccHead::create(array_merge([
            'code' => '1001',
            'aname' => 'Test Account',
            'is_basic' => 0,
            'secret' => 0,
        ], $attributes));
    }

    public function test_secret_accounts_are_hidden_for_users_without_permission(): void
    {
        [$user, $branch] = $this->createUserWithBranch();

        $this->createAccount([
            'code' => '2001',
            'aname' => 'Visible Account',
            'secret' => 0,
            'branch_id' => $branch->id,
        ]);

        $this->createAccount([
            'code' => '2002',
            'aname' => 'Secret Account',
            'secret' => 1,
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('accounts.index'));

        $response->assertStatus(200);
        $response->assertSee('Visible Account');
        $response->assertDontSee('Secret Account');
    }

    public function test_secret_accounts_are_visible_for_users_with_permission(): void
    {
        [$user, $branch] = $this->createUserWithBranch();

        Permission::create([
            'name' => 'allow_secret_accounts',
            'guard_name' => 'web',
        ]);

        $user->givePermissionTo('allow_secret_accounts');

        $this->createAccount([
            'code' => '3001',
            'aname' => 'Visible Account 2',
            'secret' => 0,
            'branch_id' => $branch->id,
        ]);

        $this->createAccount([
            'code' => '3002',
            'aname' => 'Secret Account 2',
            'secret' => 1,
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('accounts.index'));

        $response->assertStatus(200);
        $response->assertSee('Visible Account 2');
        $response->assertSee('Secret Account 2');
    }
}


