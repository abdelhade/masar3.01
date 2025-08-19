<?php

namespace App\Listeners;

use App\Events\LeaveRequestApproved;
use App\LeaveBalanceService;
use App\Models\PayrollEntry;
use App\Models\PayrollRun;
use Illuminate\Queue\InteractsWithQueue;

class UpdateLeaveBalanceOnApproved
{
    use InteractsWithQueue;

    public function __construct(
        private LeaveBalanceService $leaveBalanceService
    ) {}

    public function handle(LeaveRequestApproved $event): void
    {
        $request = $event->leaveRequest;
        $year = $request->start_date->year;

        // استهلاك الأيام المعتمدة من رصيد الموظف
        $this->leaveBalanceService->consumeApproved(
            $request->employee_id,
            $request->leave_type_id,
            $year,
            $request->duration_days
        );

        // تحديث سجل الرواتب إذا كان الإجازة مدفوعة
        if ($request->leaveType->is_paid) {
            $this->updatePayrollEntry($request);
        }
    }

    private function updatePayrollEntry($request): void
    {
        // البحث عن آخر دورة رواتب في حالة draft
        $payrollRun = PayrollRun::where('status', 'draft')
            ->latest()
            ->first();

        if ($payrollRun) {
            $payrollEntry = PayrollEntry::firstOrCreate(
                [
                    'payroll_run_id' => $payrollRun->id,
                    'employee_id' => $request->employee_id,
                ],
                [
                    'leave_days_paid' => 0,
                    'leave_days_unpaid' => 0,
                ]
            );

            if ($request->leaveType->is_paid) {
                $payrollEntry->addPaidLeaveDays($request->duration_days);
            } else {
                $payrollEntry->addUnpaidLeaveDays($request->duration_days);
            }
        }
    }
}
