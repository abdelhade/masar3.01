<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Shift;
use App\Models\Department;
use App\Services\SalaryCalculationService;
use Carbon\Carbon;

class TestAttendanceData extends Command
{
    protected $signature = 'test:attendance';
    protected $description = 'Test attendance data and processing';

    public function handle()
    {
        $this->info('🔍 Testing Attendance Data...');

        // Check if we have any employees
        $employees = Employee::all();
        $this->info('👥 Found ' . $employees->count() . ' employees');

        foreach ($employees as $employee) {
            $this->info('👤 Employee: ' . $employee->name . ' (ID: ' . $employee->id . ')');
            $this->info('   Department: ' . ($employee->department?->title ?? 'No Department'));
            $this->info('   Shift: ' . ($employee->shift?->shift_type ?? 'No Shift'));
            $this->info('   Salary: ' . $employee->salary);
            $this->info('   Salary Type: ' . $employee->salary_type);
        }

        // Check if we have any attendance records
        $totalAttendance = Attendance::count();
        $this->info('📊 Total attendance records: ' . $totalAttendance);

        if ($totalAttendance > 0) {
            $this->info('📝 Sample attendance records:');
            $sampleRecords = Attendance::with('employee')->take(5)->get();
            foreach ($sampleRecords as $record) {
                $this->info('   ID: ' . $record->id . ', Employee: ' . $record->employee->name . ', Date: ' . $record->date . ', Time: ' . $record->time . ', Type: ' . $record->type);
            }
        }

        // Test processing for first employee
        if ($employees->count() > 0) {
            $employee = $employees->first();
            $this->info('🧪 Testing processing for employee: ' . $employee->name);

            $startDate = Carbon::parse('2025-01-01');
            $endDate = Carbon::parse('2025-01-31');

            $service = new SalaryCalculationService();
            $result = $service->calculateSalary($employee, $startDate, $endDate);

            $this->info('📈 Processing Results:');
            $this->info('   Summary: ' . json_encode($result['summary']));
            $this->info('   Details count: ' . count($result['details']));
            
            // Show first few details
            $details = array_slice($result['details'], 0, 5, true);
            foreach ($details as $date => $detail) {
                $this->info('   Date: ' . $date . ' - Status: ' . $detail['status'] . ' - Hours: ' . $detail['actual_hours']);
            }
        }

        $this->info('✅ Test completed!');
    }
} 