<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class CompletePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions organized by category
        $permissions = [
            // ===== User Management =====
            'view Users',
            'create Users',
            'edit Users',
            'delete Users',

            // ===== Role Management =====
            'view Roles',
            'create Roles',
            'edit Roles',
            'delete Roles',

            // ===== Branch Management =====
            'view Branches',
            'create Branches',
            'edit Branches',
            'delete Branches',

            // ===== Settings =====
            'view Settings',
            'edit Settings',
            'view Settings Control',

            // ===== Item Management =====
            'view items',
            'create items',
            'edit items',
            'delete items',
            'print items',

            // ===== Unit Management =====
            'view units',
            'create units',
            'edit units',
            'delete units',

            // ===== Price Management =====
            'view prices',
            'create prices',
            'edit prices',
            'delete prices',

            // ===== Variable Management =====
            'view varibals',
            'create varibals',
            'edit varibals',
            'delete varibals',
            'view varibalsValues',
            'create varibalsValues',
            'edit varibalsValues',
            'delete varibalsValues',

            // ===== Statistics =====
            'view basicData-statistics',
            'view item-statistics',
            'view vouchers-statistics',
            'view multi-voucher-statistics',

            // ===== Account Management =====
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
            'view Employees',
            'create Employees',
            'edit Employees',
            'delete Employees',
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

            // ===== Check Portfolios =====
            'view check-portfolios-incoming',
            'create check-portfolios-incoming',
            'edit check-portfolios-incoming',
            'delete check-portfolios-incoming',
            'view check-portfolios-outgoing',
            'create check-portfolios-outgoing',
            'edit check-portfolios-outgoing',
            'delete check-portfolios-outgoing',

            // ===== Journal Management =====
            'view journal entries',
            'create journal entries',
            'edit journal entries',
            'delete journal entries',
            'view operation journal entries',

            // ===== Voucher Management - Receipt (سندات القبض) =====
            'view receipt vouchers',
            'create receipt vouchers',
            'edit receipt vouchers',
            'delete receipt vouchers',
            'view recipt',
            'create recipt',
            'edit recipt',
            'delete recipt',

            // ===== Voucher Management - Payment (سندات الدفع) =====
            'view payment vouchers',
            'create payment vouchers',
            'edit payment vouchers',
            'delete payment vouchers',
            'view payment',
            'create payment',
            'edit payment',
            'delete payment',

            // ===== Voucher Management - Expense Payment (سندات دفع المصاريف) =====
            'view exp-payment',
            'create exp-payment',
            'edit exp-payment',
            'delete exp-payment',
            'view expense payment',
            'create expense payment',
            'edit expense payment',
            'delete expense payment',

            // ===== Voucher Management - Multi Receipt (سندات قبض متعددة) =====
            'view multi receipt vouchers',
            'create multi receipt vouchers',
            'edit multi receipt vouchers',
            'delete multi receipt vouchers',
            'view multi-receipt',
            'create multi-receipt',
            'edit multi-receipt',
            'delete multi-receipt',

            // ===== Voucher Management - Multi Payment (سندات دفع متعددة) =====
            'view multi-payment',
            'create multi-payment',
            'edit multi-payment',
            'delete multi-payment',

            // ===== Voucher Management - Special Permissions =====
            'delete own vouchers only',
            'delete own multi-vouchers only',

            // ===== Transfer Management =====
            'view cash transfers',
            'create cash transfers',
            'edit cash transfers',
            'delete cash transfers',

            // ===== Project Management =====
            'view projects',
            'create projects',
            'edit projects',
            'delete projects',

            // ===== Discount Management - Allowed Discounts =====
            'view allowed discounts list',
            'create allowed discounts list',
            'edit allowed discounts list',
            'delete allowed discounts list',
            'view allowed discount',
            'create allowed discount',
            'edit allowed discount',
            'delete allowed discount',
            'view Allowed Discounts',
            'create Allowed Discounts',
            'edit Allowed Discounts',
            'delete Allowed Discounts',

            // ===== Discount Management - Earned Discounts =====
            'view earned discounts list',
            'create earned discounts list',
            'edit earned discounts list',
            'delete earned discounts list',
            'view earned discount',
            'create earned discount',
            'edit earned discount',
            'delete earned discount',
            'view Earned Discounts',
            'create Earned Discounts',
            'edit Earned Discounts',
            'delete Earned Discounts',

            // ===== CRM - Client Management =====
            'view client contacts',
            'create client contacts',
            'edit client contacts',
            'delete client contacts',
            'view Client Contacts',
            'create Client Contacts',
            'edit Client Contacts',
            'delete Client Contacts',
            'view client types',
            'create client types',
            'edit client types',
            'delete client types',
            'view client categories',
            'create client categories',
            'edit client categories',
            'delete client categories',

            // ===== CRM - Lead Management =====
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

            // ===== CRM - Tasks =====
            'view tasks',
            'create tasks',
            'edit tasks',
            'delete tasks',
            'view Tasks',
            'create Tasks',
            'edit Tasks',
            'delete Tasks',
            'view task types',
            'create task types',
            'edit task types',
            'delete task types',
            'view Task Types',
            'create Task Types',
            'edit Task Types',
            'delete Task Types',

            // ===== CRM - Activities =====
            'view Activities',
            'create Activities',
            'edit Activities',
            'delete Activities',

            // ===== Inquiries Module =====
            'View Inquiries',
            'Create Inquiries',
            'Edit Inquiries',
            'Delete Inquiries',
            'view Inquiries',
            'create Inquiries',
            'edit Inquiries',
            'delete Inquiries',
            'View My Drafts',
            'View Inquiries Source',
            'Create Inquiries Source',
            'Edit Inquiries Source',
            'Delete Inquiries Source',
            'view Inquiries Source',
            'create Inquiries Source',
            'edit Inquiries Source',
            'delete Inquiries Source',
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
            'view Documents',
            'create Documents',
            'edit Documents',
            'delete Documents',
            'View Project Size',
            'Create Project Size',
            'Edit Project Size',
            'Delete Project Size',
            'view Project Size',
            'create Project Size',
            'edit Project Size',
            'delete Project Size',
            'View Inquiries Roles',
            'Create Inquiries Roles',
            'Edit Inquiries Roles',
            'Delete Inquiries Roles',
            'view Inquiries Roles',
            'create Inquiries Roles',
            'edit Inquiries Roles',
            'delete Inquiries Roles',
            'View Inquiries Statistics',

            // ===== HR - Department Management =====
            'view departments',
            'create departments',
            'edit departments',
            'delete departments',
            'view jobs',
            'create jobs',
            'edit jobs',
            'delete jobs',

            // ===== HR - Location Management =====
            'view countries',
            'create countries',
            'edit countries',
            'delete countries',
            'view states',
            'create states',
            'edit states',
            'delete states',
            'view cities',
            'create cities',
            'edit cities',
            'delete cities',
            'view towns',
            'create towns',
            'edit towns',
            'delete towns',

            // ===== HR - Shift Management =====
            'view shifts',
            'create shifts',
            'edit shifts',
            'delete shifts',

            // ===== HR - Employee Management =====
            'view employees',
            'create employees',
            'edit employees',
            'delete employees',

            // ===== HR - KPI Management =====
            'view kpis',
            'create kpis',
            'edit kpis',
            'delete kpis',
            'view employee evaluations',
            'create employee evaluations',
            'edit employee evaluations',
            'delete employee evaluations',

            // ===== HR - Contract Management =====
            'view contract types',
            'create contract types',
            'edit contract types',
            'delete contract types',
            'view contracts',
            'create contracts',
            'edit contracts',
            'delete contracts',

            // ===== HR - Attendance Management =====
            'view attendances',
            'create attendances',
            'edit attendances',
            'delete attendances',
            'view attendance processing',
            'process attendance',

            // ===== HR - Leave Management =====
            'view leave balances',
            'create leave balances',
            'edit leave balances',
            'delete leave balances',
            'view leave requests',
            'create leave requests',
            'edit leave requests',
            'delete leave requests',
            'approve leave requests',
            'reject leave requests',

            // ===== Invoice Management =====
            'view invoice templates',
            'create invoice templates',
            'edit invoice templates',
            'delete invoice templates',
            'view Invoice Templates',
            'create Invoice Templates',
            'edit Invoice Templates',
            'delete Invoice Templates',
            'view invoices',
            'create invoices',
            'edit invoices',
            'delete invoices',
            'print invoices',
            'confirm requirement requests',
            'track requirement requests',

            // ===== Manufacturing =====
            'view manufacturing invoices',
            'create manufacturing invoices',
            'edit manufacturing invoices',
            'delete manufacturing invoices',
            'view Manufacturing Invoices',
            'create Manufacturing Invoices',
            'edit Manufacturing Invoices',
            'delete Manufacturing Invoices',
            'print Manufacturing Invoices',
            'view Manufacturing Stages',
            'create Manufacturing Stages',
            'edit Manufacturing Stages',
            'delete Manufacturing Stages',

            // ===== Rentals Module =====
            'view Rentals Statistics',
            'view Buildings',
            'create Buildings',
            'edit Buildings',
            'delete Buildings',
            'view Unit',
            'create Unit',
            'edit Unit',
            'delete Unit',
            'view Leases',
            'create Leases',
            'edit Leases',
            'delete Leases',

            // ===== Installments Module =====
            'view Installment Plans',
            'create Installment Plans',
            'edit Installment Plans',
            'delete Installment Plans',
            'view Overdue Installments',

            // ===== POS Module (نقاط البيع) =====
            'عرض نظام نقاط البيع',
            'إضافة معاملة نقاط البيع',
            'عرض معاملة نقاط البيع',
            'طباعة معاملة نقاط البيع',
            'حذف معاملة نقاط البيع',
            'عرض تقارير نقاط البيع',

            // ===== Notes Module =====
            'عرض ملاحظات',
            'إنشاء ملاحظات',
            'تعديل ملاحظات',
            'حذف ملاحظات',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->command->info('✅ جميع الصلاحيات تم إنشاؤها بنجاح!');
        $this->command->info('📊 إجمالي الصلاحيات: ' . count($permissions));
    }
}

