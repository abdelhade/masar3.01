<?php

declare(strict_types=1);

namespace Modules\Accounts\Database\Seeders;

use Illuminate\Database\Seeder;

class AccountsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            AccountsTypesSeeder::class,
            AccHeadSeeder::class,
            AccountsPermissionsSeeder::class,
        ]);
    }
}
