<?php

declare(strict_types=1);

namespace Modules\Invoices\Repositories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Models\AccHead;
use Modules\Invoices\Models\InvoiceTemplate;

/**
 * Repository for fetching initial invoice data
 * Optimized for single API call to load all necessary data
 */
class InvoiceDataRepository
{
    /**
     * Get all initial data needed for invoice form
     *
     * @param int $type Invoice type
     * @param int|null $branchId Branch ID
     * @return array
     */
    public function getInitialData(int $type, ?int $branchId = null): array
    {
        return [
            'accounts' => $this->getAccounts($type, $branchId),
            'settings' => $this->getSettings(),
            'branches' => $this->getBranches(),
            'price_types' => $this->getPriceTypes(),
            'units' => $this->getUnits(),
            'currencies' => $this->getCurrencies(),
        ];
    }

    /**
     * Get invoice templates for specific type
     *
     * @param int $type
     * @return array
     */
    private function getTemplates(int $type): array
    {
        // Skip templates for now - table structure might be different
        return [];
    }

    /**
     * Get accounts based on invoice type with balance and credit limit
     *
     * @param int $type
     * @param int|null $branchId
     * @return array
     */
    private function getAccounts(int $type, ?int $branchId = null): array
    {
        $accounts = [
            'customers' => [],
            'suppliers' => [],
            'cash_accounts' => [],
            'cost_centers' => [],
        ];

        // Get customer accounts (for sales invoices)
        if (in_array($type, [10, 12, 14, 16, 19, 22])) {
            $accounts['customers'] = $this->getAccountsByCode('1-1-1', $branchId);
        }

        // Get supplier accounts (for purchase invoices)
        if (in_array($type, [11, 13, 15, 17, 20, 23])) {
            $accounts['suppliers'] = $this->getAccountsByCode('2-1-1', $branchId);
        }

        // Get cash accounts
        $accounts['cash_accounts'] = $this->getAccountsByCode('1-1-2', $branchId);

        // Get cost centers
        $accounts['cost_centers'] = $this->getAccountsByCode('4-1', $branchId);

        return $accounts;
    }

    /**
     * Get accounts by code with balance calculation
     *
     * @param string $code
     * @param int|null $branchId
     * @return array
     */
    private function getAccountsByCode(string $code, ?int $branchId = null): array
    {
        $query = AccHead::where('code', 'like', $code . '%')
            ->select('id', 'aname', 'code', 'currency_id', 'debit_limit');

        if ($branchId) {
            $query->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->orWhereNull('branch_id');
            });
        }

        $accounts = $query->get();

        // Calculate balance for each account
        return $accounts->map(function ($account) {
            $balance = $this->calculateAccountBalance($account->id);
            
            return [
                'id' => $account->id,
                'name' => $account->name,
                'code' => $account->code,
                'currency_id' => $account->currency_id,
                'credit_limit' => $account->debit_limit,
                'balance' => $balance,
            ];
        })->toArray();
    }

    /**
     * Calculate account balance
     *
     * @param int $accountId
     * @return float
     */
    private function calculateAccountBalance(int $accountId): float
    {
        $result = DB::table('journal_details')
            ->where('acc_id', $accountId)
            ->selectRaw('SUM(debit) - SUM(credit) as balance')
            ->first();

        return (float) ($result->balance ?? 0);
    }

    /**
     * Get system settings
     *
     * @return array
     */
    private function getSettings(): array
    {
        return Cache::rememberForever('invoice_settings', function () {
            return [
                'vat_percentage' => (float) setting('vat_percentage', 15),
                'show_balance' => setting('show_balance', '1') === '1',
                'prevent_expired_items' => setting('prevent_selling_expired_items', '1') === '1',
                'allow_negative_stock' => setting('allow_negative_stock', '0') === '1',
                'auto_calculate_cost' => setting('auto_calculate_cost', '1') === '1',
                'default_currency_id' => (int) setting('default_currency_id', 1),
                'decimal_places' => (int) setting('decimal_places', 2),
            ];
        });
    }

    /**
     * Get branches
     *
     * @return array
     */
    private function getBranches(): array
    {
        return Cache::remember('branches', 3600, function () {
            // Check if branches table exists
            $branches = DB::table('branches')
                ->select('id', 'name')
                ->get();
            
            return $branches->map(fn($b) => (array) $b)->toArray();
        });
    }

    /**
     * Get price types
     *
     * @return array
     */
    private function getPriceTypes(): array
    {
        return Cache::remember('price_types', 3600, function () {
            return [
                ['id' => 'price1', 'name' => __('invoices.price1')],
                ['id' => 'price2', 'name' => __('invoices.price2')],
                ['id' => 'price3', 'name' => __('invoices.price3')],
                ['id' => 'price4', 'name' => __('invoices.price4')],
                ['id' => 'price5', 'name' => __('invoices.price5')],
            ];
        });
    }

    /**
     * Get units
     *
     * @return array
     */
    private function getUnits(): array
    {
        // Skip units for now - might not be needed
        return [];
    }

    /**
     * Get currencies
     *
     * @return array
     */
    private function getCurrencies(): array
    {
        // Skip currencies for now - might not be needed
        return [];
    }

    /**
     * Get invoice data for editing
     *
     * @param int $invoiceId
     * @return array
     */
    public function getInvoiceForEdit(int $invoiceId): array
    {
        $invoice = DB::table('oper_head')
            ->where('id', $invoiceId)
            ->first();

        if (!$invoice) {
            return [];
        }

        $items = DB::table('operation_items as oi')
            ->join('items as i', 'oi.item_id', '=', 'i.id')
            ->join('units as u', 'oi.unit_id', '=', 'u.id')
            ->where('oi.oper_id', $invoiceId)
            ->select(
                'oi.*',
                'i.name as item_name',
                'i.code as item_code',
                'u.name as unit_name'
            )
            ->get()
            ->toArray();

        return [
            'invoice' => (array) $invoice,
            'items' => $items,
        ];
    }
}
