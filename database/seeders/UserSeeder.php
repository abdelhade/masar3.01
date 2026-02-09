<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Authorization\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('12345678'),
            ]
        );

        // Give all permissions with 'web' guard to user (filter to avoid guard mismatch)
        // Users use 'web' guard by default, so only assign permissions with matching guard
        $webPermissions = Permission::where('guard_name', 'web')->get();
        
        if ($webPermissions->isNotEmpty()) {
            $user->syncPermissions($webPermissions);
        }
    }
}
