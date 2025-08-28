<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RentalsProTypesSeeder extends Seeder
{
    public function run(): void
    {
        // سجل واحد للإيجارات
        DB::table('pro_types')->insert([
            'id' => 64,
            'pname' => 'rentals',
            'ptext' => 'تأجير وحدات',
            'ptype' => 'تأجير'
        ]);
    }
}
