<?php

use Modules\HR\Models\Employee;
use Modules\HR\Services\SalaryCalculationService;
use Carbon\Carbon;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$employeeId = 2;
$startDate = Carbon::parse('2025-12-01');
$endDate = Carbon::parse('2025-12-31');

$employee = Employee::with('shift', 'department')->find($employeeId);

if (!$employee) {
    echo "Employee #$employeeId not found.\n";
    exit;
}

echo "Employee: {$employee->first_name} {$employee->last_name} (ID: {$employee->id})\n";
echo "Salary: {$employee->salary}\n";
echo "Salary Type: {$employee->salary_type}\n";
echo "Limit Late Days: " . ($employee->late_day_calculation ?? 'Default(1)') . "\n";
echo "Limit Late Hours: " . ($employee->late_hour_calculation ?? 'Default(1)') . "\n";

$service = new SalaryCalculationService();

// Force invalidation first to ensure fresh calculation
$service->invalidateCache($employee, $startDate, $endDate);

$result = $service->calculateSalary($employee, $startDate, $endDate);

echo "\n--- SUMMARY ---\n";
foreach ($result['summary'] as $key => $val) {
    echo "$key: $val\n";
}

echo "\n--- SALARY DATA ---\n";
foreach ($result['salary_data'] as $key => $val) {
    echo "$key: $val\n";
}

echo "\n--- DAILY BREAKDOWN (First 5 days with issues) ---\n";
$count = 0;
foreach ($result['details'] as $date => $detail) {
    if ($detail['status'] === 'absent' || $detail['status'] === 'half_day' || $detail['late_minutes'] > 0) {
        if ($count++ < 10) {
            echo "$date: {$detail['status']} | In: {$detail['check_in_time']} | Out: {$detail['check_out_time']} | Late: {$detail['late_minutes']}m | Notes: {$detail['notes']}\n";
        }
    }
}

echo "\n--- CALCULATION EXPLANATION ---\n";
$dailyRate = $result['salary_data']['daily_rate'];
$absentDays = $result['summary']['absent_days'];
$lateDayCalc = $employee->late_day_calculation ?? 1.0;
$absentDeduction = $result['salary_data']['absent_days_deduction'];

echo "Daily Rate: $dailyRate\n";
echo "Absent Days: $absentDays\n";
echo "Late Day Factors: $lateDayCalc\n";
echo "Expected Absent Deduction: $absentDays * $lateDayCalc * $dailyRate = " . ($absentDays * $lateDayCalc * $dailyRate) . "\n";
echo "Actual Absent Deduction: $absentDeduction\n";

