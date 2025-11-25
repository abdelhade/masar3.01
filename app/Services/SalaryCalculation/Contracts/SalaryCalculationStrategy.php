<?php

declare(strict_types=1);

namespace App\Services\SalaryCalculation\Contracts;

use App\Models\Employee;

interface SalaryCalculationStrategy
{
    /**
     * Calculate salary based on employee and summary data
     *
     * @param Employee $employee The employee to calculate salary for
     * @param array<string, mixed> $summary Summary data from attendance processing
     * @return array<string, mixed> Salary calculation results
     */
    public function calculate(Employee $employee, array $summary): array;
}

