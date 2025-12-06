<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AttendanceProcessing;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeDeductionReward;
use App\Models\FlexibleSalaryProcessing;
use App\Models\WorkPermission;
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
     * Create journal entry for deduction
     */
    public function createDeductionJournal(EmployeeDeductionReward $deduction): ?int
    {
        // Check if journal already exists
        if ($deduction->hasJournal()) {
            Log::warning("Deduction {$deduction->id} already has journal {$deduction->journal_id}");

            return $deduction->journal_id;
        }

        try {
            // Get employee's deductions account
            $employeeDeductionsAccount = AccHead::where('accountable_type', Employee::class)
                ->where('accountable_id', $deduction->employee_id)
                ->where('aname', 'like', '%جزاءات وخصومات%')
                ->first();

            if (! $employeeDeductionsAccount) {
                throw new \Exception("Employee deductions account not found for employee {$deduction->employee_id}");
            }

            // Get employee's main salary account (under 2102)
            $employeeMainAccount = $deduction->employee->account;
            if (! $employeeMainAccount) {
                throw new \Exception("Employee main account not found for employee {$deduction->employee_id}");
            }

            // Get parent deductions account (210402 - جزاءات وخصومات الموظفين)
            $parentDeductionsAccount = AccHead::where('code', '210402')->first();
            if (! $parentDeductionsAccount) {
                throw new \Exception('Parent deductions account (210402) not found');
            }

            // Create journal entry
            // Debit: حساب الموظف (تحت 2102) - استقطاع من الراتب المستحق
            // Credit: حساب جزاءات وخصومات الموظف الخاص (تحت 210402) - مصاريف
            $lines = [
                [
                    'account_id' => $employeeMainAccount->id,
                    'debit' => $deduction->amount,
                    'credit' => 0,
                    'type' => 1,
                    'info' => "خصم: {$deduction->reason}",
                ],
                [
                    'account_id' => $employeeDeductionsAccount->id,
                    'debit' => 0,
                    'credit' => $deduction->amount,
                    'type' => 0,
                    'info' => "خصم: {$deduction->reason}",
                ],
            ];

            $meta = [
                'pro_type' => 75,
                'date' => $deduction->date,
                'info' => "خصم للموظف: {$deduction->employee->name} - {$deduction->reason}",
                'emp_id' => $deduction->employee_id,
            ];

            $journalId = $this->journalService->createJournal($lines, $meta);

            // Update deduction with journal_id
            $deduction->update(['journal_id' => $journalId]);

            return $journalId;
        } catch (\Exception $e) {
            Log::error("Failed to create deduction journal: {$e->getMessage()}", [
                'deduction_id' => $deduction->id,
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * Create journal entry for reward
     */
    public function createRewardJournal(EmployeeDeductionReward $reward): ?int
    {
        // Check if journal already exists
        if ($reward->hasJournal()) {
            Log::warning("Reward {$reward->id} already has journal {$reward->journal_id}");

            return $reward->journal_id;
        }

        try {
            // Get employee's rewards account
            $employeeRewardsAccount = AccHead::where('accountable_type', Employee::class)
                ->where('accountable_id', $reward->employee_id)
                ->where('aname', 'like', '%مكافآت وحوافز%')
                ->first();

            if (! $employeeRewardsAccount) {
                throw new \Exception("Employee rewards account not found for employee {$reward->employee_id}");
            }

            // Get employee's main salary account (under 2102)
            $employeeMainAccount = $reward->employee->account;
            if (! $employeeMainAccount) {
                throw new \Exception("Employee main account not found for employee {$reward->employee_id}");
            }

            // Get parent rewards account (5303 - المكافآت والحوافز)
            $parentRewardsAccount = AccHead::where('code', '5303')->first();
            if (! $parentRewardsAccount) {
                throw new \Exception('Parent rewards account (5303) not found');
            }

            // Create journal entry
            // Debit: حساب مكافآت وحوافز الموظف الخاص (تحت 5303) - مصاريف
            // Credit: حساب الموظف (تحت 2102) - إضافة للراتب المستحق
            $lines = [
                [
                    'account_id' => $employeeRewardsAccount->id,
                    'debit' => $reward->amount,
                    'credit' => 0,
                    'type' => 1,
                    'info' => "مكافأة: {$reward->reason}",
                ],
                [
                    'account_id' => $employeeMainAccount->id,
                    'debit' => 0,
                    'credit' => $reward->amount,
                    'type' => 0,
                    'info' => "مكافأة: {$reward->reason}",
                ],
            ];

            $meta = [
                'pro_type' => 76,
                'date' => $reward->date,
                'info' => "مكافأة للموظف: {$reward->employee->name} - {$reward->reason}",
                'emp_id' => $reward->employee_id,
            ];

            $journalId = $this->journalService->createJournal($lines, $meta);

            // Update reward with journal_id
            $reward->update(['journal_id' => $journalId]);

            return $journalId;
        } catch (\Exception $e) {
            Log::error("Failed to create reward journal: {$e->getMessage()}", [
                'reward_id' => $reward->id,
                'exception' => $e,
            ]);
            throw $e;
        }
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
     * Apply deductions and rewards for an attendance processing
     * Also applies advances deduction from employee's salary account
     */
    public function applyDeductionsAndRewards(int $attendanceProcessingId): void
    {
        $processing = AttendanceProcessing::with('employee')->findOrFail($attendanceProcessingId);
        $employee = $processing->employee;
        $startDate = Carbon::parse($processing->period_start);
        $endDate = Carbon::parse($processing->period_end);

        // Apply deductions and rewards
        $deductions = EmployeeDeductionReward::where('attendance_processing_id', $attendanceProcessingId)
            ->whereNull('journal_id')
            ->get();

        foreach ($deductions as $deduction) {
            if ($deduction->isDeduction()) {
                $this->createDeductionJournal($deduction);
            } elseif ($deduction->isReward()) {
                $this->createRewardJournal($deduction);
            }
        }

        // Apply advances deduction (استقطاع السلف من الراتب المستحق)
        // استقطاع جميع السلف المعتمدة وغير المستقطعة (من أي فترة سابقة أو حالية)
        $advances = EmployeeAdvance::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('deducted_from_salary', false) // لم يتم استقطاعها بعد
            ->where('date', '<=', $endDate->format('Y-m-d')) // السلف حتى نهاية الفترة الحالية
            ->get();

        foreach ($advances as $advance) {
            $this->deductAdvanceFromSalary($advance, $employee);
        }
    }

    /**
     * Deduct advance from employee's salary account
     * عند الاستحقاق: Debit حساب الموظف (2102), Credit حساب سلف الموظف (110601)
     */
    private function deductAdvanceFromSalary(EmployeeAdvance $advance, Employee $employee): void
    {
        // Get employee's main salary account (under 2102)
        $employeeMainAccount = $employee->account;
        if (! $employeeMainAccount) {
            throw new \Exception("Employee main account not found for employee {$employee->id}");
        }

        // Get employee's advance account (under 110601)
        $employeeAdvanceAccount = AccHead::where('accountable_type', Employee::class)
            ->where('accountable_id', $employee->id)
            ->where('aname', 'like', '%سلف%')
            ->first();

        if (! $employeeAdvanceAccount) {
            throw new \Exception("Employee advance account not found for employee {$employee->id}");
        }

        // Create journal entry
        // Debit: حساب الموظف (تحت 2102) - استقطاع السلف من الراتب المستحق
        // Credit: حساب سلف الموظف (تحت 110601)
        $lines = [
            [
                'account_id' => $employeeMainAccount->id,
                'debit' => $advance->amount,
                'credit' => 0,
                'type' => 1,
                'info' => "استقطاع سلفة سابقة: {$advance->reason}",
            ],
            [
                'account_id' => $employeeAdvanceAccount->id,
                'debit' => 0,
                'credit' => $advance->amount,
                'type' => 0,
                'info' => "استقطاع سلفة سابقة: {$advance->reason}",
            ],
        ];

        $meta = [
            'pro_type' => 79, // نوع جديد لاستقطاع السلف من الراتب
            'date' => $advance->date,
            'info' => "استقطاع سلفة للموظف: {$employee->name}",
            'details' => "سلفة: {$advance->reason}",
            'emp_id' => $employee->id,
        ];

        $journalId = $this->journalService->createJournal($lines, $meta);

        // Mark advance as deducted
        $advance->update([
            'deducted_from_salary' => true,
            'deduction_journal_id' => $journalId,
        ]);
    }

    /**
     * Get employee account balance (salary + rewards - deductions - advances)
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

        // Get rewards
        $rewards = EmployeeDeductionReward::where('employee_id', $employee->id)
            ->where('type', 'reward')
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->sum('amount');

        // Get deductions
        $deductions = EmployeeDeductionReward::where('employee_id', $employee->id)
            ->where('type', 'deduction')
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->sum('amount');

        // Get advances
        $advances = EmployeeAdvance::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->sum('amount');

        $totalSalary = $attendanceSalary + $flexibleSalary;
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
