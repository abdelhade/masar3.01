<?php

declare(strict_types=1);

namespace Modules\Authorization\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions organized by category
        $permissions = [
            // User Management
            'view Users',
            'create Users',
            'edit Users',
            'delete Users',

            // Role Management
            'view Roles',
            'create Roles',
            'edit Roles',
            'delete Roles',

            // Branch Management
            'view Branches',
            'create Branches',
            'edit Branches',
            'delete Branches',

            // Settings
            'view Settings',
            'edit Settings',
            'view Settings Control',

            // Item Management
            'view items',
            'create items',
            'edit items',
            'delete items',
            'print items',

            // Unit Management
            'view units',
            'create units',
            'edit units',
            'delete units',

            // Price Management
            'view prices',
            'create prices',
            'edit prices',
            'delete prices',

            // Variable Management
            'view varibals',
            'create varibals',
            'edit varibals',
            'delete varibals',
            'view varibalsValues',
            'create varibalsValues',
            'edit varibalsValues',
            'delete varibalsValues',

            // Statistics
            'view basicData-statistics',
            'view item-statistics',

            // Account Management
            'view Clients',
            'create Clients',
            'edit Clients',
            'delete Clients',
            'view Suppliers',
            'create Suppliers',
            'edit Suppliers',
            'delete Suppliers',
            'view Funds',
            'create Funds',
            'edit Funds',
            'delete Funds',
            'view Banks',
            'create Banks',
            'edit Banks',
            'delete Banks',
            'view warhouses',
            'create warhouses',
            'edit warhouses',
            'delete warhouses',
            'view Expenses',
            'create Expenses',
            'edit Expenses',
            'delete Expenses',
            'view Revenues',
            'create Revenues',
            'edit Revenues',
            'delete Revenues',
            'view various_creditors',
            'create various_creditors',
            'edit various_creditors',
            'delete various_creditors',
            'view various_debtors',
            'create various_debtors',
            'edit various_debtors',
            'delete various_debtors',
            'view partners',
            'create partners',
            'edit partners',
            'delete partners',
            'view current_partners',
            'create current_partners',
            'edit current_partners',
            'delete current_partners',
            'view assets',
            'create assets',
            'edit assets',
            'delete assets',
            'view rentables',
            'create rentables',
            'edit rentables',
            'delete rentables',

            // Check Portfolios
            'view check-portfolios-incoming',
            'create check-portfolios-incoming',
            'edit check-portfolios-incoming',
            'delete check-portfolios-incoming',
            'view check-portfolios-outgoing',
            'create check-portfolios-outgoing',
            'edit check-portfolios-outgoing',
            'delete check-portfolios-outgoing',

            // Journal Management
            'view journal entries',
            'create journal entries',
            'edit journal entries',
            'delete journal entries',
            'view operation journal entries',

            // Voucher Management
            'view receipt vouchers',
            'create receipt vouchers',
            'edit receipt vouchers',
            'delete receipt vouchers',
            'view payment vouchers',
            'create payment vouchers',
            'edit payment vouchers',
            'delete payment vouchers',
            'view multi receipt vouchers',
            'create multi receipt vouchers',
            'edit multi receipt vouchers',
            'delete multi receipt vouchers',

            // Transfer Management
            'view cash transfers',
            'create cash transfers',
            'edit cash transfers',
            'delete cash transfers',

            // Project Management
            'view projects',
            'create projects',
            'edit projects',
            'delete projects',

            // Discount Management
            'view allowed discounts list',
            'create allowed discounts list',
            'edit allowed discounts list',
            'delete allowed discounts list',
            'view allowed discount',
            'create allowed discount',
            'edit allowed discount',
            'delete allowed discount',
            'view earned discounts list',
            'create earned discounts list',
            'edit earned discounts list',
            'delete earned discounts list',
            'view earned discount',
            'create earned discount',
            'edit earned discount',
            'delete earned discount',

            // CRM - Client Management
            'view client contacts',
            'create client contacts',
            'edit client contacts',
            'delete client contacts',
            'view client types',
            'create client types',
            'edit client types',
            'delete client types',
            'view client categories',
            'create client categories',
            'edit client categories',
            'delete client categories',

            // CRM - Lead Management
            'view leads',
            'create leads',
            'edit leads',
            'delete leads',
            'view lead sources',
            'create lead sources',
            'edit lead sources',
            'delete lead sources',
            'view lead statuses',
            'create lead statuses',
            'edit lead statuses',
            'delete lead statuses',

            // CRM - Tasks
            'view tasks',
            'create tasks',
            'edit tasks',
            'delete tasks',

            // Inquiries Module
            'View Inquiries',
            'Create Inquiries',
            'Edit Inquiries',
            'Delete Inquiries',
            'View My Drafts',
            'View Inquiries Source',
            'Create Inquiries Source',
            'Edit Inquiries Source',
            'Delete Inquiries Source',
            'View Work Types',
            'Create Work Types',
            'Edit Work Types',
            'Delete Work Types',
            'View Difficulty Matrix',
            'View Quotation Info',
            'View Documents',
            'Create Documents',
            'Edit Documents',
            'Delete Documents',
            'View Project Size',
            'Create Project Size',
            'Edit Project Size',
            'Delete Project Size',
            'View Inquiries Roles',
            'Create Inquiries Roles',
            'Edit Inquiries Roles',
            'Delete Inquiries Roles',
            'View Inquiries Statistics',

            // HR - Department Management
            // 'view departments',
            // 'create departments',
            // 'edit departments',
            // 'delete departments',
            // 'view jobs',
            // 'create jobs',
            // 'edit jobs',
            // 'delete jobs',

            // HR - Location Management
            // 'view countries',
            // 'create countries',
            // 'edit countries',
            // 'delete countries',
            // 'view states',
            // 'create states',
            // 'edit states',
            // 'delete states',
            // 'view cities',
            // 'create cities',
            // 'edit cities',
            // 'delete cities',
            // 'view towns',
            // 'create towns',
            // 'edit towns',
            // 'delete towns',

            // HR - Shift Management
            // 'view shifts',
            // 'create shifts',
            // 'edit shifts',
            // 'delete shifts',

            // HR - Employee Management
            // 'view employees',
            // 'create employees',
            // 'edit employees',
            // 'delete employees',

            // HR - KPI Management
            // 'view kpis',
            // 'create kpis',
            // 'edit kpis',
            // 'delete kpis',
            // 'view employee evaluations',
            // 'create employee evaluations',
            // 'edit employee evaluations',
            // 'delete employee evaluations',

            // HR - Contract Management
            // 'view contract types',
            // 'create contract types',
            // 'edit contract types',
            // 'delete contract types',
            // 'view contracts',
            // 'create contracts',
            // 'edit contracts',
            // 'delete contracts',

            // HR - Attendance Management
            // 'view attendances',
            // 'create attendances',
            // 'edit attendances',
            // 'delete attendances',
            // 'view attendance processing',
            // 'process attendance',

            // HR - Leave Management
            // 'view leave balances',
            // 'create leave balances',
            // 'edit leave balances',
            // 'delete leave balances',
            // 'view leave requests',
            // 'create leave requests',
            // 'edit leave requests',
            // 'delete leave requests',
            // 'approve leave requests',
            // 'reject leave requests',

            // Invoice Management
            'view invoice templates',
            'create invoice templates',
            'edit invoice templates',
            'delete invoice templates',
            'view invoices',
            'create invoices',
            'edit invoices',
            'delete invoices',
            'print invoices',
            'confirm requirement requests',
            'track requirement requests',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->command->info('Permissions created successfully!');
    }
}
