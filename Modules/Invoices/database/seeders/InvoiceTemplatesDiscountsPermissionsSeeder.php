<?php

namespace Modules\Invoices\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InvoiceTemplatesDiscountsPermissionsSeeder extends Seeder
{
    public function run()
    {
        $groupedPermissions = [
            'Invoice Templates' => [
                'Invoice Templates',
                'Allowed Discounts',
                'Earned Discounts'
            ],
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

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

        // Give all permissions to admin
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'web']);
        $templatePermissions = Permission::where('category', 'Invoice Templates')->get();
        $adminRole->givePermissionTo($templatePermissions);

        // Give view permission only to regular user
        $userRole = Role::firstOrCreate(['name' => 'user'], ['guard_name' => 'web']);
        $userViewPermissions = Permission::where('category', 'Invoice Templates')
            ->where('name', 'like', 'view Invoice Templates')
            ->get();
        $userRole->givePermissionTo($userViewPermissions);
    }
}
