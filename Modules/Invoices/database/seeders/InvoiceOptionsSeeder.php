<?php

namespace Modules\Invoices\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Settings\Models\PublicSetting;

class InvoiceOptionsSeeder extends Seeder
{
    public function run()
    {
        // الإعدادات المطلوبة
        $settings = [
            [
                'key' => 'invoice_allow_print',
                'label' => 'السماح بالطباعة في الفواتير',
                'input_type' => 'boolean',
                'value' => '1',
            ],
            [
                'key' => 'invoice_allow_negative_quantity',
                'label' => 'السماح باستخدام كمية سالبة في فاتورة المبيعات',
                'input_type' => 'boolean',
                'value' => '1',
            ],
            [
                'key' => 'invoice_use_templates',
                'label' => 'استخدام الأنماط في الفواتير',
                'input_type' => 'boolean',
                'value' => '1',
            ],
            [
                'key' => 'invoice_prevent_date_edit',
                'label' => 'منع تعديل التاريخ في الفواتير',
                'input_type' => 'boolean',
                'value' => '1',
            ],
            [
                'key' => 'invoice_use_last_customer_price',
                'label' => 'استخدام آخر سعر بيع للعميل في فواتير المبيعات',
                'input_type' => 'boolean',
                'value' => '0',
            ],
            [
                'key' => 'invoice_use_pricing_agreement',
                'label' => 'استخدام آخر سعر من اتفاقية تسعير في فواتير المبيعات',
                'input_type' => 'boolean',
                'value' => '0',
            ],
            [
                'key' => 'invoice_show_recommended_items',
                'label' => 'عرض العناصر الموصى بها للعميل في الفاتورة',
                'input_type' => 'boolean',
                'value' => '0',
            ],
        ];

        // حفظ أو تحديث الإعدادات
        foreach ($settings as $setting) {
            PublicSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'category_id' => 2,
                    'label' => $setting['label'],
                    'input_type' => $setting['input_type'],
                    'value' => $setting['value'],
                ]
            );
        }
    }
}
