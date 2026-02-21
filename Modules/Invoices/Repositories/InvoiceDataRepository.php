<?php

declare(strict_types=1);

namespace Modules\Invoices\Repositories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Models\AccHead;

/**
 * Repository for fetching initial invoice data
 * Optimized for single API call to load all necessary data
 */
class InvoiceDataRepository
{
    /**
     * Get all initial data needed for invoice form
     *
     * @param  int  $type  Invoice type
     * @param  int|null  $branchId  Branch ID
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
     */
    private function getTemplates(int $type): array
    {
        // Skip templates for now - table structure might be different
        return [];
    }

    /**
     * Get accounts based on invoice type with balance and credit limit
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
     */
    private function getAccountsByCode(string $code, ?int $branchId = null): array
    {
        $query = AccHead::where('code', 'like', $code.'%')
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
     */
    private function getBranches(): array
    {
        return Cache::remember('branches', 3600, function () {
            // Check if branches table exists
            $branches = DB::table('branches')
                ->select('id', 'name')
                ->get();

            return $branches->map(fn ($b) => (array) $b)->toArray();
        });
    }

    /**
     * Get price types
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
     */
    private function getUnits(): array
    {
        // Skip units for now - might not be needed
        return [];
    }

    /**
     * Get currencies
     */
    private function getCurrencies(): array
    {
        // Skip currencies for now - might not be needed
        return [];
    }

    /**
     * Get invoice data for editing
     */
    public function getInvoiceForEdit(int $invoiceId): array
    {
        $invoice = DB::table('operhead')
            ->where('id', $invoiceId)
            ->first();

        if (! $invoice) {
            return [];
        }

        // Get invoice items with all details
        $items = DB::table('operation_items as oi')
            ->join('items as i', 'oi.item_id', '=', 'i.id')
            ->leftJoin('units as u', 'oi.unit_id', '=', 'u.id')
            ->where('oi.pro_id', $invoiceId)
            ->select(
                'oi.id as operation_item_id',
                'oi.item_id',
                'i.name as item_name',
                'i.code as item_code',
                'oi.unit_id',
                'u.name as unit_name',
                'oi.fat_quantity as quantity',
                'oi.fat_price as price',
                'oi.item_discount as discount',
                'oi.item_discount_pre as discount_percentage',
                'oi.detail_value as sub_value',
                'oi.batch_number',
                'oi.expiry_date',
                'oi.notes',
                'oi.cost_price',
                'oi.profit'
            )
            ->get();

        // Get available units for each item
        $itemsWithUnits = $items->map(function ($item) {
            $availableUnits = DB::table('item_units as iu')
                ->join('units as u', 'iu.unit_id', '=', 'u.id')
                ->where('iu.item_id', $item->item_id)
                ->select(
                    'u.id',
                    'u.name',
                    'iu.u_val',
                    'iu.price1',
                    'iu.price2',
                    'iu.price3',
                    'iu.price4',
                    'iu.price5'
                )
                ->get()
                ->toArray();

            return [
                'operation_item_id' => $item->operation_item_id,
                'item_id' => $item->item_id,
                'item_name' => $item->item_name,
                'item_code' => $item->item_code,
                'unit_id' => $item->unit_id,
                'unit_name' => $item->unit_name,
                'quantity' => (float) $item->quantity,
                'price' => (float) $item->price,
                'discount' => (float) ($item->discount ?? 0),
                'discount_percentage' => (float) ($item->discount_percentage ?? 0),
                'sub_value' => (float) $item->sub_value,
                'batch_number' => $item->batch_number,
                'expiry_date' => $item->expiry_date,
                'notes' => $item->notes,
                'cost_price' => (float) $item->cost_price,
                'profit' => (float) $item->profit,
                'available_units' => $availableUnits,
            ];
        })->toArray();

        return [
            'invoice' => [
                'id' => $invoice->id,
                'type' => $invoice->pro_type,
                'pro_id' => $invoice->pro_id,
                'branch_id' => $invoice->branch_id,
                'acc1_id' => $invoice->acc1,
                'acc2_id' => $invoice->acc2,
                'emp_id' => $invoice->emp_id,
                'delivery_id' => $invoice->emp2_id,
                'pro_date' => $invoice->pro_date,
                'accural_date' => $invoice->accural_date,
                'serial_number' => $invoice->pro_serial,
                'cash_box_id' => $invoice->acc_fund,
                'notes' => $invoice->info,
                'currency_id' => $invoice->currency_id ?? 1,
                'currency_rate' => (float) ($invoice->currency_rate ?? 1),
                'template_id' => $invoice->template_id,
                'discount_percentage' => (float) ($invoice->fat_disc_per ?? 0),
                'discount_value' => (float) ($invoice->fat_disc ?? 0),
                'additional_percentage' => (float) ($invoice->fat_plus_per ?? 0),
                'additional_value' => (float) ($invoice->fat_plus ?? 0),
                'vat_percentage' => (float) ($invoice->vat_percentage ?? 0),
                'vat_value' => (float) ($invoice->vat_value ?? 0),
                'withholding_tax_percentage' => (float) ($invoice->withholding_tax_percentage ?? 0),
                'withholding_tax_value' => (float) ($invoice->withholding_tax_value ?? 0),
                'subtotal' => (float) ($invoice->fat_total ?? 0),
                'total_after_additional' => (float) ($invoice->pro_value ?? 0),
                'received_from_client' => (float) ($invoice->paid_from_client ?? 0),
                'is_posted' => (bool) ($invoice->is_posted ?? false),
            ],
            'items' => $itemsWithUnits,
        ];
    }
}
