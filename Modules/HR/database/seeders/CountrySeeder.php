<?php

declare(strict_types=1);

namespace Modules\HR\database\seeders;

use Modules\HR\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Country::firstOrCreate(
            ['title' => 'Saudi Arabia']
        );
    }
}
