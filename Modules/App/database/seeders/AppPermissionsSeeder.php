<?php

declare(strict_types=1);

namespace Modules\App\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class AppPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $groupedPermissions = [
            'app' => [
                'Excel Import',
            ],
        ];

        $actions = ['view', 'create', 'import', 'export'];

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
            'preview Excel Import',
            'download Excel Template',
        ];

        foreach ($specificPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['category' => 'app']
            );
        }
    }
}
