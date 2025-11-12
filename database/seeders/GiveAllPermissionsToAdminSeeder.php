<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GiveAllPermissionsToAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على المستخدم رقم 1
        $admin = User::find(1);

        if (!$admin) {
            $this->command->error('❌ المستخدم رقم 1 غير موجود!');
            return;
        }

        // الحصول على جميع الصلاحيات
        $allPermissions = Permission::all()->pluck('name')->toArray();

        if (empty($allPermissions)) {
            $this->command->warn('⚠️  لا توجد صلاحيات في النظام!');
            return;
        }

        // إعطاء جميع الصلاحيات للمستخدم
        $admin->syncPermissions($allPermissions);

        $this->command->info("✅ تم إعطاء {$admin->name} جميع الصلاحيات بنجاح!");
        $this->command->info("📊 إجمالي الصلاحيات: " . count($allPermissions));
        $this->command->line('');
        $this->command->table(
            ['المستخدم', 'البريد الإلكتروني', 'عدد الصلاحيات'],
            [[$admin->name, $admin->email, count($allPermissions)]]
        );
    }
}
