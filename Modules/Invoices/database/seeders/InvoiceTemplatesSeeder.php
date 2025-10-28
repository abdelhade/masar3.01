<?php

namespace Modules\Invoices\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Invoices\Models\InvoiceTemplate;
use Modules\Invoices\Models\InvoiceTypeTemplate;

class InvoiceTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'نموذج المبيعات الكامل',
                'code' => 'sales_full',
                'description' => 'نموذج كامل لفواتير المبيعات يعرض جميع التفاصيل',
                'visible_columns' => [
                    'item_name',
                    'unit',
                    'quantity',
                    'price',
                    'discount',
                    'sub_value'
                ],
                'invoice_types' => [10], // فاتورة مبيعات
                'is_default' => true,
            ],
            [
                'name' => 'نموذج المبيعات المبسط',
                'code' => 'sales_simple',
                'description' => 'نموذج مبسط للمبيعات السريعة',
                'visible_columns' => [
                    'item_name',
                    'quantity',
                    'price',
                    'sub_value'
                ],
                'invoice_types' => [10],
                'is_default' => false,
            ],
            [
                'name' => 'نموذج المشتريات',
                'code' => 'purchase_standard',
                'description' => 'النموذج القياسي لفواتير المشتريات',
                'visible_columns' => [
                    'item_name',
                    'unit',
                    'quantity',
                    'price',
                    'sub_value'
                ],
                'invoice_types' => [11], // فاتورة مشتريات
                'is_default' => true,
            ],
            [
                'name' => 'نموذج مردود المبيعات',
                'code' => 'sales_return',
                'description' => 'نموذج مردود المبيعات',
                'visible_columns' => [
                    'item_name',
                    'unit',
                    'quantity',
                    'price',
                    'sub_value'
                ],
                'invoice_types' => [12], // مردود مبيعات
                'is_default' => true,
            ],
            [
                'name' => 'نموذج التحويل بين المخازن',
                'code' => 'transfer',
                'description' => 'نموذج التحويل بين المخازن - بدون أسعار',
                'visible_columns' => [
                    'item_name',
                    'unit',
                    'quantity'
                ],
                'invoice_types' => [21], // تحويل من مخزن لمخزن
                'is_default' => true,
            ],
            [
                'name' => 'نموذج فاتورة الخدمة',
                'code' => 'service',
                'description' => 'نموذج فواتير الخدمات',
                'visible_columns' => [
                    'item_name',
                    'quantity',
                    'price',
                    'discount',
                    'sub_value'
                ],
                'invoice_types' => [24], // فاتورة خدمة
                'is_default' => true,
            ],
            [
                'name' => 'نموذج الخشب والمواد (بالأبعاد)',
                'code' => 'wood_materials',
                'description' => 'نموذج لحساب الكميات من الأبعاد (الطول × العرض × الارتفاع × الكثافة)',
                'visible_columns' => [
                    'item_name',
                    'length',
                    'width',
                    'height',
                    'density',
                    'quantity',
                    'price',
                    'sub_value'
                ],
                'invoice_types' => [10, 11], // مبيعات ومشتريات
                'is_default' => false,
            ],
        ];

        foreach ($templates as $templateData) {
            $invoiceTypes = $templateData['invoice_types'];
            $isDefault = $templateData['is_default'] ?? false;
            unset($templateData['invoice_types'], $templateData['is_default']);

            $template = InvoiceTemplate::create($templateData);

            foreach ($invoiceTypes as $type) {
                InvoiceTypeTemplate::create([
                    'template_id' => $template->id,
                    'invoice_type' => $type,
                    'is_default' => $isDefault,
                ]);
            }
        }
    }
}
