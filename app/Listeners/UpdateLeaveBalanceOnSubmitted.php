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

        // حجز الأيام المعلقة في رصيد الموظف
        $this->leaveBalanceService->reservePending(
            $request->employee_id,
            $request->leave_type_id,
            $year,
            $request->duration_days
        );
    }
}
