<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

/**
 * IssuesPermissionsSeeder
 * 
 * Creates permissions for issue management system
 */
class IssuesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define issue permissions
        $permissions = [
            'issues-list',
            'issues-create',
            'issues-edit',
            'issues-delete',
            'issues-view',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        // Assign permissions to roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

        // Admin gets all permissions
        $adminRole->givePermissionTo($permissions);

        // Manager gets all issue permissions
        $managerRole->givePermissionTo($permissions);

        // Employee can view and create issues
        $employeeRole->givePermissionTo([
            'issues-list',
            'issues-create',
            'issues-view',
        ]);

        // 👇 إضافة الصلاحيات للمستخدم a@a.com
        $user = User::where('email', 'a@a.com')->first();

        if ($user) {
            $user->givePermissionTo($permissions);
            if ($this->command) {
                $this->command->info('✅ تم إضافة صلاحيات Issues للمستخدم a@a.com');
            } else {
                echo "✅ تم إضافة صلاحيات Issues للمستخدم a@a.com\n";
            }
        } else {
            if ($this->command) {
                $this->command->warn('⚠️ لم يتم العثور على مستخدم بالإيميل a@a.com');
            } else {
                echo "⚠️ لم يتم العثور على مستخدم بالإيميل a@a.com\n";
            }
        }

        $this->command->info('Issue permissions created and assigned to roles successfully!');
    }
}
