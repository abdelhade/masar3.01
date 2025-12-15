<?php

declare(strict_types=1);

namespace Modules\Fleet\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;

class FleetPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $groupedPermissions = [
            'Fleet' => [
                'Fleet Dashboard',
                'Vehicle Types',
                'Vehicles',
                'Trips',
                'Fuel Records',
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
    }
}
