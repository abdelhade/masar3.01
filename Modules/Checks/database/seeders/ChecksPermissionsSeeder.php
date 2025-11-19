<?php

namespace Modules\Checks\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;


class ChecksPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions for checks management
        $permissions = [
            'عرض الشيكات',
            'إضافة الشيكات',
            'تعديل الشيكات',
            'حذف الشيكات',
            'تصفية الشيكات',
            'تمييز الشيكات كمرتدة',
            'إلغاء الشيكات',
            'اعتماد الشيكات',
            'تصدير الشيكات',
            // حافظات الأوراق المالية
            'عرض حافظات أوراق القبض',
            'إضافة حافظات أوراق القبض',
            'تعديل حافظات أوراق القبض',
            'حذف حافظات أوراق القبض',
            'عرض حافظات أوراق الدفع',
            'إضافة حافظات أوراق الدفع',
            'تعديل حافظات أوراق الدفع',
            'حذف حافظات أوراق الدفع',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Note: Permissions are assigned directly to users via model_has_permissions table
        // Roles are not used for permission assignment

        $this->command->info('Checks permissions created successfully!');
    }
}
