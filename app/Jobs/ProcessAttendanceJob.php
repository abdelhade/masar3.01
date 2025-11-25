<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Department;
use App\Models\Employee;
use App\Services\AttendanceProcessingService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted
     */
    public int $tries = 3;

    /**
     * Maximum number of seconds the job can run
     */
    public int $timeout = 600; // 10 minutes

    /**
     * Create a new job instance
     *
     * @param int $departmentId Department ID to process
     * @param string $startDate Start date (Y-m-d format)
     * @param string $endDate End date (Y-m-d format)
     * @param string|null $notes Optional notes
     */
    public function __construct(
        public int $departmentId,
        public string $startDate,
        public string $endDate,
        public ?string $notes = null
    ) {}

    /**
     * Execute the job
     */
    public function handle(AttendanceProcessingService $service): void
    {
        $department = Department::findOrFail($this->departmentId);
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);

        Log::info('Starting attendance processing job', [
            'department_id' => $this->departmentId,
            'department_name' => $department->title,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);

        try {
            $result = $service->processDepartment($department, $startDate, $endDate, $this->notes);

            if (isset($result['error'])) {
                Log::error('Attendance processing job failed', [
                    'department_id' => $this->departmentId,
                    'error' => $result['error'],
                ]);
                throw new \Exception($result['error']);
            }

            $processedCount = count($result['results'] ?? []);
            $successfulCount = count(array_filter($result['results'] ?? [], fn($r) => !isset($r['error'])));

            Log::info('Attendance processing job completed successfully', [
                'department_id' => $this->departmentId,
                'total_employees' => $processedCount,
                'successful' => $successfulCount,
                'failed' => $processedCount - $successfulCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Attendance processing job exception', [
                'department_id' => $this->departmentId,
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
        Log::error('Attendance processing job failed permanently', [
            'department_id' => $this->departmentId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // يمكن إضافة إشعار للمستخدم هنا
        // Notification::send(...)
    }
}

