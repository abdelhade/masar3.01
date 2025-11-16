<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InvoicesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $groupedPermissions = [
            'Sales' => [
                'فاتورة مبيعات',
                'مردود مبيعات',
                'امر بيع',
                'عرض سعر لعميل',
                'امر حجز',
                'اتفاقية تسعير',
            ],
            'Purchases' => [
                'فاتورة مشتريات',
                'مردود مشتريات',
                'امر شراء',
                'عرض سعر من مورد',
                'فاتورة خدمة',
                'طلب احتياج',
            ],
            'Inventory' => [
                'فاتورة توالف',
                'امر صرف',
                'امر اضافة',
                'تحويل من مخزن لمخزن',
            ],
        ];

        // تعريف الأفعال القياسية لكل صلاحية
        $actions = ['view', 'create', 'edit', 'delete', 'print'];

        // إنشاء الصلاحيات
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

        // البحث عن دور المدير والمستخدم، أو إنشاؤهم إذا لم يكونوا موجودين
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user'], ['guard_name' => 'web']);

        // منح جميع صلاحيات الفواتير للمدير
        $invoicePermissions = Permission::whereIn('category', ['Sales', 'Purchases', 'Inventory'])->get();
        $adminRole->givePermissionTo($invoicePermissions);

        // منح صلاحيات "view" فقط للمستخدم العادي
        $userViewPermissions = Permission::whereIn('category', ['Sales', 'Purchases', 'Inventory'])
            ->where('name', 'like', 'view%')
            ->get();
        $userRole->givePermissionTo($userViewPermissions);
    }
}
