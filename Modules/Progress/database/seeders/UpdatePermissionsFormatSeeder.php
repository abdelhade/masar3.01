<?php

namespace Modules\Progress\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

/**
 * Update Permissions Format Seeder
 * 
 * This seeder updates all permissions to the new format: {action} {module-resource}
 * Example: "view progress-projects" instead of "projects-list"
 */
class UpdatePermissionsFormatSeeder extends Seeder
{
    public function run()
    {
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions in the new format
        $permissions = [
            // Projects
            'view progress-projects',
            'create progress-projects',
            'edit progress-projects',
            'delete progress-projects',
            'export progress-projects',
            
            // Project Types
            'view progress-project-types',
            'create progress-project-types',
            'edit progress-project-types',
            'delete progress-project-types',
            
            // Project Templates
            'view progress-project-templates',
            'create progress-project-templates',
            'edit progress-project-templates',
            'delete progress-project-templates',
            
            // Project Items
            'view progress-project-items',
            'create progress-project-items',
            'edit progress-project-items',
            'delete progress-project-items',
            
            // Item Statuses
            'view progress-item-statuses',
            'create progress-item-statuses',
            'edit progress-item-statuses',
            'delete progress-item-statuses',
            
            // Daily Progress
            'view daily-progress',
            'create daily-progress',
            'edit daily-progress',
            'delete daily-progress',
            
            // Employees
            'view progress-employees',
            'create progress-employees',
            'edit progress-employees',
            'delete progress-employees',
            
            // Clients
            'view progress-clients',
            'create progress-clients',
            'edit progress-clients',
            'delete progress-clients',
            
            // Work Items
            'view progress-work-items',
            'create progress-work-items',
            'edit progress-work-items',
            'delete progress-work-items',
            
            // Categories
            'view progress-categories',
            'create progress-categories',
            'edit progress-categories',
            'delete progress-categories',
            
            // Issues
            'view progress-issues',
            'create progress-issues',
            'edit progress-issues',
            'delete progress-issues',
            
            // Activity Logs
            'view activity-logs',
            
            // Backup
            'view backup',
            'create backup',
            'delete backup',
            
            // Recycle Bin
            'view recycle-bin',
            'restore recycle-bin',
            'delete recycle-bin',
            
            // Reports
            'view progress-reports',
            
            // Dashboard
            'view progress-dashboard',
            
            // Data Export
            'export progress-data',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Get admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        
        // Assign all Progress permissions to admin
        $adminRole->givePermissionTo($permissions);

        // Assign all permissions to user ID 1 (admin user)
        $adminUser = User::find(1);
        if ($adminUser) {
            $adminUser->givePermissionTo($permissions);
            echo "✅ Assigned " . count($permissions) . " permissions to User ID 1\n";
        }

        echo "✅ Created/Updated " . count($permissions) . " permissions in new format\n";
    }
}
