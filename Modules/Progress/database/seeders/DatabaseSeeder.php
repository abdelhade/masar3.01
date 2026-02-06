<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            UserSeeder::class,
        ]);

        $this->call([
            RolesAndPermissionsSeeder::class,
            CategoriesPermissionsSeeder::class,
        ]);

              $this->call([
                AddBackupAndProjectsPermissionsSeeder::class,
        ]);
              $this->call([
                AddMissingPermissionsSeeder::class,
        ]);

        $this->call([
            IssuesPermissionsSeeder::class,
            ItemStatusesPermissionsSeeder::class,
        ]);

 
    }
}
