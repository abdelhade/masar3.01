<?php

namespace Modules\Depreciation\database\seeders;

use Illuminate\Database\Seeder;

class DepreciationDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepreciationItemsSeeder::class,
            DepreciationPermissionsSeeder::class,
        ]);
    }
}
