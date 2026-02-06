<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class GiveProgressPermissionsToAdminSeeder extends Seeder
{
    /**
     * إعطاء User #1 جميع صلاحيات Progress module
     */
    public function run(): void
    {
        // جميع صلاحيات Progress
        $progressPermissions = [
            // Dashboard
            'view progress-dashboard',
            
            // Projects
            'view progress-projects',
            'create progress-projects',
            'edit progress-projects',
            'delete progress-projects',
            
            // Daily Progress
            'view daily-progress',
            'create daily-progress',
            'edit daily-progress',
            'delete daily-progress',
            
            // Issues
            'view progress-issues',
            'create progress-issues',
            'edit progress-issues',
            'delete progress-issues',
            
            // Clients
            'view progress-clients',
            'create progress-clients',
            'edit progress-clients',
            'delete progress-clients',
            
            // Employees
            'view progress-employees',
            'create progress-employees',
            'edit progress-employees',
            'delete progress-employees',
            
            // Work Items
            'view progress-work-items',
            'create progress-work-items',
            'edit progress-work-items',
            'delete progress-work-items',
            
            // Work Item Categories
            'view progress-work-item-categories',
            'create progress-work-item-categories',
            'edit progress-work-item-categories',
            'delete progress-work-item-categories',
            
            // Item Statuses
            'view progress-item-statuses',
            'create progress-item-statuses',
            'edit progress-item-statuses',
            'delete progress-item-statuses',
            
            // Project Templates
            'view progress-project-templates',
            'create progress-project-templates',
            'edit progress-project-templates',
            'delete progress-project-templates',
            
            // Project Types
            'view progress-project-types',
            'create progress-project-types',
            'edit progress-project-types',
            'delete progress-project-types',
            
            // Activity Logs
            'view progress-activity-logs',
            
            // Recycle Bin
            'view progress-recyclebin',
            'restore progress-recyclebin',
            'force-delete progress-recyclebin',
            
            // Backup
            'view progress-backup',
            'create progress-backup',
            'download progress-backup',
            'delete progress-backup',
        ];

        // إنشاء الصلاحيات إذا لم تكن موجودة
        foreach ($progressPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        // إعطاء User #1 جميع الصلاحيات
        $admin = User::find(1);
        
        if ($admin) {
            echo "✅ إعطاء User #1 ({$admin->name}) جميع صلاحيات Progress...\n";
            
            foreach ($progressPermissions as $permission) {
                if (!$admin->hasPermissionTo($permission)) {
                    $admin->givePermissionTo($permission);
                }
            }
            
            echo "✅ تم إعطاء " . count($progressPermissions) . " صلاحية بنجاح!\n";
        } else {
            echo "❌ User #1 غير موجود!\n";
        }
    }
}
