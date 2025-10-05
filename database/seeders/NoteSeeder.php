<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NoteSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('notes')->insert([
            [
                'name' => 'المجموعات',
            ],
            [
                'name' => 'التصنيفات',
            ],
            [
                'name' => 'الاماكن',
            ],
        ]);
    }
}
