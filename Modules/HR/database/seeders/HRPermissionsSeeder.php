<?php

declare(strict_types=1);

namespace Modules\HR\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class HRPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // مصفوفة المجموعات مع العناصر داخل كل مجموعة
        $groupedPermissions = [
            'HR' => [
                'Departments',
                'Jobs',
                'Countries',
                'States',
                'Cities',
                'Towns',
                'Shifts',
                'Employees',
                'KPIs',
                'Employee Evaluations',
                'Contracts',
                'Contract Types',
                'Attendances',
                'Attendance Processing',
                'Attendance Approvals',
                'Attendance Rejections',
                'Leave Balances',
                'Leave Requests',
                'Leave Approvals',
                'Leave Rejections',
                'Leave Types',
                'Leave Categories',
                'Leaves',
                'Holidays',
                'Covenants',
                'Errands',
                'Work Permissions',
                'CVs',
                'HR Settings',
                'Flexible Salary Processing',
                'Flexible Salary Approvals',
                'Flexible Salary Rejections',
                'Flexible Salary Components',
                // السلف والخصومات والمكافآت
                'Employee Advances',
                'Employee Deductions',
                'Employee Rewards',
                'Employee Advance Approvals',
                'Employee Deduction Approvals',
                'Employee Reward Approvals',
                'Employee Advance Rejections',
                'Employee Deduction Rejections',
                'Employee Reward Rejections',
                'Mobile-fingerprint',
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

        // إصلاح الصلاحيات القديمة: KPIS -> KPIs
        // $oldKpisPermissions = Permission::where('name', 'like', '% KPIS')->get();
        // foreach ($oldKpisPermissions as $oldPerm) {
        //     $newName = str_replace(' KPIS', ' KPIs', $oldPerm->name);
        //     $existingPerm = Permission::where('name', $newName)->where('guard_name', 'web')->first();

        //     if ($existingPerm) {
        //         // إذا كانت الصلاحية الجديدة موجودة، احذف القديمة
        //         $oldPerm->delete();
        //     } else {
        //         // إذا لم تكن موجودة، حدث الاسم
        //         $oldPerm->update(['name' => $newName, 'category' => 'HR']);
        //     }
        // }

        // Note: Permissions are assigned directly to users via model_has_permissions table
        // Roles are not used for permission assignment
    }
}
