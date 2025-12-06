<?php

namespace App\Listeners;

use App\Events\LeaveRequestSubmitted;
use App\LeaveBalanceService;
use Illuminate\Queue\InteractsWithQueue;

class UpdateLeaveBalanceOnSubmitted
{
    use InteractsWithQueue;

    public function __construct(
        private LeaveBalanceService $leaveBalanceService
    ) {}

    public function handle(LeaveRequestSubmitted $event): void
    {
        $request = $event->leaveRequest;
        $year = $request->start_date->year;

        try {
            // حجز الأيام المعلقة في رصيد الموظف
            $reserved = $this->leaveBalanceService->reservePending(
                $request->employee_id,
                $request->leave_type_id,
                $year,
                $request->duration_days
            );

            if (! $reserved) {
                \Log::warning('Failed to reserve pending leave days', [
                    'leave_request_id' => $request->id,
                    'employee_id' => $request->employee_id,
                    'leave_type_id' => $request->leave_type_id,
                    'duration_days' => $request->duration_days,
                    'year' => $year,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error reserving pending leave days', [
                'leave_request_id' => $request->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
