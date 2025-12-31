<?php

declare(strict_types=1);

namespace Modules\Accounts\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class AccountsPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // مصفوفة المجموعات مع العناصر داخل كل مجموعة
        $groupedPermissions = [
            'Accounts' => [
                // أنواع الحسابات الرئيسية
                'Clients',
                'Suppliers',
                'Funds',
                'Banks',
                'Employees',
                'warhouses',
                'Expenses',
                'Revenues',
                'various_creditors',
                'various_debtors',
                'partners',
                'current_partners',
                'assets',
                'rentables',
                // التقارير والإدارة
                'account-movement-report',
                'balance-sheet',
                'start-balance-management',
                // محافظ الشيكات
                'check-portfolios-incoming',
                'check-portfolios-outgoing',
                'Check Portfolios Incoming',
                'Check Portfolios Outgoing',
                'Checks',
            ],
            'Accounts-mangment' => [
                // إدارة الحسابات
                'journals',
                'multi-journals',
                'inventory-balance',
                'opening-balance-accounts',
                'accounts-balance-sheet',
            ],
        ];

        // الأفعال القياسية
        $actions = ['view', 'create', 'edit', 'delete', 'print'];

        // إنشاء أو تحديث الصلاحيات
        foreach ($groupedPermissions as $category => $items) {
            foreach ($items as $base) {
                foreach ($actions as $action) {
                    $fullName = "$action $base";
                    $permission = Permission::firstOrCreate(
                        ['name' => $fullName, 'guard_name' => 'web'],
                        ['category' => $category]
                    );

                    // تحديث الفئة إذا كانت موجودة بالفعل
                    if ($permission->category !== $category) {
                        $permission->update(['category' => $category]);
                    }
                }
            }
        }

        // صلاحيات إضافية للشيكات
        $checksSpecialPermissions = [
            'approve Checks',
            'cancel Checks',
            'export Checks',
            'filter Checks',
            'mark Checks as bounced',
        ];

        foreach ($checksSpecialPermissions as $permName) {
            Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web'],
                ['category' => 'Accounts']
            );
        }
    }
}
