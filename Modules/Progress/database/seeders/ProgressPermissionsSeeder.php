<?php

namespace Modules\Progress\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ProgressPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        
        // 1. Clean up old namespaced permissions
        Permission::where('name', 'like', 'progress.%')->delete();

        // 2. Define Matrix Permissions (Option Type 1)
        // Format: 'target' => ['action1', 'action2', ...]
        // We will create standard CRUD: view, create, edit, delete
        
        $matrixTargets = [
            'progress-projects',
            'progress-issues',
            'daily-progress',
            'progress-project-types',
            'progress-project-templates',
            'progress-work-items',
            'progress-work-item-categories',
            'progress-item-statuses',
            'progress-dashboard',
            'progress-recyclebin'
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

        foreach ($matrixTargets as $target) {
            foreach ($actions as $action) {
                // Use the custom Permission model to access category/option_type
                \Modules\Authorization\Models\Permission::updateOrCreate(
                    [
                        'name' => "{$action} {$target}", 
                        'guard_name' => 'web'
                    ],
                    [
                        'category' => 'Progress',
                        'option_type' => '1',
                        'description' => ucfirst($action) . " " . ucfirst(str_replace('-', ' ', $target))
                    ]
                );
            }
        }

    

        // 4. Assign all to Admin
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            // Get all permissions in Progress category
            $progressPermissions = \Modules\Authorization\Models\Permission::where('category', 'Progress')->current()->get();
            $adminRole->givePermissionTo($progressPermissions);
        }
    }
}
