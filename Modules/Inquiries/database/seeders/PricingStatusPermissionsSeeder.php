<?php

namespace Modules\Inquiries\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;

class PricingStatusPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $groupedPermissions = [
            'Inquiries' => [
                'Pricing Statuses',
            ],
        ];

        $actions = ['view', 'create', 'edit', 'delete', 'print'];

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
