<?php

declare(strict_types=1);

namespace Modules\Accounts\Services;

use Modules\Accounts\Models\AccHead;
use App\Models\JournalDetail;
use App\Models\JournalHead;
use App\Models\OperHead;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

                // Skip capital account 3101 and warehouses 1104%
                if ($account->code === '3101' || str_starts_with($account->code, '1104')) {
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
            $query->where('code', 'not like', $prefix . '%');
        }

        // Sum positive (debit) and negative (credit) balances as stored
        return (float) $query->sum('start_balance');
    }

    /**
     * Build/update opening journal (pro_type=61) from current accounts start balances.
     * - Creates/updates OperHead & JournalHead
     * - Upserts JournalDetails per account (skip 3101 & 1104%)
     * - Writes capital line to balance the journal using provided computed values.
     */
    private function syncOpeningJournalFromStartBalances(float $sumOpening, float $capitalStartBalance, int $capitalAccountId): void
    {
        $startDate = (string) PublicSetting::query()->where('key', 'start_date')->value('value');
        $userId = Auth::id();

        $oper = OperHead::query()->updateOrCreate(
            ['pro_type' => 61],
            [
                'is_journal' => 1,
                'journal_type' => 1,
                'info' => 'تسجيل الارصده الافتتاحيه للحسابات',
                'pro_date' => $startDate,
                'user' => $userId,
            ]
        );

        $journalId = (int) (JournalHead::query()
            ->where('pro_type', 61)
            ->where('op_id', $oper->id)
            ->value('journal_id') ?? ((int) JournalHead::query()->max('journal_id') + 1));

        // Gather all accounts with non-zero start_balance excluding 3101 & 1104%
        $accounts = AccHead::query()
            ->where('is_basic', 0)
            ->where(function ($q): void {
                $q->orWhere('code', 'like', '1%')
                  ->orWhere('code', 'like', '2%')
                  ->orWhere('code', 'like', '3%');
            })
            ->where('code', '!=', '3101')
            ->where('code', 'not like', '1104%')
            ->get(['id', 'start_balance']);

        $totalDebit = 0.0;
        $totalCredit = 0.0;

        // Pre-compute header total from account balances (handles all-debit or all-credit cases)
        $headerDebit = (float) $accounts->sum(function ($a) { return max(0.0, (float) $a->start_balance); });
        $headerCredit = (float) $accounts->sum(function ($a) { return max(0.0, -1.0 * (float) $a->start_balance); });
        $headerTotal = max($headerDebit, $headerCredit);

        JournalHead::query()->updateOrCreate(
            ['journal_id' => $journalId, 'pro_type' => 61],
            [
                'op_id' => $oper->id,
                'total' => $headerTotal,
                'date' => $startDate,
                'op2' => $oper->id,
                'user' => $userId,
            ]
        );

        foreach ($accounts as $acc) {
            $balance = (float) $acc->start_balance;
            if ($balance > 0) {
                $totalDebit += $balance;
                JournalDetail::query()->updateOrCreate(
                    ['journal_id' => $journalId, 'account_id' => $acc->id, 'op_id' => $oper->id],
                    ['debit' => $balance, 'credit' => 0.0, 'type' => 1]
                );
            } elseif ($balance < 0) {
                $totalCredit += -$balance;
                JournalDetail::query()->updateOrCreate(
                    ['journal_id' => $journalId, 'account_id' => $acc->id, 'op_id' => $oper->id],
                    ['debit' => 0.0, 'credit' => -$balance, 'type' => 1]
                );
            } else {
                // zero -> ensure no lingering detail
                JournalDetail::query()
                    ->where('journal_id', $journalId)
                    ->where('account_id', $acc->id)
                    ->where('op_id', $oper->id)
                    ->delete();
            }
        }

        // Capital line to balance journal based on computed opening capital component (sumOpening)
        if ($sumOpening > 0) {
            JournalDetail::query()->updateOrCreate(
                ['journal_id' => $journalId, 'account_id' => $capitalAccountId, 'op_id' => $oper->id],
                ['debit' => 0.0, 'credit' => $sumOpening, 'type' => 1]
            );
        } elseif ($sumOpening < 0) {
            JournalDetail::query()->updateOrCreate(
                ['journal_id' => $journalId, 'account_id' => $capitalAccountId, 'op_id' => $oper->id],
                ['debit' => -$sumOpening, 'credit' => 0.0, 'type' => 1]
            );
        } else {
            JournalDetail::query()
                ->where('journal_id', $journalId)
                ->where('op_id', $oper->id)
                ->where('account_id', $capitalAccountId)
                ->delete();
            // Remove header if empty
            if (!JournalDetail::query()->where('journal_id', $journalId)->exists()) {
                JournalHead::query()->where('journal_id', $journalId)->delete();
            }
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
        $this->createJournalDetail($data,$journalHead->id);
    }

    // create journal detail
    private function createJournalDetail($data,$journalHeadId)
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
        $this->updateAccountBalanceRecursive($data['debit_Account_id'],$data['total']   );
        // update the credit account balance and all the parents balances
        $this->updateAccountBalanceRecursive($data['credit_Account_id'],-$data['total']);
    }

    /**
     * Recursively update account balance and all parent accounts by adding/subtracting the total
     */
    private function updateAccountBalanceRecursive(int $accountId, float $total): void
    {
        try {
            $accHead = AccHead::find($accountId);
            if (!$accHead) {
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
            Log::error('Failed to update account balance recursively: ' . $e->getMessage(), [
                'account_id' => $accountId,
                'total' => $total,
                'exception' => $e
            ]);
        }
    }
}



