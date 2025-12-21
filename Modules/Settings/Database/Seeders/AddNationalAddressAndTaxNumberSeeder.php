<?php

namespace Modules\Settings\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Settings\Models\Category;
use Modules\Settings\Models\PublicSetting;

class AddNationalAddressAndTaxNumberSeeder extends Seeder
{
    public function run(): void
    {
        $companyCategory = Category::firstOrCreate(
            ['name' => 'معلومات الشركة'],
        );

        PublicSetting::firstOrCreate(
            ['key' => 'national_address'],
            [
                'label' => 'العنوان الوطني',
                'key' => 'national_address',
                'input_type' => 'text',
                'category_id' => $companyCategory->id,
                'value' => '',
            ]
        );

        PublicSetting::firstOrCreate(
            ['key' => 'tax_number'],
            [
                'label' => 'الرقم الضريبي',
                'key' => 'tax_number',
                'input_type' => 'text',
                'category_id' => $companyCategory->id,
                'value' => '',
            ]
        );
    }
}
