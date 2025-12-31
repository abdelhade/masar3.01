<?php

namespace Modules\Inquiries\database\seeders;

use Illuminate\Database\Seeder;

class InquiriesDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            InquiriesPermissionsSeeder::class,
            DiffcultyMatrixSeeder::class,
            InquiriesRolesSeeder::class,
        ]);
    }
}
