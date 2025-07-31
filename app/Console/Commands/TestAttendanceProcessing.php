<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Department;
use App\Services\AttendanceProcessingService;
use App\Services\SalaryCalculationService;
use Carbon\Carbon;

class TestAttendanceProcessing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:attendance-processing {--employee-id=} {--department-id=} {--start-date=} {--end-date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test attendance processing and salary calculation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Attendance Processing and Salary Calculation...');

        // Get parameters
        $employeeId = $this->option('employee-id');
        $departmentId = $this->option('department-id');
        $startDate = $this->option('start-date') ? Carbon::parse($this->option('start-date')) : now()->subDays(7);
        $endDate = $this->option('end-date') ? Carbon::parse($this->option('end-date')) : now();

        $this->info("Period: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

        // Initialize services
        $attendanceService = new AttendanceProcessingService();
        $salaryService = new SalaryCalculationService();

        try {
            if ($employeeId) {
                $this->testSingleEmployee($employeeId, $startDate, $endDate, $attendanceService, $salaryService);
            } elseif ($departmentId) {
                $this->testDepartment($departmentId, $startDate, $endDate, $attendanceService, $salaryService);
            } else {
                $this->testAllEmployees($startDate, $endDate, $attendanceService, $salaryService);
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }

        $this->info('Test completed successfully!');
        return 0;
    }

    private function testSingleEmployee($employeeId, $startDate, $endDate, $attendanceService, $salaryService)
    {
        $this->info("Testing single employee (ID: {$employeeId})...");

        $employee = Employee::findOrFail($employeeId);
        $this->info("Employee: {$employee->name}");

        // Process attendance
        $result = $attendanceService->processSingleEmployee($employee, $startDate, $endDate, 'Test processing');
        $this->info("Processing ID: {$result['processing_id']}");

        // Calculate salary
        $salaryData = $salaryService->calculateSalary($employee, $startDate, $endDate);
        $this->info("Salary calculation ID: {$salaryData['calculation_id']}");

        // Display results
        $this->displayResults($result, $salaryData);
    }

    private function testDepartment($departmentId, $startDate, $endDate, $attendanceService, $salaryService)
    {
        $this->info("Testing department (ID: {$departmentId})...");

        $department = Department::findOrFail($departmentId);
        $this->info("Department: {$department->title}");

        // Process attendance
        $result = $attendanceService->processDepartment($department, $startDate, $endDate, 'Test processing');
        $this->info("Processing ID: {$result['processing_id']}");

        // Display results for each employee
        foreach ($result['results'] as $employeeResult) {
            $this->info("Employee: {$employeeResult['employee']->name}");
            $this->displayResults($employeeResult, null);
        }
    }

    private function testAllEmployees($startDate, $endDate, $attendanceService, $salaryService)
    {
        $this->info('Testing all employees...');

        $employees = Employee::where('status', 'مفعل')->get();
        $this->info("Found {$employees->count()} active employees");

        foreach ($employees as $employee) {
            $this->info("Processing employee: {$employee->name}");
            
            try {
                $result = $attendanceService->processSingleEmployee($employee, $startDate, $endDate, 'Test processing');
                $salaryData = $salaryService->calculateSalary($employee, $startDate, $endDate);
                
                $this->info("  - Processing ID: {$result['processing_id']}");
                $this->info("  - Salary calculation ID: {$salaryData['calculation_id']}");
                $this->info("  - Total salary: {$salaryData['salary_data']['total_salary']} SAR");
            } catch (\Exception $e) {
                $this->warn("  - Error processing {$employee->name}: {$e->getMessage()}");
            }
        }
    }

    private function displayResults($result, $salaryData)
    {
        $summary = $result['summary'];
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Days', $summary['total_days']],
                ['Working Days', $summary['working_days']],
                ['Present Days', $summary['present_days']],
                ['Absent Days', $summary['absent_days']],
                ['Total Hours', $summary['total_hours']],
                ['Actual Hours', $summary['actual_hours']],
                ['Overtime Hours', $summary['overtime_hours']],
                ['Late Hours', $summary['late_hours']],
            ]
        );

        if ($salaryData) {
            $salary = $salaryData['salary_data'];
            $this->table(
                ['Salary Component', 'Amount (SAR)'],
                [
                    ['Basic Salary', number_format($salary['basic_salary'], 2)],
                    ['Overtime Salary', number_format($salary['overtime_salary'], 2)],
                    ['Total Salary', number_format($salary['total_salary'], 2)],
                ]
            );
        }
    }
} 