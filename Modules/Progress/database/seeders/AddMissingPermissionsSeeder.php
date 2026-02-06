<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class AddMissingPermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'projects-save-as-template',
            'projects-copy'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}