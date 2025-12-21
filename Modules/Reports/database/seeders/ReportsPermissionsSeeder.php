<?php

declare(strict_types=1);

namespace Modules\Reports\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ReportsPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $groupedPermissions = [
            'reports' => [
                'Reports Dashboard',
                'General Reports',
                'Financial Reports',
                'Sales Reports',
                'Inventory Reports',
                'HR Reports',
                'Project Reports',
            ],
        ];

        $actions = ['view', 'create', 'export', 'print'];

        foreach ($groupedPermissions as $category => $items) {
            foreach ($items as $base) {
                foreach ($actions as $action) {
                    $fullName = "$action $base";
                    Permission::firstOrCreate(
                        ['name' => $fullName, 'guard_name' => 'web'],
                        ['category' => $category]
                    );
                }
            }
        }
    }
}
