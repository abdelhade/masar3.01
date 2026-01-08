<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Admin Role with all permissions
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(Permission::all());

        // Create Manager Role
        $managerRole = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $managerPermissions = [
            'view Users',
            'view Roles',
            'view items',
            'create items',
            'edit items',
            'view prices',
            'create prices',
            'edit prices',
            'view Clients',
            'create Clients',
            'edit Clients',
            'view Suppliers',
            'create Suppliers',
            'edit Suppliers',
            'view invoices',
            'create invoices',
            'edit invoices',
            'print invoices',
            'view journal entries',
            'create journal entries',
            'view projects',
            'create projects',
            'edit projects',
            'view Employees',
            'view attendances',
            'view leave requests',
            'approve leave requests',
        ];
        $managerRole->syncPermissions($managerPermissions);

        // Create Accountant Role
        $accountantRole = Role::firstOrCreate(['name' => 'Accountant', 'guard_name' => 'web']);
        $accountantPermissions = [
            'view Clients',
            'view Suppliers',
            'view Funds',
            'view Banks',
            'view Expenses',
            'view Revenues',
            'view journal entries',
            'create journal entries',
            'edit journal entries',
            'view receipt vouchers',
            'create receipt vouchers',
            'view payment vouchers',
            'create payment vouchers',
            'view cash transfers',
            'create cash transfers',
            'view basicData-statistics',
        ];
        $accountantRole->syncPermissions($accountantPermissions);

        // Create HR Manager Role
        $hrRole = Role::firstOrCreate(['name' => 'HR Manager', 'guard_name' => 'web']);
        $hrPermissions = [
            'view Employees',
            'create Employees',
            'edit Employees',
            'view departments',
            'create departments',
            'edit departments',
            'view jobs',
            'create jobs',
            'edit jobs',
            'view shifts',
            'create shifts',
            'edit shifts',
            'view attendances',
            'create attendances',
            'edit attendances',
            'view attendance processing',
            'process attendance',
            'view leave balances',
            'create leave balances',
            'edit leave balances',
            'view leave requests',
            'approve leave requests',
            'reject leave requests',
            'view contracts',
            'create contracts',
            'edit contracts',
            'view contract types',
            'view KPIs',
            'view employee evaluations',
            'create employee evaluations',
        ];
        $hrRole->syncPermissions($hrPermissions);

        // Create Sales Role
        $salesRole = Role::firstOrCreate(['name' => 'Sales', 'guard_name' => 'web']);
        $salesPermissions = [
            'view Clients',
            'create Clients',
            'edit Clients',
            'view items',
            'view prices',
            'view invoices',
            'create invoices',
            'print invoices',
            'view leads',
            'create leads',
            'edit leads',
            'view tasks',
            'create tasks',
            'edit tasks',
            'View Inquiries',
            'Create Inquiries',
            'Edit Inquiries',
        ];
        $salesRole->syncPermissions($salesPermissions);

        // Create Warehouse Role
        $warehouseRole = Role::firstOrCreate(['name' => 'Warehouse Manager', 'guard_name' => 'web']);
        $warehousePermissions = [
            'view items',
            'view warhouses',
            'view invoices',
            'create invoices',
            'view item-statistics',
        ];
        $warehouseRole->syncPermissions($warehousePermissions);

        // Create Employee Role (Basic)
        $employeeRole = Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);
        $employeePermissions = [
            'view attendances',
            'view leave balances',
            'create leave requests',
            'view leave requests',
        ];
        $employeeRole->syncPermissions($employeePermissions);

        $this->command->info('Roles created and permissions assigned successfully!');
    }
}
