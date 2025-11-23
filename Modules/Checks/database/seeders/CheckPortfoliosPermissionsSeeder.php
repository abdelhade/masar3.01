<?php

namespace Modules\Checks\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;


class CheckPortfoliosPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Define permissions structure for Check Portfolios
        $groupedPermissions = [
            'Accounts' => [
                'Check Portfolios Incoming',
                'Check Portfolios Outgoing',
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

        $this->command?->info('Check portfolios permissions created successfully!');
    }
}
