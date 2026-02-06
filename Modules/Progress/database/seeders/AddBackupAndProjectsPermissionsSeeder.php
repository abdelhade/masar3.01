<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddBackupAndProjectsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الصلاحيات الجديدة
        $permissions = [
            'backup-view',
            'projects-gantt',
        ];

        foreach ($permissions as $permName) {
            // إضافة الصلاحية لو مش موجودة
            $permission = Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web',
            ]);
        }

        // ربط الصلاحيات بالـ admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            foreach ($permissions as $permName) {
                if (!$adminRole->hasPermissionTo($permName)) {
                    $adminRole->givePermissionTo($permName);
                }
            }
        }
    }
}
