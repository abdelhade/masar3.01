<?php

namespace Modules\CRM\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CRM\Models\LeadStatus;

class LeadStatusSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            [
                'name' => 'جديد',
                'color' => '#17a2b8',
                'order_column' => 1,
                'branch_id' => 1
            ],
            [
                'name' => 'مؤهل',
                'color' => '#ffc107',
                'order_column' => 2,
                'branch_id' => 1

            ],
            [
                'name' => 'مقترح',
                'color' => '#fd7e14',
                'order_column' => 3,
                'branch_id' => 1
            ],
            [
                'name' => 'تم الفوز',
                'color' => '#28a745',
                'order_column' => 4,
                'branch_id' => 1
            ],
            [
                'name' => 'مرفوض',
                'color' => '#dc3545',
                'order_column' => 5,
                'branch_id' => 1
            ]
        ];

        foreach ($statuses as $status) {
            LeadStatus::create($status);
        }
    }
}
