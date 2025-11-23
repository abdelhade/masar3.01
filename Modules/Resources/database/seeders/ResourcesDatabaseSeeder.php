<?php

namespace Modules\MyResources\Database\Seeders;

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
