<?php

declare(strict_types=1);

namespace Modules\ActivityLog\database\seeders;

use Illuminate\Database\Seeder;

class ActivityLogDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ActivityLogPermissionsSeeder::class,
        ]);
    }
}
