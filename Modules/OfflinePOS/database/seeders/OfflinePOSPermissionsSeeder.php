<?php

namespace Modules\OfflinePOS\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;

class OfflinePOSPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $groupedPermissions = [
            'Offline POS' => [
                'Offline POS System',
                'Offline POS Transactions',
                'Offline POS Reports',
                'Offline POS Sync Status',
                'Offline POS Return Invoice',
                'Offline POS Settings',
                'Offline POS Invoice',
                'Offline POS Thermal',
                'Offline POS Data',
                'Offline POS Local Data',
                'Offline POS Reports Advanced',
            ],
        ];

        $actions = ['view', 'create', 'edit', 'delete', 'print'];

        foreach ($groupedPermissions as $category => $permissions) {
            foreach ($permissions as $basePermission) {
                // إضافة صلاحيات خاصة لبعض العناصر
                $currentActions = $actions;

                if ($basePermission === 'Offline POS Transactions') {
                    $currentActions = array_merge($actions, ['sync', 'export']);
                }

                if ($basePermission === 'Offline POS Data') {
                    $currentActions = array_merge($actions, ['download']);
                }

                if ($basePermission === 'Offline POS Local Data') {
                    $currentActions = array_merge($actions, ['clear']);
                }

                foreach ($currentActions as $action) {
                    $fullName = "$action $basePermission";

                    Permission::firstOrCreate(
                        ['name' => $fullName, 'guard_name' => 'web'],
                        ['category' => $category]
                    );
                }
            }
        }
    }
}
