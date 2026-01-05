<?php

declare(strict_types=1);

namespace Modules\Authorization\Database\Seeders;

use Illuminate\Database\Seeder;

class RoleAndPermissionDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            PermissionSelectiveOptionsSeeder::class,
            // PermissionSeeder::class,
            // DashboardPermissionsSeeder::class,
        ]);
    }
}
