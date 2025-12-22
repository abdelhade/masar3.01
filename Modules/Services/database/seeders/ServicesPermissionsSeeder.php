<?php

declare(strict_types=1);

namespace Modules\Services\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ServicesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $groupedPermissions = [
            'services' => [
                'Services Dashboard',
                'Services',
                'Service Bookings',
                'Service Types',
                'Service Units',
                'Service Invoices',
                'Service Categories',
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
            'complete Service Bookings',
            'cancel Service Bookings',
            'toggle Services',
            'view Service Available Slots',
        ];

        foreach ($specificPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['category' => 'services']
            );
        }
    }
}
