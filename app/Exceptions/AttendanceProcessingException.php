<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class AttendanceProcessingException extends Exception
{
    /** @var array<int, array> */
    public array $overlappingProcessings = [];

    /**
     * Create a new exception instance for overlapping processing
     *
     * @param string $message Error message
     * @param array<int, array> $overlappingProcessings Array of overlapping processing records
     * @return self
     */
    public static function overlappingProcessing(string $message, array $overlappingProcessings = []): self
    {
        $exception = new self($message);
        $exception->overlappingProcessings = $overlappingProcessings;
        
        return $exception;
    }

    /**
     * Create a new exception instance for inactive employee
     */
    public static function inactiveEmployee(string $employeeName, int $employeeId): self
    {
        return new self("❌ الموظف {$employeeName} (كود: {$employeeId}) معطل ولا يمكن معالجة بصماته أو رواتبه.");
    }

    /**
     * Create a new exception instance for no attendance records
     */
    public static function noAttendanceRecords(string $message): self
    {
        return new self($message);
    }
}

