<?php

namespace Modules\POS\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

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

        // Note: Permissions are assigned directly to users via model_has_permissions table
        // Roles are not used for permission assignment

        $this->command->info('تم إنشاء صلاحيات وأدوار POS بنجاح!');
    }
}
