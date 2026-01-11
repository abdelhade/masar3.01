<?php

declare(strict_types=1);

namespace Modules\HR\Services;

use Modules\HR\Models\AttendanceProcessing;
use Modules\HR\Models\AttendanceProcessingDetail;
use App\Services\JournalService;
use Modules\HR\Models\Employee;
use Modules\HR\Models\EmployeeAdvance;
use Modules\HR\Models\EmployeeDeductionReward;
use Modules\HR\Models\FlexibleSalaryProcessing;
use Modules\HR\Models\WorkPermission;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Accounts\Models\AccHead;

class EmployeeDeductionRewardService
{
    public function __construct(
        private JournalService $journalService
    ) {}

    /**
     * Calculate monthly deductions based on late days and permissions exceeded
     */
    public function calculateMonthlyDeductions(Employee $employee, Carbon $startDate, Carbon $endDate, ?int $attendanceProcessingId = null): array
    {
        $deductions = [];

        // Get daily rate for calculation
        // Use actual period days instead of current month days for accurate calculation
        $periodDays = $startDate->diffInDays($endDate) + 1;
        if ($periodDays <= 0 || $employee->salary <= 0) {
            return []; // Return empty if invalid period or salary
        }
        $dailyRate = $employee->salary / $periodDays;
        $halfDayDeduction = $dailyRate * 0.5;

        // Count late days that exceeded allowed_late_days
        $lateDaysCount = $this->countLateDaysExceeded($employee, $startDate, $endDate, $attendanceProcessingId);
        if ($lateDaysCount > 0) {
            $deductions[] = [
                'type' => 'deduction',
                'reason' => "تجاوز عدد أيام التأخير المسموح بها ({$lateDaysCount} يوم)",
                'amount' => $lateDaysCount * $halfDayDeduction,
                'date' => $endDate->format('Y-m-d'),
            ];
        }

        // Count permissions that exceeded allowed_permission_days
        $permissionsCount = $this->countPermissionsExceeded($employee, $startDate, $endDate);
        if ($permissionsCount > 0) {
            $deductions[] = [
                'type' => 'deduction',
                'reason' => "تجاوز عدد أيام الإذونات المسموح بها ({$permissionsCount} يوم)",
                'amount' => $permissionsCount * $halfDayDeduction,
                'date' => $endDate->format('Y-m-d'),
            ];
        }

        // Save deductions to database using firstOrCreate to prevent duplicates
        $savedDeductions = [];
        foreach ($deductions as $deduction) {
            $savedDeductions[] = EmployeeDeductionReward::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'attendance_processing_id' => $attendanceProcessingId,
                    'reason' => $deduction['reason'],
                    'date' => $deduction['date'],
                ],
                [
                    'type' => $deduction['type'],
                    'amount' => $deduction['amount'],
                    'created_by' => Auth::id(),
                ]
            );
        }

        return $savedDeductions;
    }

    /**
     * Count late days that exceeded allowed_late_days
     */
    private function countLateDaysExceeded(Employee $employee, Carbon $startDate, Carbon $endDate, ?int $attendanceProcessingId = null): int
    {
        $allowedLateDays = $employee->allowed_late_days ?? 0;
        if ($allowedLateDays <= 0) {
            return 0;
        }

        // Count actual late days from attendance processing details
        // If attendanceProcessingId is provided, use it to get accurate count for current processing
        $query = AttendanceProcessingDetail::where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('attendance_late_minutes_count', '>', 0);

        if ($attendanceProcessingId) {
            // Use specific processing to get accurate count
            $query->where('attendance_processing_id', $attendanceProcessingId);
        } else {
            // Fallback: use all processings in the period (for backward compatibility)
            $query->whereHas('attendanceProcessing', function ($q) use ($employee, $startDate, $endDate) {
                $q->where('employee_id', $employee->id)
                    ->whereBetween('period_start', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            });
        }

        $lateDaysCount = $query->count();

        return max(0, $lateDaysCount - $allowedLateDays);
    }

    /**
     * Count permissions that exceeded allowed_permission_days
     * Only counts approved permissions within the specified period
     */
    private function countPermissionsExceeded(Employee $employee, Carbon $startDate, Carbon $endDate): int
    {
        $allowedPermissionDays = $employee->allowed_permission_days ?? 0;
        if ($allowedPermissionDays <= 0) {
            return 0;
        }

        // Count approved permissions in the period only
        $permissionsCount = WorkPermission::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->count();

        // Return only the exceeded count (permissions beyond allowed limit)
        return max(0, $permissionsCount - $allowedPermissionDays);
    }

    /**
     * Get rewards payable balance from AccHead (2109)
     */
    public function getRewardsPayableBalance(Employee $employee): float
    {
        return (float) (AccHead::where('accountable_type', Employee::class)
            ->where('accountable_id', $employee->id)
            ->where('aname', 'like', 'مكافآت وحوافز مستحقه%')
            ->value('balance') ?? 0);
    }

    /**
     * Get deductions receivable balance from AccHead (110602)
     */
    public function getDeductionsReceivableBalance(Employee $employee): float
    {
        return (float) (AccHead::where('accountable_type', Employee::class)
            ->where('accountable_id', $employee->id)
            ->where('aname', 'like', 'جزاءات وخصومات مستحقه%')
            ->value('balance') ?? 0);
    }

    /**
     * Get advances balance from AccHead (110601)
     */
    public function getAdvancesBalance(Employee $employee): float
    {
        return (float) (AccHead::where('accountable_type', Employee::class)
            ->where('accountable_id', $employee->id)
            ->where('aname', 'like', 'سلف%')
            ->value('balance') ?? 0);
    }
    public function getSettledRewards(Employee $employee, Carbon $startDate, Carbon $endDate): float
    {
        return (float) DB::table('journal_details')
            ->join('acc_head', 'journal_details.account_id', '=', 'acc_head.id')
            ->join('operhead', 'journal_details.op_id', '=', 'operhead.id')
            ->join('acc_head as parents', 'acc_head.parent_id', '=', 'parents.id')
            ->where('acc_head.accountable_type', Employee::class)
            ->where('acc_head.accountable_id', $employee->id)
            ->where('parents.code', '2109') // Rewards Payable Parent
            ->whereIn('operhead.pro_type', [76])
            ->whereBetween('operhead.pro_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->sum('journal_details.debit');
    }

    public function getSettledDeductions(Employee $employee, Carbon $startDate, Carbon $endDate): float
    {
        return (float) DB::table('journal_details')
            ->join('acc_head', 'journal_details.account_id', '=', 'acc_head.id')
            ->join('operhead', 'journal_details.op_id', '=', 'operhead.id')
            ->join('acc_head as parents', 'acc_head.parent_id', '=', 'parents.id')
            ->where('acc_head.accountable_type', Employee::class)
            ->where('acc_head.accountable_id', $employee->id)
            ->where('parents.code', '110602') // Deductions Receivable Parent
            ->whereIn('operhead.pro_type', [75])
            ->whereBetween('operhead.pro_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->sum('journal_details.credit');
    }

    public function getSettledAdvances(Employee $employee, Carbon $startDate, Carbon $endDate): float
    {
        return (float) DB::table('journal_details')
            ->join('acc_head', 'journal_details.account_id', '=', 'acc_head.id')
            ->join('operhead', 'journal_details.op_id', '=', 'operhead.id')
            ->join('acc_head as parents', 'acc_head.parent_id', '=', 'parents.id')
            ->where('acc_head.accountable_type', Employee::class)
            ->where('acc_head.accountable_id', $employee->id)
            ->where('parents.code', '110601') // Advances Parent
            ->whereIn('operhead.pro_type', [79])
            ->whereBetween('operhead.pro_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->sum('journal_details.credit');
    }

    /**
     * Create journal entry for approved deduction
     */
    public function createDeductionJournal(EmployeeDeductionReward $deduction): void
    {
        if ($deduction->type !== 'deduction') {
            throw new \InvalidArgumentException('Item must be a deduction');
        }

        $employee = $deduction->employee;

        // 1. Find Accounts
        // Target: Employee Sub-Account under 110602 (Deductions Receivable)
        $receivableParent = AccHead::where('code', '110602')->firstOrFail();

        $employeeReceivableAccount = AccHead::where('accountable_type', Employee::class)
            ->where('accountable_id', $employee->id)
            ->where('parent_id', $receivableParent->id)
            ->first();

        if (!$employeeReceivableAccount) {
            $employeeReceivableAccount = AccHead::where('accountable_type', Employee::class)
                ->where('accountable_id', $employee->id)
                ->where('aname', 'like', 'جزاءات وخصومات مستحقه%')
                ->firstOrFail();
        }

        // Source: Deductions Income Sub-Account under 4202
        $incomeParent = AccHead::where('code', '4202')->firstOrFail();
        
        $employeeIncomeAccount = AccHead::where('accountable_type', Employee::class)
            ->where('accountable_id', $employee->id)
            ->where('parent_id', $incomeParent->id)
            ->first();

        if (!$employeeIncomeAccount) {
            $employeeIncomeAccount = AccHead::where('accountable_type', Employee::class)
                ->where('accountable_id', $employee->id)
                ->where('aname', 'like', 'جزاءات وخصومات%')
                ->firstOrFail();
        }

        // 2. Create Journal Entry
        // Debit: Employee Receivable (110602 Sub)
        // Credit: Deductions Income (4202 Sub)

        $lines = [
            [
                'account_id' => $employeeReceivableAccount->id,
                'debit' => $deduction->amount,
                'credit' => 0,
                'type' => 1,
                'info' => "خصم للموظف: {$employee->name} - {$deduction->reason}",
            ],
            [
                'account_id' => $employeeIncomeAccount->id, // Employee-specific income account
                'debit' => 0,
                'credit' => $deduction->amount,
                'type' => 0,
                'info' => "خصم للموظف: {$employee->name} - {$deduction->reason}",
            ],
        ];

        $meta = [
            'pro_type' => 75,
            'date' => $deduction->date->format('Y-m-d'),
            'info' => "خصم للموظف: {$employee->name}",
            'details' => "خصم: {$deduction->reason}",
            'emp_id' => $employee->id,
        ];

        $journalId = $this->journalService->createJournal($lines, $meta);

        // 3. Update Item
        $deduction->update([
            'status' => 'approved',
            'journal_id' => $journalId,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Create journal entry for approved reward
     */
    public function createRewardJournal(EmployeeDeductionReward $reward): void
    {
        if ($reward->type !== 'reward') {
            throw new \InvalidArgumentException('Item must be a reward');
        }

        $employee = $reward->employee;

        // 1. Find Accounts
        // Target: Employee Sub-Account under 2109 (Rewards Payable)
        $payableParent = AccHead::where('code', '2109')->firstOrFail();

        $employeePayableAccount = AccHead::where('accountable_type', Employee::class)
            ->where('accountable_id', $employee->id)
            ->where('parent_id', $payableParent->id)
            ->first();

        if (!$employeePayableAccount) {
            $employeePayableAccount = AccHead::where('accountable_type', Employee::class)
                ->where('accountable_id', $employee->id)
                ->where('aname', 'like', 'مكافآت وحوافز مستحقه%')
                ->firstOrFail();
        }

        // Source: Rewards Expense Sub-Account under 5303
        $expenseParent = AccHead::where('code', '5303')->firstOrFail();
        
        $employeeExpenseAccount = AccHead::where('accountable_type', Employee::class)
            ->where('accountable_id', $employee->id)
            ->where('parent_id', $expenseParent->id)
            ->first();

        if (!$employeeExpenseAccount) {
            $employeeExpenseAccount = AccHead::where('accountable_type', Employee::class)
                ->where('accountable_id', $employee->id)
                ->where('aname', 'like', 'مكافآت وحوافز%')
                ->firstOrFail();
        }

        // 2. Create Journal Entry
        // Debit: Rewards Expense (5303 Sub)
        // Credit: Employee Payable (2109 Sub)

        $lines = [
            [
                'account_id' => $employeeExpenseAccount->id, // Employee-specific expense account
                'debit' => $reward->amount,
                'credit' => 0,
                'info' => "مكافأة للموظف: {$employee->name} - {$reward->reason}",
            ],
            [
                'account_id' => $employeePayableAccount->id,
                'debit' => 0,
                'credit' => $reward->amount,
                'info' => "مكافأة للموظف: {$employee->name} - {$reward->reason}",
            ],
        ];

        $meta = [
            'pro_type' => 76,
            'date' => $reward->date->format('Y-m-d'),
            'info' => "مكافأة للموظف: {$employee->name}",
            'details' => "مكافأة: {$reward->reason}",
            'emp_id' => $employee->id,
        ];

        $journalId = $this->journalService->createJournal($lines, $meta);

        // 3. Update Item
        $reward->update([
            'status' => 'approved',
            'journal_id' => $journalId,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Add reward manually
     */
    public function addReward(Employee $employee, float $amount, string $reason, Carbon $date, ?string $notes = null): EmployeeDeductionReward
    {
        // Validate amount
        if ($amount <= 0) {
            throw new \InvalidArgumentException('المبلغ يجب أن يكون أكبر من صفر');
        }

        return EmployeeDeductionReward::create([
            'employee_id' => $employee->id,
            'type' => 'reward',
            'reason' => $reason,
            'amount' => $amount,
            'date' => $date->format('Y-m-d'),
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Add deduction manually
     */
    public function addDeduction(Employee $employee, float $amount, string $reason, Carbon $date, ?string $notes = null): EmployeeDeductionReward
    {
        // Validate amount
        if ($amount <= 0) {
            throw new \InvalidArgumentException('المبلغ يجب أن يكون أكبر من صفر');
        }

        return EmployeeDeductionReward::create([
            'employee_id' => $employee->id,
            'type' => 'deduction',
            'reason' => $reason,
            'amount' => $amount,
            'date' => $date->format('Y-m-d'),
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Get monthly summary of deductions and rewards
     */
    public function getMonthlySummary(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        $deductions = EmployeeDeductionReward::where('employee_id', $employee->id)
            ->where('type', 'deduction')
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        $rewards = EmployeeDeductionReward::where('employee_id', $employee->id)
            ->where('type', 'reward')
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        return [
            'deductions' => $deductions,
            'rewards' => $rewards,
            'total_deductions' => $deductions->sum('amount'),
            'total_rewards' => $rewards->sum('amount'),
            'net' => $rewards->sum('amount') - $deductions->sum('amount'),
        ];
    }

    /**
     * Get employee account balance (salary + rewards - deductions - advances)
     * Updated to use AccHead balances for consistency
     */
    public function getEmployeeAccountBalance(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        // Get salary from attendance processing
        $attendanceSalary = AttendanceProcessing::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('period_start', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->sum('total_salary');

        // Get salary from flexible salary processing
        $flexibleSalary = FlexibleSalaryProcessing::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('period_start', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->sum('total_salary');

        // Use AccHead balances for current outstanding amounts
        $rewards = abs($this->getRewardsPayableBalance($employee));
        $deductions = abs($this->getDeductionsReceivableBalance($employee));
        $advances = abs($this->getAdvancesBalance($employee));

        $totalSalary = $attendanceSalary + $flexibleSalary;
        
        // Net balance is total salary + rewards (outstanding) - deductions (outstanding) - advances (outstanding)
        // Note: This logic assumes we are looking for "what is payable to employee" considering their current account state
        $netBalance = $totalSalary + $rewards - $deductions - $advances;

        return [
            'attendance_salary' => $attendanceSalary,
            'flexible_salary' => $flexibleSalary,
            'total_salary' => $totalSalary,
            'rewards' => $rewards,
            'deductions' => $deductions,
            'advances' => $advances,
            'net_balance' => $netBalance,
        ];
    }
}

