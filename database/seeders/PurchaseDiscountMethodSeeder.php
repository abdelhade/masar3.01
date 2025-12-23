<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Settings\Models\PublicSetting;

class PurchaseDiscountMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * هذا الـ Seeder يضيف إعدادين منفصلين:
     * 1. طريقة معالجة الخصم في فواتير المشتريات
     * 2. طريقة معالجة الخصم في فواتير المبيعات
     */
    public function run(): void
    {
        // حذف الإعدادات القديمة (boolean) إن وُجدت
        PublicSetting::whereIn('key', [
            'purchase_discount_deduct_from_cost',
            'purchase_discount_as_income',
            'purchase_discount_allowed'
        ])->delete();

        // إعداد المشتريات: طريقة معالجة الخصم
        PublicSetting::updateOrCreate(
            ['key' => 'purchase_discount_method'],
            [
                'category_id' => 2, // Invoices category
                'label' => 'المشتريات: طريقة معالجة الخصم',
                'input_type' => 'select',
                'value' => '2', // القيمة الافتراضية: الخصم كإيراد منفصل
            ]
        );

        // إعداد المبيعات: طريقة معالجة الخصم المسموح به
        PublicSetting::updateOrCreate(
            ['key' => 'sales_discount_method'],
            [
                'category_id' => 2, // Invoices category
                'label' => 'المبيعات: طريقة معالجة الخصم المسموح به',
                'input_type' => 'select',
                'value' => '1', // القيمة الافتراضية: الطريقة الحالية
            ]
        );

        // إعداد المشتريات: طريقة معالجة الإضافي
        PublicSetting::updateOrCreate(
            ['key' => 'purchase_additional_method'],
            [
                'category_id' => 2, // Invoices category
                'label' => 'المشتريات: طريقة معالجة الإضافي',
                'input_type' => 'select',
                'value' => '1', // القيمة الافتراضية: يُضاف للتكلفة
            ]
        );

        // إعداد المبيعات: طريقة معالجة الإضافي
        PublicSetting::updateOrCreate(
            ['key' => 'sales_additional_method'],
            [
                'category_id' => 2, // Invoices category
                'label' => 'المبيعات: طريقة معالجة الإضافي',
                'input_type' => 'select',
                'value' => '1', // القيمة الافتراضية: يُضاف للإيراد
            ]
        );

        // إعداد تفعيل حقول ضريبة القيمة المضافة
        PublicSetting::updateOrCreate(
            ['key' => 'enable_vat_fields'],
            [
                'category_id' => 2, // Invoices category
                'label' => 'تفعيل حقول ضريبة القيمة المضافة (VAT)',
                'input_type' => 'boolean',
                'value' => '0', // القيمة الافتراضية: معطل
            ]
        );

        // نسبة ضريبة القيمة المضافة الافتراضية
        PublicSetting::updateOrCreate(
            ['key' => 'default_vat_percentage'],
            [
                'category_id' => 2, // Invoices category
                'label' => 'نسبة ضريبة القيمة المضافة الافتراضية (%)',
                'input_type' => 'number',
                'value' => '0', // القيمة الافتراضية: 0%
            ]
        );

        // نسبة خصم المنبع الافتراضية
        PublicSetting::updateOrCreate(
            ['key' => 'default_withholding_tax_percentage'],
            [
                'category_id' => 2, // Invoices category
                'label' => 'نسبة خصم المنبع الافتراضية (%)',
                'input_type' => 'number',
                'value' => '0', // القيمة الافتراضية: 0%
            ]
        );
    }
}
