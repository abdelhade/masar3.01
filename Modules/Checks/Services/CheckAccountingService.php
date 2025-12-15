<?php

namespace Modules\Checks\Services;

use App\Models\JournalDetail;
use App\Models\JournalHead;
use App\Models\OperHead;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Checks\Events\CheckBounced;
use Modules\Checks\Events\CheckCleared;
use Modules\Checks\Models\Check;

class CheckAccountingService
{
    public function __construct(
        private CheckPortfolioService $portfolioService
    ) {}

    /**
     * Create operation head for check
     */
    public function createOperHead(array $data, int $proType, int $portfolioAccountId): OperHead
    {
        $lastProId = OperHead::withoutGlobalScopes()->where('pro_type', $proType)->max('pro_id') ?? 0;
        $newProId = $lastProId + 1;

        return OperHead::create([
            'pro_id' => $newProId,
            'pro_type' => $proType,
            'pro_date' => $data['pro_date'],
            'pro_num' => $data['check_number'],
            'pro_serial' => $data['reference_number'] ?? null,
            'acc1' => $portfolioAccountId,
            'acc2' => $data['acc1_id'],
            'acc1_before' => 0,
            'acc1_after' => 0,
            'acc2_before' => $data['acc2_before'] ?? 0,
            'acc2_after' => $data['acc2_after'] ?? 0,
            'pro_value' => $data['amount'],
            'fat_net' => $data['amount'],
            'details' => "شيك رقم {$data['check_number']} - {$data['bank_name']} - استحقاق: {$data['due_date']}",
            'info' => $data['payee_name'] ?? $data['payer_name'] ?? $data['account_holder_name'],
            'info2' => $data['notes'] ?? null,
            'info3' => json_encode([
                'bank_name' => $data['bank_name'],
                'account_number' => $data['account_number'],
                'account_holder' => $data['account_holder_name'],
                'due_date' => $data['due_date'],
            ]),
            'is_finance' => 1,
            'is_journal' => 1,
            'journal_type' => 2,
            'isdeleted' => 0,
            'tenant' => 0,
            'user' => Auth::id(),
            'branch_id' => $data['branch_id'],
        ]);
    }

    /**
     * Create journal entry for check
     */
    public function createJournalEntry(OperHead $oper, array $data, int $portfolioAccountId, int $proType): JournalHead
    {
        $lastJournalId = JournalHead::withoutGlobalScopes()->max('journal_id') ?? 0;
        $newJournalId = $lastJournalId + 1;

        $journalHead = JournalHead::create([
            'journal_id' => $newJournalId,
            'total' => $data['amount'],
            'date' => $data['pro_date'],
            'op_id' => $oper->id,
            'pro_type' => $proType,
            'details' => "شيك رقم {$data['check_number']} - {$data['bank_name']} - استحقاق: {$data['due_date']}",
            'user' => Auth::id(),
            'branch_id' => $data['branch_id'],
        ]);

        $checkInfo = "شيك {$data['check_number']} - {$data['bank_name']} - استحقاق {$data['due_date']}";

        if ($data['type'] === 'incoming') {
            // ورقة قبض: من ح/ حافظة أوراق القبض (مدين) إلى ح/ العميل (دائن)
            JournalDetail::create([
                'journal_id' => $newJournalId,
                'account_id' => $portfolioAccountId,
                'debit' => $data['amount'],
                'credit' => 0,
                'type' => 0,
                'info' => $checkInfo,
                'op_id' => $oper->id,
                'isdeleted' => 0,
                'tenant' => 0,
                'branch_id' => $data['branch_id'],
            ]);

            if (isset($data['acc1_id']) && $data['acc1_id']) {
                JournalDetail::create([
                    'journal_id' => $newJournalId,
                    'account_id' => $data['acc1_id'],
                    'debit' => 0,
                    'credit' => $data['amount'],
                    'type' => 1,
                    'info' => $checkInfo,
                    'op_id' => $oper->id,
                    'isdeleted' => 0,
                    'tenant' => 0,
                    'branch_id' => $data['branch_id'],
                ]);
            }
        } else {
            // ورقة دفع: من ح/ المورد (مدين) إلى ح/ حافظة أوراق الدفع (دائن)
            if (isset($data['acc1_id']) && $data['acc1_id']) {
                JournalDetail::create([
                    'journal_id' => $newJournalId,
                    'account_id' => $data['acc1_id'],
                    'debit' => $data['amount'],
                    'credit' => 0,
                    'type' => 0,
                    'info' => $checkInfo,
                    'op_id' => $oper->id,
                    'isdeleted' => 0,
                    'tenant' => 0,
                    'branch_id' => $data['branch_id'],
                ]);
            }

            JournalDetail::create([
                'journal_id' => $newJournalId,
                'account_id' => $portfolioAccountId,
                'debit' => 0,
                'credit' => $data['amount'],
                'type' => 1,
                'info' => $checkInfo,
                'op_id' => $oper->id,
                'isdeleted' => 0,
                'tenant' => 0,
                'branch_id' => $data['branch_id'],
            ]);
        }

        return $journalHead;
    }

    /**
     * Clear check (تحصيل الشيك - تحويل للبنك)
     */
    public function clearCheck(Check $check, int $bankAccountId, string $collectionDate, int $branchId): void
    {
        DB::beginTransaction();

        try {
            $proType = 67; // تحصيل شيك
            $portfolioAccount = $this->portfolioService->getPortfolioAccount($check->type);

            if (! $portfolioAccount) {
                throw new \Exception('حافظة الأوراق المالية غير موجودة');
            }

            $lastProId = OperHead::withoutGlobalScopes()->where('pro_type', $proType)->max('pro_id') ?? 0;
            $newProId = $lastProId + 1;

            $oper = OperHead::create([
                'pro_id' => $newProId,
                'pro_type' => $proType,
                'pro_date' => $collectionDate,
                'pro_num' => $check->check_number,
                'acc1' => $bankAccountId,
                'acc2' => $portfolioAccount->id,
                'acc1_before' => 0,
                'acc1_after' => 0,
                'acc2_before' => 0,
                'acc2_after' => 0,
                'pro_value' => $check->amount,
                'fat_net' => $check->amount,
                'details' => "تحصيل شيك رقم {$check->check_number} من {$check->bank_name}",
                'info' => $check->account_holder_name,
                'info2' => "تحويل للبنك بتاريخ {$collectionDate}",
                'is_finance' => 1,
                'is_journal' => 1,
                'journal_type' => 2,
                'isdeleted' => 0,
                'tenant' => 0,
                'user' => Auth::id(),
                'branch_id' => $branchId,
            ]);

            $lastJournalId = JournalHead::withoutGlobalScopes()->max('journal_id') ?? 0;
            $newJournalId = $lastJournalId + 1;

            $journalHead = JournalHead::create([
                'journal_id' => $newJournalId,
                'total' => $check->amount,
                'date' => $collectionDate,
                'op_id' => $oper->id,
                'pro_type' => $proType,
                'details' => "تحصيل شيك رقم {$check->check_number}",
                'user' => Auth::id(),
                'branch_id' => $branchId,
            ]);

            $checkInfo = "تحصيل شيك {$check->check_number} - {$check->bank_name}";

            // من ح/ البنك (مدين)
            JournalDetail::create([
                'journal_id' => $newJournalId,
                'account_id' => $bankAccountId,
                'debit' => $check->amount,
                'credit' => 0,
                'type' => 0,
                'info' => $checkInfo,
                'op_id' => $oper->id,
                'isdeleted' => 0,
                'tenant' => 0,
                'branch_id' => $branchId,
            ]);

            // إلى ح/ حافظة الأوراق المالية (دائن)
            JournalDetail::create([
                'journal_id' => $newJournalId,
                'account_id' => $portfolioAccount->id,
                'debit' => 0,
                'credit' => $check->amount,
                'type' => 1,
                'info' => $checkInfo,
                'op_id' => $oper->id,
                'isdeleted' => 0,
                'tenant' => 0,
                'branch_id' => $branchId,
            ]);

            $check->markAsCleared($collectionDate);

            // Dispatch event
            event(new CheckCleared($check));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Bounce check (شيك مرتد)
     */
    public function bounceCheck(Check $check, int $branchId): void
    {
        DB::beginTransaction();

        try {
            $proType = 69; // شيك مرتد
            $portfolioAccount = $this->portfolioService->getPortfolioAccount($check->type);

            if (! $portfolioAccount) {
                throw new \Exception('حافظة الأوراق المالية غير موجودة');
            }

            $customerAccount = $check->type === 'incoming'
                ? $check->customer_id
                : $check->supplier_id;

            if (! $customerAccount) {
                throw new \Exception('حساب العميل/المورد غير موجود');
            }

            $lastProId = OperHead::withoutGlobalScopes()->where('pro_type', $proType)->max('pro_id') ?? 0;
            $newProId = $lastProId + 1;

            $oper = OperHead::create([
                'pro_id' => $newProId,
                'pro_type' => $proType,
                'pro_date' => now()->toDateString(),
                'pro_num' => $check->check_number,
                'acc1' => $customerAccount,
                'acc2' => $portfolioAccount->id,
                'acc1_before' => 0,
                'acc1_after' => 0,
                'acc2_before' => 0,
                'acc2_after' => 0,
                'pro_value' => $check->amount,
                'fat_net' => $check->amount,
                'details' => "شيك مرتد رقم {$check->check_number} من {$check->bank_name}",
                'info' => $check->account_holder_name,
                'is_finance' => 1,
                'is_journal' => 1,
                'journal_type' => 2,
                'isdeleted' => 0,
                'tenant' => 0,
                'user' => Auth::id(),
                'branch_id' => $branchId,
            ]);

            $lastJournalId = JournalHead::withoutGlobalScopes()->max('journal_id') ?? 0;
            $newJournalId = $lastJournalId + 1;

            $journalHead = JournalHead::create([
                'journal_id' => $newJournalId,
                'total' => $check->amount,
                'date' => now()->toDateString(),
                'op_id' => $oper->id,
                'pro_type' => $proType,
                'details' => "شيك مرتد رقم {$check->check_number}",
                'user' => Auth::id(),
                'branch_id' => $branchId,
            ]);

            $checkInfo = "شيك مرتد {$check->check_number} - {$check->bank_name}";

            // من ح/ العميل/المورد (مدين)
            JournalDetail::create([
                'journal_id' => $newJournalId,
                'account_id' => $customerAccount,
                'debit' => $check->amount,
                'credit' => 0,
                'type' => 0,
                'info' => $checkInfo,
                'op_id' => $oper->id,
                'isdeleted' => 0,
                'tenant' => 0,
                'branch_id' => $branchId,
            ]);

            // إلى ح/ حافظة الأوراق المالية (دائن)
            JournalDetail::create([
                'journal_id' => $newJournalId,
                'account_id' => $portfolioAccount->id,
                'debit' => 0,
                'credit' => $check->amount,
                'type' => 1,
                'info' => $checkInfo,
                'op_id' => $oper->id,
                'isdeleted' => 0,
                'tenant' => 0,
                'branch_id' => $branchId,
            ]);

            $check->markAsBounced();

            // Dispatch event
            event(new CheckBounced($check));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cancel check with reversal entry (إلغاء شيك مع قيد عكسي)
     */
    public function cancelCheckWithReversal(Check $check, int $branchId): void
    {
        DB::beginTransaction();

        try {
            $proType = 71; // قيد عكسي لشيك
            $portfolioAccount = $this->portfolioService->getPortfolioAccount($check->type);

            if (! $portfolioAccount) {
                throw new \Exception('حافظة الأوراق المالية غير موجودة');
            }

            $customerAccount = $check->type === 'incoming'
                ? $check->customer_id
                : $check->supplier_id;

            if (! $customerAccount) {
                throw new \Exception('حساب العميل/المورد غير موجود');
            }

            $lastProId = OperHead::withoutGlobalScopes()->where('pro_type', $proType)->max('pro_id') ?? 0;
            $newProId = $lastProId + 1;

            $oper = OperHead::create([
                'pro_id' => $newProId,
                'pro_type' => $proType,
                'pro_date' => now()->toDateString(),
                'pro_num' => $check->check_number,
                'acc1' => $portfolioAccount->id,
                'acc2' => $customerAccount,
                'acc1_before' => 0,
                'acc1_after' => 0,
                'acc2_before' => 0,
                'acc2_after' => 0,
                'pro_value' => $check->amount,
                'fat_net' => $check->amount,
                'details' => "قيد عكسي لشيك رقم {$check->check_number}",
                'info' => $check->account_holder_name,
                'is_finance' => 1,
                'is_journal' => 1,
                'journal_type' => 2,
                'isdeleted' => 0,
                'tenant' => 0,
                'user' => Auth::id(),
                'branch_id' => $branchId,
            ]);

            $lastJournalId = JournalHead::withoutGlobalScopes()->max('journal_id') ?? 0;
            $newJournalId = $lastJournalId + 1;

            $journalHead = JournalHead::create([
                'journal_id' => $newJournalId,
                'total' => $check->amount,
                'date' => now()->toDateString(),
                'op_id' => $oper->id,
                'pro_type' => $proType,
                'details' => "قيد عكسي لشيك رقم {$check->check_number}",
                'user' => Auth::id(),
                'branch_id' => $branchId,
            ]);

            $checkInfo = "قيد عكسي لشيك {$check->check_number}";

            if ($check->type === 'incoming') {
                // عكس القيد: من ح/ العميل (مدين) إلى ح/ حافظة أوراق القبض (دائن)
                JournalDetail::create([
                    'journal_id' => $newJournalId,
                    'account_id' => $customerAccount,
                    'debit' => $check->amount,
                    'credit' => 0,
                    'type' => 0,
                    'info' => $checkInfo,
                    'op_id' => $oper->id,
                    'isdeleted' => 0,
                    'tenant' => 0,
                    'branch_id' => $branchId,
                ]);

                JournalDetail::create([
                    'journal_id' => $newJournalId,
                    'account_id' => $portfolioAccount->id,
                    'debit' => 0,
                    'credit' => $check->amount,
                    'type' => 1,
                    'info' => $checkInfo,
                    'op_id' => $oper->id,
                    'isdeleted' => 0,
                    'tenant' => 0,
                    'branch_id' => $branchId,
                ]);
            } else {
                // عكس القيد: من ح/ حافظة أوراق الدفع (مدين) إلى ح/ المورد (دائن)
                JournalDetail::create([
                    'journal_id' => $newJournalId,
                    'account_id' => $portfolioAccount->id,
                    'debit' => $check->amount,
                    'credit' => 0,
                    'type' => 0,
                    'info' => $checkInfo,
                    'op_id' => $oper->id,
                    'isdeleted' => 0,
                    'tenant' => 0,
                    'branch_id' => $branchId,
                ]);

                JournalDetail::create([
                    'journal_id' => $newJournalId,
                    'account_id' => $customerAccount,
                    'debit' => 0,
                    'credit' => $check->amount,
                    'type' => 1,
                    'info' => $checkInfo,
                    'op_id' => $oper->id,
                    'isdeleted' => 0,
                    'tenant' => 0,
                    'branch_id' => $branchId,
                ]);
            }

            $check->cancel();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Batch collect checks
     */
    public function batchCollectChecks(array $checkIds, int $bankAccountId, string $collectionDate, int $branchId): int
    {
        $checks = Check::whereIn('id', $checkIds)
            ->where('status', Check::STATUS_PENDING)
            ->get();

        $processedCount = 0;

        foreach ($checks as $check) {
            try {
                $this->clearCheck($check, $bankAccountId, $collectionDate, $branchId);
                $processedCount++;
            } catch (\Exception $e) {
                // Log error but continue with other checks
                \Log::error("Failed to clear check {$check->id}: ".$e->getMessage());
            }
        }

        return $processedCount;
    }
}
