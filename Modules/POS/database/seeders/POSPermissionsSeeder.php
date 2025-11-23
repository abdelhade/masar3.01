<?php

namespace Modules\POS\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class POSPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Define permissions structure for POS module
        $groupedPermissions = [
            'POS' => [
                'POS System',
                'POS Transactions',
                'POS Reports',
                'POS Settings',
            ],
        ];

        // Standard actions
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

        // Note: Permissions are assigned directly to users via model_has_permissions table
        // Roles are not used for permission assignment

        $this->command->info('POS permissions created successfully!');
    }
}
