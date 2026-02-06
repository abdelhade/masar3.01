<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class WorkItemCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('work_item_categories')->insert([
            ['name' => 'Activity'],
            ['name' => 'Materials'],
            ['name' => 'Machine and Tools'],
            ['name' => 'Menpower'],
            ['name' => 'Other'],
        ]);

        // إعطاء الـ admin جميع الصلاحيات الجديدة
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions(Permission::all());
        }
    }
}
