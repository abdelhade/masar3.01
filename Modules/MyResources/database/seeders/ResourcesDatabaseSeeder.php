<?php

namespace Modules\MyResources\database\seeders;

use Illuminate\Database\Seeder;

class ResourcesDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ResourceCategoriesSeeder::class,
            ResourceTypesSeeder::class,
            ResourceStatusesSeeder::class,
            ResourcesPermissionsSeeder::class,
        ]);
    }
}
