<?php

namespace Modules\Quality\database\seeders;

use Illuminate\Database\Seeder;

class QualityDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            QualityModulePermissionsSeeder::class,
        ]);
    }
}
