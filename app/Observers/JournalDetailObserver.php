<?php

namespace App\Observers;

use App\Models\AccHead;
use App\Models\JournalDetail;
use Illuminate\Support\Facades\Log;

class JournalDetailObserver
{
    public function saved(JournalDetail $journalDetail)
    {
        $this->updateAccHeadBalance($journalDetail->account_id);
    }

    public function updated(JournalDetail $journalDetail)
    {
        $this->updateAccHeadBalance($journalDetail->account_id);
    }

    public function deleted(JournalDetail $journalDetail)
    {
        $this->updateAccHeadBalance($journalDetail->account_id);
    }

    protected function updateAccHeadBalance($accountId)
    {
        try {
            $totalDebit = JournalDetail::where('account_id', $accountId)->sum('debit');
            $totalCredit = JournalDetail::where('account_id', $accountId)->sum('credit');

            $balance = $totalDebit - $totalCredit;

            $accHead = AccHead::find($accountId);
            if ($accHead) {
                $accHead->update(['balance' => $balance]);
                $accHead->save();
            }
        } catch (\Throwable $e) {
            // يمكنك تسجيل الخطأ في اللوج أو التعامل معه حسب الحاجة
            Log::error('Failed to update AccHead balance for account ID: ' . $accountId, [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
