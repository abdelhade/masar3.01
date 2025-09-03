<?php

namespace Modules\POS\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class POSPermissionsSeeder extends Seeder
{
    public function run()
    {
        // إنشاء صلاحيات POS
        $posPermissions = [
            'استخدام نظام نقاط البيع' => 'الوصول إلى نظام نقاط البيع الرئيسي',
            'إنشاء معاملة نقاط البيع' => 'إنشاء معاملات بيع جديدة في POS',
            'عرض معاملة نقاط البيع' => 'عرض تفاصيل معاملات POS',
            'طباعة فاتورة نقاط البيع' => 'طباعة فواتير POS',
            'حذف معاملة نقاط البيع' => 'حذف معاملات POS',
            'عرض تقارير نقاط البيع' => 'الوصول إلى تقارير POS',
            'إدارة إعدادات نقاط البيع' => 'تعديل إعدادات نظام POS',
        ];

        foreach ($posPermissions as $name => $description) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web'
            ]);
        }

        // إنشاء أدوار POS
        $posRoles = [
            'كاشير' => [
                'description' => 'موظف نقاط البيع - صلاحيات أساسية',
                'permissions' => [
                    'استخدام نظام نقاط البيع',
                    'إنشاء معاملة نقاط البيع',
                    'عرض معاملة نقاط البيع',
                    'طباعة فاتورة نقاط البيع',
                ]
            ],
            'مشرف نقاط البيع' => [
                'description' => 'مشرف نقاط البيع - صلاحيات متقدمة',
                'permissions' => [
                    'استخدام نظام نقاط البيع',
                    'إنشاء معاملة نقاط البيع',
                    'عرض معاملة نقاط البيع',
                    'طباعة فاتورة نقاط البيع',
                    'حذف معاملة نقاط البيع',
                    'عرض تقارير نقاط البيع',
                ]
            ],
            'مدير نقاط البيع' => [
                'description' => 'مدير نقاط البيع - صلاحيات كاملة',
                'permissions' => [
                    'استخدام نظام نقاط البيع',
                    'إنشاء معاملة نقاط البيع',
                    'عرض معاملة نقاط البيع',
                    'طباعة فاتورة نقاط البيع',
                    'حذف معاملة نقاط البيع',
                    'عرض تقارير نقاط البيع',
                    'إدارة إعدادات نقاط البيع',
                ]
            ]
        ];

        foreach ($posRoles as $roleName => $roleData) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);

            // إضافة الصلاحيات للدور
            $permissions = Permission::whereIn('name', $roleData['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        $this->command->info('تم إنشاء صلاحيات وأدوار POS بنجاح!');
    }
}
