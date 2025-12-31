<?php

namespace Modules\Rentals\database\seeders;

use Illuminate\Database\Seeder;

class RentalsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RentalsPermissionsSeeder::class,
        ]);
    }
}
