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
            'progress-categories',
            'progress-item-statuses',
            'progress-dashboard',
            'progress-recycle-bin',
            'progress-clients',
            'progress-employees'
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

        foreach ($matrixTargets as $target) {
            foreach ($actions as $action) {
                // Use the custom Permission model to access category/option_type
                // Use 'web' guard (default for users)
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

        // Note: 'restore' is handled by 'edit' permission for recycle-bin
        // No need for separate restore permission

    

        // 4. Assign all to Admin
        // Note: Skip if guard mismatch - let UserSeeder handle role assignments
        try {
            $adminRole = Role::where('name', 'Admin')->first();
            if ($adminRole) {
                // Get all permissions in Progress category with web guard
                $progressPermissions = \Modules\Authorization\Models\Permission::where('category', 'Progress')
                    ->where('guard_name', 'web')
                    ->get();
                
                if ($progressPermissions->isNotEmpty()) {
                    // Sync permissions (will only work if guards match)
                    $adminRole->syncPermissions($progressPermissions);
                }
            }
        } catch (\Exception $e) {
            // Ignore guard mismatch errors - permissions will be assigned in UserSeeder
            \Log::info('Progress permissions created, role assignment skipped due to guard mismatch');
        }
    }
}
