<?php

namespace Modules\Settings\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Settings\Models\{Category, PublicSetting};

class SystemSettingsSeeder extends Seeder
{
    public function run()
    {
        $invoiceCategory = Category::firstOrCreate(['name' => ' الفواتير']);

        $settings = [
            [
                'key' => 'invoice_enable_all_client_types',
                'label' => 'السماح بالبيع لجميع أنواع العملاء في فواتير المبيعات',
                'input_type' => 'boolean',
                'value' => '1',
            ],
            [
                'key' => 'invoice_show_add_clients_suppliers',
                'label' => 'إظهار خيار إضافة عملاء وموردين من شاشة الفاتورة',
                'input_type' => 'boolean',
                'value' => '1',
            ],
            [
                'key' => 'invoice_use_due_date',
                'label' => 'استخدام تاريخ الاستحقاق في الفاتورة',
                'input_type' => 'boolean',
                'value' => '1',
            ],
            [
                'key' => 'invoice_select_price_type',
                'label' => 'اختيار نوع السعر في الفاتورة (جملة - تجزئة - خاص)',
                'input_type' => 'boolean',
                'value' => '1'
            ],
            [
                'key' => 'invoice_show_item_details',
                'label' => 'إظهار تفاصيل الصنف في الفاتورة (الوصف - المواصفات)',
                'input_type' => 'boolean',
                'value' => '1',
            ],
            [
                'key' => 'invoice_show_recommended_items',
                'label' => 'إظهار الأصناف الأكثر توصية للعميل',
                'input_type' => 'boolean',
                'value' => '1',
            ],
        ];

        foreach ($settings as $setting) {
            PublicSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'category_id' => $invoiceCategory->id,
                    'label' => $setting['label'],
                    'input_type' => $setting['input_type'],
                    'value' => $setting['value'],
                ]
            );
        }

        $manufactureCategory = Category::firstOrCreate(['name' => 'فواتير التصنيع']);

        $manufactureSettings = [
            [
                'key' => 'manufacture_enable_template_saving',
                'label' => 'إمكانية حفظ النماذج في فاتورة التصنيع',
                'input_type' => 'boolean',
                'value' => '1',
            ],
            [
                'key' => 'manufacture_enable_expenses',
                'label' => 'استخدام المصروفات في فاتورة التصنيع',
                'input_type' => 'boolean',
                'value' => '1',
            ],
        ];

        foreach ($manufactureSettings as $setting) {
            PublicSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'category_id' => $manufactureCategory->id,
                    'label' => $setting['label'],
                    'input_type' => $setting['input_type'],
                    'value' => $setting['value'],
                ]
            );
        }
    }
}
