<?php

namespace Modules\Invoices\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceDimensionsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('public_settings')->insert([
            [
                'category_id' => 2,
                'label' => 'تفعيل حساب الكمية من الأبعاد',
                'input_type' => 'boolean',
                'key' => 'enable_dimensions_calculation',
                'value' => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => 2,
                'label' => 'وحدة قياس الأبعاد',
                'input_type' => 'select',
                'key' => 'dimensions_unit',
                'value' => json_encode(['cm', 'm']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
