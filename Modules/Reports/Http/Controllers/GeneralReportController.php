<?php

declare(strict_types=1);

namespace Modules\Reports\Http\Controllers;

use App\Models\User;
use App\Models\OperHead;
use App\Http\Controllers\Controller;
use App\Models\JournalHead;
use Modules\Accounts\Models\AccHead;
use App\Models\JournalDetail;
use App\Models\CostCenter;
use Modules\Reports\Services\ReportCalculationTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class GeneralReportController extends Controller
{
    use ReportCalculationTrait;
    // محلل العمل اليومي
    public function overall()
    {
        // Get filters from request
        $userId = request('user_id');
        $typeId = request('type_id');
        $dateFrom = request('date_from');
        $dateTo = request('date_to');

        // Build query with filters
        $query = OperHead::with(['user', 'type']);

        if ($userId) {
            $query->where('user', $userId);
        }

        if ($typeId) {
            $query->where('pro_type', $typeId);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $opers = $query->orderBy('created_at', 'desc')
            ->paginate(100);

        // Get users for the filter dropdown
        $users = User::all();

        // Get operation types for the filter dropdown
        $types = \App\Models\ProType::all();

        return view('reports::general-reports.overall', compact('opers', 'users', 'types'));
    }

     // اليومية العامة
     public function journalSummery()
     {
        $journalHeads = JournalHead::with(['dets' => function ($query) {
            $query->orderBy('debit', 'desc');
        }])->get();

        return view('reports::general-reports.journal-summery', compact('journalHeads'));
    }

    // كشف حساب عام - تفاصيل اليومية
    public function generalJournalDetails()
    {
        return view('reports::general-reports.general-journal-details');
    }

    public function dailyActivityAnalyzer()
    {
        $users = User::all();
        $operations = OperHead::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('reports::general-reports.daily-activity-analyzer', compact('users', 'operations'));
    }

    public function generalAccountStatement()
    {
        $accounts = AccHead::where('is_deleted', 0)->get();
        $selectedAccount = null;
        $movements = new LengthAwarePaginator([], 0, 50);
        $openingBalance = 0;
        $closingBalance = 0;

        if (request('account_id')) {
            $selectedAccount = AccHead::find(request('account_id'));
            if ($selectedAccount) {
                $fromDate = request('from_date');
                $toDate = request('to_date');

                $movements = JournalDetail::where('account_id', $selectedAccount->id)
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

        return view('reports::general-reports.general-account-statement', compact(
            'accounts',
            'selectedAccount',
            'movements',
            'openingBalance',
            'closingBalance'
        ));
    }

    public function generalAccountBalancesByStore()
    {
        $warehouses = AccHead::where('code', 'like', '1104%')->where('isdeleted', 0)->get();
        $asOfDate = request('as_of_date', now()->format('Y-m-d'));
        $selectedWarehouse = null;
        $accountBalances = collect();

        if (request('warehouse_id')) {
            $selectedWarehouse = AccHead::find(request('warehouse_id'));
            if ($selectedWarehouse) {
                $accountBalances = AccHead::where('isdeleted', 0)
                    ->paginate(50)
                    ->through(function ($account) use ($asOfDate, $selectedWarehouse) {
                        $balance = $this->calculateAccountBalance($account->id, $asOfDate);
                        $account->debit = $balance > 0 ? $balance : 0;
                        $account->credit = $balance < 0 ? abs($balance) : 0;
                        $account->balance = $balance;
                        return $account;
                    });
            }
        }

        $totalDebit = $accountBalances->sum('debit');
        $totalCredit = $accountBalances->sum('credit');
        $totalBalance = $accountBalances->sum('balance');

        return view('reports::accounts-reports.general-account-balances-by-store', compact(
            'warehouses',
            'selectedWarehouse',
            'accountBalances',
            'totalDebit',
            'totalCredit',
            'totalBalance',
            'asOfDate'
        ));
    }

    public function generalAccountsReport()
    {
        return view('reports::general-reports.general-accounts-report');
    }

    public function generalAccountStatementReport()
    {
        return view('reports::general-reports.general-account-statement-report');
    }

    public function generalCashboxMovementReport()
    {
        return view('reports::general-reports.general-cashbox-movement-report');
    }

    public function agingReport()
    {
        $today = now();

        $data = DB::table('operhead as o')
            ->leftJoin('journal_details as jd', 'jd.oper_id', '=', 'o.id')
            ->select(
                'o.id',
                'o.pro_num',
                'o.pro_date',
                'o.end_date as due_date',
                'o.fat_net as invoice_value',
                DB::raw('(o.fat_net - IFNULL(SUM(jd.amount),0)) as balance'),
                DB::raw("
                CASE
                    WHEN DATEDIFF(CURDATE(), o.end_date) <= 30 THEN '0-30 يوم'
                    WHEN DATEDIFF(CURDATE(), o.end_date) BETWEEN 31 AND 60 THEN '31-60 يوم'
                    WHEN DATEDIFF(CURDATE(), o.end_date) BETWEEN 61 AND 90 THEN '61-90 يوم'
                    ELSE '+90 يوم'
                END as aging_bucket
            ")
            )
            ->where('o.isdeleted', 0)
            ->where('o.pro_type', 1)
            ->groupBy('o.id', 'o.pro_num', 'o.pro_date', 'o.end_date', 'o.fat_net')
            ->get();

        return view('reports::general-reports.oper_aging', compact('data', 'today'));
    }
}