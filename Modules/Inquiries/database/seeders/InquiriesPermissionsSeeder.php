<?php

namespace Modules\Inquiries\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;
use Modules\Authorization\Models\Role;

class InquiriesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $groupedPermissions = [
            'Inquiries' => [
                'Inquiries',
                'Difficulty Matrix',
                'My Drafts',
                'Inquiries Source',
                'Work Types',
                'Documents',
                'Quotation Info',
                'Project Size',
                'Inquiries Roles',
                'Inquiries Statistics',
            ],
        ];

        $actions = ['view', 'create', 'edit', 'delete', 'print'];

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

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user'], ['guard_name' => 'web']);

        $inquiryPermissions = Permission::where('category', 'Inquiries')->get();
        $adminRole->givePermissionTo($inquiryPermissions);

        $userViewPermissions = Permission::where('category', 'Inquiries')
            ->where('name', 'like', 'View Inquiries')
            ->get();

        $userRole->givePermissionTo($userViewPermissions);
    }
}
