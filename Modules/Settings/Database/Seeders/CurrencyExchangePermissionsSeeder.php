<?php

declare(strict_types=1);

namespace Modules\Settings\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class CurrencyExchangePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            [
                'name' => 'view currency-exchange',
                'category' => 'تبادل العملات',
                'description' => 'عرض عمليات تبادل العملات',
            ],
            [
                'name' => 'create currency-exchange',
                'category' => 'تبادل العملات',
                'description' => 'إضافة عملية تبادل عملات جديدة',
            ],
            [
                'name' => 'edit currency-exchange',
                'category' => 'تبادل العملات',
                'description' => 'تعديل عمليات تبادل العملات',
            ],
            [
                'name' => 'delete currency-exchange',
                'category' => 'تبادل العملات',
                'description' => 'حذف عمليات تبادل العملات',
            ],
        ];

        foreach ($permissions as $permissionData) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                [
                    'category' => $permissionData['category'],
                    'description' => $permissionData['description'] ?? null,
                ]
            );

            // Update metadata if permission already exists but data changed
            if ($permission->wasRecentlyCreated === false) {
                $updated = false;

                if (isset($permissionData['category']) && $permission->category !== $permissionData['category']) {
                    $permission->category = $permissionData['category'];
                    $updated = true;
                }

                if (isset($permissionData['description']) && $permission->description !== $permissionData['description']) {
                    $permission->description = $permissionData['description'];
                    $updated = true;
                }

                if ($updated) {
                    $permission->save();
                }
            }
        }

        // Assign all permissions to admin role
        $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(array_column($permissions, 'name'));
        }
    }
}
