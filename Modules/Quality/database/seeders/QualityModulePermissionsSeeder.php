<?php

namespace Modules\Quality\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;

class QualityModulePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $groupedPermissions = [
            'quality' => [
                'quality',
                'inspections',
                'standards',
                'ncr',
                'capa',
                'batches',
                'rateSuppliers',
                'certificates',
                'audits',
                        ],
        ];

        $actions = ['view', 'create', 'edit', 'delete', 'print'];

        foreach ($groupedPermissions as $category => $permissions) {
            foreach ($permissions as $basePermission) {
                foreach ($actions as $action) {
                    $fullName = "$action $basePermission";

                    Permission::firstOrCreate(
                        ['name' => $fullName, 'guard_name' => 'web'],
                        ['category' => $category, 'option_type' => '1']
                    );
                }
            }
        }
    }
}
