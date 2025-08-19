<?php

namespace App;

use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveBalanceService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * حجز أيام معلقة في رصيد الموظف
     */
    public function reservePending(int $employeeId, int $leaveTypeId, int $year, float $days): bool
    {
        try {
            DB::beginTransaction();

            $balance = $this->getOrCreateBalance($employeeId, $leaveTypeId, $year);

            if (! $balance->hasSufficientBalance($days)) {
                DB::rollBack();

                return false;
            }

            $balance->reservePending($days);
            $balance->save();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * استهلاك أيام معتمدة من رصيد الموظف
     */
    public function consumeApproved(int $employeeId, int $leaveTypeId, int $year, float $days): void
    {
        DB::transaction(function () use ($employeeId, $leaveTypeId, $year, $days) {
            $balance = $this->getOrCreateBalance($employeeId, $leaveTypeId, $year);
            $balance->consumeApproved($days);
            $balance->save();
        });
    }

    /**
     * إطلاق أيام معلقة من رصيد الموظف
     */
    public function releasePending(int $employeeId, int $leaveTypeId, int $year, float $days): void
    {
        DB::transaction(function () use ($employeeId, $leaveTypeId, $year, $days) {
            $balance = $this->getOrCreateBalance($employeeId, $leaveTypeId, $year);
            $balance->releasePending($days);
            $balance->save();
        });
    }

    /**
     * إعادة حساب الرصيد المتبقي
     */
    public function recalculateRemaining(int $employeeId, int $leaveTypeId, int $year): float
    {
        $balance = $this->getOrCreateBalance($employeeId, $leaveTypeId, $year);

        return $balance->remaining_days;
    }

    /**
     * الحصول على رصيد موجود أو إنشاء رصيد جديد
     */
    public function getOrCreateBalance(int $employeeId, int $leaveTypeId, int $year): EmployeeLeaveBalance
    {
        return EmployeeLeaveBalance::firstOrCreate(
            [
                'employee_id' => $employeeId,
                'leave_type_id' => $leaveTypeId,
                'year' => $year,
            ],
            [
                'opening_balance_days' => 0,
                'accrued_days' => 0,
                'used_days' => 0,
                'pending_days' => 0,
                'carried_over_days' => 0,
            ]
        );
    }

    /**
     * حساب التراكم الشهري للإجازات
     */
    public function calculateMonthlyAccrual(int $employeeId, int $leaveTypeId, int $year, int $month): float
    {
        $leaveType = LeaveType::findOrFail($leaveTypeId);

        if (! $leaveType->hasAccrualPolicy()) {
            return 0;
        }

        return $leaveType->accrual_rate_per_month;
    }

    /**
     * تطبيق التراكم الشهري
     */
    public function applyMonthlyAccrual(int $employeeId, int $leaveTypeId, int $year, int $month): void
    {
        $accruedDays = $this->calculateMonthlyAccrual($employeeId, $leaveTypeId, $year, $month);

        if ($accruedDays > 0) {
            $balance = $this->getOrCreateBalance($employeeId, $leaveTypeId, $year);
            $balance->addAccruedDays($accruedDays);
            $balance->save();
        }
    }

    /**
     * نقل الرصيد المتبقي للعام الجديد
     */
    public function carryOverToNextYear(int $employeeId, int $leaveTypeId, int $currentYear): void
    {
        $currentBalance = $this->getOrCreateBalance($employeeId, $leaveTypeId, $currentYear);
        $carryOverDays = $currentBalance->carryOverToNextYear();

        if ($carryOverDays > 0) {
            $nextYearBalance = $this->getOrCreateBalance($employeeId, $leaveTypeId, $currentYear + 1);
            $nextYearBalance->opening_balance_days = $carryOverDays;
            $nextYearBalance->save();
        }
    }

    /**
     * التحقق من كفاية الرصيد لطلب إجازة
     */
    public function hasSufficientBalance(int $employeeId, int $leaveTypeId, int $year, float $days): bool
    {
        $balance = $this->getOrCreateBalance($employeeId, $leaveTypeId, $year);

        return $balance->hasSufficientBalance($days);
    }

    /**
     * الحصول على رصيد الموظف لجميع أنواع الإجازات في سنة معينة
     */
    public function getEmployeeBalances(int $employeeId, int $year): \Illuminate\Database\Eloquent\Collection
    {
        return EmployeeLeaveBalance::with(['leaveType'])
            ->where('employee_id', $employeeId)
            ->where('year', $year)
            ->get();
    }

    /**
     * التحقق من تداخل الطلبات المعتمدة
     */
    public function hasOverlappingApprovedRequests(int $employeeId, string $startDate, string $endDate, ?int $excludeRequestId = null): bool
    {
        $query = LeaveRequest::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->overlapping($startDate, $endDate);

        if ($excludeRequestId) {
            $query->where('id', '!=', $excludeRequestId);
        }

        return $query->exists();
    }

    /**
     * حساب أيام العمل الفعلية (استثناء العطلات الرسمية)
     */
    public function calculateWorkingDays(string $startDate, string $endDate, bool $excludeHolidays = true): float
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $workingDays = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            // استثناء أيام العطل الأسبوعية (الجمعة والسبت)
            if (! $current->isFriday() && ! $current->isSaturday()) {
                // TODO: إضافة استثناء العطلات الرسمية من جدول official_holidays
                $workingDays++;
            }
            $current->addDay();
        }

        return $workingDays;
    }
}
