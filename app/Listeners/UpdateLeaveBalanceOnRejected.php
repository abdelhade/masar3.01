<?php

namespace App\Listeners;

use App\Events\LeaveRequestRejected;
use App\LeaveBalanceService;
use Illuminate\Queue\InteractsWithQueue;

class UpdateLeaveBalanceOnRejected
{
    use InteractsWithQueue;

    public function __construct(
        private LeaveBalanceService $leaveBalanceService
    ) {}

    public function handle(LeaveRequestRejected $event): void
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
            \Log::error('Error releasing pending leave days on rejection', [
                'leave_request_id' => $request->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
