<?php

namespace Modules\HR\database\seeders;

use Illuminate\Database\Seeder;
use Modules\HR\Models\Employee;
use Modules\HR\Models\Department;
use Modules\HR\Models\Shift;
use Modules\HR\Models\Attendance;
use Carbon\Carbon;

class AttendanceTestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create departments
        $departments = ['Finance', 'IT', 'HR', 'Marketing'];
        foreach ($departments as $deptName) {
            Department::firstOrCreate(['title' => $deptName]);
        }

        // Create shifts
        $shifts = [
            [
                'name' => 'Morning Shift',
                'shift_type' => 'morning',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
                'notes' => 'Morning Shift (8:00-16:00)'
            ],
            [
                'name' => 'evening Shift',
                'shift_type' => 'evening',
                'start_time' => '16:00:00',
                'end_time' => '00:00:00',
                'days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
                'notes' => 'Evening Shift (16:00-00:00)'
            ],
            [
                'name' => 'Weekend Shift',
                'shift_type' => 'night',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'days' => json_encode(['saturday', 'sunday']),
                'notes' => 'Weekend Shift (9:00-17:00)'
            ]
        ];

        foreach ($shifts as $shiftData) {
            Shift::firstOrCreate(['shift_type' => $shiftData['shift_type']], $shiftData);
        }

        // Create employees with test data
        $employees = [
            [
                'name' => 'محمد عبد الله',
                'email' => 'mohammed@example.com',
                'phone' => '0123456789',
                'department' => 'Finance',
                'shift_type' => 'morning',
                'salary' => 5000,
                'salary_type' => 'ساعات عمل فقط'
            ],
            [
                'name' => 'أحمد علي',
                'email' => 'ahmed@example.com',
                'phone' => '0123456790',
                'department' => 'IT',
                'shift_type' => 'evening',
                'salary' => 6000,
                'salary_type' => 'ساعات عمل و إضافي يومى'
            ],
            [
                'name' => 'فاطمة محمد',
                'email' => 'fatima@example.com',
                'phone' => '0123456791',
                'department' => 'HR',
                'shift_type' => 'morning',
                'salary' => 4500,
                'salary_type' => 'حضور فقط'
            ]
        ];

        foreach ($employees as $empData) {
            $department = Department::where('title', $empData['department'])->first();
            $shift = Shift::where('shift_type', $empData['shift_type'])->first();

            $employee = Employee::firstOrCreate(
                ['email' => $empData['email']],
                [
                    'name' => $empData['name'],
                    'phone' => $empData['phone'],
                    'department_id' => $department->id,
                    'shift_id' => $shift->id,
                    'salary' => $empData['salary'],
                    'salary_type' => $empData['salary_type'],
                    'additional_hour_calculation' => 1.5,
                    'additional_day_calculation' => 1.0
                ]
            );

            // Create attendance records for this employee
            $this->createAttendanceRecords($employee);
        }
    }

    private function createAttendanceRecords(Employee $employee): void
    {
        // Create attendance records for January 2025
        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-01-31');

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
            }
        }

        // Create some overtime records
        $overtimeDates = ['2025-01-03', '2025-01-10', '2025-01-17', '2025-01-24'];
        foreach ($overtimeDates as $date) {
            // Additional check-in for overtime
            Attendance::create([
                'employee_id' => $employee->id,
                'employee_attendance_finger_print_id' => 'FP_' . $employee->id . '_' . str_replace('-', '', $date) . '_ot',
                'employee_attendance_finger_print_name' => $employee->name,
                'type' => 'check_in',
                'date' => $date,
                'time' => '16:00:00',
                'location' => 'Main Office',
                'status' => 'approved'
            ]);

            // Additional check-out for overtime
            Attendance::create([
                'employee_id' => $employee->id,
                'employee_attendance_finger_print_id' => 'FP_' . $employee->id . '_' . str_replace('-', '', $date) . '_ot_out',
                'employee_attendance_finger_print_name' => $employee->name,
                'type' => 'check_out',
                'date' => $date,
                'time' => '20:00:00',
                'location' => 'Main Office',
                'status' => 'approved'
            ]);
        }
    }
}
