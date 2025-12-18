<?php

namespace Modules\Settings\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;

class SettingsPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // مصفوفة المجموعات مع العناصر داخل كل مجموعة
        $groupedPermissions = [
            'Settings' => [
                'General Settings',
                'Currencies',
                'Barcode Settings',
                'Export Data',
            ],
        ];

        // الأفعال القياسية
        $standardActions = ['view', 'edit'];

        // أفعال العملات
        $currenciesActions = ['view', 'create', 'edit', 'delete'];

        // أفعال أسعار الصرف
        $exchangeRatesActions = ['view', 'edit', 'update'];

        // إنشاء الصلاحيات إن لم تكن موجودة
        foreach ($groupedPermissions as $category => $items) {
            foreach ($items as $base) {
                // تحديد الأفعال حسب العنصر
                $currentActions = match ($base) {
                    'Currencies' => $currenciesActions,
                    'Exchange Rates' => $exchangeRatesActions,
                    default => $standardActions
                };

                foreach ($currentActions as $action) {
                    $fullName = "$action $base";
                    Permission::firstOrCreate(
                        ['name' => $fullName, 'guard_name' => 'web'],
                        ['category' => $category]
                    );
                }
            }
        }
    }
}
