<?php

namespace Modules\Checks\database\seeders;

use Illuminate\Database\Seeder;

class ChecksDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            CheckPortfoliosAccountsSeeder::class,
            CheckPortfoliosPermissionsSeeder::class,
            ChecksPermissionsSeeder::class,
        ]);
    }
}
