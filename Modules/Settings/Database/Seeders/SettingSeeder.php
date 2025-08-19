<?php

namespace Modules\Settings\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Settings\Models\Category;
use Modules\Settings\Models\PublicSetting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $general = Category::create(['name' => 'الثوابت العامه']);
        $invoices = Category::create(['name' => ' الفواتير']);
        $accounts = Category::create(['name' => 'حساب الخصم المكتسب ']);
        $disc = Category::create(['name' => 'حساب فرق الجرد ']);

        // ثوابت عامه


        PublicSetting::create([
            'category_id' => $general->id,
            'label' => 'اسم الشركه',
            'key' => 'campany_name',
            'input_type' => 'text',
            'value' => 'الشركه',
        ]);

        PublicSetting::create([
            'category_id' => $general->id,
            'label' => 'تاريخ بدايه المده',
            'key' => 'start_date',
            'input_type' => 'date',
            'value' => '2023-01-01',
        ]);

        PublicSetting::create([
            'category_id' => $general->id,
            'label' => 'تاريخ نهاية المده',
            'key' => 'start_date',
            'input_type' => 'date',
            'value' => '2023-01-01',
        ]);

        PublicSetting::create([
            'category_id' => $general->id,
            'label' => 'العنوان',
            'key' => 'address',
            'input_type' => 'text',
            'value' => '123 شارع المثال، المدينة، الدولة',
        ]);

        PublicSetting::create([
            'category_id' => $general->id,
            'label' => 'البريد الإلكتروني',
            'key' => 'email',
            'input_type' => 'email',
            'value' => 'a@a.com',
        ]);

        PublicSetting::create([
            'category_id' => $general->id,
            'label' => 'إمكانية إنشاء حسابات متفرعة من الحسابات الخاصة (عملاء - موردين - مصروفات)',
            'key' => 'allow_sub_accounts',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $general->id,
            'label' => 'تاريخ تسجيل العملية هو نفس تاريخ الجهاز',
            'key' => 'use_system_date_for_transactions',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $general->id,
            'label' => 'السماح بتغيير تاريخ العملية',
            'key' => 'allow_edit_transaction_date',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $general->id,
            'label' => 'عدم إظهار المستخدمين الموقوفين عند تسجيل الدخول',
            'key' => 'hide_blocked_users_login',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $general->id,
            'label' => 'تفعيل اليوم بعد الساعة 12 بعدد ساعات',
            'key' => 'extend_day_after_midnight_hours',
            'input_type' => 'number',
            'value' => '4',
        ]);

        // -------------------- ثوابت الفواتير---------------------------

        PublicSetting::create([
            'category_id' => $invoices->id,
            'label' => 'عدم السماح بإدخال ارقام سالبة بالفواتير',
            'key' => 'prevent_negative_invoice',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $invoices->id,
            'label' => 'جديد تلقائي بعد الحفظ - بفاتورة البيع العاديه',
            'key' => 'new_after_save',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $invoices->id,
            'label' => 'السماح بتعديل الفئات السعرية في الفواتير',
            'key' => 'allow_edit_price_payments',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $invoices->id,
            'label' => 'تحريك الصنف من الفواتير بالنقر المزدوج علي المسلسل',
            'key' => 'scrap_by_barcode_only',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $invoices->id,
            'label' => 'السماح بان يكون السعر بالفواتير صفر',
            'key' => 'allow_zero_price_in_invoice',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $invoices->id,
            'label' => 'السماح بان يكون الرصيد الافتتاحي صفر',
            'key' => 'allow_zero_opening_balance',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $invoices->id,
            'label' => 'السماح بان يكون قيمة الفاتوره صفر',
            'key' => 'allow_zero_invoice_total',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $invoices->id,
            'label' => 'السماح بتعديل حقل القيمه بالفاتورة',
            'key' => 'allow_edit_invoice_value',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $invoices->id,
            'label' => 'عند تعديل القيمه ستتغير الكميه بدلا من السعر',
            'key' => 'change_quantity_on_value_edit',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $invoices->id,
            'label' => 'عدم تكرار الصنف في فواتير المبيعات',
            'key' => 'prevent_duplicate_items_in_sales',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $invoices->id,
            'label' => 'عدم التكرار في فواتير المشتريات',
            'key' => 'prevent_duplicate_items_in_purchases',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $invoices->id,
            'label' => 'طباعة الكميه المجانيه في سجل منفصل بالفواتير',
            'key' => 'print_free_quantity_separately',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $invoices->id,
            'label' => 'السماح بتغيير سعر البيع في فاتوره المشتريات',
            'key' => 'allow_purchase_price_change',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $invoices->id,
            'label' => 'اظهار الوحده مع معامل التحويل بالفواتير',
            'key' => 'show_unit_with_conversion_factor',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $invoices->id,
            'label' => 'اظهار تاريخ الاستحقاق بالفواتير',
            'key' => 'show_due_date_in_invoices',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $invoices->id,
            'label' => 'الكميه اكبر من صفر هي الوضع الافتراضي في حاله فواتير البيع',
            'key' => 'default_quantity_greater_than_zero',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $invoices->id,
            'label' => 'السماح بايقاف ظهور الاصناف من قائمة الاصناف حسب شركة منتجه',
            'key' => 'allow_hide_items_by_company',
            'input_type' => 'boolean',
            'value' => '1',
        ]);


        PublicSetting::create([
            'category_id' => $invoices->id,
            'label' => 'حساب اضافي الموظفين',
            'key' => 'employee_adding_account',
            'input_type' => 'integer',
            'value' => '123456789',
        ]);

        PublicSetting::create([
            'category_id' => $invoices->id,
            'label' => 'حساب رواتب الموظفين',
            'key' => 'employee_salary_account',
            'input_type' => 'integer',
            'value' => '123456789',
        ]);

        PublicSetting::create([
            'category_id' => $invoices->id,
            'label' => 'حساب خصم الموظفين',
            'key' => 'employee_discount_account',
            'input_type' => 'integer',
            'value' => '123456789',
        ]);
        //-------------------- ثوابت العمليات--- --------------------------------
        PublicSetting::create([
            'category_id' => $accounts->id,
            'label' => 'حساب الخصم المسموح به ',
            'key' => 'allowed_discount_account',
            'input_type' => 'integer',
            'value' => '123456789',
        ]);

        PublicSetting::create([
            'category_id' => $accounts->id,
            'label' => 'تفعيل نظام الحسابات السرية',
            'key' => 'enable_secret_accounts',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $accounts->id,
            'label' => 'إظهار زر التبديل بين الطباعة العادية وطباعة الكاشير',
            'key' => 'show_print_mode_switch',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $accounts->id,
            'label' => 'السماح بتسجيل الرصيد الافتتاحي للحساب عند الإنشاء',
            'key' => 'allow_opening_balance_on_create',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $accounts->id,
            'label' => 'السماح بتصفير الأرصدة الافتتاحية للمخازن',
            'key' => 'allow_reset_opening_balance_stores',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $accounts->id,
            'label' => 'إظهار وقت بداية ونهاية الوردية للمستخدمين',
            'key' => 'show_shift_time_for_users',
            'input_type' => 'boolean',
            'value' => '1',
        ]);

        PublicSetting::create([
            'category_id' => $disc->id,
            'label' => 'كود حساب فرق الجرد',
            'key' => 'show_inventory_difference_account',
            'input_type' => 'text',
            'value' => '',
        ]);
    }
}
