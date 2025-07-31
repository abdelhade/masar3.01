<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */                         
    public function run()
    {
        DB::table('attendances')->insert([
            [
                'employee_id' => 1,
                'employee_attendance_finger_print_id' => '01',
                'employee_attendance_finger_print_name' => 'محمد عبد الله',
                'type' => 'check_in',
                'date' => '2025-07-01',
                'time' => '08:00:00',
                'location' => 'Focus House',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 1,
                'employee_attendance_finger_print_id' => '01',
                'employee_attendance_finger_print_name' => 'محمد عبد الله',
                'type' => 'check_out',
                'date' => '2025-07-01',
                'time' => '17:00:00',
                'location' => 'Focus House',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
