<?php

namespace Modules\POS\database\seeders;

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
    }
}
