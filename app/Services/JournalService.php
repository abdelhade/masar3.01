<?php

declare(strict_types=1);

namespace App\Services;

use Modules\Accounts\Models\AccHead;
use App\Models\JournalDetail;
use App\Models\JournalHead;
use App\Models\OperHead;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JournalService
{
    /**
     * Create a journal (OperHead + JournalHead + JournalDetails) with validation and transactions.
     *
     * $lines = [
     *   ['account_id' => int, 'debit' => float, 'credit' => float, 'type' => int, 'info' => ?string]
     * ]
     * $meta = [
     *   'pro_type' => int, 'date' => Carbon|string, 'pro_num' => ?string, 'emp_id' => ?int,
     *   'info' => ?string, 'info2' => ?string, 'info3' => ?string, 'details' => ?string,
     *   'acc1' => ?int, 'acc2' => ?int, 'cost_center' => ?int
     * ]
     */
    public function createJournal(array $lines, array $meta): int
    {
        $this->assertBalanced($lines);

        return DB::transaction(function () use ($lines, $meta): int {
            $date = $this->normalizeDate($meta['date'] ?? now());
            $proType = (int) ($meta['pro_type'] ?? 7);

            $lastProId = (int) OperHead::query()->where('pro_type', $proType)->max('pro_id');
            $newProId = $lastProId > 0 ? $lastProId + 1 : 1;

            $debitTotal = $this->sumDebit($lines);

            $oper = OperHead::query()->create([
                'pro_id' => $newProId,
                'branch_id' => 1,
                'is_stock' => 0,
                'is_finance' => 0,
                'is_manager' => 0,
                'is_journal' => 1,
                'journal_type' => 1,
                'info' => $meta['info'] ?? null,
                'info2' => $meta['info2'] ?? null,
                'info3' => $meta['info3'] ?? null,
                'details' => $meta['details'] ?? null,
                'pro_date' => $date->toDateString(),
                'pro_num' => $meta['pro_num'] ?? null,
                'emp_id' => $meta['emp_id'] ?? null,
                'acc1' => $meta['acc1'] ?? null,
                'acc2' => $meta['acc2'] ?? null,
                'pro_value' => $debitTotal,
                'cost_center' => $meta['cost_center'] ?? null,
                'user' => Auth::id(),
                'pro_type' => $proType,
            ]);

            $lastJournalId = (int) JournalHead::query()->max('journal_id');
            $newJournalId = $lastJournalId > 0 ? $lastJournalId + 1 : 1;

            JournalHead::query()->create([
                'journal_id' => $newJournalId,
                'total' => $debitTotal,
                'date' => $date->toDateString(),
                'op_id' => $oper->id,
                'pro_type' => $proType,
                'details' => $meta['details'] ?? null,
                'user' => Auth::id(),
            ]);

            $this->upsertDetails($newJournalId, $oper->id, $lines);

            return $newJournalId;
        });
    }

    /**
     * Update an existing journal by journalId. Replaces details and updates heads.
     */
    public function updateJournal(int $journalId, array $lines, array $meta): void
    {
        $this->assertBalanced($lines);

        DB::transaction(function () use ($journalId, $lines, $meta): void {
            $journalHead = JournalHead::query()->where('journal_id', $journalId)->lockForUpdate()->firstOrFail();
            $oper = OperHead::query()->lockForUpdate()->findOrFail($journalHead->op_id);

            $date = $this->normalizeDate($meta['date'] ?? $journalHead->date);
            $proType = (int) ($meta['pro_type'] ?? $journalHead->pro_type);
            $debitTotal = $this->sumDebit($lines);

            $oper->update([
                'pro_date' => $date->toDateString(),
                'pro_num' => $meta['pro_num'] ?? $oper->pro_num,
                'emp_id' => $meta['emp_id'] ?? $oper->emp_id,
                'info' => $meta['info'] ?? $oper->info,
                'info2' => $meta['info2'] ?? $oper->info2,
                'info3' => $meta['info3'] ?? $oper->info3,
                'details' => $meta['details'] ?? $oper->details,
                'acc1' => $meta['acc1'] ?? $oper->acc1,
                'acc2' => $meta['acc2'] ?? $oper->acc2,
                'pro_value' => $debitTotal,
                'cost_center' => $meta['cost_center'] ?? $oper->cost_center,
                'user' => Auth::id(),
                'pro_type' => $proType,
            ]);

            $journalHead->update([
                'total' => $debitTotal,
                'date' => $date->toDateString(),
                'details' => $meta['details'] ?? $journalHead->details,
                'user' => Auth::id(),
            ]);

            JournalDetail::query()->where('op_id', $oper->id)->delete();
            $this->upsertDetails($journalId, $oper->id, $lines);
        });
    }

    /**
     * Delete a journal entirely (details + head + oper).
     */
    public function deleteJournal(int $journalId): void
    {
        DB::transaction(function () use ($journalId): void {
            $journalHead = JournalHead::query()->where('journal_id', $journalId)->lockForUpdate()->firstOrFail();
            $opId = (int) $journalHead->op_id;

            JournalDetail::query()->where('op_id', $opId)->delete();
            JournalHead::query()->where('journal_id', $journalId)->delete();
            OperHead::query()->where('id', $opId)->delete();
        });
    }

    /**
     * Ensure debits == credits with 2 decimals.
     */
    private function assertBalanced(array $lines): void
    {
        $debit = $this->sumDebit($lines);
        $credit = $this->sumCredit($lines);
        if (round($debit, 2) !== round($credit, 2)) {
            throw new \InvalidArgumentException('الحسابات المدينة لا تتساوى مع الحسابات الدائنة.');
        }
    }

    private function sumDebit(array $lines): float
    {
        $total = 0.0;
        foreach ($lines as $line) {
            $total += (float) ($line['debit'] ?? 0);
        }
        return $total;
    }

    private function sumCredit(array $lines): float
    {
        $total = 0.0;
        foreach ($lines as $line) {
            $total += (float) ($line['credit'] ?? 0);
        }
        return $total;
    }

    /**
     * Upsert details for a journal. Assumes prior deletion on update when needed.
     */
    private function upsertDetails(int $journalId, int $opId, array $lines): void
    {
        foreach ($lines as $line) {
            $accountId = (int) $line['account_id'];
            // Lock the account row to avoid race conditions if needed in future balance apps
            AccHead::query()->lockForUpdate()->findOrFail($accountId);

            JournalDetail::query()->create([
                'journal_id' => $journalId,
                'account_id' => $accountId,
                'debit' => (float) ($line['debit'] ?? 0),
                'credit' => (float) ($line['credit'] ?? 0),
                'type' => (int) ($line['type'] ?? 0),
                'info' => $line['info'] ?? null,
                'op_id' => $opId,
                'isdeleted' => 0,
            ]);
        }
    }

    private function normalizeDate(Carbon|string $date): Carbon
    {
        if ($date instanceof Carbon) {
            return $date;
        }
        return Carbon::parse($date);
    }
}


