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
                'Client Categories',
                'Clients',
                'Client Types',
                'Client Contacts',
                'Task Types',
                'Tasks',
                'Tickets',
                'Returns',
                'CRM Statistics',
            ],
        ];

        // الأفعال القياسية
        $actions = ['view', 'create', 'edit', 'delete', 'print'];

        // صلاحيات خاصة للعملاء
        $clientsActions = ['view', 'create', 'edit', 'delete', 'print', 'import'];

        // إنشاء الصلاحيات إن لم تكن موجودة
        foreach ($groupedPermissions as $category => $items) {
            foreach ($items as $base) {
                // استخدام صلاحيات خاصة للعملاء
                $currentActions = ($base === 'Clients') ? $clientsActions : $actions;

                foreach ($currentActions as $action) {
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
