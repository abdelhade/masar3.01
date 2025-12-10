<?php

declare(strict_types=1);

namespace Modules\Accounts\Services;

use App\Models\JournalDetail;
use App\Models\JournalHead;
use App\Models\OperHead;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Accounts\Models\AccHead;
use Modules\Settings\Models\PublicSetting;

class AccountService
{
    /**
     * Update start balances in bulk for given accounts (skip 3101 and 1104%).
     */
    public function setStartBalances(array $accountIdToStartBalance): void
    {
        DB::transaction(function () use ($accountIdToStartBalance): void {
            if (empty($accountIdToStartBalance)) {
                return;
            }

            $accounts = AccHead::query()
                ->whereIn('id', array_keys($accountIdToStartBalance))
                ->lockForUpdate()
                ->get(['id', 'code', 'start_balance', 'parent_id']);

            foreach ($accounts as $account) {
                $newStart = (float) ($accountIdToStartBalance[$account->id] ?? $account->start_balance);

                // Skip accounts that cannot be edited:
                // 3101 (capital), 1104% (warehouses), 2107 (customer points),
                // 110301 (cash customer), 110501 (receivables portfolio), 210301 (payables portfolio), 210101 (cash supplier)
                if ($account->code === '3101'
                    || str_starts_with($account->code, '1104')
                    || $account->code === '2107'
                    || $account->code === '110301'
                    || $account->code === '110501'
                    || $account->code === '210301'
                    || $account->code === '210101') {
                    continue;
                }

                if ($newStart !== (float) $account->start_balance) {
                    $old = (float) $account->start_balance;
                    $account->start_balance = $newStart;
                    $account->save();

                    // Propagate delta to parents
                    $this->updateParentBalanceCascade($account->parent_id, $old, $newStart);
                }
            }
        });
    }

    /**
     * Recalculate capital (3101) start_balance from opening balances and items opening entries.
     * Also synchronizes or creates the corresponding opening journal (pro_type=61).
     */
    public function recalculateOpeningCapitalAndSyncJournal(): void
    {
        DB::transaction(function (): void {
            $capital = AccHead::query()->where('code', '3101')->lockForUpdate()->first();
            if ($capital === null) {
                throw new \RuntimeException('حساب رأس المال (3101) غير موجود');
            }

            $oldTotal = (float) $capital->start_balance;

            // Sum of opening balances excluding 1104% and excluding the capital account itself
            $sumOpening = $this->sumOpeningBalancesExcluding(['1104']);

            // Items opening balance (pro_type=60): credit on capital aggregated (stored as positive)
            $itemsOpeningHeads = JournalHead::query()->where('pro_type', 60)->pluck('id');
            $itemsOpeningOnCapital = 0.0;
            if ($itemsOpeningHeads->isNotEmpty()) {
                $itemsOpeningOnCapital = (float) JournalDetail::query()
                    ->whereIn('journal_id', $itemsOpeningHeads)
                    ->where('account_id', $capital->id)
                    ->sum('credit');
            }

            // newTotalCapital = -(sumOpening) - itemsOpeningOnCapital
            // Where sumOpening already accounts for debit(+)/credit(-) signs via stored start_balance
            $newTotal = ($sumOpening) * -1 + ($itemsOpeningOnCapital * -1);

            $capital->start_balance = $newTotal;
            $capital->save();

            if ($capital->parent_id) {
                $this->updateParentBalanceCascade($capital->parent_id, $oldTotal, $newTotal);
            }

            // Sync opening journal for accounts start balances (pro_type=61)
            $this->syncOpeningJournalFromStartBalances($sumOpening, (float) $newTotal, $capital->id);
        });
    }

    /**
     * Update parent chain start_balance by applying delta (new - old) upwards.
     */
    private function updateParentBalanceCascade(?int $parentId, float $old, float $new): void
    {
        if ($parentId === null) {
            return;
        }

        $delta = $new - $old;
        if ($delta === 0.0) {
            return;
        }

        $currentParentId = $parentId;
        $visited = [];
        while ($currentParentId !== null && (int) $currentParentId !== 0) {
            // Prevent accidental cycles
            if (isset($visited[$currentParentId])) {
                break;
            }
            $visited[$currentParentId] = true;

            $parent = AccHead::query()
                ->lockForUpdate()
                ->find($currentParentId, ['id', 'start_balance', 'parent_id']);
            if ($parent === null) {
                break;
            }

            $parent->start_balance = (float) $parent->start_balance + $delta;
            $parent->save();

            $currentParentId = $parent->parent_id;
        }
    }

    /**
     * Sum opening balances (start_balance) for accounts with codes starting by 1/2/3,
     * excluding any prefixes provided and excluding capital 3101.
     */
    public function sumOpeningBalancesExcluding(array $excludedPrefixes = []): float
    {
        $query = AccHead::query()
            ->where('is_basic', 0)
            ->where(function ($q): void {
                $q->orWhere('code', 'like', '1%')
                    ->orWhere('code', 'like', '2%')
                    ->orWhere('code', 'like', '3%');
            });

        // Exclude capital 3101
        $query->where('code', '!=', '3101');

        // Exclude prefixes (e.g., 1104)
        foreach ($excludedPrefixes as $prefix) {
            $query->where('code', 'not like', $prefix.'%');
        }

        // Sum positive (debit) and negative (credit) balances as stored
        return (float) $query->sum('start_balance');
    }

    /**
     * Get the date for opening balance journal.
     * Priority: start_date from settings -> first date of current year -> first date in oper_head
     */
    private function getOpeningBalanceDate(): string
    {
        // Try start_date from settings
        $startDate = PublicSetting::query()->where('key', 'start_date')->value('value');
        if ($startDate) {
            return \Carbon\Carbon::parse($startDate)->toDateString();
        }

        // Try first date of current year
        $firstOfYear = \Carbon\Carbon::now()->startOfYear()->toDateString();

        // Try first date in oper_head
        $firstOperDate = OperHead::query()
            ->whereNotNull('pro_date')
            ->orderBy('pro_date', 'asc')
            ->value('pro_date');

        if ($firstOperDate) {
            return \Carbon\Carbon::parse($firstOperDate)->toDateString();
        }

        // Fallback to first of year
        return $firstOfYear;
    }

    /**
     * Build/update opening journal (pro_type=61) from current accounts start balances.
     * - Updates existing OperHead & JournalHead if exists, otherwise creates new (like multi-journal)
     * - Saves JournalDetails with debit first, then credit
     * - Balance difference goes to capital account (3101) for balancing
     */
    private function syncOpeningJournalFromStartBalances(float $sumOpening, float $capitalStartBalance, int $capitalAccountId): void
    {
        $proDate = $this->getOpeningBalanceDate();
        $userId = Auth::id();

        // Check if operation exists (pro_type 61)
        $existingOper = OperHead::query()->where('pro_type', 61)->first();

        // Gather all accounts with non-zero start_balance excluding 3101 & 1104% and other excluded accounts
        $accounts = AccHead::query()
            ->where('is_basic', 0)
            ->where(function ($q): void {
                $q->orWhere('code', 'like', '1%')
                    ->orWhere('code', 'like', '2%')
                    ->orWhere('code', 'like', '3%');
            })
            ->where('code', '!=', '3101')
            ->where('code', 'not like', '1104%')
            ->where('code', '!=', '2107')
            ->where('code', '!=', '110301')
            ->where('code', '!=', '110501')
            ->where('code', '!=', '210301')
            ->where('code', '!=', '210101')
            ->orderBy('code')
            ->get(['id', 'start_balance']);

        // Calculate totals
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        // Collect debit and credit entries separately (debit first, then credit)
        $debitEntries = [];
        $creditEntries = [];

        foreach ($accounts as $acc) {
            $balance = (float) $acc->start_balance;
            if ($balance > 0) {
                $totalDebit += $balance;
                $debitEntries[] = [
                    'account_id' => $acc->id,
                    'debit' => $balance,
                    'credit' => 0.0,
                    'type' => 0, // type 0 for debit
                ];
            } elseif ($balance < 0) {
                $totalCredit += -$balance;
                $creditEntries[] = [
                    'account_id' => $acc->id,
                    'debit' => 0.0,
                    'credit' => -$balance,
                    'type' => 1, // type 1 for credit
                ];
            }
        }

        // Calculate difference for capital account
        $difference = $totalDebit - $totalCredit;

        // If operation exists, delete old journal details
        if ($existingOper) {
            $oldJournal = JournalHead::query()->where('op_id', $existingOper->id)->first();
            if ($oldJournal) {
                JournalDetail::query()->where('journal_id', $oldJournal->journal_id)->delete();
                JournalHead::query()->where('journal_id', $oldJournal->journal_id)->delete();
            }
        }

        // Determine pro_id for pro_type 61
        if ($existingOper) {
            $newProId = $existingOper->pro_id;
        } else {
            $lastProId = OperHead::query()->where('pro_type', 61)->max('pro_id');
            $newProId = $lastProId ? $lastProId + 1 : 1;
        }

        // Update or create OperHead (like multi-journal)
        $oper = OperHead::query()->updateOrCreate(
            ['pro_type' => 61],
            [
                'pro_id' => $newProId,
                'is_journal' => 1,
                'journal_type' => 1,
                'info' => 'تسجيل الارصده الافتتاحيه للحسابات',
                'pro_date' => $proDate,
                'pro_value' => $totalDebit,
                'user' => $userId,
            ]
        );

        // Create new journal_id (always create new journal_id for update)
        $lastJournalId = JournalHead::query()->max('journal_id');
        $newJournalId = $lastJournalId ? $lastJournalId + 1 : 1;

        // Create JournalHead (like multi-journal)
        JournalHead::query()->create([
            'journal_id' => $newJournalId,
            'total' => $totalDebit,
            'date' => $proDate,
            'op_id' => $oper->id,
            'pro_type' => 61,
            'user' => $userId,
        ]);

        // Save debit entries first
        foreach ($debitEntries as $entry) {
            JournalDetail::query()->create([
                'journal_id' => $newJournalId,
                'account_id' => $entry['account_id'],
                'debit' => $entry['debit'],
                'credit' => $entry['credit'],
                'type' => $entry['type'],
                'op_id' => $oper->id,
                'isdeleted' => 0,
            ]);
        }

        // Save credit entries
        foreach ($creditEntries as $entry) {
            JournalDetail::query()->create([
                'journal_id' => $newJournalId,
                'account_id' => $entry['account_id'],
                'debit' => $entry['debit'],
                'credit' => $entry['credit'],
                'type' => $entry['type'],
                'op_id' => $oper->id,
                'isdeleted' => 0,
            ]);
        }

        // Add capital account entry to balance the journal
        if ($difference > 0) {
            // Debit difference (capital account is credit)
            JournalDetail::query()->create([
                'journal_id' => $newJournalId,
                'account_id' => $capitalAccountId,
                'debit' => 0.0,
                'credit' => $difference,
                'type' => 1,
                'op_id' => $oper->id,
                'isdeleted' => 0,
            ]);
        } elseif ($difference < 0) {
            // Credit difference (capital account is debit)
            JournalDetail::query()->create([
                'journal_id' => $newJournalId,
                'account_id' => $capitalAccountId,
                'debit' => -$difference,
                'credit' => 0.0,
                'type' => 0,
                'op_id' => $oper->id,
                'isdeleted' => 0,
            ]);
        }
    }

    // create journal head
    public function createJournalHead($data)
    {
        $journalHead = JournalHead::create([
            'journal_id' => JournalHead::max('journal_id') + 1,
            'pro_type' => $data['pro_type'],
            'total' => $data['total'],
            'date' => now(),
            'op_id' => $data['op_id'],
            'user' => Auth::id(),
            'branch' => Auth::user()->branch_id,
        ]);
        $this->createJournalDetail($data, $journalHead->id);
    }

    // create journal detail
    private function createJournalDetail($data, $journalHeadId)
    {
        // debit account
        JournalDetail::create([
            'journal_id' => $journalHeadId,
            'account_id' => $data['debit_Account_id'],
            'debit' => $data['total'],
            'credit' => 0,
            'type' => 1,
        ]);
        // credit account
        JournalDetail::create([
            'journal_id' => $journalHeadId,
            'account_id' => $data['credit_Account_id'],
            'debit' => 0,
            'credit' => $data['total'],
            'type' => 0,
        ]);

        // update the debit account balance and all the parents balances by the data total
        $this->updateAccountBalanceRecursive($data['debit_Account_id'], $data['total']);
        // update the credit account balance and all the parents balances
        $this->updateAccountBalanceRecursive($data['credit_Account_id'], -$data['total']);
    }

    /**
     * Recursively update account balance and all parent accounts by adding/subtracting the total
     */
    private function updateAccountBalanceRecursive(int $accountId, float $total): void
    {
        try {
            $accHead = AccHead::find($accountId);
            if (! $accHead) {
                return;
            }

            // Update current account balance by adding/subtracting the total
            $currentBalance = (float) ($accHead->balance ?? 0);
            $accHead->balance = $currentBalance + $total;
            $accHead->save();

            // Recursively update parent account with the same total
            if ($accHead->parent_id) {
                $this->updateAccountBalanceRecursive($accHead->parent_id, $total);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to update account balance recursively: '.$e->getMessage(), [
                'account_id' => $accountId,
                'total' => $total,
                'exception' => $e,
            ]);
        }
    }
}
