<?php

declare(strict_types=1);

namespace Modules\Progress\Database\Seeders;

use Illuminate\Database\Seeder;

class ProgressDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            ProgressPermissionsSeeder::class,
        ]);
    }
}
