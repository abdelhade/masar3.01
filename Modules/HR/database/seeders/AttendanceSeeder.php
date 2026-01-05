<?php

declare(strict_types=1);

namespace Modules\HR\database\seeders;

use Modules\HR\Models\Attendance;
use Modules\HR\Models\Employee;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employee = Employee::where('email', 'mohamed@example.com')->first();

        if (! $employee) {
            return;
        }

        $attendances = [
            [
                'employee_id' => $employee->id,
                'employee_attendance_finger_print_id' => '01',
                'employee_attendance_finger_print_name' => 'محمد عبد الله',
                'type' => 'check_in',
                'date' => '2025-07-01',
                'time' => '08:00:00',
                'location' => 'Focus House',
                'status' => 'pending',
            ],
            [
                'employee_id' => $employee->id,
                'employee_attendance_finger_print_id' => '01',
                'employee_attendance_finger_print_name' => 'محمد عبد الله',
                'type' => 'check_out',
                'date' => '2025-07-01',
                'time' => '17:00:00',
                'location' => 'Focus House',
                'status' => 'pending',
            ],
        ];

        foreach ($attendances as $attendance) {
            Attendance::firstOrCreate(
                [
                    'employee_id' => $attendance['employee_id'],
                    'type' => $attendance['type'],
                    'date' => $attendance['date'],
                    'time' => $attendance['time'],
                ],
                $attendance
            );
        }
    }
}
