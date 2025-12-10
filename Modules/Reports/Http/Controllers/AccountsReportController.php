<?php

declare(strict_types=1);

namespace Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\JournalDetail;
use Modules\Accounts\Models\AccHead;
use Modules\Reports\Services\ReportCalculationTrait;

class AccountsReportController extends Controller
{
    use ReportCalculationTrait;

    // accounts tree
    public function accountsTree()
    {
        // Load all accounts with recursive children relationships
        $accounts = AccHead::where('parent_id', null)
            ->with('children.children.children.children.children')
            ->get();

        return view('reports::accounts-reports.accounts-tree', compact('accounts'));
    }

    // الميزانية العمومية
    public function generalBalanceSheet()
    {
        $asOfDate = request('as_of_date', now()->format('Y-m-d'));

        // جميع الأصول الرئيسية (الحسابات التي ليس لديها parent) مع جميع الأطفال بشكل recursive
        $assets = AccHead::where('code', 'like', '1%')
            ->where('isdeleted', 0)
            ->whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->where('isdeleted', 0)->orderBy('code')
                    ->with(['children' => function ($q2) {
                        $q2->where('isdeleted', 0)->orderBy('code')
                            ->with(['children' => function ($q3) {
                                $q3->where('isdeleted', 0)->orderBy('code')
                                    ->with(['children' => function ($q4) {
                                        $q4->where('isdeleted', 0)->orderBy('code');
                                    }]);
                            }]);
                    }]);
            }])
            ->orderBy('code')
            ->get();

        // نفس الشيء للخصوم
        $liabilities = AccHead::where('code', 'like', '2%')
            ->where('isdeleted', 0)
            ->whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->where('isdeleted', 0)->orderBy('code')
                    ->with(['children' => function ($q2) {
                        $q2->where('isdeleted', 0)->orderBy('code')
                            ->with(['children' => function ($q3) {
                                $q3->where('isdeleted', 0)->orderBy('code')
                                    ->with(['children' => function ($q4) {
                                        $q4->where('isdeleted', 0)->orderBy('code');
                                    }]);
                            }]);
                    }]);
            }])
            ->orderBy('code')
            ->get();

        // حقوق الملكية
        $equity = AccHead::where('code', 'like', '3%')
            ->where('isdeleted', 0)
            ->whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->where('isdeleted', 0)->orderBy('code')
                    ->with(['children' => function ($q2) {
                        $q2->where('isdeleted', 0)->orderBy('code')
                            ->with(['children' => function ($q3) {
                                $q3->where('isdeleted', 0)->orderBy('code')
                                    ->with(['children' => function ($q4) {
                                        $q4->where('isdeleted', 0)->orderBy('code');
                                    }]);
                            }]);
                    }]);
            }])
            ->orderBy('code')
            ->get();

        // Calculate totals recursively
        $totalAssets = $this->calculateTotalBalance($assets);
        $totalLiabilities = $this->calculateTotalBalance($liabilities);
        $totalEquity = $this->calculateTotalBalance($equity);

        // Calculate net profit/loss (revenues - expenses)
        // Assuming revenues start with 4 and expenses with 5
        $revenues = AccHead::where('code', 'like', '4%')->where('isdeleted', 0)->sum('balance');
        $expenses = AccHead::where('code', 'like', '5%')->where('isdeleted', 0)->sum('balance');
        $netProfit = $revenues - $expenses;

        $totalLiabilitiesEquity = $totalLiabilities + $totalEquity + $netProfit;

        return view('reports::accounts-reports.general-balance-sheet', compact(
            'assets',
            'liabilities',
            'equity',
            'asOfDate',
            'totalAssets',
            'totalLiabilities',
            'totalEquity',
            'netProfit',
            'totalLiabilitiesEquity'
        ));
    }

    // Helper method to calculate total balance recursively
    private function calculateTotalBalance($accounts)
    {
        $total = 0;
        foreach ($accounts as $account) {
            $total += $account->balance ?? 0;
            if ($account->children && $account->children->count() > 0) {
                $total += $this->calculateTotalBalance($account->children);
            }
        }

        return $total;
    }

    public function generalProfitLossReport()
    {
        $fromDate = request('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = request('to_date', now()->format('Y-m-d'));

        // جلب الحسابات الرئيسية للإيرادات
        $revenueAccounts = AccHead::where('code', 'like', '4%')
            ->where('isdeleted', 0)
            ->whereNull('parent_id')
            ->with('allChildren')
            ->orderBy('code')
            ->get();

        // جلب الحسابات الرئيسية للمصروفات
        $expenseAccounts = AccHead::where('code', 'like', '5%')
            ->where('isdeleted', 0)
            ->whereNull('parent_id')
            ->with('allChildren')
            ->orderBy('code')
            ->get();

        $totalRevenue = 0;
        $totalExpenses = 0;

        // حساب الإيرادات
        $this->calculateAccountBalances($revenueAccounts, $fromDate, $toDate, 'revenue', $totalRevenue);

        // حساب المصروفات
        $this->calculateAccountBalances($expenseAccounts, $fromDate, $toDate, 'expense', $totalExpenses);

        $netProfit = $totalRevenue - $totalExpenses;

        return view('reports::accounts-reports.general-profit-loss-report', compact(
            'revenueAccounts',
            'expenseAccounts',
            'totalRevenue',
            'totalExpenses',
            'netProfit',
            'fromDate',
            'toDate'
        ));
    }

    /**
     * حساب أرصدة الحسابات بشكل متداخل
     */
    private function calculateAccountBalances($accounts, $fromDate, $toDate, $type, &$total)
    {
        foreach ($accounts as $account) {
            // حساب رصيد الحساب الحالي
            $query = JournalDetail::where('account_id', $account->id)
                ->where('isdeleted', 0)
                ->whereHas('head.oper', function ($q) use ($fromDate, $toDate) {
                    $q->whereBetween('pro_date', [$fromDate, $toDate])
                        ->where('isdeleted', 0);
                });

            // حساب الصافي: للإيرادات (credit - debit)، للمصروفات (debit - credit)
            $totalCredit = (clone $query)->sum('credit') ?? 0.0;
            $totalDebit = (clone $query)->sum('debit') ?? 0.0;

            if ($type === 'revenue') {
                // الإيرادات: credit - debit (الصافي)
                $balance = $totalCredit - $totalDebit;
            } else {
                // المصروفات: debit - credit (الصافي)
                $balance = $totalDebit - $totalCredit;
            }

            $account->balance = $balance;
            $account->childrenTotal = 0;

            // حساب أرصدة الحسابات الفرعية
            if ($account->children && $account->children->count() > 0) {
                $childrenTotal = 0;
                $this->calculateAccountBalances($account->children, $fromDate, $toDate, $type, $childrenTotal);
                $account->childrenTotal = $childrenTotal;
            }

            // الإجمالي الكلي للحساب وأبنائه
            $account->totalWithChildren = $balance + $account->childrenTotal;
            $total += $account->totalWithChildren;
        }
    }

    // ميزان الحسابات
    public function generalAccountBalances()
    {
        $asOfDate = request('as_of_date', now()->format('Y-m-d'));
        $accountGroup = request('account_group');

        $query = AccHead::where('isdeleted', 0)->orderBy('code', 'asc');

        if ($accountGroup) {
            $query->where('code', 'like', $accountGroup.'%');
        }

        $accountBalances = $query->paginate(200)->through(function ($account) use ($asOfDate) {
            $balance = $this->calculateAccountBalance($account->id, $asOfDate);
            $debit = $balance > 0 ? $balance : 0;
            $credit = $balance < 0 ? abs($balance) : 0;

            $account->debit = $debit;
            $account->credit = $credit;
            $account->balance = $balance;

            return $account;
        });

        $totalDebit = $accountBalances->sum('debit');
        $totalCredit = $accountBalances->sum('credit');
        $totalBalance = $accountBalances->sum('balance');

        return view('reports::accounts-reports.general-account-balances', compact(
            'accountBalances',
            'totalDebit',
            'totalCredit',
            'totalBalance',
            'asOfDate'
        ));
    }

    /**
     * مقارنة أرصدة الحسابات من acchead.balance مع القيود اليومية
     */
    public function compareAccountBalances()
    {
        $asOfDate = request('as_of_date', now()->format('Y-m-d'));

        // جلب الحسابات غير الأساسية فقط (is_basic = 0)
        $accounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->orderBy('code')
            ->get();

        $comparisons = [];
        $totalDifference = 0;

        foreach ($accounts as $account) {
            // حساب الرصيد من القيود اليومية حتى التاريخ المحدد
            // استخدام نفس الطريقة المستخدمة في calculateAccountBalance
            $query = JournalDetail::where('account_id', $account->id)
                ->where('isdeleted', 0);

            if ($asOfDate) {
                // فلترة حسب تاريخ القيد أو تاريخ العملية
                $query->where(function ($q) use ($asOfDate) {
                    $q->whereDate('crtime', '<=', $asOfDate)
                        ->orWhereHas('head.oper', function ($subQ) use ($asOfDate) {
                            $subQ->whereDate('pro_date', '<=', $asOfDate)
                                ->where('isdeleted', 0);
                        });
                });
            }

            $totalDebit = (clone $query)->sum('debit') ?? 0.0;
            $totalCredit = (clone $query)->sum('credit') ?? 0.0;
            $journalBalance = (float) ($totalDebit - $totalCredit);

            // الرصيد من acchead.balance
            $accountBalance = (float) ($account->balance ?? 0);

            // حساب الفرق
            $difference = $accountBalance - $journalBalance;

            // إضافة فقط الحسابات التي لديها فرق أو لديها حركة
            if (abs($difference) > 0.01 || abs($journalBalance) > 0.01 || abs($accountBalance) > 0.01) {
                $comparisons[] = [
                    'account_id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->aname,
                    'account_balance' => $accountBalance,
                    'journal_balance' => $journalBalance,
                    'difference' => $difference,
                    'has_difference' => abs($difference) > 0.01,
                ];

                $totalDifference += abs($difference);
            }
        }

        return response()->json([
            'success' => true,
            'as_of_date' => $asOfDate,
            'comparisons' => $comparisons,
            'total_accounts' => count($comparisons),
            'accounts_with_difference' => count(array_filter($comparisons, fn ($c) => $c['has_difference'])),
            'total_difference' => $totalDifference,
        ]);
    }
}
