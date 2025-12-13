<?php

namespace Modules\Authorization\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Modules\Authorization\Models\Permission;

class DashboardPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissionCategories =
        [
            'Dashboard-Basic-Settings' => ['Home', 'Basic-Data', 'Items', 'Permissions', 'Settings'],
            'Dashboard-Sales-Management' => ['CRM', 'Sales', 'Procurement', 'POS', 'Rental-Management'],
            'Dashboard-Accounting-Finance' => ['Accounts-Management', 'Expenses-Management', 'Financial-Vouchers', 'Cash-Transfers', 'Payments-Management', 'Cheques-Management', 'Files-Management', 'Employee-Salaries', 'Accruals'],
            'Dashboard-Inventory-Production-Management' => ['Inventory-Management', 'Production', 'Procurement', 'Maintenance'],
            'Dashboard-Projects-Production' => ['Projects', 'Daily-Progress', 'Asset-Operations', 'Resource-Management'],
            'Dashboard-HR' => ['hr', 'Mobile-fingerprint'],
            'Dashboard-Services-Operations' => ['Tenants-Management', 'Shipping-Management', 'Inquiries'],
            'Dashboard-Quality-Management' => ['Quality-Dashboard', 'Quality-Inspections', 'Quality-Standards', 'NCR', 'Corrective-Actions', 'Batch-Tracking', 'Suppliers-Evaluation', 'Certifications-Compliance', 'Internal-Audit', 'Quality-Reports'],
            'Dashboard-Reports' => ['DailyWorkAnalysis', 'Chart-of-Accounts', 'Balance-Sheet', 'Profit-Loss', 'Sales-Reports', 'Purchasing-Reports', 'Inventory-Reports', 'Expenses-Reports'],
        ];

        // Accounts specific permissions with CRUD actions
        $accountsPermissions = [
            'Dashboard-Accounting-Finance' => [
                'accounts' => ['view', 'create', 'edit', 'delete'],
                'accounts-start-balance' => ['view'],
                'accounts-balance-sheet' => ['view'],
                'accounts-basic-data-statistics' => ['view'],
            ],
        ];

        $actions = ['view'];

        // Create permissions with option_type = 1 so they appear in the users create/edit view
        foreach ($permissionCategories as $category => $permissions) {
            foreach ($permissions as $permission) {
                foreach ($actions as $action) {
                    $name = "$action $permission";

                    $data = ['category' => $category];

                    // Add optional columns only if they exist in the table
                    if (Schema::hasColumn('permissions', 'option_type')) {
                        $data['option_type'] = '1';
                    }

                    Permission::updateOrCreate(
                        [
                            'name' => $name,
                            'guard_name' => 'web',
                        ],
                        $data
                    );
                }
            }
        }

        // Create accounts specific permissions with CRUD actions
        foreach ($accountsPermissions as $category => $resources) {
            foreach ($resources as $resource => $resourceActions) {
                foreach ($resourceActions as $action) {
                    $name = "$action $resource";

                    $data = ['category' => $category];

                    // Add optional columns only if they exist in the table
                    if (Schema::hasColumn('permissions', 'option_type')) {
                        $data['option_type'] = '1';
                    }

                    Permission::updateOrCreate(
                        [
                            'name' => $name,
                            'guard_name' => 'web',
                        ],
                        $data
                    );
                }
            }
        }
    }
}
