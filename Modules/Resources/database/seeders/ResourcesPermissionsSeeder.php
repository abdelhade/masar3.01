<?php

namespace Modules\MyResources\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;

class ResourcesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $groupedPermissions = [
            'MyResources Management' => [
                'MyResources',
                'Resource Assignments',
                'Resource Categories',
                'Resource Types',
                'Resource Statuses',
                'MyResources Dashboard',
                'MyResources Reports',
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

        // Special permissions
        $specialPermissions = [
            'change Resource Status',
            'view Resource History',
            'assign MyResources to Projects',
        ];

        foreach ($specialPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['category' => 'MyResources Management']
            );
        }

        $this->command->info('MyResources permissions created successfully!');
    }
}

