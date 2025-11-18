<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InvoicesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $groupedPermissions = [
            'Sales' => [
                'Sales Invoice',
                'Sales Return',
                'Sales Order',
                'Quotation to Customer',
                'Booking Order',
                'Pricing Agreement',
            ],
            'Purchases' => [
                'Purchase Invoice',
                'Purchase Return',
                'Purchase Order',
                'Quotation from Supplier',
                'Service Invoice',
                'Requisition',
            ],
            'Inventory' => [
                'Damaged Goods Invoice',
                'Dispatch Order',
                'Addition Order',
                'Store-to-Store Transfer',
            ],
        ];

        // Standard actions
        $actions = ['view', 'create', 'edit', 'delete', 'print'];

        // Create permissions
        foreach ($groupedPermissions as $category => $permissions) {
            foreach ($permissions as $basePermission) {
                foreach ($actions as $action) {
                    $fullName = "$action $basePermission";
                    Permission::firstOrCreate(
                        ['name' => $fullName, 'guard_name' => 'web'],
                        ['category' => $category]
                    );
                }
            }
        }

        // Find or create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user'], ['guard_name' => 'web']);

        // Grant all permissions to admin
        $allInvoicePermissions = Permission::whereIn('category', ['Sales', 'Purchases', 'Inventory'])->get();
        $adminRole->givePermissionTo($allInvoicePermissions);

        // Grant 'view' only permissions to user
        $userViewPermissions = Permission::whereIn('category', ['Sales', 'Purchases', 'Inventory'])
            ->where('name', 'like', 'view%')
            ->get();
        $userRole->givePermissionTo($userViewPermissions);
    }
}
