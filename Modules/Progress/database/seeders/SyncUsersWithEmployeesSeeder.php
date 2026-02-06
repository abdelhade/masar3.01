<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;

class SyncUsersWithEmployeesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * إنشاء سجلات موظفين للمستخدمين الذين ليس لديهم سجل موظف
     */
    public function run(): void
    {
        // جلب جميع المستخدمين الذين ليس لديهم سجل موظف
        $usersWithoutEmployee = User::whereDoesntHave('employee')->get();

        if ($usersWithoutEmployee->isEmpty()) {
            $this->command->info('✅ جميع المستخدمين لديهم سجلات موظفين.');
            return;
        }

        $this->command->info("🔄 جاري إنشاء سجلات موظفين لـ {$usersWithoutEmployee->count()} مستخدم...");

        foreach ($usersWithoutEmployee as $user) {
            Employee::create([
                'name' => $user->name,
                'email' => $user->email,
                'user_id' => $user->id,
                'position' => 'موظف', // يمكن تعديله حسب الحاجة
            ]);

            $this->command->info("✅ تم إنشاء سجل موظف للمستخدم: {$user->name}");
        }

        $this->command->info("🎉 تم إنشاء {$usersWithoutEmployee->count()} سجل موظف بنجاح!");
    }
}

