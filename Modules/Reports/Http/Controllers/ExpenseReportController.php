<?php

declare(strict_types=1);

namespace Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Accounts\Models\AccHead;
use App\Models\JournalDetail;
use App\Models\CostCenter;
use Modules\Reports\Services\ReportCalculationTrait;
use Illuminate\Pagination\LengthAwarePaginator;

class ExpenseReportController extends Controller
{
    use ReportCalculationTrait;

    public function generalExpensesReport()
    {
        $expenseAccounts = AccHead::where('code', 'like', '57%')->where('isdeleted', 0)->get();

        $expenseTransactions = JournalDetail::whereHas('accHead', function ($q) {
            $q->where('code', 'like', '57%'); // Expense accounts
        })->with(['accHead', 'head', 'costCenter'])
            ->when(request('from_date'), function ($q) {
                $q->whereDate('crtime', '>=', request('from_date'));
            })
            ->when(request('to_date'), function ($q) {
                $q->whereDate('crtime', '<=', request('to_date'));
            })
            ->when(request('expense_account'), function ($q) {
                $q->where('account_id', request('expense_account'));
            })
            ->orderBy('crtime', 'desc')
            ->paginate(50);

        $totalExpenses = $expenseTransactions->sum('debit');
        $totalPayments = $expenseTransactions->sum('credit');
        $netExpenses = $totalExpenses - $totalPayments;
        $totalTransactions = $expenseTransactions->count();

        return view('reports::expenses.general-expenses-report', compact(
            'expenseAccounts',
            'expenseTransactions',
            'totalExpenses',
            'totalPayments',
            'netExpenses',
            'totalTransactions'
        ));
    }

    public function generalExpensesDailyReport()
    {
        $expenseAccounts = AccHead::where('code', 'like', '57%')->where('isdeleted', 0)->where('is_basic', 0)->get();
        $selectedAccount = null;
        $openingBalance = 0;
        $closingBalance = 0;

        $expenseTransactions = JournalDetail::whereNull('id')->paginate(50);

        if (request('expense_account')) {
            $selectedAccount = AccHead::find(request('expense_account'));
            if ($selectedAccount) {
                $fromDate = request('from_date');
                $toDate = request('to_date');

                $expenseTransactions = JournalDetail::where('account_id', $selectedAccount->id)
                    ->with(['head', 'costCenter'])
                    ->when($fromDate, function ($q) use ($fromDate) {
                        $q->whereDate('crtime', '>=', $fromDate);
                    })
                    ->when($toDate, function ($q) use ($toDate) {
                        $q->whereDate('crtime', '<=', $toDate);
                    })
                    ->orderBy('crtime', 'asc')
                    ->paginate(50);

                $openingBalance = $this->calculateAccountBalance($selectedAccount->id, $fromDate);
                $closingBalance = $this->calculateAccountBalance($selectedAccount->id, $toDate);
            }
        }

        return view('reports::expenses.general-expenses-daily-report', compact(
            'expenseAccounts',
            'selectedAccount',
            'expenseTransactions',
            'openingBalance',
            'closingBalance'
        ));
    }

    public function expensesBalanceReport()
    {
        $asOfDate = request('as_of_date', now()->format('Y-m-d'));
        $expenseCategory = request('expense_category');
        $costCenter = request('cost_center');

        $expenseCategories = collect();
        $costCenters = CostCenter::all();

        $expenseBalances = AccHead::where('code', 'like', '57%')
            ->where('isdeleted', 0)
            ->paginate(50)
            ->through(function ($account) use ($asOfDate) {
                $balance = $this->calculateAccountBalance($account->id, $asOfDate);
                $account->total_expenses = $balance > 0 ? $balance : 0;
                $account->total_payments = $balance < 0 ? abs($balance) : 0;
                $account->balance = $balance;
                return $account;
            });

        $totalExpenses = $expenseBalances->sum('total_expenses');
        $totalPayments = $expenseBalances->sum('total_payments');
        $totalBalance = $expenseBalances->sum('balance');
        $totalAccounts = $expenseBalances->count();
        $highestExpense = $expenseBalances->sortByDesc('total_expenses')->first()->aname ?? '---';
        $averageExpensePerAccount = $totalAccounts > 0 ? $totalExpenses / $totalAccounts : 0;
        $netExpenses = $totalExpenses - $totalPayments;

        return view('reports::expenses.expenses-balance-report', compact(
            'expenseCategories',
            'costCenters',
            'expenseBalances',
            'totalExpenses',
            'totalPayments',
            'totalBalance',
            'totalAccounts',
            'highestExpense',
            'averageExpensePerAccount',
            'netExpenses',
            'asOfDate'
        ));
    }
}



