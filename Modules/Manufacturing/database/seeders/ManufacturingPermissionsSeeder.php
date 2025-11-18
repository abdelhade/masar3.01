<?php

namespace Modules\Manufacturing\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ManufacturingPermissionsSeeder extends Seeder
{
    public function run()
    {
        $groupedPermissions = [
            'Manufacturing' => [
                'Manufacturing Orders',
                'Manufacturing Invoices',
                'Manufacturing Stages'
            ],
        ];

        $actions = ['view', 'create', 'edit', 'delete', 'approve', 'print'];

        foreach ($groupedPermissions as $category => $permissions) {
            foreach ($permissions as $basePermission) {
                foreach ($actions as $action) {
                    $fullName = "$action $basePermission";
                    Permission::firstOrCreate(
                        ['name' => $fullName, 'guard_name' => 'web'],
                        ['category' => $category]
                    );
                }
            }
        }

        // Roles setup (يمكنك تعديل الأدوار حسب احتياجك)
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user'], ['guard_name' => 'web']);

        $manufacturingPermissions = Permission::where('category', 'Manufacturing')->get();
        $adminRole->givePermissionTo($manufacturingPermissions);

        // المستخدم العادي يحصل فقط على صلاحية العرض والطباعة
        $userPermissions = Permission::where('category', 'Manufacturing')
            ->whereIn('name', [
                'view Manufacturing Orders',
                'print Manufacturing Orders',
                'view Manufacturing Invoices',
                'print Manufacturing Invoices',
                'view Manufacturing Stages',
                'print Manufacturing Stages',
            ])->get();
        $userRole->givePermissionTo($userPermissions);
    }
}
