<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UpdateAdminPermissionsSeeder extends Seeder
{
    public function run()
    {
        // مسح الكاش
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // الحصول على دور الـ admin
        $adminRole = Role::where('name', 'admin')->first();
        
        if ($adminRole) {
            // إعطاء الـ admin جميع الصلاحيات الموجودة
            $adminRole->syncPermissions(Permission::all());
        }
    }
}