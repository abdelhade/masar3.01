<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // مسح الكاش
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // الصلاحيات مقسمة حسب الكاتيجوري
        $permissionsByCategory = [
            'users' => ['list', 'create', 'edit', 'delete'],
            'projects' => ['list','view-all','create', 'edit', 'delete', 'view', 'progress' ],
            'employees' => ['list', 'create','permissions', 'edit', 'delete'],
            'dailyprogress' => ['list', 'create', 'edit', 'delete'],
            'items' => ['list', 'create', 'edit', 'delete'],
            'dashboard' => ['view'],
            'project-templates' => ['list', 'create', 'edit', 'delete' , 'view'],
            'project-types' => ['list', 'create', 'edit', 'delete'],
            'activity-logs' => ['list', 'view', 'delete'],
            'recycle-bin' => ['list', 'restore', 'permanent-delete'],
            'backup' => ['create', 'view'],
        ];

        // إنشاء الصلاحيات مع guard_name
        foreach ($permissionsByCategory as $category => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(
                    ['name' => "{$category}-{$action}", 'guard_name' => 'web']
                );
            }
        }

        // إنشاء الأدوار مع guard_name
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

        // ربط الصلاحيات بالأدوار
        $adminRole->syncPermissions(Permission::all());

        $managerRole->syncPermissions([
            'projects-list', 'projects-create', 'projects-edit',
            'employees-list', 'employees-create', 'employees-edit',
            'dailyprogress-list', 'dailyprogress-create', 'dailyprogress-edit',
        ]);

        $employeeRole->syncPermissions([
            'dailyprogress-list', 'dailyprogress-edit',
        ]);

        // ربط أول مستخدم كـ Admin
        $user = User::first();
        if ($user) {
            $user->assignRole('admin');
        }
    }
}
