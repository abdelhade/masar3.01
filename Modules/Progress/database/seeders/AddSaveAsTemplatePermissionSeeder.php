<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddSaveAsTemplatePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Add missing permissions
        $permissions = [
            'projects-save-as-template',
            'projects-copy',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web',
            ]);
        }

        // Give permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            foreach ($permissions as $permName) {
                if (!$adminRole->hasPermissionTo($permName)) {
                    $adminRole->givePermissionTo($permName);
                }
            }
        }

        // Give permissions to manager role as well
        $managerRole = Role::where('name', 'manager')->first();
        if ($managerRole) {
            foreach ($permissions as $permName) {
                if (!$managerRole->hasPermissionTo($permName)) {
                    $managerRole->givePermissionTo($permName);
                }
            }
        }
    }
}