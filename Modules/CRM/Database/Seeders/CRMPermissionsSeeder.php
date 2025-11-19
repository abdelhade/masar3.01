<?php

namespace Modules\CRM\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;

class CRMPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // مصفوفة المجموعات مع العناصر داخل كل مجموعة
        $groupedPermissions = [
            'CRM' => [
                'Activities',
                'Leads',
                'Lead Statuses',
                'Lead Sources',
                'Chance Sources',
                'Client Types',
                'Client Contacts',
                'Task Types',
                'Tasks',
                'CRM Statistics',
            ],
        ];

        // الأفعال القياسية
        $actions = ['view', 'create', 'edit', 'delete', 'print'];

        // إنشاء الصلاحيات إن لم تكن موجودة
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

        // Note: Permissions are assigned directly to users via model_has_permissions table
        // Roles are not used for permission assignment
    }
}
