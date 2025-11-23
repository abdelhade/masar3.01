<?php

namespace Modules\Checks\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;


class ChecksPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define permissions structure for Checks module
        $groupedPermissions = [
            'Accounts' => [
                'Checks',
            ],
        ];

        // Standard actions for checks
        $actions = ['view', 'create', 'edit', 'delete', 'print'];

        // Create permissions
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

        // Additional special permissions for checks
        $specialPermissions = [
            'filter Checks',
            'mark Checks as bounced',
            'cancel Checks',
            'approve Checks',
            'export Checks',
        ];

        foreach ($specialPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['category' => 'Accounts']
            );
        }

        // Note: Permissions are assigned directly to users via model_has_permissions table
        // Roles are not used for permission assignment

        $this->command->info('Checks permissions created successfully!');
    }
}
