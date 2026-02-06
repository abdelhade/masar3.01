<?php

namespace Modules\Progress\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AddClientsEmployeesPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define missing resources
        $missingResources = [
            'progress-clients',
            'progress-employees',
            'progress-activity-logs',
            'progress-backup',
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

        foreach ($missingResources as $resource) {
            foreach ($actions as $action) {
                // Skip create/edit for activity-logs (read-only)
                if ($resource === 'progress-activity-logs' && in_array($action, ['create', 'edit'])) {
                    continue;
                }

                \Modules\Authorization\Models\Permission::updateOrCreate(
                    [
                        'name' => "{$action} {$resource}",
                        'guard_name' => 'web'
                    ],
                    [
                        'category' => 'Progress',
                        'option_type' => '1',
                        'description' => ucfirst($action) . " " . ucfirst(str_replace('-', ' ', $resource))
                    ]
                );
            }
        }

        // Assign all Progress permissions to Admin role
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $progressPermissions = \Modules\Authorization\Models\Permission::where('category', 'Progress')->current()->get();
            $adminRole->syncPermissions($progressPermissions);
        }

        $this->command->info('✅ Added missing Progress permissions: clients, employees, activity-logs, backup');
        $this->command->info('✅ Assigned all Progress permissions to Admin role');
    }
}
