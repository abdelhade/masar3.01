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
            // 1. نموذج المبيعات (الكامل والافتراضي)
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
            // 2. نموذج المبيعات المبسط (اختياري)
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
            // 3. نموذج المشتريات
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
            // 4. نموذج مردود المبيعات
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
            // 5. نموذج مردود المشتريات (جديد)
            [
                'name' => 'نموذج مردود المشتريات',
                'code' => 'purchase_return',
                'description' => 'النموذج القياسي لمردود المشتريات',
                'visible_columns' => [
                    'item_name',
                    'unit',
                    'quantity',
                    'price',
                    'sub_value'
                ],
                'invoice_types' => [13], // مردود مشتريات
                'is_default' => true,
            ],
            // 6. نموذج أوامر البيع وعروض الأسعار للعملاء (جديد ومجمع)
            [
                'name' => 'نموذج أمر بيع / عرض سعر',
                'code' => 'sales_order_quote',
                'description' => 'نموذج لأوامر البيع وعروض أسعار العملاء',
                'visible_columns' => [
                    'item_name',
                    'unit',
                    'quantity',
                    'price',
                    'discount',
                    'sub_value'
                ],
                'invoice_types' => [14, 16], // 14: أمر بيع, 16: عرض سعر لعميل
                'is_default' => true,
            ],
            // 7. نموذج أوامر الشراء وعروض الأسعار من الموردين (جديد ومجمع)
            [
                'name' => 'نموذج أمر شراء / عرض مورد',
                'code' => 'purchase_order_quote',
                'description' => 'نموذج لأوامر الشراء وعروض أسعار الموردين',
                'visible_columns' => [
                    'item_name',
                    'unit',
                    'quantity',
                    'price',
                    'sub_value'
                ],
                'invoice_types' => [15, 17], // 15: أمر شراء, 17: عرض سعر من مورد
                'is_default' => true,
            ],
            // 8. نموذج فاتورة التوالف (جديد)
            [
                'name' => 'نموذج فاتورة توالف',
                'code' => 'wasted_items',
                'description' => 'نموذج لتسجيل التوالف والهالك (بسعر التكلفة)',
                'visible_columns' => [
                    'item_name',
                    'unit',
                    'quantity',
                    'price',
                    'sub_value'
                ],
                'invoice_types' => [18], // فاتورة توالف
                'is_default' => true,
            ],
            // 9. نموذج حركات المخزون (تعديل وتجميع)
            [
                'name' => 'نموذج حركات المخزون',
                'code' => 'inventory_movement',
                'description' => 'نموذج لحركات المخزون (تحويل، صرف، إضافة، حجز، طلب احتياج)',
                'visible_columns' => [
                    'item_name',
                    'unit',
                    'quantity'
                ],
                'invoice_types' => [19, 20, 21, 22, 25], // 19: صرف, 20: إضافة, 21: تحويل, 22: حجز, 25: احتياج
                'is_default' => true,
            ],
            // 10. نموذج فاتورة الخدمة
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
            // 11. نموذج الخشب والمواد (اختياري للمبيعات والمشتريات)
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

        // حذف البيانات القديمة لضمان عدم التكرار
        InvoiceTemplate::query()->delete();
        InvoiceTypeTemplate::query()->delete();

        foreach ($templates as $templateData) {
            $invoiceTypes = $templateData['invoice_types'];
            $isDefault = $templateData['is_default'] ?? false;
            unset($templateData['invoice_types'], $templateData['is_default']);

            $template = InvoiceTemplate::create($templateData);

            foreach ($invoiceTypes as $type) {
                // إذا كان هذا النموذج هو الافتراضي، فتأكد من عدم وجود افتراضي آخر لنفس النوع
                if ($isDefault) {
                    InvoiceTypeTemplate::where('invoice_type', $type)
                        ->update(['is_default' => false]);
                }

                InvoiceTypeTemplate::create([
                    'template_id' => $template->id,
                    'invoice_type' => $type,
                    'is_default' => $isDefault,
                ]);
            }
        }
    }
}
