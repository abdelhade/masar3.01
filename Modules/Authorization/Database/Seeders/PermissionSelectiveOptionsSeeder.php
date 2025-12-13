<?php

declare(strict_types=1);

namespace Modules\Authorization\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
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

            ],

            'purchase_cancel_access' => [
                'allow_purchase_with_zero_price' => 'السماح بالشراء عندما يكون سعر الشراء صفراً',
                'allow_secret_accounts' => 'السماح برؤية الحسابات السرية',
            ],

        ];

        foreach ($permissionGroups as $category => $permissions) {
            foreach ($permissions as $name => $description) {
                $data = ['category' => $category];

                // Add optional columns only if they exist in the table
                if (Schema::hasColumn('permissions', 'option_type')) {
                    $data['option_type'] = '2';
                }

                if (Schema::hasColumn('permissions', 'description')) {
                    $data['description'] = $description;
                }

                Permission::updateOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    $data
                );
            }
        }

        $this->command?->info('Option type 2 permissions seeded successfully!');
    }
}
