<?php

namespace Modules\Rentals\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;
use Modules\Authorization\Models\Role;

class RentalsPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // مصفوفة المجموعات مع العناصر داخل كل مجموعة
        $groupedPermissions = [
            'Rentals' => [
                'Buildings',
                'Unit',
                'Leases',
                'Rentals Statistics',
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

        // إنشاء أو جلب الأدوار
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'web']);
        $userRole  = Role::firstOrCreate(['name' => 'user'],  ['guard_name' => 'web']);

        // إسناد جميع صلاحيات CRM للـ admin
        $rentalsCategories = array_keys($groupedPermissions);
        $adminPermissions = Permission::whereIn('category', $rentalsCategories)->get();
        $adminRole->givePermissionTo($adminPermissions);

        // إسناد صلاحيات العرض فقط للـ user
        $userViewPermissions = Permission::whereIn('category', $rentalsCategories)
            ->where(function ($q) {
                $q->where('name', 'like', 'view %');
            })->get();

        $userRole->givePermissionTo($userViewPermissions);
    }
}
