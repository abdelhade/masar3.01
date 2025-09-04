<?php

namespace App\Observers;

use App\Models\AccHead;
use App\Models\JournalDetail;
use Illuminate\Support\Facades\Log;

class JournalDetailObserver
{
    public function saved(JournalDetail $journalDetail)
    {
        $this->updateAccountBalanceRecursive($journalDetail->account_id);
    }

    public function updated(JournalDetail $journalDetail)
    {
        $this->updateAccountBalanceRecursive($journalDetail->account_id);
    }

    public function deleted(JournalDetail $journalDetail)
    {
        $this->updateAccountBalanceRecursive($journalDetail->account_id);
    }

    /**
     * Recursively update account balance and all parent accounts
     */
    protected function updateAccountBalanceRecursive($accountId)
    {
        try {
            $accHead = AccHead::find($accountId);
            if (!$accHead) {
                return;
            }

            // احسب الرصيد للحساب الحالي
            if ($this->isLeafAccount($accountId)) {
                $this->updateLeafAccountBalance($accountId);
            } else {
                $this->updateParentAccountBalance($accountId);
            }

            // مهم: اطلع للأب وخليه يعمل نفس الحساب بعد ما تخلص
            if ($accHead->parent_id) {
                $this->updateAccountBalanceRecursive($accHead->parent_id);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to update account balance recursively: ' . $e->getMessage());
        }
    }


    /**
     * Check if account is a leaf account (has no children)
     */
    protected function isLeafAccount($accountId)
    {
        return !AccHead::where('parent_id', $accountId)->exists();
    }

    /**
     * Update leaf account balance from journal details
     */
    protected function updateLeafAccountBalance($accountId)
    {
        $totalDebit = JournalDetail::where('account_id', $accountId)->sum('debit');
        $totalCredit = JournalDetail::where('account_id', $accountId)->sum('credit');
        $balance = $totalDebit - $totalCredit;

        $accHead = AccHead::find($accountId);
        if ($accHead) {
            $accHead->balance = $balance;
            $accHead->save();
        }
    }

    /**
     * Update parent account balance from children balances
     */
    protected function updateParentAccountBalance($accountId)
    {
        $children = AccHead::where('parent_id', $accountId)->get();
        $total = 0;

        foreach ($children as $child) {
            // حدّث الولد الأول (سواء كان Leaf أو Parent)
            if ($this->isLeafAccount($child->id)) {
                $this->updateLeafAccountBalance($child->id);
            } else {
                $this->updateParentAccountBalance($child->id);
            }

            $total += $child->balance;
        }

        $accHead = AccHead::find($accountId);
        if ($accHead) {
            $accHead->balance = $total;
            $accHead->save();
        }
    }


    /**
     * Alternative recursive method that updates entire account tree
     * This can be used for bulk operations or when you need to update the entire hierarchy
     */
    protected function updateEntireAccountTree($rootAccountId = null)
    {
        try {
            if ($rootAccountId) {
                // Update specific account tree
                $this->updateAccountBalanceRecursive($rootAccountId);
            } else {
                // Update all leaf accounts first, then their parents
                $this->updateAllLeafAccounts();
                $this->updateAllParentAccounts();
            }
        } catch (\Throwable $e) {
            Log::error('Failed to update entire account tree: ' . $e->getMessage());
        }
    }

    /**
     * Update all leaf accounts (accounts with no children)
     */
    protected function updateAllLeafAccounts()
    {
        $leafAccounts = AccHead::whereNotIn('id', function ($query) {
            $query->select('parent_id')->from('acc_heads')->whereNotNull('parent_id');
        })->get();

        foreach ($leafAccounts as $account) {
            $this->updateLeafAccountBalance($account->id);
        }
    }

    /**
     * Update all parent accounts recursively
     */
    protected function updateAllParentAccounts()
    {
        $parentAccounts = AccHead::whereIn('id', function ($query) {
            $query->select('parent_id')->from('acc_heads')->whereNotNull('parent_id');
        })->orderBy('level', 'desc')->get();

        foreach ($parentAccounts as $account) {
            $this->updateParentAccountBalance($account->id);
        }
    }
}
