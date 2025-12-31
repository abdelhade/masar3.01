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
     * هذا الـ Seeder يضيف جميع إعدادات الفواتير:
     * - إعدادات الخصم والإضافي (على مستوى الفاتورة والصنف)
     * - إعدادات ضريبة القيمة المضافة (VAT)
     * - إعدادات الخصم من المنبع (Withholding Tax)
     * - أكواد الحسابات المحاسبية
     * - النسب الافتراضية
     */
    public function run(): void
    {
        // حذف الإعدادات القديمة (boolean) التي لم تعد مستخدمة
        PublicSetting::whereIn('key', [
            'purchase_discount_deduct_from_cost',
            'purchase_discount_as_income',
            'purchase_discount_allowed',
            'enable_invoice_discount',
            'enable_item_discount',
            'enable_invoice_additional',
            'enable_item_additional',
            'enable_invoice_vat',
            'enable_item_vat',
            'enable_invoice_withholding_tax',
            'enable_item_withholding_tax',
        ])->delete();

        // ==================== مستويات الخصم والإضافي والضرائب ====================
        // القيم المتاحة: disabled, invoice_level, item_level, both
        
        PublicSetting::updateOrCreate(
            ['key' => 'discount_level'],
            [
                'category_id' => 2,
                'label' => 'مستوى الخصم',
                'input_type' => 'select',
                'value' => 'invoice_level',
            ]
        );

        PublicSetting::updateOrCreate(
            ['key' => 'additional_level'],
            [
                'category_id' => 2,
                'label' => 'مستوى الإضافي',
                'input_type' => 'select',
                'value' => 'invoice_level',
            ]
        );

        // ==================== تفعيل حقول الضرائب (VAT & Withholding Tax) - المفتاح الرئيسي ====================
        // هذا الإعداد هو المفتاح الرئيسي لتشغيل/إيقاف جميع الضرائب
        // يجب تفعيله أولاً قبل استخدام vat_level أو withholding_tax_level
        PublicSetting::updateOrCreate(
            ['key' => 'enable_vat_fields'],
            [
                'category_id' => 2,
                'label' => 'تفعيل حقول الضرائب (المفتاح الرئيسي)',
                'input_type' => 'boolean',
                'value' => '0',
            ]
        );

        PublicSetting::updateOrCreate(
            ['key' => 'vat_level'],
            [
                'category_id' => 2,
                'label' => 'مستوى ضريبة القيمة المضافة',
                'input_type' => 'select',
                'value' => 'disabled',
            ]
        );

        PublicSetting::updateOrCreate(
            ['key' => 'withholding_tax_level'],
            [
                'category_id' => 2,
                'label' => 'مستوى الخصم من المنبع',
                'input_type' => 'select',
                'value' => 'disabled',
            ]
        );

        // ==================== أكواد حسابات الضرائب ====================
        PublicSetting::updateOrCreate(
            ['key' => 'vat_sales_account_code'],
            [
                'category_id' => 2,
                'label' => 'كود حساب ضريبة القيمة المضافة - المبيعات',
                'input_type' => 'text',
                'value' => '21040101',
            ]
        );

        PublicSetting::updateOrCreate(
            ['key' => 'vat_purchase_account_code'],
            [
                'category_id' => 2,
                'label' => 'كود حساب ضريبة القيمة المضافة - المشتريات',
                'input_type' => 'text',
                'value' => '21040102',
            ]
        );

        PublicSetting::updateOrCreate(
            ['key' => 'withholding_tax_account_code'],
            [
                'category_id' => 2,
                'label' => 'كود حساب الخصم من المنبع',
                'input_type' => 'text',
                'value' => '21040103',
            ]
        );

        // ==================== طرق معالجة الخصم والإضافي ====================
        // إعداد المشتريات: طريقة معالجة الخصم
        PublicSetting::updateOrCreate(
            ['key' => 'purchase_discount_method'],
            [
                'category_id' => 2,
                'label' => 'المشتريات: طريقة معالجة الخصم',
                'input_type' => 'select',
                'value' => '2',
            ]
        );

        // إعداد المبيعات: طريقة معالجة الخصم المسموح به
        PublicSetting::updateOrCreate(
            ['key' => 'sales_discount_method'],
            [
                'category_id' => 2,
                'label' => 'المبيعات: طريقة معالجة الخصم المسموح به',
                'input_type' => 'select',
                'value' => '1',
            ]
        );

        // إعداد المشتريات: طريقة معالجة الإضافي
        PublicSetting::updateOrCreate(
            ['key' => 'purchase_additional_method'],
            [
                'category_id' => 2,
                'label' => 'المشتريات: طريقة معالجة الإضافي',
                'input_type' => 'select',
                'value' => '1',
            ]
        );

        // إعداد المبيعات: طريقة معالجة الإضافي
        PublicSetting::updateOrCreate(
            ['key' => 'sales_additional_method'],
            [
                'category_id' => 2,
                'label' => 'المبيعات: طريقة معالجة الإضافي',
                'input_type' => 'select',
                'value' => '1',
            ]
        );

        // ==================== نسب افتراضية ====================
        // نسبة ضريبة القيمة المضافة الافتراضية
        PublicSetting::updateOrCreate(
            ['key' => 'default_vat_percentage'],
            [
                'category_id' => 2,
                'label' => 'نسبة ضريبة القيمة المضافة الافتراضية (%)',
                'input_type' => 'number',
                'value' => '0',
            ]
        );

        // نسبة خصم المنبع الافتراضية
        PublicSetting::updateOrCreate(
            ['key' => 'default_withholding_tax_percentage'],
            [
                'category_id' => 2,
                'label' => 'نسبة خصم المنبع الافتراضية (%)',
                'input_type' => 'number',
                'value' => '0',
            ]
        );

        // ==================== منع بيع الأصناف منتهية الصلاحية ====================
        PublicSetting::updateOrCreate(
            ['key' => 'prevent_selling_expired_items'],
            [
                'category_id' => 2,
                'label' => 'منع بيع الأصناف منتهية الصلاحية',
                'input_type' => 'boolean',
                'value' => '1',
            ]
        );
    }
}
