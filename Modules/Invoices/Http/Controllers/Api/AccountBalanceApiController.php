<?php

declare(strict_types=1);

namespace Modules\Invoices\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

/**
 * API Controller for account balance operations in invoices
 */
class AccountBalanceApiController extends Controller
{
    /**
     * Get account balance
     *
     * @param int $accountId
     * @return JsonResponse
     */
    public function getBalance(int $accountId): JsonResponse
    {
        try {
            // Get account info
            $account = DB::table('acc_head')
                ->where('id', $accountId)
                ->where('isdeleted', 0)
                ->first(['id', 'aname', 'start_balance', 'balance']);

            if (!$account) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account not found',
                ], 404);
            }

            // Use the balance from acc_head table (already calculated)
            $balance = (float) ($account->balance ?? 0);

            return response()->json([
                'success' => true,
                'balance' => $balance,
                'account_name' => $account->aname,
                'opening_balance' => (float) ($account->start_balance ?? 0),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching account balance', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching account balance',
                'balance' => 0,
            ], 500);
        }
    }

    /**
     * Calculate account balance from operations
     *
     * @param int $accountId
     * @return float
     */
    private function calculateAccountBalance(int $accountId): float
    {
        // Get opening balance
        $openingBalance = DB::table('acc_head')
            ->where('id', $accountId)
            ->value('start_balance') ?? 0;

        // Calculate from operations (operhead table)
        // acc1 = debit (مدين), acc2 = credit (دائن)
        $debitSum = DB::table('operhead')
            ->where('acc1', $accountId)
            ->where('isdeleted', 0)
            ->sum('pro_value') ?? 0;

        $creditSum = DB::table('operhead')
            ->where('acc2', $accountId)
            ->where('isdeleted', 0)
            ->sum('pro_value') ?? 0;

        // Balance = Opening + Debit - Credit
        $balance = (float) $openingBalance + (float) $debitSum - (float) $creditSum;

        return $balance;
    }
}
