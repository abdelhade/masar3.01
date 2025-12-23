<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VatAccountsSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'vat_sales_account_code',
                'value' => '21040101',
                'input_type' => 'text',
                'category_id' => 2, // فئة الفواتير
                'label' => 'كود حساب ض.ق.م المبيعات',
            ],
            [
                'key' => 'vat_purchase_account_code',
                'value' => '21040102',
                'input_type' => 'text',
                'category_id' => 2,
                'label' => 'كود حساب ض.ق.م المشتريات',
            ],
            [
                'key' => 'withholding_tax_account_code',
                'value' => '21040103',
                'input_type' => 'text',
                'category_id' => 2,
                'label' => 'كود حساب خصم المنبع',
            ],
        ];

        foreach ($settings as $setting) {
            // التحقق من عدم وجود الإعداد مسبقاً
            $exists = DB::table('public_settings')->where('key', $setting['key'])->exists();
            
            if (!$exists) {
                DB::table('public_settings')->insert([
                    'key' => $setting['key'],
                    'value' => $setting['value'],
                    'input_type' => $setting['input_type'],
                    'category_id' => $setting['category_id'],
                    'label' => $setting['label'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
