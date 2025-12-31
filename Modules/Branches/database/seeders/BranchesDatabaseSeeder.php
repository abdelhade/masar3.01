<?php

namespace Modules\Branches\database\seeders;

use Illuminate\Database\Seeder;

class BranchesDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            BranchesPermissionsSeeder::class,
            BranchSeeder::class,
            AttachUserToDefaultBranchSeeder::class,
        ]);
    }
}
