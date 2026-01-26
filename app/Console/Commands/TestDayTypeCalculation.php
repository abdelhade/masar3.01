<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\HR\Models\Employee;
use App\Models\Shift;
use App\Services\SalaryCalculationService;
use Carbon\Carbon;

class TestDayTypeCalculation extends Command
{
    protected $signature = 'test:day-type-calculation {employee_id} {start_date} {end_date}';
    protected $description = 'Test day type calculation for an employee';

    public function handle()
    {
        $employeeId = $this->argument('employee_id');
        $startDate = $this->argument('start_date');
        $endDate = $this->argument('end_date');

        $employee = Employee::find($employeeId);
        if (!$employee) {
            $this->error("Employee not found with ID: {$employeeId}");
            return 1;
        }

        $this->info("Testing day type calculation for employee: {$employee->name}");
        $this->info("Shift: " . ($employee->shift ? $employee->shift->notes : 'No shift'));
        
        if ($employee->shift) {
            $workingDays = json_decode($employee->shift->days, true) ?? [];
            $this->info("Working days: " . implode(', ', $workingDays));
        }

        $service = new SalaryCalculationService();
        $result = $service->calculateSalary($employee, Carbon::parse($startDate), Carbon::parse($endDate));

        $this->info("\nSummary:");
        $this->info("Total days: " . $result['summary']['total_days']);
        $this->info("Working days: " . $result['summary']['working_days']);
        $this->info("Holiday days: " . $result['summary']['holiday_days']);
        $this->info("Overtime days: " . $result['summary']['overtime_days']);
        $this->info("Present days: " . $result['summary']['present_days']);
        $this->info("Absent days: " . $result['summary']['absent_days']);

        $this->info("\nDaily details:");
        foreach ($result['details'] as $date => $dayData) {
            $this->info("{$date}: {$dayData['day_type']} - {$dayData['status']}");
        }

        return 0;
    }
} 