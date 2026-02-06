<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkItemCategory;
use App\Models\WorkItem;

class WorkItemsTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء Categories أولاً إذا لم تكن موجودة
        $categories = [
            'أعمال خرسانية',
            'أعمال حديد التسليح',
            'أعمال النجارة',
            'أعمال البلاط',
            'أعمال الكهرباء',
            'أعمال السباكة',
            'أعمال الدهانات',
            'أعمال العزل',
            'أعمال التشطيبات',
            'أعمال الحفر والردم',
        ];

        $categoryIds = [];
        foreach ($categories as $categoryName) {
            $category = WorkItemCategory::firstOrCreate(['name' => $categoryName]);
            $categoryIds[] = $category->id;
        }

        // أنواع الوحدات
        $units = ['متر مكعب', 'متر مربع', 'متر طولي', 'طن', 'كجم', 'قطعة', 'لتر', 'عدد'];
        
        // أنواع البنود
        $itemTypes = [
            'صب خرسانة',
            'توريد وتركيب حديد',
            'أعمال نجارة',
            'تركيب بلاط',
            'تمديد كهرباء',
            'تمديد سباكة',
            'دهان',
            'عزل',
            'تشطيب',
            'حفر',
            'ردم',
            'نقل',
            'توريد',
            'تركيب',
            'صيانة',
            'اختبار',
            'فحص',
            'معايرة',
        ];

        // إنشاء 1000 Work Item
        echo "Creating 1000 Work Items...\n";
        
        $order = 1;
        for ($i = 1; $i <= 1000; $i++) {
            $itemType = $itemTypes[array_rand($itemTypes)];
            $unit = $units[array_rand($units)];
            $categoryId = $categoryIds[array_rand($categoryIds)];
            
            WorkItem::create([
                'name' => $itemType . ' ' . $i,
                'unit' => $unit,
                'description' => 'وصف البند ' . $i . ' - ' . $itemType,
                'category_id' => $categoryId,
                'order' => $order++,
            ]);

            if ($i % 100 == 0) {
                echo "Created {$i} items...\n";
            }
        }

        echo "✅ Successfully created 1000 Work Items!\n";
    }
}

