<?php

namespace Modules\MyResources\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MyResources\Models\ResourceStatus;

class ResourceStatusesSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Available',
                'name_ar' => 'متاح',
                'description' => 'Resource is available for assignment',
                'color' => 'success',
                'icon' => 'fas fa-check-circle',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'In Use',
                'name_ar' => 'قيد الاستخدام',
                'description' => 'Resource is currently assigned to a project',
                'color' => 'primary',
                'icon' => 'fas fa-cog',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Under Maintenance',
                'name_ar' => 'تحت الصيانة',
                'description' => 'Resource is undergoing maintenance',
                'color' => 'warning',
                'icon' => 'fas fa-tools',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Broken',
                'name_ar' => 'عطلان',
                'description' => 'Resource is broken and needs repair',
                'color' => 'danger',
                'icon' => 'fas fa-exclamation-triangle',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Reserved',
                'name_ar' => 'محجوز',
                'description' => 'Resource is reserved for future use',
                'color' => 'info',
                'icon' => 'fas fa-calendar-check',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Retired',
                'name_ar' => 'خارج الخدمة',
                'description' => 'Resource is no longer in service',
                'color' => 'secondary',
                'icon' => 'fas fa-ban',
                'sort_order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($statuses as $status) {
            ResourceStatus::firstOrCreate(
                ['name' => $status['name']],
                $status
            );
        }
    }
}

