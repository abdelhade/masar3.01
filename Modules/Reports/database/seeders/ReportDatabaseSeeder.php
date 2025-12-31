<?php

namespace Modules\Reports\database\seeders;

use Illuminate\Database\Seeder;

class ReportDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            ReportPermissionsSeeder::class,
        ]);
    }
}
