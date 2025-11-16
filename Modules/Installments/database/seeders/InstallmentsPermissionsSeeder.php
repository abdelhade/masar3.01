<?php

namespace Modules\Installments\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;
use Modules\Authorization\Models\Role;

class InstallmentsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Define the permission structure for the Installments module
        $groupedPermissions = [
            'Installments' => [
                'Installment Plans',
                'Overdue Installments',
            ],
        ];

        // Define the standard actions for each permission
        $actions = ['view', 'create', 'edit', 'delete', 'print'];

        // Create the permissions
        foreach ($groupedPermissions as $category => $permissions) {
            foreach ($permissions as $basePermission) {
                foreach ($actions as $action) {
                    $fullName = "$action $basePermission";

                    Permission::firstOrCreate(
                        ['name' => $fullName, 'guard_name' => 'web'],
                        ['category' => $category]
                    );
                }
            }
        }

        // Find the admin and user roles, or create them if they don't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user'], ['guard_name' => 'web']);

        // Assign all Installments permissions to the admin role
        $installmentPermissions = Permission::where('category', 'Installments')->get();
        $adminRole->givePermissionTo($installmentPermissions);

        // Assign only "view" permissions to the user role
        $userViewPermissions = Permission::where('category', 'Installments')
            ->where('name', 'like', 'view%')
            ->get();

        $userRole->givePermissionTo($userViewPermissions);
    }
}
