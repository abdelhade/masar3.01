<?php

declare(strict_types=1);

namespace Modules\HR\Database\Seeders;

use Modules\HR\Models\Country;
use Modules\HR\Models\State;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $country = Country::firstOrCreate(['title' => 'Saudi Arabia']);

        State::firstOrCreate(
            [
                'title' => 'Riyadh',
                'country_id' => $country->id,
            ]
        );
    }
}
