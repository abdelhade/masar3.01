<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class CategoriesPermissionsSeeder extends Seeder
{
    public function run()
    {
        // 🧹 مسح الكاش
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 🟢 صلاحيات categories
        $categoryActions = ['list', 'create', 'edit', 'delete'];

        foreach ($categoryActions as $action) {
            Permission::firstOrCreate([
                'name' => "categories-{$action}",
                'guard_name' => 'web'
            ]);
        }

        // 👇 إضافة الصلاحيات للمستخدم المحدد
        $user = User::where('id', '1')->first();

        if ($user) {
            $user->givePermissionTo([
                'categories-list',
                'categories-create',
                'categories-edit',
                'categories-delete'
            ]);
        } else {
            $this->command->info('⚠️ لم يتم العثور على مستخدم بالإيميل a@a.com');
        }
    }
}
