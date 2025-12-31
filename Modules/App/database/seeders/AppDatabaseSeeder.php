<?php

namespace Modules\App\database\seeders;

use Illuminate\Database\Seeder;

class AppDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            AppPermissionsSeeder::class,
        ]);
    }
}
