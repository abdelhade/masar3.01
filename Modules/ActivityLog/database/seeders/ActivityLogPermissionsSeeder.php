<?php

declare(strict_types=1);

namespace Modules\ActivityLog\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;

class ActivityLogPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $groupedPermissions = [
            'permissions' => [
                'activity-logs',
            ],
        ];

        $actions = ['view'];

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
