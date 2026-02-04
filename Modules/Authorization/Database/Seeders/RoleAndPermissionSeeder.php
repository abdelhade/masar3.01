<?php

namespace Modules\Authorization\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionCategories = [
            'Accounts' => [
                'Clients',
                'Suppliers',
                'Funds',
                'Banks',
                'Employees',
                'warhouses',
                'Expenses',
                'Revenues',
                'various_creditors',
                'various_debtors',
                'partners',
                'current_partners',
                'assets',
                'rentables',
                'check-portfolios-incoming',
                'check-portfolios-outgoing',
                'account-movement-report',
                'balance-sheet',
                'start-balance-management'
            ],
            'Journals' => [
                'service-agreement',
                'accured-expenses',
                'accured-revenues',
                'bank-commission',
                'sales-contract',
                'partner-profit-sharing',
            ],
            // 'Home' => ['basicData-statistics', 'item-statistics', 'statistics', 'system-statistics', 'vouchers-statistics', 'multi-voucher-statistics', 'transfer-statistics', 'journals-statistics'],
            'items' => ['items', 'units', 'prices', 'notes-names', 'varibals', 'varibalsValues', 'Categories'],
            'permissions' => ['roles', 'branches', 'settings', 'login-history', 'active-sessions', 'activity-logs'],
            'vouchers' => [
                'recipt',
                'payment',
                'exp-payment',
                'multi-payment',
                'multi-receipt',
                'extra-salary-calculation',
                'salary-calculation',
                'discount-salary-calculation',
                'insurance-calculation',
                'tax-calculationtax-calculation',
                'depreciation',
                'sell-asset',
                'buy-asset',
                'increase-asset-value',
                'decrease-asset-value'
            ],
            'transfers' => ['transfers', 'cash-to-cash', 'cash-to-bank', 'bank-to-cash', 'bank-to-bank'],
            'Accounts-mangment' => ['journals', 'multi-journals', 'inventory-balance', 'opening-balance-accounts', 'accounts-balance-sheet'],
            'Users' => ['Users'],
            'Sales' => ['Sales Invoice', 'Sales Return', 'Sales Order'],
            'Purchases' => ['Purchase Invoice', 'Purchase Return', 'Purchase Order'],
            'Inventory' => ['Stock Transfer', 'Stock Adjustment'],
            'POS' => ['POS System', 'POS Transaction', 'POS Reports', 'POS Settings'],
            'Expnses' => ['Expenses', 'Cost Centers'],
            // 'Settings' => ['System Settings'],

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

        // Ensure vouchers statistics permission exists
        // Permission::firstOrCreate(
        //     ['name' => 'view vouchers-statistics', 'guard_name' => 'web'],
        //     ['category' => 'vouchers']
        // );
    }
}
