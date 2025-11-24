<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Employee;
use App\Services\AttendanceProcessingService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSingleEmployeeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted
     */
    public int $tries = 3;

    /**
     * Maximum number of seconds the job can run
     */
    public int $timeout = 300; // 5 minutes

    /**
     * Create a new job instance
     *
     * @param int $employeeId Employee ID to process
     * @param string $startDate Start date (Y-m-d format)
     * @param string $endDate End date (Y-m-d format)
     * @param string|null $notes Optional notes
     */
    public function __construct(
        public int $employeeId,
        public string $startDate,
        public string $endDate,
        public ?string $notes = null
    ) {}

    /**
     * Execute the job
     */
    public function handle(AttendanceProcessingService $service): void
    {
        $employee = Employee::findOrFail($this->employeeId);
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);

        Log::info('Starting single employee attendance processing job', [
            'employee_id' => $this->employeeId,
            'employee_name' => $employee->name,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);

        try {
            $result = $service->processSingleEmployee($employee, $startDate, $endDate, $this->notes);

            if (isset($result['error'])) {
                Log::error('Single employee attendance processing job failed', [
                    'employee_id' => $this->employeeId,
                    'error' => $result['error'],
                ]);
                throw new \Exception($result['error']);
            }

            Log::info('Single employee attendance processing job completed successfully', [
                'employee_id' => $this->employeeId,
                'processing_id' => $result['processing_id'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Single employee attendance processing job exception', [
                'employee_id' => $this->employeeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Single employee attendance processing job failed permanently', [
            'employee_id' => $this->employeeId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }
}

