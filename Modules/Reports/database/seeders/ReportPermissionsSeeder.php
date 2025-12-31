<?php

declare(strict_types=1);

namespace Modules\Reports\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ReportPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $groupedPermissions = [
            'Reports' => [
                'Daily Activity Analyzer',        // محلل النشاط اليومي
                'General Journal',                // اليومية العامة
                'General Account Statement',      // كشف حساب عام
                'Accounts Tree',                  // شجرة الحسابات
                'Balance Sheet',                  // الميزانية العمومية
                'Profit Loss Report',             // تقرير الأرباح والخسائر
                'Income Statement Total',         // إجمالي قائمة الدخل
                'Accounts Balance',               // أرصدة الحسابات
                'Account Movement Report',        // تقرير حركة الحساب

                'Items Report',       // قائمة الأصناف بالأرصدة

                'Sales Report',        // المبيعات حسب مندوب البيع

                'Customer Quotation Report',      // تقرير عروض أسعار العملاء
                'Supplier Quotation Report',      // تقرير عروض أسعار الموردين

                'Purchases Report',         // تقرير المشتريات اليومي

                'Expenses Report',        // تقرير أرصدة المصروفات

                'Cost Centers Report',            // تقرير مراكز التكلفة
                'General Cashbox Movement Report', // تقرير حركة الخزينة العامة

                'Manufacturing Invoices Report',  // تقرير فواتير التصنيع

                'Quality Report',              // لوحة تحكم الجودة

            ],
        ];

        // الأفعال (عرض فقط للتقارير)
        $actions = ['view'];

        // إنشاء أو تحديث الصلاحيات
        foreach ($groupedPermissions as $category => $items) {
            foreach ($items as $base) {
                foreach ($actions as $action) {
                    $fullName = "$action $base";
                    $permission = Permission::firstOrCreate(
                        ['name' => $fullName, 'guard_name' => 'web'],
                        ['category' => $category, 'option_type' => '1', 'description' => "Reports Access for $base"]
                    );

                    // تحديث الفئة والنوع إذا كانت موجودة بالفعل
                    if ($permission->category !== $category || $permission->option_type !== '1') {
                        $permission->update([
                            'category' => $category,
                            'option_type' => '1'
                        ]);
                    }
                }
            }
        }
    }
}
