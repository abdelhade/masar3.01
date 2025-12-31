<?php

namespace Modules\Maintenance\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Maintenance\database\seeders\MaintenancePermissionsSeeder;

class MaintenanceDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            MaintenancePermissionsSeeder::class,
        ]);
    }
}
