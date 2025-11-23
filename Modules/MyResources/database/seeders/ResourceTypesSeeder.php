<?php

namespace Modules\MyResources\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MyResources\Models\ResourceCategory;
use Modules\MyResources\Models\ResourceType;

class ResourceTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            // Machinery Types
            'Machinery' => [
                ['name' => 'Excavator', 'name_ar' => 'حفارة'],
                ['name' => 'Bulldozer', 'name_ar' => 'جرافة'],
                ['name' => 'Crane', 'name_ar' => 'رافعة'],
                ['name' => 'Loader', 'name_ar' => 'لودر'],
                ['name' => 'Grader', 'name_ar' => 'جريدر'],
                ['name' => 'Compactor', 'name_ar' => 'مدحلة'],
                ['name' => 'Concrete Mixer', 'name_ar' => 'خلاطة خرسانة'],
                ['name' => 'Paver', 'name_ar' => 'آلة رصف'],
            ],
            // Manpower Types
            'Manpower' => [
                ['name' => 'Engineer', 'name_ar' => 'مهندس'],
                ['name' => 'Supervisor', 'name_ar' => 'مشرف'],
                ['name' => 'Foreman', 'name_ar' => 'رئيس عمال'],
                ['name' => 'Technician', 'name_ar' => 'فني'],
                ['name' => 'Skilled Worker', 'name_ar' => 'عامل ماهر'],
                ['name' => 'Laborer', 'name_ar' => 'عامل'],
                ['name' => 'Operator', 'name_ar' => 'مشغل معدات'],
                ['name' => 'Electrician', 'name_ar' => 'كهربائي'],
                ['name' => 'Plumber', 'name_ar' => 'سباك'],
                ['name' => 'Carpenter', 'name_ar' => 'نجار'],
                ['name' => 'Welder', 'name_ar' => 'لحام'],
            ],
            // Vehicles Types
            'Vehicles' => [
                ['name' => 'Dump Truck', 'name_ar' => 'شاحنة قلابة'],
                ['name' => 'Pickup Truck', 'name_ar' => 'شاحنة صغيرة'],
                ['name' => 'Van', 'name_ar' => 'فان'],
                ['name' => 'Bus', 'name_ar' => 'حافلة'],
                ['name' => 'Water Tanker', 'name_ar' => 'صهريج مياه'],
                ['name' => 'Fuel Tanker', 'name_ar' => 'صهريج وقود'],
            ],
            // Equipment Types
            'Equipment' => [
                ['name' => 'Generator', 'name_ar' => 'مولد كهرباء'],
                ['name' => 'Compressor', 'name_ar' => 'ضاغط هواء'],
                ['name' => 'Welding Machine', 'name_ar' => 'ماكينة لحام'],
                ['name' => 'Pump', 'name_ar' => 'مضخة'],
                ['name' => 'Scaffolding', 'name_ar' => 'سقالات'],
                ['name' => 'Formwork', 'name_ar' => 'قوالب صب'],
            ],
            // Tools Types
            'Tools' => [
                ['name' => 'Power Tools', 'name_ar' => 'أدوات كهربائية'],
                ['name' => 'Hand Tools', 'name_ar' => 'أدوات يدوية'],
                ['name' => 'Measuring Tools', 'name_ar' => 'أدوات قياس'],
                ['name' => 'Safety Equipment', 'name_ar' => 'معدات سلامة'],
            ],
        ];

        foreach ($types as $categoryName => $categoryTypes) {
            $category = ResourceCategory::where('name', $categoryName)->first();

            if ($category) {
                foreach ($categoryTypes as $type) {
                    ResourceType::firstOrCreate(
                        [
                            'resource_category_id' => $category->id,
                            'name' => $type['name'],
                        ],
                        [
                            'name_ar' => $type['name_ar'],
                            'description' => null,
                            'specifications' => null,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }
}

