<?php

namespace Modules\Authorization\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionCategories = [
            'Accounts' => ['Clients', 'Suppliers', 'Funds', 'Banks', 'Employees', 'warhouses', 'Expenses', 'Revenues', 'various_creditors', 'various_debtors', 'partners', 'current_partners', 'assets', 'rentables', 'check-portfolios-incoming', 'check-portfolios-outgoing', 'account-movement-report', 'balance-sheet', 'start-balance-management'],
            'Home' => ['basicData-statistics', 'item-statistics', 'statistics', 'system-statistics', 'vouchers-statistics', 'multi-voucher-statistics', 'transfer-statistics', 'journals-statistics'],
            'items' => ['items', 'units', 'prices', 'notes-names', 'varibals', 'varibalsValues'],
            'permissions' => ['roles', 'branches', 'settings', 'login-history', 'active-sessions', 'activity-logs'],
            'vouchers' => ['recipt', 'payment', 'exp-payment', 'multi-payment', 'multi-receipt'],
            'transfers' => ['transfers', 'cash-to-cash', 'cash-to-bank', 'bank-to-cash', 'bank-to-bank'],
            'Accounts-mangment' => ['journals', 'multi-journals', 'inventory-balance', 'opening-balance-accounts', 'accounts-balance-sheet'],
            'Products' => ['Categories', 'Products', 'Product Movements'],
            'Users' => ['Users'],
            'Sales' => ['Sales Invoice', 'Sales Return', 'Sales Order'],
            'Purchases' => ['Purchase Invoice', 'Purchase Return', 'Purchase Order'],
            'Inventory' => ['Stock Transfer', 'Stock Adjustment'],
            'POS' => ['POS System', 'POS Transaction', 'POS Reports', 'POS Settings'],
            'Reports' => ['Financial Reports', 'Sales Reports', 'Inventory Reports'],
            'Settings' => ['System Settings'],

        ];

        $actions = ['view', 'create', 'edit', 'delete', 'print'];

        // Create permissions
        foreach ($permissionCategories as $category => $permissions) {
            foreach ($permissions as $permission) {
                foreach ($actions as $action) {
                    $name = "$action $permission";

                    Permission::firstOrCreate(
                        ['name' => $name, 'guard_name' => 'web'],
                        ['category' => $category]
                    );
                }
            }
        }

        // Note: Roles are created but not assigned permissions
        // Permissions are assigned directly to users via model_has_permissions table
        // This maintains compatibility with Spatie package while using direct permission assignment
    }
}
