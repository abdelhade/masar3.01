<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class ItemStatusesPermissionsSeeder extends Seeder
{
    public function run()
    {
        // 🧹 مسح الكاش
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'item-statuses-list',
            'item-statuses-create',
            'item-statuses-edit',
            'item-statuses-delete',
            'item-statuses-view',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Assign all permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        // Assign view and list permissions to manager role
        $managerRole = Role::where('name', 'manager')->first();
        if ($managerRole) {
            $managerRole->givePermissionTo([
                'item-statuses-list',
                'item-statuses-view',
            ]);
        }

        // 👇 إضافة الصلاحيات للمستخدم a@a.com
        $user = User::where('email', 'a@a.com')->first();

        if ($user) {
            $user->givePermissionTo($permissions);
            if ($this->command) {
                $this->command->info('✅ تم إضافة صلاحيات Item Statuses للمستخدم a@a.com');
            } else {
                echo "✅ تم إضافة صلاحيات Item Statuses للمستخدم a@a.com\n";
            }
        } else {
            if ($this->command) {
                $this->command->warn('⚠️ لم يتم العثور على مستخدم بالإيميل a@a.com');
            } else {
                echo "⚠️ لم يتم العثور على مستخدم بالإيميل a@a.com\n";
            }
        }
    }
}

