<?php

namespace Modules\Authorization\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;
use Modules\Authorization\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionCategories = [
            'Dashboard' => ['Dashboard'],
            'Accounts'=>['Clients','Suppliers' , 'Funds', 'Banks', 'Employees', 'warhouses', 'Expenses', 'Revenues','various_creditors', 'various_debtors', 'partners', 'current_partners', 'assets', 'rentables', 'check-portfolios-incoming', 'check-portfolios-outgoing', 'account-movement-report', 'balance-sheet','start-balance-management'],
            'Home' => ['basicData-statistics','item-statistics','statistics'],
            'Products' => ['Units', 'Categories', 'Products', 'Product Movements'],
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

                    Permission::firstOrCreate([
                        'name' => $name,
                        'guard_name' => 'web',
                        'category' => $category
                    ]);
                }
            }
        }

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);

        $userRole = Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'web'
        ]);

        // Assign permissions to roles
        $adminRole->syncPermissions(Permission::all());

        $userPermissions = Permission::where('name', 'like', 'view%')->get();
        $userRole->syncPermissions($userPermissions);

        // Create POS-specific roles
        $posRoles = [
            'Cashier' => [
                'view POS System',
                'create POS Transaction',
                'view POS Transaction',
                'print POS Transaction',
            ],
            'POS Supervisor' => [
                'view POS System',
                'create POS Transaction',
                'view POS Transaction',
                'edit POS Transaction',
                'print POS Transaction',
                'view POS Reports',
            ],
            'POS Manager' => [
                'view POS System',
                'create POS Transaction',
                'view POS Transaction',
                'edit POS Transaction',
                'delete POS Transaction',
                'print POS Transaction',
                'view POS Reports',
                'create POS Settings',
                'edit POS Settings',
            ]
        ];

        foreach ($posRoles as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);

            $rolePermissions = Permission::whereIn('name', $permissions)->get();
            $role->syncPermissions($rolePermissions);
        }
    }
}
