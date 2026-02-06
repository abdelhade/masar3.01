<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder2 extends Seeder
{
    public function run()
    {
        $permissions = [
            Permission::firstOrCreate(['name' => 'recycle-bin-list']),
            Permission::firstOrCreate(['name' => 'recycle-bin-restore']),
            Permission::firstOrCreate(['name' => 'recycle-bin-permanent-delete']),
        ];
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo($permissions);
    }
}
