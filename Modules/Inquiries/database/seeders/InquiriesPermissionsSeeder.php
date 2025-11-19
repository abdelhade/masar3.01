<?php

namespace Modules\Inquiries\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;

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
                'Contacts'
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

        // Note: Permissions are assigned directly to users via model_has_permissions table
        // Roles are not used for permission assignment
    }
}
