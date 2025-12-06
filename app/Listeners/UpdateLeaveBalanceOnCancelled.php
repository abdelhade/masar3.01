<?php

namespace App\Listeners;

use App\Events\LeaveRequestCancelled;
use App\LeaveBalanceService;
use Illuminate\Queue\InteractsWithQueue;

class UpdateLeaveBalanceOnCancelled
{
    use InteractsWithQueue;

    public function __construct(
        private LeaveBalanceService $leaveBalanceService
    ) {}

    public function handle(LeaveRequestCancelled $event): void
    {
        $request = $event->leaveRequest;
        $year = $request->start_date->year;

        try {
            // إطلاق الأيام المعلقة من رصيد الموظف
            $this->leaveBalanceService->releasePending(
                $request->employee_id,
                $request->leave_type_id,
                $year,
                $request->duration_days
            );
        } catch (\Exception $e) {
            \Log::error('Error releasing pending leave days on cancellation', [
                'leave_request_id' => $request->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
