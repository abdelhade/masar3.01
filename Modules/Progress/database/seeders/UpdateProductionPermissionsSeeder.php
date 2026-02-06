<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UpdateProductionPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Clear permission cache to key new permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Starting production permission update...');

        // List of all permissions that SHOULD exist now
        // Included recently added ones like backup, projects-gantt, etc.
        $permissionsToAdd = [
            // Backup
            'backup-create',
            'backup-view',
            
            // Projects
            'projects-gantt',
            'projects-save-as-template',
            'projects-copy',
            'projects-view-all',
        ];

        foreach ($permissionsToAdd as $permName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web']
            );
            $this->command->info("Verified permission: {$permName}");
        }

        // 2. Assign these specifically to Admin Role (safest bet)
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'web')->first();
        
        if ($adminRole) {
            foreach ($permissionsToAdd as $permName) {
                if (!$adminRole->hasPermissionTo($permName)) {
                    $adminRole->givePermissionTo($permName);
                    $this->command->info("Granted {$permName} to Admin.");
                }
            }
        } else {
            $this->command->error('Admin role not found!');
        }

        $this->command->info('Update completed successfully.');
    }
}
