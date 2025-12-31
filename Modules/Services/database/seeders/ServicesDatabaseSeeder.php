<?php

namespace Modules\Services\database\seeders;

use Illuminate\Database\Seeder;

class ServicesDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            ServicesPermissionsSeeder::class,
        ]);
    }
}
