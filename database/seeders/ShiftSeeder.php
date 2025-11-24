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

                'name'=>'general',
                'start_time' => '08:00:00',
                'beginning_check_in' => '07:30:00',
                'ending_check_in' => '08:30:00',
                'allowed_late_minutes' => 15,
                'end_time' => '16:00:00',
                'beginning_check_out' => '15:30:00',
                'ending_check_out' => '16:30:00',
                'allowed_early_leave_minutes' => 15,
                'days' => json_encode(['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday']),
                'shift_type' => 'morning',
                'notes' => 'Shift 1',
            ],
        ]);
    }
}
