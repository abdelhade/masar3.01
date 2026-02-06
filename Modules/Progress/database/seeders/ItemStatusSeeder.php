<?php

namespace Database\Seeders;

use App\Models\ItemStatus;
use Illuminate\Database\Seeder;

class ItemStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'شغالة بكفاءة',
                'color' => 'green',
                'icon' => '🟢',
                'description' => 'أخضر',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'تحتاج متابعة / صيانة دورية',
                'color' => 'yellow',
                'icon' => '🟡',
                'description' => 'أصفر',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'مشكلة غير حرجة',
                'color' => 'orange',
                'icon' => '🟠',
                'description' => 'برتقالي',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'عطل متوقف',
                'color' => 'red',
                'icon' => '🔴',
                'description' => 'أحمر',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'خارج الخدمة نهائيًا',
                'color' => 'black',
                'icon' => '⚫',
                'description' => 'أسود',
                'order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($statuses as $status) {
            ItemStatus::updateOrCreate(
                ['name' => $status['name']],
                $status
            );
        }
    }
}

