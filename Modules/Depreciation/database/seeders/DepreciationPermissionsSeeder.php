<?php

declare(strict_types=1);

namespace Modules\Depreciation\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class DepreciationPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $groupedPermissions = [
            'depreciation' => [
                'Depreciation Dashboard',
                'Depreciation Items',
                'Depreciation Schedules',
                'Accounts Assets',
            ],
        ];

        $actions = ['view', 'create', 'edit', 'delete', 'print'];

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

        // Additional specific permissions
        $specificPermissions = [
            'view Depreciation Schedule',
            'view Depreciation Report',
            'calculate All Depreciation',
            'sync Depreciation Accounts',
            'generate Depreciation Schedule',
            'export Depreciation Schedule',
            'bulk Process Depreciation Schedule',
        ];

        foreach ($specificPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['category' => 'depreciation']
            );
        }
    }
}
