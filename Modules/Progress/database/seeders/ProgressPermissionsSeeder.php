<?php

declare(strict_types=1);

namespace Modules\Progress\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ProgressPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // مصفوفة المجموعات مع العناصر داخل كل مجموعة
        $groupedPermissions = [
            'Progress' => [
                'Projects',
                'Project Types',
                'Work Items',
                'Project Templates',
                'Project Items',
                'Daily Progress',
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
                        ['category' => $category, 'option_type' => '1']
                    );

                    // تحديث الفئة و option_type إذا كانت موجودة بالفعل
                    $updateData = [];
                    if ($permission->category !== $category) {
                        $updateData['category'] = $category;
                    }
                    if ($permission->option_type !== '1') {
                        $updateData['option_type'] = '1';
                    }
                    if (! empty($updateData)) {
                        $permission->update($updateData);
                }
            }
        }
        }

        // تحديث الصلاحيات القديمة للمشاريع التي ليس لها فئة
        $oldProjectPermissions = ['view projects', 'create projects', 'edit projects', 'delete projects'];
        foreach ($oldProjectPermissions as $permName) {
            $permission = Permission::where('name', $permName)->where('guard_name', 'web')->first();
            if ($permission) {
                $updateData = [];
                if ($permission->category !== 'Progress') {
                    $updateData['category'] = 'Progress';
                }
                if ($permission->option_type !== '1') {
                    $updateData['option_type'] = '1';
                }
                if (! empty($updateData)) {
                    $permission->update($updateData);
                }
            }
        }
    }
}
