<?php

declare(strict_types=1);

namespace Modules\Notifications\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class NotificationsPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $groupedPermissions = [
            'notifications' => [
                'Notifications',
            ],
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

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
            'mark Notification as Read',
            'mark All Notifications as Read',
            'view Notification Count',
        ];

        foreach ($specificPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['category' => 'notifications']
            );
        }
    }
}
