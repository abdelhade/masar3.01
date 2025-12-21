<?php

namespace Modules\Depreciation\Database\Seeders;

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