<?php

declare(strict_types=1);

namespace Modules\HR\Database\Seeders;

use App\Models\CostCenter;
use Illuminate\Database\Seeder;

class CostCentersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CostCenter::firstOrCreate(
            ['cname' => 'الإدارة العامة'],
            [
                'parent_id' => null,
                'info' => 'المركز الرئيسي',
            ]
        );
    }
}
