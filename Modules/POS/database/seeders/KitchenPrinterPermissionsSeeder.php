<?php

declare(strict_types=1);

namespace Modules\POS\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class KitchenPrinterPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء الأذونات
        $permissions = [
            'view Kitchen Printer Settings',
            'edit Kitchen Printer Settings',
            'view Print Jobs',
            'retry Print Jobs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // تعيين الأذونات لدور المدير (Admin)
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        // تعيين أذونات العرض لدور الكاشير (Cashier)
        $cashierRole = Role::where('name', 'Cashier')->first();
        if ($cashierRole) {
            $cashierRole->givePermissionTo([
                'view Kitchen Printer Settings',
                'view Print Jobs',
            ]);
        }

        $this->command->info('Kitchen Printer permissions created and assigned successfully.');
    }
}
