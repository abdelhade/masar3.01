<?php

namespace Modules\Quality\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
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

                    $data = ['category' => $category];

                    // Add optional columns only if they exist in the table
                    if (Schema::hasColumn('permissions', 'option_type')) {
                        $data['option_type'] = '1';
                    }

                    Permission::firstOrCreate(
                        ['name' => $fullName, 'guard_name' => 'web'],
                        $data
                    );
                }
            }
        }
    }
}
