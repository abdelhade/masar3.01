<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BackupPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create backup permission
        $permission = Permission::firstOrCreate([
            'name' => 'backup-create',
            'guard_name' => 'web',
        ]);

        // Give permission to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$adminRole->hasPermissionTo('backup-create')) {
            $adminRole->givePermissionTo('backup-create');
        }

        echo "Backup permission created and assigned to admin\n";
    }
}
