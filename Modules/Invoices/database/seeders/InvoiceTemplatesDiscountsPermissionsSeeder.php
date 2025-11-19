<?php

namespace Modules\Invoices\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;


class InvoiceTemplatesDiscountsPermissionsSeeder extends Seeder
{
    public function run()
    {
        $groupedPermissions = [
            'Invoice Templates' => [
                'Invoice Templates',
                'Allowed Discounts',
                'Earned Discounts'
            ],
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

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
