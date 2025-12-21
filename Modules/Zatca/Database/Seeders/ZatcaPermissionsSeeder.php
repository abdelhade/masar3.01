<?php

declare(strict_types=1);

namespace Modules\Zatca\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ZatcaPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $groupedPermissions = [
            'zatca' => [
                'Zatca Dashboard',
                'Zatca Invoices',
                'Zatca Settings',
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
            'submit Zatca Invoice',
            'validate Zatca Invoice',
            'view Zatca QR Code',
        ];

        foreach ($specificPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['category' => 'zatca']
            );
        }
    }
}
