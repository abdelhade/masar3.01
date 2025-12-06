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

        try {
            $month = $request->start_date->month;

            // التحقق من الرصيد قبل الاستهلاك (للإجازات المدفوعة)
            if ($request->leaveType->is_paid) {
                $hasBalance = $this->leaveBalanceService->hasSufficientBalance(
                    $request->employee_id,
                    $request->leave_type_id,
                    $year,
                    $request->duration_days
                );

                if (! $hasBalance) {
                    \Log::error('Leave balance insufficient on approval', [
                        'leave_request_id' => $request->id,
                        'employee_id' => $request->employee_id,
                        'leave_type_id' => $request->leave_type_id,
                        'duration_days' => $request->duration_days,
                        'year' => $year,
                    ]);

                    throw new \RuntimeException('Insufficient leave balance for approval');
                }

                // التحقق من الحد الشهري
                if (! $this->leaveBalanceService->checkMonthlyLimit(
                    $request->employee_id,
                    $request->leave_type_id,
                    $year,
                    $month,
                    $request->duration_days
                )) {
                    \Log::error('Monthly leave limit exceeded on approval', [
                        'leave_request_id' => $request->id,
                        'employee_id' => $request->employee_id,
                        'leave_type_id' => $request->leave_type_id,
                        'duration_days' => $request->duration_days,
                        'year' => $year,
                        'month' => $month,
                    ]);

                    throw new \RuntimeException('Monthly leave limit exceeded for approval');
                }
            }

            // استهلاك الأيام المعتمدة من رصيد الموظف
            $this->leaveBalanceService->consumeApproved(
                $request->employee_id,
                $request->leave_type_id,
                $year,
                $request->duration_days,
                $month
            );

            // تحديث سجل الرواتب إذا كان الإجازة مدفوعة
            if ($request->leaveType->is_paid) {
                $this->updatePayrollEntry($request);
            }
        } catch (\Exception $e) {
            \Log::error('Error updating leave balance on approval', [
                'leave_request_id' => $request->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
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
