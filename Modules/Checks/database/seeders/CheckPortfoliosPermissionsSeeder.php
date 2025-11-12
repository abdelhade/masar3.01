<?php

namespace Modules\Checks\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;
use Modules\Authorization\Models\Role;

class CheckPortfoliosPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $localizedPermissions = [
            'عرض حافظات أوراق القبض',
            'إضافة حافظات أوراق القبض',
            'تعديل حافظات أوراق القبض',
            'حذف حافظات أوراق القبض',
            'عرض حافظات أوراق الدفع',
            'إضافة حافظات أوراق الدفع',
            'تعديل حافظات أوراق الدفع',
            'حذف حافظات أوراق الدفع',
        ];

        foreach ($localizedPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['category' => 'Checks']
            );
        }

        $sidebarPermissions = [
            'view check-portfolios-incoming',
            'create check-portfolios-incoming',
            'view check-portfolios-outgoing',
            'create check-portfolios-outgoing',
        ];

        foreach ($sidebarPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['category' => 'Accounts']
            );
        }

        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole) {
            $adminRole->givePermissionTo([
                ...$localizedPermissions,
                ...$sidebarPermissions,
            ]);
        }

        $this->command?->info('Check portfolios permissions created successfully!');
    }
}
