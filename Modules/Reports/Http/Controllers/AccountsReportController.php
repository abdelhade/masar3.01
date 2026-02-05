<?php

declare(strict_types=1);

namespace Modules\Reports\Http\Controllers;

use App\Models\JournalDetail;
use Illuminate\Routing\Controller;
use Modules\Accounts\Models\AccHead;
use Modules\Reports\Services\ReportCalculationTrait;

class AccountsReportController extends Controller
{
    use ReportCalculationTrait;

    public function __construct()
    {
        $this->middleware('can:view account-movement-report');
    }

    /**
     * جلب الحسابات مع جميع الأطفال بشكل recursive
     */
    private function loadAccountsWithChildren(string $codePrefix, ?int $parentId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = AccHead::where('code', 'like', "{$codePrefix}%")
            ->where('isdeleted', 0);

        if ($parentId === null) {
            // للحسابات الرئيسية، نجلب الحسابات التي ليس لها parent
            $query->whereNull('parent_id');
        } else {
            // للحسابات الفرعية، نجلب الحسابات التي لها parent محدد
            $query->where('parent_id', $parentId);
        }

        return $query->with(['children' => function ($q) {
            $q->where('isdeleted', 0)
                ->orderBy('code')
                ->with(['children' => function ($q2) {
                    $q2->where('isdeleted', 0)
                        ->orderBy('code')
                        ->with(['children' => function ($q3) {
                            $q3->where('isdeleted', 0)
                                ->orderBy('code')
                                ->with(['children' => function ($q4) {
                                    $q4->where('isdeleted', 0)
                                        ->orderBy('code')
                                        ->with(['children' => function ($q5) {
                                            $q5->where('isdeleted', 0)
                                                ->orderBy('code');
                                        }]);
                                }]);
                        }]);
                }]);
        }])
            ->orderBy('code')
            ->get();
    }

    // accounts tree
    public function accountsTree()
    {
        // Load all accounts with recursive children relationships
        $accounts = AccHead::where('parent_id', null)
            ->with('allChildren')
            ->get();

        return view('reports::accounts-reports.accounts-tree', compact('accounts'));
    }

    // الميزانية العمومية
    public function generalBalanceSheet()
    {
        $asOfDate = request('as_of_date', now()->format('Y-m-d'));

        // جميع الأصول الرئيسية (الحسابات التي ليس لديها parent) مع جميع الأطفال بشكل recursive
        $assets = $this->loadAccountsWithChildren('1');

        // نفس الشيء للخصوم
        $liabilities = $this->loadAccountsWithChildren('2');

        // حقوق الملكية
        $equity = $this->loadAccountsWithChildren('3');

        // حساب totalWithChildren لكل حساب من journal_details حتى التاريخ المحدد
        // الأصول: نجمع كما هي (موجبة)
        $this->calculateTotalWithChildren($assets, $asOfDate, false);
        // الخصوم وحقوق الملكية: نستخدم القيمة المطلقة للعرض (نحول السالب إلى موجب)
        $this->calculateTotalWithChildren($liabilities, $asOfDate, true);
        $this->calculateTotalWithChildren($equity, $asOfDate, true);

        // Calculate totals recursively
        // الأصول: نجمع كما هي (موجبة)
        $totalAssets = $this->calculateTotalBalance($assets);
        // الخصوم وحقوق الملكية: نجمع القيمة المطلقة (نحول السالب إلى موجب)
        $totalLiabilities = abs($this->calculateTotalBalance($liabilities));
        $totalEquity = abs($this->calculateTotalBalance($equity));

        // Calculate net profit/loss (revenues - expenses)
        // Assuming revenues start with 4 and expenses with 5
        // جلب الحسابات الرئيسية للإيرادات والمصروفات
        $revenueAccounts = $this->loadAccountsWithChildren('4');
        $expenseAccounts = $this->loadAccountsWithChildren('5');

        // حساب totalWithChildren للإيرادات والمصروفات من journal_details حتى التاريخ المحدد
        $this->calculateTotalWithChildren($revenueAccounts, $asOfDate);
        $this->calculateTotalWithChildren($expenseAccounts, $asOfDate);

        // حساب إجمالي الإيرادات والمصروفات من الحسابات الرئيسية فقط
        $revenues = $this->calculateTotalBalance($revenueAccounts);
        $expenses = $this->calculateTotalBalance($expenseAccounts);
        $netProfit = $revenues - $expenses;

        // المعادلة: الأصول = الخصوم + حقوق الملكية + صافي الأرباح والخسائر
        // صافي الربح/الخسارة: إذا كان موجب فهو ربح (يضاف)، إذا كان سالب فهو خسارة (يطرح)
        $totalLiabilitiesEquity = $totalLiabilities + $totalEquity + $netProfit;

        // حساب الفرق والتحقق من التوازن
        $difference = abs($totalAssets - $totalLiabilitiesEquity);
        $isBalanced = $difference < 0.01;

        return view('reports::accounts-reports.general-balance-sheet', compact(
            'assets',
            'liabilities',
            'equity',
            'asOfDate',
            'totalAssets',
            'totalLiabilities',
            'totalEquity',
            'netProfit',
            'totalLiabilitiesEquity',
            'difference',
            'isBalanced'
        ));
    }

    /**
     * جمع جميع account IDs من جميع المستويات بشكل recursive
     */
    private function getAllAccountIds(\Illuminate\Database\Eloquent\Collection $accounts): array
    {
        $ids = [];
        foreach ($accounts as $account) {
            $ids[] = $account->id;
            if ($account->children && $account->children->count() > 0) {
                $ids = array_merge($ids, $this->getAllAccountIds($account->children));
            }
        }

        return array_unique($ids);
    }

    /**
     * حساب totalWithChildren لكل حساب بشكل recursive من journal_details
     */
    private function calculateTotalWithChildren(\Illuminate\Database\Eloquent\Collection $accounts, ?string $asOfDate = null, bool $useAbsoluteValue = false): void
    {
        foreach ($accounts as $account) {
            // حساب رصيد الحساب من journal_details حتى التاريخ المحدد
            $accountBalance = $this->calculateAccountBalance($account->id, $asOfDate);

            // للحسابات الدائنة (الخصوم وحقوق الملكية)، نستخدم القيمة المطلقة للعرض
            if ($useAbsoluteValue && $accountBalance < 0) {
                $accountBalance = abs($accountBalance);
            }

            $childrenTotal = 0.0;

            if ($account->children && $account->children->count() > 0) {
                // حساب totalWithChildren للأطفال أولاً
                $this->calculateTotalWithChildren($account->children, $asOfDate, $useAbsoluteValue);
                // ثم جمع أرصدة الأطفال
                foreach ($account->children as $child) {
                    $childrenTotal += (float) ($child->totalWithChildren ?? 0);
                }
            }

            // totalWithChildren = رصيد الحساب + مجموع أرصدة الأطفال
            $account->totalWithChildren = $accountBalance + $childrenTotal;
            // حفظ الرصيد المحسوب في balance للعرض
            $account->balance = $accountBalance;
        }
    }

    /**
     * حساب إجمالي الرصيد بشكل recursive
     */
    private function calculateTotalBalance(\Illuminate\Database\Eloquent\Collection $accounts): float
    {
        $total = 0.0;
        foreach ($accounts as $account) {
            // استخدام totalWithChildren إذا كان موجوداً، وإلا استخدام balance
            $balance = (float) ($account->totalWithChildren ?? $account->balance ?? 0);
            $total += $balance;
        }

        return $total;
    }

    public function generalProfitLossReport()
    {
        $fromDate = request('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = request('to_date', now()->format('Y-m-d'));

        // جلب الحسابات الرئيسية للإيرادات
        $revenueAccounts = $this->loadAccountsWithChildren('4');

        // جلب الحسابات الرئيسية للمصروفات
        $expenseAccounts = $this->loadAccountsWithChildren('5');

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
     * تقرير قائمة الدخل لإجمالي الفترة (بدون فلترة زمنية)
     */
    public function generalProfitLossReportTotal()
    {
        // جلب الحسابات الرئيسية للإيرادات
        $revenueAccounts = $this->loadAccountsWithChildren('4');

        // جلب الحسابات الرئيسية للمصروفات
        $expenseAccounts = $this->loadAccountsWithChildren('5');

        // حساب totalWithChildren للإيرادات والمصروفات من journal_details (كل الفترة)
        $this->calculateTotalWithChildren($revenueAccounts, null, false);
        $this->calculateTotalWithChildren($expenseAccounts, null, false);

        // حساب الإيرادات من رصيد الحساب مباشرة (مثل الميزانية العمومية)
        $totalRevenue = $this->calculateTotalBalance($revenueAccounts);

        // حساب المصروفات من رصيد الحساب مباشرة (مثل الميزانية العمومية)
        $totalExpenses = $this->calculateTotalBalance($expenseAccounts);

        $netProfit = $totalRevenue - $totalExpenses;

        // تعيين القيم للعرض
        $fromDate = null;
        $toDate = null;
        $isTotalPeriod = true;

        return view('reports::accounts-reports.general-profit-loss-report-total', compact(
            'revenueAccounts',
            'expenseAccounts',
            'totalRevenue',
            'totalExpenses',
            'netProfit',
            'fromDate',
            'toDate',
            'isTotalPeriod'
        ));
    }

    /**
     * حساب أرصدة الحسابات بشكل متداخل
     */
    private function calculateAccountBalances(
        \Illuminate\Database\Eloquent\Collection $accounts,
        string $fromDate,
        string $toDate,
        string $type,
        float &$total
    ): void {
        // جمع جميع account IDs من جميع المستويات
        $allAccountIds = $this->getAllAccountIds($accounts);

        // Query واحد لجميع الحسابات
        $journalTotals = JournalDetail::whereIn('account_id', $allAccountIds)
            ->where('isdeleted', 0)
            ->whereHas('head.oper', function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('pro_date', [$fromDate, $toDate])
                    ->where('isdeleted', 0);
            })
            ->selectRaw('account_id, SUM(credit) as total_credit, SUM(debit) as total_debit')
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        // معالجة كل حساب باستخدام البيانات المحملة
        foreach ($accounts as $account) {
            $this->processAccountBalance($account, $journalTotals, $fromDate, $toDate, $type, $total);
        }
    }

    /**
     * معالجة رصيد حساب واحد وأبنائه
     */
    private function processAccountBalance(
        AccHead $account,
        \Illuminate\Support\Collection $journalTotals,
        string $fromDate,
        string $toDate,
        string $type,
        float &$total
    ): void {
        // جلب البيانات المحملة مسبقاً
        $details = $journalTotals->get($account->id);

        $totalCredit = $details ? (float) ($details->total_credit ?? 0.0) : 0.0;
        $totalDebit = $details ? (float) ($details->total_debit ?? 0.0) : 0.0;

        if ($type === 'revenue') {
            // الإيرادات: credit - debit (الصافي)
            $balance = $totalCredit - $totalDebit;
        } else {
            // المصروفات: debit - credit (الصافي)
            $balance = $totalDebit - $totalCredit;
        }

        $account->balance = $balance;
        $account->childrenTotal = 0.0;

        // حساب أرصدة الحسابات الفرعية
        if ($account->children && $account->children->count() > 0) {
            $childrenTotal = 0.0;
            foreach ($account->children as $child) {
                $this->processAccountBalance($child, $journalTotals, $fromDate, $toDate, $type, $childrenTotal);
            }
            $account->childrenTotal = $childrenTotal;
        }

        // الإجمالي الكلي للحساب وأبنائه
        $account->totalWithChildren = $balance + $account->childrenTotal;
        $total += $account->totalWithChildren;
    }

    // ميزان الحسابات
    public function generalAccountBalances()
    {
        $asOfDate = request('as_of_date', now()->format('Y-m-d'));
        $accountGroup = request('account_group');

        $query = AccHead::where('isdeleted', 0)->orderBy('code', 'asc');

        if ($accountGroup) {
            $query->where('code', 'like', $accountGroup . '%');
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

        // جمع جميع account IDs
        $accountIds = $accounts->pluck('id')->toArray();

        // Query واحد لجميع الحسابات
        $query = JournalDetail::whereIn('account_id', $accountIds)
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

        // جلب الإجماليات لكل حساب في query واحد
        $journalBalances = $query->selectRaw('account_id, SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        $comparisons = [];
        $totalDifference = 0.0;

        foreach ($accounts as $account) {
            // جلب البيانات المحملة مسبقاً
            $journalData = $journalBalances->get($account->id);

            $totalDebit = (float) ($journalData->total_debit ?? 0.0);
            $totalCredit = (float) ($journalData->total_credit ?? 0.0);
            $journalBalance = $totalDebit - $totalCredit;

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
            'accounts_with_difference' => count(array_filter($comparisons, fn($c) => $c['has_difference'])),
            'total_difference' => $totalDifference,
        ]);
    }
}
