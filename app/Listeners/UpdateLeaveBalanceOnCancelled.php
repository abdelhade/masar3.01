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

        // إطلاق الأيام المعلقة من رصيد الموظف
        $this->leaveBalanceService->releasePending(
            $request->employee_id,
            $request->leave_type_id,
            $year,
            $request->duration_days
        );
    }
}
