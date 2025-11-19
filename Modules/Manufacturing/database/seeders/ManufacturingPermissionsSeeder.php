<?php

namespace Modules\Manufacturing\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ManufacturingPermissionsSeeder extends Seeder
{
    public function run()
    {
        $groupedPermissions = [
            'Manufacturing' => [
                'Manufacturing Orders',
                'Manufacturing Invoices',
                'Manufacturing Stages'
            ],
        ];

        $actions = ['view', 'create', 'edit', 'delete', 'approve', 'print'];

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
