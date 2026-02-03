<?php

declare(strict_types=1);

namespace Modules\Reports\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Reports\Services\ReportCalculationTrait;

class CashBoxBankReportController extends Controller
{
    use ReportCalculationTrait;

    public function __construct()
    {
        $this->middleware('can:view Funds');
    }

    // public function generalCashboxMovementReport()
    // {
    //     $cashboxBank = AccHead::where('code', 'like', '101%')->where('isdeleted', 0)->get();

    //     // Get cash box account IDs
    //     $cashboxAccountIds = $cashboxBank->pluck('id');

    //     // If specific cashbox_bank is selected, filter by it
    //     if (request('cashbox_bank')) {
    //         $cashboxAccountIds = collect([request('cashbox_bank')]);
    //     }

    //     // Get journal details that belong to OperHead (through JournalHead) and are related to cash box accounts
    //     $cashboxBankTransactions = JournalDetail::whereHas('head.oper')
    //         ->whereHas('accHead', function ($q) use ($cashboxAccountIds) {
    //             $q->whereIn('id', $cashboxAccountIds);
    //         })
    //         ->with(['accHead', 'head.oper', 'operHead'])
    //         ->when(request('from_date'), function ($q) {
    //             $q->whereDate('crtime', '>=', request('from_date'));
    //         })
    //         ->when(request('to_date'), function ($q) {
    //             $q->whereDate('crtime', '<=', request('to_date'));
    //         })
    //         ->when(request('cashbox_bank'), function ($q) {
    //             $q->where('account_id', request('cashbox_bank'));
    //         })
    //         ->orderBy('crtime', 'asc')
    //         ->orderBy('id', 'asc')
    //         ->get();

    //     // Calculate running balance for each transaction (in chronological order)
    //     $runningBalance = 0;
    //     $cashboxBankTransactions = $cashboxBankTransactions->map(function ($transaction) use (&$runningBalance) {
    //         $runningBalance += ($transaction->debit - $transaction->credit);
    //         $transaction->balance = $runningBalance;
    //         return $transaction;
    //     });

    //     // Calculate totals from full collection before pagination
    //     $totalAmount = $cashboxBankTransactions->sum('debit') + $cashboxBankTransactions->sum('credit');
    //     $totalDebit = $cashboxBankTransactions->sum('debit');
    //     $totalCredit = $cashboxBankTransactions->sum('credit');
    //     $netBalance = $totalDebit - $totalCredit;
    //     $totalTransactions = $cashboxBankTransactions->count();

    //     // Reverse to show newest first for display
    //     $cashboxBankTransactions = $cashboxBankTransactions->reverse()->values();

    //     // Paginate manually
    //     $currentPage = request()->get('page', 1);
    //     $perPage = 50;
    //     $items = $cashboxBankTransactions->forPage($currentPage, $perPage);
    //     $total = $cashboxBankTransactions->count();

    //     $cashboxBankTransactions = new LengthAwarePaginator(
    //         $items,
    //         $total,
    //         $perPage,
    //         $currentPage,
    //         ['path' => request()->url(), 'query' => request()->query()]
    //     );

    //     return view('reports::cash-box-bank.general-cashbox-movement-report', compact('cashboxBank', 'cashboxBankTransactions', 'totalAmount', 'totalDebit', 'totalCredit', 'netBalance', 'totalTransactions'));
    // }

    public function generalCashboxMovementReport()
    {
        return view('reports::cash-box-bank.general-cashbox-movement-report');
    }

    public function generalCashBankReport()
    {
        return view('reports::cash-box-bank.general-cash-bank-report');
    }
}
