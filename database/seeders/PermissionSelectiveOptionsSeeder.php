<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSelectiveOptionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionGroups = [
            'user_scope_reports' => [
                'prevent_transactions_without_stock' => 'منع التجاوز للكمية في جميع العمليات  في حالة عدم وجود رصيد كافي',
                'prevent_editing_store' => 'منع تعديل المخزن في العمليات',
                'prevent_editing_store_in_edit_mode' => 'منع تعديل المخزن في وضع التعديل',
                'prevent_editing_main_account_name' => 'منع تعديل اسم الحساب الرئيسي في وضع التعديل',
               
            ],
            'control_lists' => [
                'invoice_void' => 'اصدار تحذير عند التراجع عن فاتورة بيع',
                'allow_price_change' => 'السماح بتغيير سعر البيع',
                'allow_discount_change' => 'السماح بتغيير الخصم في حالة تغير سعر البيع',
                'allow_discount_change_without_price_change' => 'السماح بتغير الخصومات في حال عدم تعديل المبيعات بتغير سعر البيع',
               
            ],
          
            'purchase_cancel_access' => [
                'allow_purchase_with_zero_price' => 'السماح بالشراء عندما يكون سعر الشراء صفراً',
                'allow_zero_quantity_in_sales_invoice' => 'السماح بتفعيل الكمية صفر داخل فاتورة البيع',
                'allow_zero_quantity_in_purchase_invoice' => 'السماح بتفعيل الكمية صفر داخل فاتورة الشراء',
                'allow_secret_accounts' => 'السماح برؤية الحسابات السرية',
                ],
               
           
        ];

        foreach ($permissionGroups as $category => $permissions) {
            foreach ($permissions as $name => $description) {
                Permission::updateOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['category' => $category, 'option_type' => '2', 'description' => $description]
                );
            }
        }

        $this->command?->info('Option type 2 permissions seeded successfully!');
    }
}
