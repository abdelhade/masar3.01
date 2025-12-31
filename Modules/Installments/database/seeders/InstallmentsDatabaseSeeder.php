<?php

namespace Modules\Installments\database\seeders;

use Illuminate\Database\Seeder;

class InstallmentsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            InstallmentsPermissionsSeeder::class,
        ]);
    }
}
