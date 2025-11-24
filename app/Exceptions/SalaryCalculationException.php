<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class SalaryCalculationException extends Exception
{
    /**
     * Create a new exception instance for invalid period
     */
    public static function invalidPeriod(string $startDate, string $endDate): self
    {
        return new self("الفترة غير صحيحة: من {$startDate} إلى {$endDate}");
    }

    /**
     * Create a new exception instance for missing shift
     */
    public static function missingShift(int $employeeId): self
    {
        return new self("الموظف (كود: {$employeeId}) لا يملك مناوبة محددة.");
    }
}

