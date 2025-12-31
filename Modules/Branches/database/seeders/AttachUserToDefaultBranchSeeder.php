<?php

namespace Modules\Branches\database\seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Branches\Models\Branch;

class AttachUserToDefaultBranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // هجيب اليوزر admin
        $user = User::where('email', 'admin@admin.com')->first();

        if (! $user) {
            $this->command->error('User not found!');
            return;
        }

        // هجيب الفرع MAIN
        $branch = Branch::where('code', 'MAIN')->first();

        if (! $branch) {
            $this->command->error('Branch MAIN not found!');
            return;
        }

        // اربط اليوزر بالفرع (pivot)
        $user->branches()->syncWithoutDetaching([$branch->id]);

    }
}
