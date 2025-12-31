<?php

namespace Modules\Invoices\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

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
    }
}
