<?php

namespace Modules\OfflinePOS\database\seeders;

use Illuminate\Database\Seeder;

class OfflinePOSDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            OfflinePOSPermissionsSeeder::class,
        ]);
    }
}
