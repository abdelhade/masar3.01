<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Department;
use Carbon\Carbon;

class AttendanceProcessingTestSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // Create sample departments if they don't exist
        $departments = [
            'الإدارة العامة',
            'المحاسبة',
            'الموارد البشرية',
            'تقنية المعلومات',
            'المبيعات'
        ];

        foreach ($departments as $deptName) {
            Department::firstOrCreate(['title' => $deptName]);
        }

        // Get existing employees or create sample ones
        $employees = Employee::with('department')->take(10)->get();
        
        if ($employees->count() < 5) {
            // Create sample employees if not enough exist
            $sampleEmployees = [
                ['name' => 'نور إبراهيم', 'department' => 'الإدارة العامة', 'salary' => 5000],
                ['name' => 'عبدالهادي العبير', 'department' => 'المحاسبة', 'salary' => 4500],
                ['name' => 'مصطفى حمادة', 'department' => 'الموارد البشرية', 'salary' => 4000],
                ['name' => 'عصام الجولاني', 'department' => 'تقنية المعلومات', 'salary' => 6000],
                ['name' => 'شمس الدين', 'department' => 'المبيعات', 'salary' => 3500],
            ];

            foreach ($sampleEmployees as $empData) {
                $department = Department::where('title', $empData['department'])->first();
                if ($department) {
                    Employee::firstOrCreate([
                        'name' => $empData['name']
                    ], [
                        'department_id' => $department->id,
                        'salary' => $empData['salary'],
                        'salary_type' => 'ساعات عمل فقط',
                        'email' => strtolower(str_replace(' ', '.', $empData['name'])) . '@company.com',
                        'phone' => '0' . rand(100000000, 999999999),
                        'additional_hour_calculation' => 1.5,
                    ]);
                }
            }
            
            $employees = Employee::with('department')->take(10)->get();
        }

        // Generate attendance data for the last 30 days
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        foreach ($employees as $employee) {
            $this->generateAttendanceForEmployee($employee, $startDate, $endDate);
        }

        $this->command->info('تم إنشاء بيانات الحضور التجريبية بنجاح!');
    }

    /**
     * Generate attendance data for an employee
     */
    private function generateAttendanceForEmployee(Employee $employee, Carbon $startDate, Carbon $endDate): void
    {
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            // Skip weekends (Friday and Saturday for most Middle Eastern countries)
            if (!in_array($currentDate->dayOfWeek, [5, 6])) { // 5=Friday, 6=Saturday
                
                // 85% chance of attendance
                if (rand(1, 100) <= 85) {
                    $this->createAttendanceRecord($employee, $currentDate);
                }
            }
            
            $currentDate->addDay();
        }
    }

    /**
     * Create attendance record for employee on a specific date
     */
    private function createAttendanceRecord(Employee $employee, Carbon $date): void
    {
        // Define normal working hours (8:00 AM to 4:00 PM)
        $normalCheckIn = '08:00:00';
        $normalCheckOut = '16:00:00';
        
        // Add some variance to check-in time (-30 to +60 minutes)
        $checkInVariance = rand(-30, 60);
        $checkInTime = Carbon::parse($normalCheckIn)->addMinutes($checkInVariance)->format('H:i:s');
        
        // Add some variance to check-out time (-30 to +120 minutes)  
        $checkOutVariance = rand(-30, 120);
        $checkOutTime = Carbon::parse($normalCheckOut)->addMinutes($checkOutVariance)->format('H:i:s');
        
        // Create check-in record
        Attendance::create([
            'employee_id' => $employee->id,
            'employee_attendance_finger_print_id' => str_pad($employee->id, 4, '0', STR_PAD_LEFT),
            'employee_attendance_finger_print_name' => $employee->name,
            'type' => 'check_in',
            'date' => $date->format('Y-m-d'),
            'time' => $checkInTime,
            'status' => 'approved',
            'location' => 'المكتب الرئيسي',
        ]);

        // Create check-out record (90% chance)
        if (rand(1, 100) <= 90) {
            Attendance::create([
                'employee_id' => $employee->id,
                'employee_attendance_finger_print_id' => str_pad($employee->id, 4, '0', STR_PAD_LEFT),
                'employee_attendance_finger_print_name' => $employee->name,
                'type' => 'check_out',
                'date' => $date->format('Y-m-d'),
                'time' => $checkOutTime,
                'status' => 'approved',
                'location' => 'المكتب الرئيسي',
            ]);
        }
    }
}