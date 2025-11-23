<?php

namespace Modules\Resources\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Resources\Models\ResourceCategory;

class ResourceCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Machinery',
                'name_ar' => 'معدات',
                'description' => 'Heavy machinery and equipment',
                'icon' => 'fas fa-cogs',
                'color' => 'primary',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Manpower',
                'name_ar' => 'قوى عاملة',
                'description' => 'Human resources and workers',
                'icon' => 'fas fa-users',
                'color' => 'success',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Vehicles',
                'name_ar' => 'مركبات',
                'description' => 'Transportation vehicles',
                'icon' => 'fas fa-truck',
                'color' => 'info',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Equipment',
                'name_ar' => 'أدوات',
                'description' => 'Tools and equipment',
                'icon' => 'fas fa-toolbox',
                'color' => 'warning',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Tools',
                'name_ar' => 'أدوات يدوية',
                'description' => 'Hand tools and small equipment',
                'icon' => 'fas fa-wrench',
                'color' => 'secondary',
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ResourceCategory::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}

