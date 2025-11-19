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

        // Note: Permissions are assigned directly to users via model_has_permissions table
        // Roles are not used for permission assignment
    }
}
