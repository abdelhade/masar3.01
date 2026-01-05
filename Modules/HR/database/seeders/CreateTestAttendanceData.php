<?php

namespace Modules\HR\database\seeders;

use Illuminate\Database\Seeder;
use Modules\HR\Models\Employee;
use Modules\HR\Models\Attendance;
use Carbon\Carbon;

class CreateTestAttendanceData extends Seeder
{
    public function run(): void
    {
        // Get first employee
        $employee = Employee::first();

        if (!$employee) {
            echo "No employees found!\n";
            return;
        }

        echo "Creating attendance data for employee: " . $employee->name . "\n";

        // Create attendance records for January 2025
        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-01-31');

        $recordsCreated = 0;

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayOfWeek = $date->dayOfWeek;

            // Only create attendance for working days (Monday to Friday)
            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                // Create check-in record
                Attendance::create([
                    'employee_id' => $employee->id,
                    'employee_attendance_finger_print_id' => 'FP_' . $employee->id . '_' . $date->format('Ymd'),
                    'employee_attendance_finger_print_name' => $employee->name,
                    'type' => 'check_in',
                    'date' => $date->format('Y-m-d'),
                    'time' => '08:00:00',
                    'location' => 'Main Office',
                    'status' => 'approved'
                ]);

                // Create check-out record
                Attendance::create([
                    'employee_id' => $employee->id,
                    'employee_attendance_finger_print_id' => 'FP_' . $employee->id . '_' . $date->format('Ymd') . '_out',
                    'employee_attendance_finger_print_name' => $employee->name,
                    'type' => 'check_out',
                    'date' => $date->format('Y-m-d'),
                    'time' => '16:00:00',
                    'location' => 'Main Office',
                    'status' => 'approved'
                ]);

                $recordsCreated += 2;
            }
        }

        echo "Created " . $recordsCreated . " attendance records\n";
        echo "Date range: " . $startDate->format('Y-m-d') . " to " . $endDate->format('Y-m-d') . "\n";
    }
}
