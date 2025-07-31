<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftSeeder extends Seeder
{
    public function run()
    {
        DB::table('shifts')->insert([
            [
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'days' => json_encode(['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday']),
                'shift_type' => 'morning',
                'notes' => 'Shift 1',
            ],
        ]);
    }
}